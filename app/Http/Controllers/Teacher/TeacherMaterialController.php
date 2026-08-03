<?php

namespace App\Http\Controllers\Teacher;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreStudyMaterialRequest;
use App\Services\Teacher\StudyMaterialService;
use App\Models\GradeSubject;
use App\Models\StudyMaterial;
use Illuminate\Http\Request;
use Exception;

class TeacherMaterialController extends Controller
{
    use ApiResource;

    public function __construct(protected StudyMaterialService $materialService)
    {
    }


    public function getTeacherSubjects(Request $request)
    {
        try {
            $teacherId = $request->user()->staff->id ?? $request->user()->id;

            $subjects = GradeSubject::with(['subject:id,subject_name', 'gradeLevel:id,name'])
                ->whereHas('teacherAssignments', function ($q) use ($teacherId) {
                    $q->where('teacher_id', $teacherId)
                      ->whereHas('academicYear', fn($ay) => $ay->where('is_current', true));
                })
                ->get()
                ->map(function ($gs) {
                    return [
                        'grade_subject_id' => $gs->id,
                        'subject_name'     => $gs->subject->subject_name ?? 'Unknown',
                        'grade_name'       => $gs->gradeLevel->name ?? 'Unknown',
                    ];
                });

            return $this->successResponse($subjects, 'Teacher subjects retrieved successfully.', 200);
        } catch (Exception $e) {
            $code = $this->getExceptionCode($e);
            return $this->errorResponse($e->getMessage(), $code);
        }
    }

    public function index(Request $request, $gradeSubjectId)
    {
        try {
            $teacherId = $request->user()->staff->id ?? $request->user()->id;
            $perPage = $request->input('per_page', 15);

            $materials = $this->materialService->getTeacherMaterialsBySubject((int) $gradeSubjectId, $teacherId, (int) $perPage);

            $responseData = [
                'items' => collect($materials->items())->map(function ($material) {
                    return [
                        'id'             => $material->id,
                        'title'          => $material->title,
                        'description'    => $material->description,
                        'type'           => $material->type,
                        'link_url'       => $material->type === 'link' ? $material->link_url : null,
                        'file_extension' => $material->type === 'file' ? $material->file_extension : null,
                        'file_size_kb'   => $material->type === 'file' ? round($material->file_size / 1024, 2) : null,
                        'created_at'     => $material->created_at->format('Y-m-d H:i'),
                    ];
                }),
                'pagination' => [
                    'total'        => $materials->total(),
                    'current_page' => $materials->currentPage(),
                    'last_page'    => $materials->lastPage(),
                    'per_page'     => $materials->perPage(),
                    'first_page_url' => $materials->url(1),
                    'last_page_url'  => $materials->url($materials->lastPage()),
                    'next_page_url'  => $materials->nextPageUrl(),
                    'prev_page_url'  => $materials->previousPageUrl(),
                ]
            ];

            return $this->successResponse($responseData, 'Teacher materials retrieved successfully.', 200);

        } catch (Exception $e) {
            $code = $this->getExceptionCode($e);
            return $this->errorResponse($e->getMessage(), $code);
        }
    }

    public function store(StoreStudyMaterialRequest $request)
    {
        try {
            $teacherId = $request->user()->staff->id ?? $request->user()->id;

            $isAuthorized = GradeSubject::where('id', $request->grade_subject_id)
                ->whereHas('teacherAssignments', function ($q) use ($teacherId) {
                    $q->where('teacher_id', $teacherId);
                })->exists();

            if (!$isAuthorized) {
                return $this->errorResponse('You are not authorized to upload materials for this subject.', 403);
            }

            $material = $this->materialService->storeMaterial(
                $request->validated(),
                $request->file('file'),
                $teacherId
            );

            $responseData = [
                'id'         => $material->id,
                'title'      => $material->title,
                'type'       => $material->type,
                'created_at' => $material->created_at->format('Y-m-d H:i'),
            ];

            return $this->successResponse($responseData, 'Study material shared successfully with all class rooms.', 201);
        } catch (Exception $e) {
            $code = $this->getExceptionCode($e);
            return $this->errorResponse($e->getMessage(), $code);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $material = StudyMaterial::findOrFail($id);

            $this->materialService->deleteMaterial($material, $request->user());

            return $this->successResponse(null, 'Study material deleted successfully.', 200);
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
                'id'             => $material->id,
                'title'          => $material->title,
                'description'    => $material->description,
                'type'           => $material->type,
                'link_url'       => $material->type === 'link' ? $material->link_url : null,
                'file_extension' => $material->type === 'file' ? $material->file_extension : null,
                'file_size_kb'   => $material->type === 'file' ? round($material->file_size / 1024, 2) : null,
                'created_at'     => $material->created_at->format('Y-m-d H:i'),
            ];

            return $this->successResponse($responseData, 'Study material details retrieved successfully.', 200);
        } catch (Exception $e) {
            $code = $this->getExceptionCode($e);
            return $this->errorResponse($e->getMessage(), $code);
        }
    }


}
