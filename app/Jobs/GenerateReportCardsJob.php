<?php

namespace App\Jobs;

use App\Models\Enrollment;
use App\Models\Semester;
use App\Services\Report\ReportCardGenerationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateReportCardsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $semesterId;
public $maxAllowedNonFailingFailures;

public function __construct($semesterId, $maxAllowedNonFailingFailures = 2)
{
    $this->semesterId = $semesterId;
    $this->maxAllowedNonFailingFailures = $maxAllowedNonFailingFailures ?? 2;
}

public function handle(ReportCardGenerationService $reportService)
{
    $semester = Semester::findOrFail($this->semesterId);

    Enrollment::where('academic_year_id', $semester->academic_year_id)
        ->whereIn('enrollment_status', ['enrolled', 'completed'])
        ->chunkById(100, function ($enrollments) use ($reportService) {
            foreach ($enrollments as $enrollment) {
                $reportService->generateForEnrollment(
                    $enrollment, 
                    $this->semesterId, 
                    $this->maxAllowedNonFailingFailures
                );
            }
        });
}}