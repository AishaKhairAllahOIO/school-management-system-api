<?php

namespace App\Http\Controllers\Setting;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    use ApiResource;

    public function index()
    {
        $subjects = Subject::orderBy('subject_name', 'asc')->get();
        return $this->successResponse(
            $subjects,
            'تم جلب المواد',
            200
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject_name' => ['required', 'string', 'max:255', 'unique:subjects,subject_name']
        ]);

        $subject = Subject::create([
            'subject_name' => $request->subject_name
        ]);

        return $this->successResponse($subject,
         'تم إضافة المادة الجديدة بنجاح',
          201);
    }
}
