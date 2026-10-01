@extends('layouts.app')
@section('title', $workout->name)
@section('content')
<a class="link" href="{{ route('workouts.index') }}">&larr; All workouts</a>
<div class="mb-7 mt-5 flex flex-wrap items-start justify-between gap-4">
<div>
<div class="mb-3 flex gap-2">
<span class="badge">{{ $workout->workoutType->name }}</span>
<span class="badge {{ $workout->status === 'completed' ? 'bg-stone-50 text-stone-800' : '' }}">{{ ucfirst($workout->status) }}</span>
</div>
<h1>{{ $workout->name }}</h1>
<p class="mt-2 text-slate-500">{{ $workout->workout_date->format('l, d M Y') }} · {{ $workout->sets->count() }} sets recorded</p>
</div>
<div class="flex flex-wrap gap-2">
<a class="btn-secondary" href="{{ route('workouts.edit', $workout) }}">Edit workout / status</a>
<form method="POST" action="{{ route('workouts.destroy', $workout) }}" data-confirm="Delete this workout, its sets and attachment?">
@csrf
 @method('DELETE')
<button class="btn-danger" type="submit">Delete workout</button>
</form>
</div>
</div>
@if($workout->notes || $workout->attachment_path)<section class="panel mb-6">
<h2>Session notes</h2>
@if($workout->notes)<p class="mt-3 whitespace-pre-wrap">{{ $workout->notes }}</p>
@endif
 @if($workout->attachment_path)<a class="link mt-3 inline-block" href="{{ route('workouts.attachment', $workout) }}">Download workout plan</a>
@endif
</section>
@endif

<div class="grid gap-6 lg:grid-cols-3">
<div class="space-y-5 lg:col-span-2">
@forelse($workout->exercises as $exercise)
<section class="panel">
<div class="flex flex-wrap items-start justify-between gap-3">
<div>
<p class="mb-1 text-xs font-bold uppercase tracking-wider text-stone-700">{{ $exercise->muscle_group }}</p>
<h2>
<a href="{{ route('exercises.show', $exercise) }}">{{ $exercise->name }}</a>
</h2>
</div>
<form method="POST" action="{{ route('workouts.exercises.destroy', [$workout, $exercise]) }}" data-confirm="Remove this exercise and all its sets from this workout?">
@csrf
 @method('DELETE')
<button class="btn-danger" type="submit">Remove</button>
</form>
</div>
@php($sets = $workout->sets->where('exercise_id', $exercise->id))
<div class="mt-4 overflow-x-auto">
<table class="w-full">
<thead class="bg-slate-50">
<tr>
<th>Set</th>
<th>Weight</th>
<th>Reps</th>
<th>Actions</th>
</tr>
</thead>
<tbody class="divide-y divide-slate-100">
@forelse($sets as $set)<tr>
<td>{{ $loop->iteration }}</td>
<td>{{ number_format($set->weight, 2) }} kg</td>
<td>{{ $set->reps }}</td>
<td>
<div class="flex items-center gap-3">
<a class="link" href="{{ route('sets.edit', [$workout, $set]) }}">Edit</a>
<form method="POST" action="{{ route('sets.destroy', [$workout, $set]) }}" data-confirm="Delete this set?">
@csrf
 @method('DELETE')
<button class="text-sm font-medium text-red-700 underline" type="submit">Delete</button>
</form>
</div>
</td>
</tr>
@empty
<tr>
<td colspan="4" class="text-slate-500">No sets yet. Record your first set below.</td>
</tr>
@endforelse

</tbody>
</table>
</div>
<form method="POST" action="{{ route('sets.store', [$workout, $exercise]) }}" class="mt-4 grid grid-cols-2 gap-3 border-t border-slate-100 pt-4 sm:grid-cols-3">
@csrf

<div>
<label for="weight-{{ $exercise->id }}">Weight (kg)</label>
<input type="number" id="weight-{{ $exercise->id }}" name="weight" min="0" max="1000" step="0.01" value="{{ old('form_exercise_id') == $exercise->id ? old('weight') : '' }}" required placeholder="40">
</div>
<div>
<label for="reps-{{ $exercise->id }}">Repetitions</label>
<input type="number" id="reps-{{ $exercise->id }}" name="reps" min="1" max="1000" step="1" value="{{ old('form_exercise_id') == $exercise->id ? old('reps') : '' }}" required placeholder="10">
</div>
<input type="hidden" name="form_exercise_id" value="{{ $exercise->id }}">
<button class="btn col-span-2 self-end sm:col-span-1" type="submit">+ Record set</button>
</form>
<p class="hint">Use 0 kg for bodyweight-only sets.</p>
</section>
@empty
<div class="panel py-12 text-center">
<h2>Your workout is ready</h2>
<p class="mt-3 text-slate-500">Add an exercise to start logging sets.</p>
</div>
@endforelse

</div>
<aside class="space-y-5">
<section class="panel">
<h2>Add an exercise</h2>
@if($available->isNotEmpty())<form method="POST" action="{{ route('workouts.exercises.store', $workout) }}" class="mt-4 space-y-4">
@csrf
<div>
<label for="exercise_id">From your library</label>
<select id="exercise_id" name="exercise_id" required>
@foreach($available as $exercise)<option value="{{ $exercise->id }}">{{ $exercise->name }}</option>
@endforeach
</select>
</div>
<button class="btn w-full" type="submit">Add to workout</button>
</form>
@else
<p class="mt-3 text-sm text-slate-500">All available exercises have been added, or your library is empty.</p>
@endif

<a class="link mt-4 inline-block text-sm" href="{{ route('exercises.create') }}">Create a new exercise</a>
</section>
<section class="rounded-2xl bg-slate-900 p-6 text-white">
<p class="text-sm font-semibold text-stone-300">Keep your history useful</p>
<p class="mt-3 text-sm leading-relaxed text-slate-200">When you finish training, edit the workout and choose Completed. Your recorded sets will appear in Progress.</p>
<a class="mt-4 inline-block text-sm font-semibold text-white underline" href="{{ route('progress.index') }}">View your progress &rarr;</a>
</section>
</aside>
</div>
@endsection
