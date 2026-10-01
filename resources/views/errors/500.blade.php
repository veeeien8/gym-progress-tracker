@extends('layouts.app')
@section('title', 'Something went wrong')
@section('content')
<div class="panel mx-auto max-w-xl py-12 text-center">
<p class="mb-3 text-sm font-bold text-stone-700">500</p>
<h1>Something went wrong</h1>
<p class="mt-4 text-slate-500">Please try again. If it continues, check the application log.</p>
<a class="btn mt-6" href="{{ url('/') }}">Back to home</a>
</div>
@endsection
