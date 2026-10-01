@extends('layouts.app')
@section('title', 'Progress')
@section('page-header')
<h1>Exercise progress</h1>
@endsection
@section('content')
<form method="GET" action="{{ route('progress.index') }}" class="panel mb-6 flex flex-wrap items-end gap-4">
<div><label for="search">Search by name</label><input type="search" id="search" name="search" value="{{ request('search') }}" maxlength="100" placeholder="Search by name"></div>
<div class="min-w-0 flex-1">
<label for="exercise_id">Choose an exercise</label>
<select id="exercise_id" name="exercise_id" required>
<option value="">Select an exercise</option>
@foreach($exercises as $item)<option value="{{ $item->id }}" @selected($exercise?->id === $item->id)>{{ $item->name }}</option>
@endforeach
</select>
</div>
<button class="btn" type="submit">View history</button>
</form>
@if($exercise)
<div class="mb-6 rounded-2xl bg-slate-900 p-6 text-white">
<p class="text-sm text-slate-300">Heaviest recorded set · {{ $exercise->name }}</p>
<p class="mt-3 text-4xl font-bold">{{ $bestWeight !== null ? number_format($bestWeight, 2).' kg' : 'No sets yet' }}</p>
<p class="mt-3 text-sm text-slate-300">Maximum logged weight across completed sessions, regardless of repetitions. This is not an estimated one-rep max.</p>
</div>
<section class="panel">
<h2 class="mb-4">Session history</h2>
<div class="overflow-x-auto">
<table class="w-full">
<thead class="bg-slate-50">
<tr>
<th>Date</th>
<th>Workout</th>
<th>Recorded sets</th>
</tr>
</thead>
<tbody class="divide-y divide-slate-100">
@forelse($workouts as $workout)<tr>
<td class="whitespace-nowrap">{{ $workout->workout_date->format('d M Y') }}</td>
<td>
<a class="link" href="{{ route('workouts.show', $workout) }}">{{ $workout->name }}</a>
</td>
<td>
<div class="flex flex-wrap gap-2">
@foreach($workout->sets as $set)<span class="badge whitespace-nowrap">{{ number_format($set->weight, 2) }} kg × {{ $set->reps }}</span>
@endforeach
</div>
</td>
</tr>
@empty
<tr>
<td colspan="3" class="py-8 text-slate-500">No completed sessions with sets for this exercise yet. Record sets and mark a workout Completed.</td>
</tr>
@endforelse
</tbody>
</table>
</div>
@include('shared.pagination', ['items' => $workouts])</section>
@else
<div class="panel text-slate-500">Choose an exercise above to see its history.@if($exercises->isEmpty()) <a class="link" href="{{ route('exercises.create') }}">Create your first exercise</a>.@endif
</div>
@endif

@endsection
