<?php

namespace App\Http\Controllers\Teacher;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StorePracticeQuizRequest;
use App\Services\Quiz\PracticeQuizService;
use Exception;
use Illuminate\Support\Facades\Log;

class PracticeQuizController extends Controller
{
    use ApiResource;

    protected PracticeQuizService $quizService;

    public function __construct(PracticeQuizService $quizService)
    {
        $this->quizService = $quizService;
    }

    public function store(StorePracticeQuizRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $teacherId = $request->user()->staff->id;

            $quiz = $this->quizService->createQuiz($validatedData, $teacherId);

            return $this->successResponse(
                ['quiz_id' => $quiz->id],
                'Practice quiz created successfully and students have been notified.',
                201
            );

        } catch (Exception $e) {
            Log::error('Teacher Quiz Creation Error: ' . $e->getMessage());

            return $this->errorResponse(
                'An error occurred while creating the practice quiz.',
                500
            );
        }
    }

    
}
