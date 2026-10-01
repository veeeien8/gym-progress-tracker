@extends('layouts.app')
@section('title', 'Exercises')
@section('page-header')
<div class="flex flex-wrap items-center justify-between gap-4">
<div>
<h1>Exercise library</h1>
</div>
<a class="btn" href="{{ route('exercises.create') }}">+ New exercise</a>
</div>
@endsection
@section('content')
<form class="panel mb-6 grid items-end gap-4 sm:grid-cols-3" method="GET" action="{{ route('exercises.index') }}">
<div><label for="search">Search by name</label><input type="search" id="search" name="search" value="{{ request('search') }}" maxlength="100" placeholder="Search by name"></div>
<div>
<label for="muscle_group">Muscle group</label>
<select id="muscle_group" name="muscle_group">
<option value="">All groups</option>
@foreach($groups as $group)<option value="{{ $group }}" @selected(request('muscle_group') === $group)>{{ $group }}</option>
@endforeach
</select>
</div>
<div class="flex gap-2">
<button class="btn" type="submit">Search</button>
<a class="btn-secondary" href="{{ route('exercises.index') }}">Reset</a>
</div>
</form>
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
@forelse($exercises as $exercise)<article class="panel">
<span class="badge">{{ $exercise->muscle_group }}</span>
<h2 class="mt-4">
<a class="link" href="{{ route('exercises.show', $exercise) }}">{{ $exercise->name }}</a>
</h2>
<p class="mt-3 text-sm text-slate-500">Used in {{ $exercise->workouts_count }} workouts</p>
</article>
@empty
<div class="panel sm:col-span-2">No exercises found. Add one or adjust your search.</div>
@endforelse
</div>
@include('shared.pagination', ['items' => $exercises])
@endsection
