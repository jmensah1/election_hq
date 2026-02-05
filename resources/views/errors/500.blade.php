@extends('errors.layout')

@section('title', 'Server Error')
@section('code', '500')
@section('message', 'Internal Server Error')

@section('icon')
    <div class="h-24 w-24 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
        <svg class="h-12 w-12 text-red-600 dark:text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
        </svg>
    </div>
@endsection

@section('description')
    Oops, something went wrong on our servers. We're working to fix it. Please try again later.
@endsection

@section('actions')
    <button onclick="window.location.reload()" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-3 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
        Refresh Page
    </button>
@endsection
