@extends('layouts.app')
@section('title', 'Workout types')
@section('page-header')
<div class="flex flex-wrap items-center justify-between gap-4">
<div>
<h1>Workout types</h1>
</div>
<a class="btn" href="{{ route('workout-types.create') }}">+ New type</a>
</div>
@endsection
@section('content')
<form method="GET" action="{{ route('workout-types.index') }}" class="panel mb-6 flex flex-wrap items-end gap-3">
<div class="flex-1"><label for="search">Search by name</label><input type="search" id="search" name="search" value="{{ request('search') }}" maxlength="100" placeholder="Search workout types"></div>
<button class="btn" type="submit">Search</button>
<a class="btn-secondary" href="{{ route('workout-types.index') }}">Reset</a>
</form>
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
@forelse($types as $type)<section class="panel">
<h2>{{ $type->name }}</h2>
<p class="mt-2 whitespace-pre-wrap text-sm text-slate-500">{{ $type->description }}</p>
<p class="mt-4 text-sm">{{ $type->workouts_count }} workouts</p>
<div class="mt-4 flex items-center gap-3">
<a class="btn-secondary" href="{{ route('workout-types.edit', $type) }}">Edit</a>
<form method="POST" action="{{ route('workout-types.destroy', $type) }}" data-confirm="Delete this workout type?">
@csrf
 @method('DELETE')
<button class="btn-danger" type="submit">Delete</button>
</form>
</div>
</section>
@empty
<div class="panel sm:col-span-2">
<p>No workout types found. Create one or adjust your search.</p>
</div>
@endforelse
</div>
@include('shared.pagination', ['items' => $types])
@endsection
