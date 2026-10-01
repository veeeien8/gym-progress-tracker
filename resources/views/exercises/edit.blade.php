@extends('layouts.app')
@section('title', 'Edit exercise')
@section('content')
<div class="mx-auto max-w-2xl">
<a class="link" href="{{ route('exercises.index') }}">&larr; Back to exercises</a>
<h1 class="mb-6 mt-5">Edit exercise</h1>
<form class="panel space-y-5" method="POST"  action="{{ route('exercises.update', $exercise) }}">
@csrf

@method('PUT')

@include('exercises._form')
<div class="flex gap-3">
<button class="btn" type="submit">Save exercise</button>
<a class="btn-secondary" href="{{ route('exercises.index') }}">Cancel</a>
</div>
</form>
</div>
@endsection
