<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Domain\Scheduling\Actions\GenerateScheduleAction;
use Exception;

class GenerateScheduleCommand extends Command
{
    protected $signature = 'schedule:generate {year} {term}';

    protected $description = 'Generate school timetable';

    public function handle(GenerateScheduleAction $action): int
    {
        $this->info('Starting schedule generation...');

        try {
            // Casting arguments to int to prevent TypeErrors in Action
            $schedule = $action->execute(
                (int) $this->argument('year'),
                (int) $this->argument('term')
            );

            $this->info("Generated Schedule ID: " . $schedule->id);

            return Command::SUCCESS;

        } catch (Exception $e) {
            // English Error Message
            $this->error("Generation failed: " . $e->getMessage());

            return Command::FAILURE;
        }
    }
}
