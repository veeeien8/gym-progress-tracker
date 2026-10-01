<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExerciseController extends Controller
{
    public const GROUPS = ['Chest', 'Back', 'Legs', 'Shoulders', 'Arms', 'Core', 'Full body'];

    public function index(Request $request)
    {
        $filters = $request->validate(['search' => 'nullable|string|max:100', 'muscle_group' => ['nullable', Rule::in(self::GROUPS)]]);
        $query = $request->user()->exercises()->withCount('workouts');
        if (! empty($filters['search'])) {
            $query->where('name', 'like', '%'.$filters['search'].'%');
        }
        if (! empty($filters['muscle_group'])) {
            $query->where('muscle_group', $filters['muscle_group']);
        }
        $exercises = $query->orderBy('name')->paginate(12)->withQueryString();

        return view('exercises.index', ['exercises' => $exercises, 'groups' => self::GROUPS]);
    }

    public function create()
    {
        return view('exercises.create', ['exercise' => new Exercise, 'groups' => self::GROUPS]);
    }

    public function store(Request $request)
    {
        $exercise = $request->user()->exercises()->create($this->validateExercise($request));

        return redirect()->route('exercises.show', $exercise)->with('success', 'Exercise created.');
    }

    public function show(Request $request, $exercise)
    {
        $exercise = $request->user()->exercises()->withCount('workouts')->findOrFail($exercise);

        return view('exercises.show', compact('exercise'));
    }

    public function edit(Request $request, $exercise)
    {
        $exercise = $request->user()->exercises()->findOrFail($exercise);

        return view('exercises.edit', ['exercise' => $exercise, 'groups' => self::GROUPS]);
    }

    public function update(Request $request, $exercise)
    {
        $exercise = $request->user()->exercises()->findOrFail($exercise);
        $exercise->update($this->validateExercise($request, $exercise));

        return redirect()->route('exercises.show', $exercise)->with('success', 'Exercise updated.');
    }

    public function destroy(Request $request, $exercise)
    {
        $exercise = $request->user()->exercises()->findOrFail($exercise);
        if ($exercise->workouts()->exists()) {
            return back()->withErrors(['exercise' => 'This exercise belongs to a workout. Remove it from those workouts before deleting it.']);
        }
        $exercise->delete();

        return redirect()->route('exercises.index')->with('success', 'Exercise deleted.');
    }

    private function validateExercise(Request $request, ?Exercise $exercise = null)
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('exercises')->where('user_id', $request->user()->id)->ignore($exercise)],
            'muscle_group' => ['required', Rule::in(self::GROUPS)],
            'instructions' => 'nullable|string|max:2000',
        ]);
    }
}
