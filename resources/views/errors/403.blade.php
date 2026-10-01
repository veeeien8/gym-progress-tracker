@extends('layouts.app')
@section('title', 'Access denied')
@section('content')
<div class="panel mx-auto max-w-xl py-12 text-center">
<p class="mb-3 text-sm font-bold text-stone-700">403</p>
<h1>Access denied</h1>
<p class="mt-4 text-slate-500">You do not have permission to open this page.</p>
<a class="btn mt-6" href="{{ url('/') }}">Back to home</a>
</div>
@endsection
