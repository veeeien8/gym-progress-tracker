<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WorkoutExerciseController extends Controller
{
    public function store(Request $request, $workout)
    {
        $workout = $request->user()->workouts()->findOrFail($workout);
        $validated = $request->validate([
            'exercise_id' => ['required', 'integer', Rule::exists('exercises', 'id')->where('user_id', $request->user()->id)],
        ]);
        // Add a pivot row without removing existing exercises; repeated submits are safe.
        $workout->exercises()->syncWithoutDetaching([$validated['exercise_id']]);

        return redirect()->route('workouts.show', $workout)->with('success', 'Exercise added.');
    }

    public function destroy(Request $request, $workout, $exercise)
    {
        $workout = $request->user()->workouts()->findOrFail($workout);
        $exercise = $workout->exercises()->findOrFail($exercise);
        // Both writes succeed together, so detached exercises never leave orphan sets.
        DB::transaction(function () use ($workout, $exercise) {
            $workout->sets()->where('exercise_id', $exercise->id)->delete();
            $workout->exercises()->detach($exercise->id);
        });

        return redirect()->route('workouts.show', $workout)->with('success', 'Exercise and its sets removed from this workout.');
    }
}
