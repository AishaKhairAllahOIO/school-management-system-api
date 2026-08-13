<?php

namespace App\Providers;

use App\Models\StudentMark;
use App\Policies\MarkPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Domain\Scheduling\Algorithms\BacktrackingSolver;
use App\Domain\Scheduling\Algorithms\VariableSelector;
use App\Domain\Scheduling\Algorithms\CandidateGenerator;
use App\Domain\Scheduling\Algorithms\ForwardChecker;


use App\Domain\Scheduling\Builders\GenerationContextBuilder;
use App\Domain\Scheduling\Builders\LessonRequirementBuilder;


use App\Domain\Scheduling\Generators\TimeSlotGenerator;


use App\Domain\Scheduling\Services\LessonExpander;


use App\Domain\Scheduling\Persistence\SchedulePersistenceService;


use App\Domain\Scheduling\Scoring\ScoreCalculator;
use App\Domain\Scheduling\Scoring\PenaltyCalculator;


use App\Domain\Scheduling\Constraints\ConstraintFactory;
use App\Domain\Scheduling\Scoring\Penalties\AvoidFirstPeriodPenalty;
use App\Domain\Scheduling\Scoring\Penalties\AvoidLastPeriodPenalty;
use App\Domain\Scheduling\Scoring\Penalties\HeavySubjectPenalty;
use App\Domain\Scheduling\Scoring\Penalties\TeacherGapPenalty;
use App\Models\ClassRoom;
use App\Observers\ClassRoomObserver;

class AppServiceProvider extends ServiceProvider
{

    public function register(): void
    {



        $this->app->singleton(
            TimeSlotGenerator::class
        );


        $this->app->singleton(
            LessonRequirementBuilder::class
        );


        $this->app->singleton(
            LessonExpander::class
        );


        $this->app->singleton(
            GenerationContextBuilder::class
        );


        $this->app->singleton(
            VariableSelector::class
        );


        $this->app->singleton(
            CandidateGenerator::class
        );


        $this->app->singleton(
            ForwardChecker::class
        );



        $this->app->singleton(
            BacktrackingSolver::class
        );




        $this->app->singleton(
            ConstraintFactory::class
        );




        $this->app->singleton(
            SchedulePersistenceService::class
        );


        $this->app->bind(PenaltyCalculator::class, function ($app) {
            return
                    new PenaltyCalculator([
                    new AvoidFirstPeriodPenalty(),
                    new AvoidLastPeriodPenalty(),
                    new HeavySubjectPenalty(),
                    new TeacherGapPenalty(),
                ]);
        });


        $this->app->singleton(
            ScoreCalculator::class
        );
    }

    public function boot(): void
    {
        Gate::policy(StudentMark::class, MarkPolicy::class);
        ClassRoom::observe(ClassRoomObserver::class);
    }
}
