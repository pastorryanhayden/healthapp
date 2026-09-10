<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light" data-controller="theme">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="turbo-refresh-method" content="morph">

        <title>@yield('title', config('app.name'))</title>

        <script>
            (() => {
                const stored = localStorage.getItem('theme')
                const theme = stored === 'light' || stored === 'dark'
                    ? stored
                    : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
                document.documentElement.setAttribute('data-theme', theme)
            })()
        </script>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-base-200 text-base-content">
        <div class="bg-base-100 border-b border-base-300">
            <div class="navbar container mx-auto px-4">
                <div class="flex-1">
                    <a href="{{ url('/') }}" class="btn btn-ghost text-xl">{{ config('app.name') }}</a>
                </div>
                <div class="flex-none">
                    <button
                        type="button"
                        class="btn btn-ghost"
                        data-action="theme#toggle"
                        aria-label="Toggle color theme"
                    >
                        Theme
                    </button>
                </div>
            </div>
        </div>

        <main class="container mx-auto px-4 py-10">
            @yield('content')
        </main>
    </body>
</html>
