<?php

namespace App\Http\Controllers\Teacher;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Services\Teacher\TeacherDropdownService;
use Illuminate\Http\Request;

class TeacherDropdownController extends Controller
{
    use ApiResource;

    public function __construct(private readonly TeacherDropdownService $dropdownService)
    {
    }

public function subjectsTree(Request $request)
{
    $subjectId = $request->query('subject_id') ? (int) $request->query('subject_id') : null;

    $tree = $this->dropdownService->getSubjectsTree($request->user(), $subjectId);

    return $this->successResponse(
        $tree,
        'شجرة التعيينات التدريسية.',
        200
    );
}
}
