<?php

namespace Database\Seeders;

use App\Models\Content;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $file = database_path('data/madrasaty-website.json');

        if (! file_exists($file)) {
            $this->command->error(
                "Content JSON file not found: {$file}"
            );

            return;
        }

        $json = file_get_contents($file);

        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error(
                'Invalid JSON: ' . json_last_error_msg()
            );

            return;
        }

        $contents = [];

        $this->flattenContent(
            $data,
            '',
            $contents
        );

        foreach ($contents as $key => $value) {
            Content::updateOrCreate(
                [
                    'key' => $key,
                ],
                [
                    'value' => $value,
                ]
            );
        }

        $this->command->info(
            "Content seeded successfully: " . count($contents) . " items."
        );
    }

    /**
     * Convert nested JSON into dot notation.
     *
     * Example:
     *
     * global.navbar.links.home
     * global.navbar.links.about
     * home_page.hero.title_part_1
     */
    private function flattenContent(
        array $data,
        string $prefix,
        array &$result
    ): void {
        foreach ($data as $key => $value) {

            $fullKey = $prefix === ''
                ? $key
                : $prefix . '.' . $key;

            if (is_array($value)) {
                $this->flattenContent(
                    $value,
                    $fullKey,
                    $result
                );

                continue;
            }

            // All leaf values are stored as string or null.
            $result[$fullKey] = $value === null
                ? null
                : (string) $value;
        }
    }
}