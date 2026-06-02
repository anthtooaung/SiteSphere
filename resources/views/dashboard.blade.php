@extends('index')

@section('title')
    Dashboard
@endsection

@section('content')
    @php
        $dashboardMenuLocation = in_array($menuBarLocation ?? 'left', ['top', 'right', 'bottom', 'left'], true)
            ? $menuBarLocation
            : 'left';
    @endphp

    <x-layout.nav />

    <div class="dashboard-page dashboard-page--{{ $dashboardMenuLocation }}">
        <x-layout.menu :menu-bar-location="$dashboardMenuLocation" />

        <main class="dashboard-content" aria-labelledby="dashboardTitle">
            <section class="dashboard-panel">
                <p class="dashboard-kicker">Dashboard</p>
                <h1 id="dashboardTitle">Welcome back, {{ auth()->user()->name }}</h1>
                <p>
                    Your workspace is ready.
                </p>
            </section>
        </main>
    </div>
@endsection
