@extends('index')

@section('title')
    Create Post
@endsection

@push('styles')
    @vite('resources/css/upload-post.css')
@endpush

@section('content')
    <x-layout.nav />

    <div style="font-family: var(--font-family); background-color: var(--background-color); color: var(--text-color);">
        <x-layout.upload-post :categories="$categories" />
    </div>
@endsection

@push('scripts')
    @vite('resources/js/upload-post.js')
@endpush
