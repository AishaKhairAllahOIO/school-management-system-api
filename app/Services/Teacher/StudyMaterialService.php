<?php

namespace App\Services\Teacher;

use App\Models\StudyMaterial;
use App\Models\GradeSubject;
use App\Models\Enrollment;
use App\Models\Alert;
use App\Jobs\SendPushNotification;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Storage;

class StudyMaterialService
{
    public function storeMaterial(array $data, ?UploadedFile $file, int $teacherId)
    {
        return DB::transaction(function () use ($data, $file, $teacherId) {

            $materialData = [
                'grade_subject_id' => $data['grade_subject_id'],
                'teacher_id'       => $teacherId,
                'title'            => $data['title'],
                'description'      => $data['description'] ?? null,
                'type'             => $data['type'],
            ];

            if ($data['type'] === 'file' && $file) {
                $extension = $file->getClientOriginalExtension();
                $fileName = Str::uuid() . '.' . $extension;
                $path = $file->storeAs("materials/" . date('Y'), $fileName, 'local');

                $materialData['file_path'] = $path;
                $materialData['original_name'] = $file->getClientOriginalName();
                $materialData['file_extension'] = $extension;
                $materialData['file_size'] = $file->getSize();
            }
            elseif ($data['type'] === 'link') {
                $materialData['link_url'] = $data['link_url'];
            }

            $material = StudyMaterial::create($materialData);

            $this->notifyStudentsAboutMaterial($material);

            return $material;
        });
    }

    private function notifyStudentsAboutMaterial(StudyMaterial $material): void
    {
        try {
            $gradeSubject = GradeSubject::with('subject', 'gradeLevel')->find($material->grade_subject_id);
            if (!$gradeSubject) return;

            $subjectName = $gradeSubject->subject->subject_name ?? 'Subject';
            $gradeName = $gradeSubject->gradeLevel->name ?? 'Class';

            $title = "New Study Material!";
            $body = "A new " . ($material->type === 'file' ? 'file' : 'link') . " has been added for {$subjectName}.";

            $enrollments = Enrollment::whereHas('classRoom', function ($q) use ($gradeSubject) {
                    $q->where('grade_level_id', $gradeSubject->grade_level_id);
                })
                ->whereHas('academicYear', fn($q) => $q->where('is_current', true))
                ->with('student.user')
                ->get();

            $userIdsToPush = [];
            $alertsToInsert = [];
            $now = now();

            foreach ($enrollments as $enrollment) {
                $alertsToInsert[] = [
                    'title'           => $title,
                    'body'            => $body,
                    'type'            => 'study_material',
                    'notifiable_type' => Enrollment::class,
                    'notifiable_id'   => $enrollment->id,
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ];

                if ($enrollment->student && $enrollment->student->user) {
                    $userIdsToPush[] = $enrollment->student->user->id;
                }
            }

            if (!empty($alertsToInsert)) {
                Alert::insert($alertsToInsert);
            }

            if (!empty($userIdsToPush)) {
                SendPushNotification::dispatch(
                    array_unique($userIdsToPush),
                    $title,
                    $body,
                    [
                        'type'             => 'new_study_material',
                        'material_id'      => (string) $material->id,
                        'grade_subject_id' => (string) $gradeSubject->id,
                    ]
                );
            }
        } catch (Exception $e) {
            Log::error('Study Material Notification Error', ['error' => $e->getMessage()]);
        }
    }


    private function getBaseQueryForUser(User $user)
    {
        if ($user->hasRole('student') && $user->student) {
            $enrollment = Enrollment::where('student_id', $user->student->id)
                ->whereHas('academicYear', fn($q) => $q->where('is_current', true))
                ->with('classRoom')
                ->latest()
                ->first();

            if (!$enrollment || !$enrollment->classRoom) {
                return StudyMaterial::query()->where('id', '<', 0);
            }

            $gradeLevelId = $enrollment->classRoom->grade_level_id;

            return StudyMaterial::whereHas('gradeSubject', function ($q) use ($gradeLevelId) {
                $q->where('grade_level_id', $gradeLevelId);
            });
        }

        return StudyMaterial::query()->where('id', '<', 0);
    }


    public function unreadCount(User $user): int
    {
        return $this->getBaseQueryForUser($user)
            ->whereDoesntHave('readers', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->count();
    }


    public function markAllRead(User $user): int
    {
        $unreadMaterialIds = $this->getBaseQueryForUser($user)
            ->whereDoesntHave('readers', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->pluck('id');

        if ($unreadMaterialIds->isNotEmpty()) {
            $syncData = [];
            $now = now();

            foreach ($unreadMaterialIds as $id) {
                $syncData[$id] = ['read_at' => $now];
            }

            $user->readStudyMaterials()->syncWithoutDetaching($syncData);
        }

        return $this->unreadCount($user);
    }

    public function getTeacherMaterialsBySubject(int $gradeSubjectId, int $teacherId, int $perPage = 15)
    {
        $isAuthorized = GradeSubject::where('id', $gradeSubjectId)
            ->whereHas('teacherAssignments', function ($q) use ($teacherId) {
                $q->where('teacher_id', $teacherId)
                  ->whereHas('academicYear', fn($ay) => $ay->where('is_current', true));
            })->exists();

        if (!$isAuthorized) {
            throw new Exception('You are not authorized to view materials for this subject.', 403);
        }

        return StudyMaterial::where('grade_subject_id', $gradeSubjectId)
            ->where('teacher_id', $teacherId)
            ->latest()
            ->paginate($perPage);
    }

    public function getStudentMaterialsBySubject(int $gradeSubjectId, int $gradeLevelId, User $user, int $perPage = 15)
    {
        $isValidSubject = GradeSubject::where('id', $gradeSubjectId)
            ->where('grade_level_id', $gradeLevelId)
            ->exists();

        if (!$isValidSubject) {
            throw new Exception('Access Denied. This subject does not belong to your current grade.', 403);
        }

        return StudyMaterial::where('grade_subject_id', $gradeSubjectId)
            ->latest()
            ->paginate($perPage);
    }

    public function showOneMaterial(int $materialId, User $user): StudyMaterial
    {
        $material = StudyMaterial::find($materialId);
        if (!$material) {
            throw new Exception('Study material not found.', 404);
        }
        if ($user->hasRole('student') && $user->student) {
            $enrollment = Enrollment::where('student_id', $user->student->id)
                ->whereHas('academicYear', fn($q) => $q->where('is_current', true))
                ->with('classRoom')
                ->latest()
                ->first();

            if (!$enrollment || !$enrollment->classRoom || $enrollment->classRoom->grade_level_id !== $material->gradeSubject->grade_level_id) {
                throw new Exception('Access Denied. This material does not belong to your current grade.', 403);
            }
        }

        return $material;
    }

    public function deleteMaterial(StudyMaterial $material, User $user): void
    {
        if ($user->hasRole('teacher') && $user->staff) {
            if ($material->teacher_id !== $user->staff->id) {
                throw new Exception('You are not authorized to delete this material.', 403);
            }
        } else {
            throw new Exception('You are not authorized to delete this material.', 403);
        }

        if ($material->type === 'file' && $material->file_path) {
            try {
                Storage::disk('local')->delete($material->file_path);
            } catch (Exception $e) {
                Log::error("Failed to delete file for material ID {$material->id}: " . $e->getMessage());
            }
        }

        $material->delete();
    }
}
