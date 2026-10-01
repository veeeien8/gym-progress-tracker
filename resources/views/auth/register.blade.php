@extends('layouts.app')
@section('title', 'Create account')
@section('content')
<div class="mx-auto grid max-w-4xl gap-8 py-8 md:grid-cols-2 md:items-center">
 <div>
<p class="mb-4 text-sm font-bold uppercase tracking-widest text-stone-700">Build a record. See your progress.</p>
<h1 class="text-4xl leading-tight">Make every<br>set count.</h1>
<p class="mt-5 max-w-sm leading-relaxed text-slate-600">Plan your training, log weights and reps, and look back at how far you have come.</p>
<div class="mt-6 flex gap-2">
<span class="badge">Workouts</span>
<span class="badge">Sets & reps</span>
<span class="badge">Progress</span>
</div>
</div>
 <section class="panel">
<h2>Create your account</h2>
<p class="mt-2 text-sm text-slate-500">Your workouts stay private to your account.</p>
 <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
@csrf

 <div>
<label for="name">Name</label>
<input id="name" name="name" value="{{ old('name') }}" maxlength="100" autocomplete="name" required>
</div>
 <div>
<label for="email">Email</label>
<input id="email" name="email" type="email" value="{{ old('email') }}" maxlength="255" autocomplete="email" required>
</div>
 <div>
<label for="password">Password (at least 8 characters)</label>
<input id="password" name="password" type="password" minlength="8" maxlength="72" autocomplete="new-password" required>
</div>
 <div>
<label for="password_confirmation">Confirm password</label>
<input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
</div>
 <button class="btn w-full" type="submit">Create account</button>
</form>
 <p class="mt-5 text-sm text-slate-600">Already registered? <a class="link" href="{{ route('login') }}">Log in</a>
</p>
</section>
</div>
@endsection
