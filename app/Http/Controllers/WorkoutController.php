<?php

namespace App\Http\Controllers;

use App\Models\Workout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class WorkoutController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => 'nullable|string|max:100',
            'status' => ['nullable', Rule::in(['planned', 'completed'])],
            'workout_type_id' => ['nullable', 'integer', Rule::exists('workout_types', 'id')->where('user_id', $request->user()->id)],
            'from' => 'nullable|date_format:Y-m-d',
            'to' => ['nullable', 'date_format:Y-m-d', Rule::when($request->filled('from'), 'after_or_equal:from')],
        ]);
        $query = $request->user()->workouts()->with('workoutType')->withCount(['exercises', 'sets']);
        if (! empty($filters['search'])) {
            $query->where('name', 'like', '%'.$filters['search'].'%');
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['workout_type_id'])) {
            $query->where('workout_type_id', $filters['workout_type_id']);
        }
        if (! empty($filters['from'])) {
            $query->whereDate('workout_date', '>=', $filters['from']);
        }
        if (! empty($filters['to'])) {
            $query->whereDate('workout_date', '<=', $filters['to']);
        }
        $workouts = $query->orderByDesc('workout_date')->orderByDesc('id')->paginate(2)->withQueryString();
        $types = $request->user()->workoutTypes()->orderBy('name')->get();
        $total = $request->user()->workouts()->count();
        $completed = $request->user()->workouts()->where('status', 'completed')->count();
        $exerciseCount = $request->user()->exercises()->count();

        return view('workouts.index', compact('workouts', 'types', 'total', 'completed', 'exerciseCount'));
    }

    public function create(Request $request)
    {
        $types = $request->user()->workoutTypes()->orderBy('name')->get();

        return view('workouts.create', ['workout' => new Workout, 'types' => $types]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateWorkout($request);
        // The relationship assigns user_id; a forged input cannot choose the owner.
        $workout = $request->user()->workouts()->create(collect($validated)->except(['attachment', 'remove_attachment'])->all());
        $this->saveAttachment($request, $workout);

        return redirect()->route('workouts.show', $workout)->with('success', 'Workout created. Add your exercises below.');
    }

    public function show(Request $request, $workout)
    {
        $workout = $request->user()->workouts()->with(['workoutType', 'exercises', 'sets'])->findOrFail($workout);
        $available = $request->user()->exercises()->whereNotIn('id', $workout->exercises->pluck('id'))->orderBy('name')->get();

        return view('workouts.show', compact('workout', 'available'));
    }

    public function edit(Request $request, $workout)
    {
        $workout = $request->user()->workouts()->findOrFail($workout);
        $types = $request->user()->workoutTypes()->orderBy('name')->get();

        return view('workouts.edit', compact('workout', 'types'));
    }

    public function update(Request $request, $workout)
    {
        $workout = $request->user()->workouts()->findOrFail($workout);
        $validated = $this->validateWorkout($request);
        $workout->update(collect($validated)->except(['attachment', 'remove_attachment'])->all());
        $this->saveAttachment($request, $workout);

        return redirect()->route('workouts.show', $workout)->with('success', 'Workout updated.');
    }

    public function destroy(Request $request, $workout)
    {
        $workout = $request->user()->workouts()->findOrFail($workout);
        $path = $workout->attachment_path;
        $workout->delete(); // Foreign keys remove this workout's links and sets.
        if ($path) {
            Storage::disk('local')->delete($path);
        }

        return redirect()->route('workouts.index')->with('success', 'Workout deleted.');
    }

    public function download(Request $request, $workout)
    {
        $workout = $request->user()->workouts()->findOrFail($workout);
        abort_unless($workout->attachment_path && Storage::disk('local')->exists($workout->attachment_path), 404);

        return Storage::disk('local')->download($workout->attachment_path, $workout->attachment_name);
    }

    private function validateWorkout(Request $request)
    {
        return $request->validate([
            'name' => 'required|string|max:100',
            'workout_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:2000-01-01', 'before_or_equal:2100-12-31', Rule::when($request->input('status') === 'completed', 'before_or_equal:today')],
            'workout_type_id' => ['required', 'integer', Rule::exists('workout_types', 'id')->where('user_id', $request->user()->id)],
            'status' => ['required', Rule::in(['planned', 'completed'])],
            'notes' => 'nullable|string|max:3000',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'remove_attachment' => 'sometimes|boolean',
        ]);
    }

    private function saveAttachment(Request $request, Workout $workout)
    {
        $oldPath = $workout->attachment_path;
        if ($request->hasFile('attachment')) {
            // Store privately with a generated name. Only the download route exposes it.
            $path = $request->file('attachment')->store('workout-plans', 'local');
            abort_unless($path, 500, 'The attachment could not be saved.');
            $workout->attachment_path = $path;
            // Use a safe download name independent of the browser-supplied filename.
            $workout->attachment_name = 'workout-plan.'.$request->file('attachment')->extension();
            $workout->save();
            if ($oldPath) {
                Storage::disk('local')->delete($oldPath);
            }
        } elseif ($request->boolean('remove_attachment')) {
            $workout->attachment_path = null;
            $workout->attachment_name = null;
            $workout->save();
            if ($oldPath) {
                Storage::disk('local')->delete($oldPath);
            }
        }
    }
}
