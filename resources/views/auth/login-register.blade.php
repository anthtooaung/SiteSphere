@extends('index')
@section('title')
    {{$isLogin ? 'Login' : 'Register'}}
@endsection
@section('content')
    <div class="container mx-auto mt-10 ">

{{--        Login Form --}}
        <form action="{{}}"></form>
        <x-input-field id="email">
            <x-slot:name >
                Email
            </x-slot:name>
            <x-slot>
                <svg class="w-4 h-4 text-body" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="m3.5 5.5 7.893 6.036a1 1 0 0 0 1.214 0L20.5 5.5M4 19h16a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1Z"/></svg>
            </x-slot>
        </x-input-field>
    </div>
@endsection
