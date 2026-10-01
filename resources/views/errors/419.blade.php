@extends('layouts.app')
@section('title', 'Session expired')
@section('content')
<div class="panel mx-auto max-w-xl py-12 text-center">
<p class="mb-3 text-sm font-bold text-stone-700">419</p>
<h1>Session expired</h1>
<p class="mt-4 text-slate-500">Go back, refresh the form and try again.</p>
<a class="btn mt-6" href="{{ url('/') }}">Back to home</a>
</div>
@endsection
