@extends('errors.layout')

@section('title', 'Access Denied')
@section('code', '403')
@section('message', 'Access Denied')

@section('icon')
    <div class="h-24 w-24 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
        <svg class="h-12 w-12 text-red-600 dark:text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
        </svg>
    </div>
@endsection

@section('description')
    Sorry, you do not have permission to access this page. Please contact your administrator if you believe this is a mistake.
@endsection
