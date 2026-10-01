@extends('layouts.app')
@section('title', 'Edit workout type')
@section('content')
<div class="mx-auto max-w-2xl">
<a class="link" href="{{ route('workout-types.index') }}">&larr; Back to workout types</a>
<h1 class="mb-6 mt-5">Edit workout type</h1>
<form class="panel space-y-5" method="POST"  action="{{ route('workout-types.update', $type) }}">
@csrf

@method('PUT')

@include('types._form')
<div class="flex gap-3">
<button class="btn" type="submit">Save workout type</button>
<a class="btn-secondary" href="{{ route('workout-types.index') }}">Cancel</a>
</div>
</form>
</div>
@endsection
