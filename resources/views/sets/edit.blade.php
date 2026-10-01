@extends('layouts.app')
@section('title', 'Edit set')
@section('content')
<div class="mx-auto max-w-xl">
<a class="link" href="{{ route('workouts.show', $workout) }}">&larr; Back to workout</a>
<h1 class="mt-5">Edit set</h1>
<p class="mb-5 mt-2 text-slate-500">{{ $set->exercise->name }} · {{ $workout->name }}</p>
<form class="panel space-y-4" method="POST" action="{{ route('sets.update', [$workout, $set]) }}">
@csrf
 @method('PUT')
<div>
<label for="weight">Weight (kg)</label>
<input id="weight" type="number" name="weight" step="0.01" min="0" max="1000" value="{{ old('weight', $set->weight) }}" required>
</div>
<div>
<label for="reps">Repetitions</label>
<input id="reps" type="number" name="reps" step="1" min="1" max="1000" value="{{ old('reps', $set->reps) }}" required>
</div>
<button class="btn" type="submit">Save set</button>
</form>
</div>
@endsection
