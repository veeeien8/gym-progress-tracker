<?php

namespace App\Http\Controllers;

use App\Models\WorkoutType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WorkoutTypeController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate(['search' => 'nullable|string|max:100']);
        $types = $request->user()->workoutTypes()->withCount('workouts')
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where('name', 'like', '%'.$search.'%'))
            ->orderBy('name')->paginate(12)->withQueryString();

        return view('types.index', compact('types'));
    }

    public function create()
    {
        return view('types.create', ['type' => new WorkoutType]);
    }

    public function store(Request $request)
    {
        $request->user()->workoutTypes()->create($this->validateType($request));

        return redirect()->route('workout-types.index')->with('success', 'Workout type created.');
    }

    public function edit(Request $request, $workout_type)
    {
        $type = $request->user()->workoutTypes()->findOrFail($workout_type);

        return view('types.edit', compact('type'));
    }

    public function update(Request $request, $workout_type)
    {
        $type = $request->user()->workoutTypes()->findOrFail($workout_type);
        $type->update($this->validateType($request, $type));

        return redirect()->route('workout-types.index')->with('success', 'Workout type updated.');
    }

    public function destroy(Request $request, $workout_type)
    {
        $type = $request->user()->workoutTypes()->findOrFail($workout_type);
        if ($type->workouts()->exists()) {
            return back()->withErrors(['type' => 'This type is used by a workout. Change that workout’s type before deleting it.']);
        }
        $type->delete();

        return redirect()->route('workout-types.index')->with('success', 'Workout type deleted.');
    }

    private function validateType(Request $request, ?WorkoutType $type = null)
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:80', Rule::unique('workout_types')->where('user_id', $request->user()->id)->ignore($type)],
            'description' => 'nullable|string|max:500',
        ]);
    }
}
