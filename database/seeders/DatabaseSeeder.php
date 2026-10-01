<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Public demo credentials belong only in a local development database.
        if (! app()->environment('local', 'testing')) {
            return;
        }
        foreach (['alex', 'sam'] as $name) {
            if (User::where('email', $name.'@example.com')->exists()) {
                continue;
            }
            $user = User::create(['name' => ucfirst($name), 'email' => $name.'@example.com', 'password' => Hash::make('GymDemo123!')]);
            $push = $user->workoutTypes()->create(['name' => 'Push', 'description' => 'Chest, shoulders and triceps.']);
            $pull = $user->workoutTypes()->create(['name' => 'Pull', 'description' => 'Back and biceps.']);
            $legs = $user->workoutTypes()->create(['name' => 'Legs', 'description' => 'Lower body training.']);
            $bench = $user->exercises()->create(['name' => 'Bench press', 'muscle_group' => 'Chest', 'instructions' => 'Record total barbell weight, including the bar.']);
            $press = $user->exercises()->create(['name' => 'Shoulder press', 'muscle_group' => 'Shoulders']);
            $row = $user->exercises()->create(['name' => 'Seated row', 'muscle_group' => 'Back']);
            $squat = $user->exercises()->create(['name' => 'Squat', 'muscle_group' => 'Legs']);
            $user->exercises()->create(['name' => 'Push-up', 'muscle_group' => 'Chest', 'instructions' => 'Use 0 kg when no extra weight is added.']);
            for ($i = 0; $i < 12; $i++) {
                $type = [$push, $pull, $legs][$i % 3];
                $exercise = [$bench, $row, $squat][$i % 3];
                $workout = $user->workouts()->create([
                    'name' => $type->name.' session '.($i + 1), 'workout_type_id' => $type->id,
                    'workout_date' => now()->subDays(24 - $i * 2)->toDateString(),
                    'status' => 'completed', 'notes' => 'Sample workout for exploring the app.',
                ]);
                $workout->exercises()->attach($exercise->id);
                foreach ([10, 10, 8] as $reps) {
                    $workout->sets()->create(['exercise_id' => $exercise->id, 'weight' => 30 + $i * 2.5, 'reps' => $reps]);
                }
                if ($i % 3 === 0) {
                    $workout->exercises()->attach($press->id);
                    $workout->sets()->create(['exercise_id' => $press->id, 'weight' => 15, 'reps' => 10]);
                }
            }
            $next = $user->workouts()->create(['name' => 'Next push session', 'workout_type_id' => $push->id, 'workout_date' => now()->addDay()->toDateString(), 'status' => 'planned']);
            $next->exercises()->attach([$bench->id, $press->id]);
        }
    }
}
