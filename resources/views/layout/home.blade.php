@extends('dashboard')
@section('title')
    Home
@endsection
@section('content')
    <x-layout.nav />
    <main class="min-h-screen bg-slate-50 px-4 pb-24 pt-24 sm:px-6 md:pt-28">
        <section class="mx-auto flex w-full max-w-5xl justify-center">
            <x-layout.post-card />
        </section>
    </main>
@endsection
