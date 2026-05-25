@extends('index')
@section('title')
    SiteSphere
@endsection
@section('content')
    <x-layout.nav />
    <div class="md:mt-24"></div>
    
    <main class="container mx-auto px-4 mb-5">
        <h1 class="text-4xl font-bold mb-6">Welcome to SiteSphere</h1>
        <p class="text-lg mb-8">Your one-stop solution for website management and customization.</p>
        <a href="{{ route('dashboard') }}" class="inline-block bg-[var(--accent-color)] text-white px-6 py-3 rounded-lg hover:bg-[var(--accent-color-hover)] transition-colors duration-300">Get Started</a>
    </main>
    <x-layout.footer class="mt-auto"/>

@endsection
