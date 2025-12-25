<?php

namespace Database\Seeders;

use App\Models\Organizer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OrganizerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update existing organizers with new passwords
        $organizer1 = Organizer::where('email', 'demo1@worldskills.org')->first();
        if ($organizer1) {
            $organizer1->password_hash = Hash::make('demopass1');
            $organizer1->save();
        }

        $organizer2 = Organizer::where('email', 'demo2@worldskills.org')->first();
        if ($organizer2) {
            $organizer2->password_hash = Hash::make('demopass2');
            $organizer2->save();
        }
    }
}
