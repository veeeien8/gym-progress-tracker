@extends('layouts.app')
@section('title', 'Workouts')
@section('page-header')
<div class="flex flex-wrap items-center justify-between gap-4">
<div>
<h1>Workouts</h1>
</div>
<a class="btn" href="{{ route('workouts.create') }}">+ New workout</a>
</div>
@endsection
@section('content')
<div class="mb-7 grid gap-4 sm:grid-cols-3">
 <div class="panel">
<p class="text-sm text-slate-500">Total workouts</p>
<p class="mt-2 text-3xl font-bold">{{ $total }}</p>
</div>
 <div class="panel">
<p class="text-sm text-slate-500">Completed sessions</p>
<p class="mt-2 text-3xl font-bold text-stone-700">{{ $completed }}</p>
</div>
 <div class="panel">
<p class="text-sm text-slate-500">Exercises in your library</p>
<p class="mt-2 text-3xl font-bold">{{ $exerciseCount }}</p>
</div>
</div>
@if($types->isEmpty() || $exerciseCount === 0)
<div class="mb-6 rounded-xl border border-stone-200 bg-stone-50 p-5">
<h2>Start with the basics</h2>
<p class="mt-2 text-sm">Create a <a class="link" href="{{ route('workout-types.create') }}">workout type</a> such as Push, Pull or Legs, then add your <a class="link" href="{{ route('exercises.create') }}">exercises</a>. You can then build your first workout.</p>
</div>
@endif

<section class="panel">
<h2 class="mb-4">Workout history</h2>
<form method="GET" action="{{ route('workouts.index') }}" class="mb-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
<div><label for="search">Search by name</label><input type="search" id="search" name="search" value="{{ request('search') }}" maxlength="100" placeholder="Search by name"></div>
 <div>
<label for="status">Status</label>
<select id="status" name="status">
<option value="">All statuses</option>
<option value="planned" @selected(request('status') === 'planned')>Planned</option>
<option value="completed" @selected(request('status') === 'completed')>Completed</option>
</select>
</div>
 <div>
<label for="workout_type_id">Workout type</label>
<select id="workout_type_id" name="workout_type_id">
<option value="">All types</option>
@foreach($types as $type)<option value="{{ $type->id }}" @selected(request('workout_type_id') == $type->id)>{{ $type->name }}</option>
@endforeach
</select>
</div>
 <div>
<label for="from">From date</label>
<input type="date" id="from" name="from" value="{{ request('from') }}">
</div>
<div>
<label for="to">To date</label>
<input type="date" id="to" name="to" value="{{ request('to') }}">
</div>
 <div class="flex items-end gap-2">
<button class="btn" type="submit">Apply filters</button>
<a class="btn-secondary" href="{{ route('workouts.index') }}">Reset</a>
</div>
</form>
<div class="overflow-x-auto">
<table class="w-full">
<thead class="border-y border-slate-100 bg-slate-50">
<tr>
<th>Workout</th>
<th>Date</th>
<th>Type</th>
<th>Logged</th>
<th>Status</th>
</tr>
</thead>
<tbody class="divide-y divide-slate-100">
@forelse($workouts as $workout)<tr>
<td>
<a class="link" href="{{ route('workouts.show', $workout) }}">{{ $workout->name }}</a>
</td>
<td class="whitespace-nowrap">{{ $workout->workout_date->format('d M Y') }}</td>
<td>{{ $workout->workoutType->name }}</td>
<td class="whitespace-nowrap">{{ $workout->exercises_count }} exercises · {{ $workout->sets_count }} sets</td>
<td>
<span class="badge {{ $workout->status === 'completed' ? 'bg-stone-50 text-stone-800' : '' }}">{{ ucfirst($workout->status) }}</span>
</td>
</tr>
@empty
<tr>
<td colspan="5" class="py-12 text-center text-slate-500">No workouts found. Create a workout or adjust your filters.</td>
</tr>
@endforelse

</tbody>
</table>
</div>
@include('shared.pagination', ['items' => $workouts])
</section>
@endsection
