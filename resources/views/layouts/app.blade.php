<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'Your training') | Gym Tracker</title>
<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<script src="{{ asset('js/app.js') }}" defer></script>
</head>
<body>
<a href="#main" class="sr-only focus:not-sr-only">Skip to content</a>
@auth
@php
$section = request()->routeIs('exercises.*') ? 'exercises' : (request()->routeIs('workout-types.*') ? 'workout-types' : (request()->routeIs('progress.*') ? 'progress' : 'workouts'));
@endphp
<aside class="sidebar" id="sidebar">
<a class="brand" aria-label="Gym Tracker" href="{{ route('workouts.index') }}"><img src="{{ asset('favicon.svg') }}" width="38" height="38" alt=""><span>Gym Tracker</span></a>
<p class="sidebar-caption">TRAINING</p>
<nav aria-label="Main navigation" class="sidebar-nav">
@foreach(['workouts' => ['Workouts', 'M8 2v4m8-4v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14H3V6a2 2 0 0 1 2-2Z'], 'exercises' => ['Exercises', 'M6 7v10m12-10v10M3 9v6m18-6v6M6 12h12'], 'workout-types' => ['Workout types', 'M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h7v7h-7z'], 'progress' => ['Progress', 'M4 3v17h17M8 14l4-5 4 3 5-7']] as $key => [$label, $path])
<a href="{{ route($key.'.index') }}" aria-label="{{ $label }}" class="sidebar-link {{ $section === $key ? 'is-active' : '' }}" @if($section === $key) aria-current="page" @endif><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $path }}"/></svg><span class="sidebar-label">{{ $label }}</span></a>
@endforeach
</nav>
<div class="sidebar-account"><details class="user-menu">
<summary><span class="user-avatar" aria-hidden="true">{{ mb_substr(auth()->user()->name, 0, 1) }}</span><span class="user-name">{{ auth()->user()->name }}</span></summary>
<div class="user-dropdown"><form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Log out</button></form></div>
</details></div>
</aside>
<div class="app-shell">
<header class="topbar">
<button type="button" class="btn-secondary sidebar-toggle" aria-controls="sidebar" aria-expanded="false" aria-label="Toggle navigation">☰</button>
<div class="topbar-heading">
@hasSection('page-header')
@yield('page-header')
@else
<p class="section-title">{{ ['workouts' => 'Workouts', 'exercises' => 'Exercise library', 'workout-types' => 'Workout types', 'progress' => 'Exercise progress'][$section] }}</p>
@endif
</div>

</header>
@else
<div class="guest-shell">
<header class="guest-header">
<a class="brand" href="{{ url('/') }}"><img src="{{ asset('favicon.svg') }}" width="38" height="38" alt="">Gym Tracker</a>
<nav aria-label="Account"><a class="link mr-4" href="{{ route('login') }}">Log in</a><a class="btn" href="{{ route('register') }}">Create account</a></nav>
</header>
@endauth
<main id="main" class="page-content">
@if(session('success'))<div role="status" class="mb-6 rounded-xl border border-stone-300 bg-stone-100 p-4 text-stone-800">{{ session('success') }}</div>@endif
@if($errors->any())
<div role="alert" class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-red-900"><p class="font-semibold">Please check the following:</p><ul class="mt-2 list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
@yield('content')
</main>
<footer class="page-footer">Your training, one set at a time. <span>All weights in kilograms.</span></footer>
</div>
</body>
</html>
