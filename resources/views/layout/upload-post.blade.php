@extends('index')

@section('title')
    Create Post
@endsection

@push('styles')
    @vite('resources/css/upload-post.css')
@endpush

@section('content')
    <x-layout.nav />

    <x-layout.upload-post :categories="$categories" />
@endsection

@push('scripts')
    @vite('resources/js/upload-post.js')
@endpush
