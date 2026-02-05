@extends('errors.layout')

@section('title', 'Page Not Found')
@section('code', '404')
@section('message', 'Page Not Found')

@section('icon')
    <div class="h-24 w-24 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
        <svg class="h-12 w-12 text-blue-600 dark:text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
        </svg>
    </div>
@endsection

@section('description')
    Sorry, the page you are looking for does not exist. It might have been moved or deleted.
@endsection
