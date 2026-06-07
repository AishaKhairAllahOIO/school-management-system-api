<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\school;

class GeneralSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        School::updateOrCreate(
            ['id' => 1], // لضمان وجود سجل واحد فقط للمدرسة
            [
                'school_name'           => 'Global Academy',
                'short_name'            => 'GA',
                'description'           => 'A leading educational institution.',
                'phone_number'          => '+1234567890',
                'emergency_phone_number'=> '+1987654321',
                'email'                 => 'admin@globalacademy.com',
                'website'               => 'https://globalacademy.com',
                'address'               => '123 Education St',
                'city'                  => 'New York',
                'country'               => 'USA',
                'latitude'              => 40.7128,
                'longitude'             => -74.0060,
                'default_language'      => 'en',
                'timezone'              => 'UTC',
                'date_format'           => 'Y-m-d',
                'currency'              => 'USD',
                'working_days'          => json_encode(['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday']),
                'opening_time'          => '08:00',
                'closing_time'          => '15:00',
            ]
        );
    }
}
