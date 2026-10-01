@extends('layouts.app')
@section('title', 'Page not found')
@section('content')
<div class="panel mx-auto max-w-xl py-12 text-center">
<p class="mb-3 text-sm font-bold text-stone-700">404</p>
<h1>Page not found</h1>
<p class="mt-4 text-slate-500">This page does not exist or is not available to your account.</p>
<a class="btn mt-6" href="{{ url('/') }}">Back to home</a>
</div>
@endsection
