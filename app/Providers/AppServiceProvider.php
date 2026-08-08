<?php

namespace App\Providers;

use App\Models\StudentMark;
use App\Policies\MarkPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Domain\Scheduling\Algorithms\BacktrackingSolver;
use App\Domain\Scheduling\Algorithms\SearchState;
use App\Domain\Scheduling\Algorithms\VariableSelector;
use App\Domain\Scheduling\Algorithms\CandidateGenerator;
use App\Domain\Scheduling\Algorithms\ForwardChecker;


// Builders
use App\Domain\Scheduling\Builders\GenerationContextBuilder;
use App\Domain\Scheduling\Builders\LessonRequirementBuilder;


// Generators
use App\Domain\Scheduling\Generators\TimeSlotGenerator;


// Services
use App\Domain\Scheduling\Services\LessonExpander;


// Persistence
use App\Domain\Scheduling\Persistence\SchedulePersistenceService;


// Scoring
use App\Domain\Scheduling\Scoring\ScoreCalculator;
use App\Domain\Scheduling\Scoring\PenaltyCalculator;


// Constraints
use App\Domain\Scheduling\Constraints\ConstraintFactory;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
   public function register(): void
    {


        /*
        |--------------------------------------------------------------------------
        | Scheduling Engine
        |--------------------------------------------------------------------------
        */


        // Time Slots
        $this->app->singleton(
            TimeSlotGenerator::class
        );


        // Lesson Builder
        $this->app->singleton(
            LessonRequirementBuilder::class
        );


        // Lesson Expander
        $this->app->singleton(
            LessonExpander::class
        );



        // Context
        $this->app->singleton(
            GenerationContextBuilder::class
        );



        // Algorithm Components

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



        // Constraints

        $this->app->singleton(
            ConstraintFactory::class
        );



        // Persistence

        $this->app->singleton(
            SchedulePersistenceService::class
        );



        // Scoring
            $this->app->bind(PenaltyCalculator::class, function ($app) {
            return new PenaltyCalculator([
                new \App\Domain\Scheduling\Scoring\Penalties\AvoidFirstPeriodPenalty(),
                new \App\Domain\Scheduling\Scoring\Penalties\AvoidLastPeriodPenalty(),
                new \App\Domain\Scheduling\Scoring\Penalties\HeavySubjectPenalty(),
                new \App\Domain\Scheduling\Scoring\Penalties\TeacherGapPenalty(),
            ]);
        });


        $this->app->singleton(
            ScoreCalculator::class
        );


    }
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(StudentMark::class, MarkPolicy::class);
    }
}
