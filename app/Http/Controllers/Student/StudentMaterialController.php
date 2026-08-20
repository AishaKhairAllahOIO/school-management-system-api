<?php

namespace App\Http\Controllers\Student;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Models\StudyMaterial;
use App\Models\Enrollment;
use App\Services\Teacher\StudyMaterialService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Exception;

class StudentMaterialController extends Controller
{
    use ApiResource;

    public function __construct(protected StudyMaterialService $materialService)
    {
    }

    private function getCurrentEnrollment($user)
    {
        if (!$user->student)
            return null;

        return Enrollment::where('student_id', $user->student->id)
            ->whereHas('academicYear', fn($q) => $q->where('is_current', true))
            ->with('classRoom')
            ->latest()
            ->first();
    }


    public function getBySubject(Request $request)
    {
        try {
            $user = $request->user();
            $enrollment = $this->getCurrentEnrollment($user);
            $perPage = $request->input('per_page', 15);

            if (!$enrollment || !$enrollment->classRoom) {
                return $this->errorResponse('Active enrollment not found.', 404);
            }

            $materials = $this->materialService->getStudentMaterialsBySubject(
                $user,
                (int) $perPage
            );


            $responseData = [
                'items' => collect($materials->items())->map(function ($material) {
                    $publicDisk = config('filesystems.public_disk');
                    return [
                        'id' => $material->id,
                        'title' => $material->title,
                        'description' => $material->description,
                        'type' => $material->type,
                        ' link_url' => $material->type === 'link'
                            ? $material->link_url
                            : null,

                        'file_path' => $material->type === 'file'
                            ? (
                                $publicDisk === 's3'
                                ? Storage::disk($publicDisk)->temporaryUrl(
                                    $material->file_path,
                                    now()->addMinutes(30)
                                )
                                : Storage::disk($publicDisk)->url(
                                    $material->file_path
                                )
                            )
                            : null,
                        'file_extension' => $material->type === 'file' ? $material->file_extension : null,
                        'file_size_kb' => $material->type === 'file' ? round($material->file_size / 1024, 2) : null,
                        'is_read' => $material->readers->isNotEmpty(),
                        'created_at' => $material->created_at->format('Y-m-d H:i'),
                    ];
                }),
                'pagination' => [
                    'total' => $materials->total(),
                    'current_page' => $materials->currentPage(),
                    'last_page' => $materials->lastPage(),
                    'per_page' => $materials->perPage(),

                    'first_page_url' => $materials->url(1),
                    'last_page_url' => $materials->url($materials->lastPage()),
                    'next_page_url' => $materials->nextPageUrl(),
                    'prev_page_url' => $materials->previousPageUrl(),
                ]
            ];

            return $this->successResponse($responseData, 'Study materials retrieved successfully.', 200);

        } catch (Exception $e) {
            $code = $this->getExceptionCode($e);
            return $this->errorResponse($e->getMessage(), $code);
        }
    }


    public function download(Request $request, $id)
    {
        try {
            $enrollment = $this->getCurrentEnrollment($request->user());

            if (!$enrollment || !$enrollment->classRoom) {
                return $this->errorResponse('Active enrollment not found.', 404);
            }

            $material = StudyMaterial::findOrFail($id);

            if ($material->type !== 'file') {
                return $this->errorResponse('This material is a link, not a downloadable file.', 400);
            }

            $gradeLevelId = $enrollment->classRoom->grade_level_id;
            $materialGradeLevelId = $material->gradeSubject->grade_level_id;

            if ($gradeLevelId !== $materialGradeLevelId) {
                return $this->errorResponse('Access Denied. You are not enrolled in the grade this material belongs to.', 403);
            }

            $publicDisk = config('filesystems.public_disk');

            if (!Storage::disk($publicDisk)->exists($material->file_path)) {
                return $this->errorResponse('File not found on the server.', 404);
            }

            if ($publicDisk === 's3') {
                return redirect()->away(
                    Storage::disk($publicDisk)->temporaryUrl(
                        $material->file_path,
                        now()->addMinutes(30)
                    )
                );
            }

            $absolutePath = Storage::disk($publicDisk)->path($material->file_path);

            return response()->download(
                $absolutePath,
                $material->original_name
            );
        } catch (Exception $e) {
            $code = $this->getExceptionCode($e);
            return $this->errorResponse($e->getMessage(), $code);
        }
    }

    public function unreadCount(Request $request)
    {
        try {
            $count = $this->materialService->unreadCount($request->user());
            return $this->successResponse(['unread_count' => $count], 'Unread materials count retrieved.', 200);
        } catch (Exception $e) {
            $code = $this->getExceptionCode($e);
            return $this->errorResponse($e->getMessage(), $code);
        }
    }

    public function markAllRead(Request $request)
    {
        try {
            $count = $this->materialService->markAllRead($request->user());
            return $this->successResponse(['unread_count' => $count], 'All materials marked as read.', 200);
        } catch (Exception $e) {
            $code = $this->getExceptionCode($e);
            return $this->errorResponse($e->getMessage(), $code);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $material = $this->materialService->showOneMaterial((int) $id, $request->user());

            $responseData = [
                'id' => $material->id,
                'title' => $material->title,
                'description' => $material->description,
                'type' => $material->type,
                'link_url' => $material->type === 'link' ? $material->link_url : null,
                'file_extension' => $material->type === 'file' ? $material->file_extension : null,
                'file_size_kb' => $material->type === 'file' ? round($material->file_size / 1024, 2) : null,
                'created_at' => $material->created_at->format('Y-m-d H:i'),
            ];

            return $this->successResponse($responseData, 'Study material details retrieved successfully.', 200);
        } catch (Exception $e) {
            $code = $this->getExceptionCode($e);
            return $this->errorResponse($e->getMessage(), $code);
        }
    }
}
