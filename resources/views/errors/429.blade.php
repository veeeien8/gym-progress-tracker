@extends('layouts.app')
@section('title', 'Too many attempts')
@section('content')
<div class="panel mx-auto max-w-xl py-12 text-center">
<p class="mb-3 text-sm font-bold text-stone-700">429</p>
<h1>Too many attempts</h1>
<p class="mt-4 text-slate-500">Please wait a minute before trying again.</p>
<a class="btn mt-6" href="{{ url('/') }}">Back to home</a>
</div>
@endsection
