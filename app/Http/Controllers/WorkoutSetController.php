<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WorkoutSetController extends Controller
{
    public function store(Request $request, $workout, $exercise)
    {
        $workout = $request->user()->workouts()->findOrFail($workout);
        $exercise = $workout->exercises()->findOrFail($exercise);
        $validated = $this->validateSet($request);
        // Use the checked URL exercise; ignore any forged exercise_id in the form.
        $validated['exercise_id'] = $exercise->id;
        $workout->sets()->create($validated);

        return redirect()->route('workouts.show', $workout)->with('success', 'Set recorded.');
    }

    public function edit(Request $request, $workout, $set)
    {
        $workout = $request->user()->workouts()->findOrFail($workout);
        $set = $workout->sets()->with('exercise')->findOrFail($set);

        return view('sets.edit', compact('workout', 'set'));
    }

    public function update(Request $request, $workout, $set)
    {
        $workout = $request->user()->workouts()->findOrFail($workout);
        $set = $workout->sets()->findOrFail($set);
        $set->update($this->validateSet($request));

        return redirect()->route('workouts.show', $workout)->with('success', 'Set updated.');
    }

    public function destroy(Request $request, $workout, $set)
    {
        $workout = $request->user()->workouts()->findOrFail($workout);
        $set = $workout->sets()->findOrFail($set);
        $set->delete();

        return redirect()->route('workouts.show', $workout)->with('success', 'Set deleted.');
    }

    private function validateSet(Request $request)
    {
        return $request->validate(['reps' => 'required|integer|min:1|max:1000', 'weight' => 'required|numeric|decimal:0,2|min:0|max:1000']);
    }
}
