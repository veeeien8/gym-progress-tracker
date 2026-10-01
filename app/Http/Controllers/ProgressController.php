<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProgressController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:100',
            'exercise_id' => ['nullable', 'integer', Rule::exists('exercises', 'id')->where('user_id', $request->user()->id)],
        ]);
        $exercises = $request->user()->exercises()->orderBy('name')->get();
        $exercise = null;
        $workouts = null;
        $bestWeight = null;
        if (! empty($validated['exercise_id'])) {
            $exercise = $request->user()->exercises()->findOrFail($validated['exercise_id']);
            // Only completed workouts with actual recorded sets count as history.
            $workouts = $exercise->workouts()->where('workouts.user_id', $request->user()->id)
                ->where('status', 'completed')
                ->when($validated['search'] ?? null, fn ($query, $search) => $query->where('workouts.name', 'like', '%'.$search.'%'))
                ->whereHas('sets', fn ($query) => $query->where('exercise_id', $exercise->id))
                ->with(['sets' => fn ($query) => $query->where('exercise_id', $exercise->id)])
                ->orderByDesc('workout_date')->orderByDesc('workouts.id')->paginate(10)->withQueryString();
            $bestWeight = $exercise->sets()->whereHas('workout', fn ($query) => $query->where('user_id', $request->user()->id)->where('status', 'completed'))->max('weight');
        }

        return view('progress.index', compact('exercises', 'exercise', 'workouts', 'bestWeight'));
    }
}
