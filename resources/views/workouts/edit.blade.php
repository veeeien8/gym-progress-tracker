@extends('layouts.app')
@section('title', 'Edit workout')
@section('content')
<div class="mx-auto max-w-2xl">
<a class="link" href="{{ route('workouts.index') }}">&larr; Back to workouts</a>
<h1 class="mb-6 mt-5">Edit workout</h1>
<form class="panel space-y-5" method="POST" enctype="multipart/form-data" action="{{ route('workouts.update', $workout) }}">
@csrf

@method('PUT')

@include('workouts._form')
<div class="flex gap-3">
<button class="btn" type="submit">Save workout</button>
<a class="btn-secondary" href="{{ route('workouts.index') }}">Cancel</a>
</div>
</form>
</div>
@endsection
