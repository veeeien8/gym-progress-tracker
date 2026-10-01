@extends('layouts.app')
@section('title', $exercise->name)
@section('content')
<div class="mx-auto max-w-2xl">
<a class="link" href="{{ route('exercises.index') }}">&larr; Exercise library</a>
<section class="panel mt-5">
<span class="badge">{{ $exercise->muscle_group }}</span>
<h1 class="mt-4">{{ $exercise->name }}</h1>
<p class="mt-3 text-slate-500">Used in {{ $exercise->workouts_count }} workouts</p>
<p class="mt-5 whitespace-pre-wrap">{{ $exercise->instructions ?: 'No personal notes added.' }}</p>
<div class="mt-6 flex flex-wrap gap-3">
<a class="btn" href="{{ route('progress.index', ['exercise_id' => $exercise->id]) }}">View progress</a>
<a class="btn-secondary" href="{{ route('exercises.edit', $exercise) }}">Edit</a>
<form method="POST" action="{{ route('exercises.destroy', $exercise) }}" data-confirm="Delete this exercise?">
@csrf
 @method('DELETE')
<button class="btn-danger" type="submit">Delete</button>
</form>
</div>
</section>
</div>
@endsection
