@extends('layouts.app')

@section('title', config('app.name'))

@section('content')
    <section class="hero bg-base-100 rounded-box border border-base-300 shadow-sm">
        <div class="hero-content text-center py-16">
            <div class="max-w-2xl">
                <p class="badge badge-primary badge-outline mb-4">Ready to build</p>
                <h1 class="text-4xl font-semibold lg:text-5xl">{{ config('app.name') }}</h1>
                <p class="py-6 text-base-content/70">
                    Laravel with Tailwind CSS, daisyUI, and Hotwire. Pages navigate with Turbo;
                    interactions live in Stimulus controllers.
                </p>
                <div class="flex flex-wrap justify-center gap-2">
                    <span class="badge badge-neutral">Laravel 13</span>
                    <span class="badge badge-neutral">Tailwind CSS 4</span>
                    <span class="badge badge-neutral">daisyUI</span>
                    <span class="badge badge-neutral">Turbo</span>
                    <span class="badge badge-neutral">Stimulus</span>
                </div>
            </div>
        </div>
    </section>

    <section class="mt-8 grid gap-6 lg:grid-cols-3">
        <article class="card bg-base-100 border border-base-300 shadow-sm">
            <div class="card-body">
                <h2 class="card-title">Laravel</h2>
                <p class="text-base-content/70">
                    Blade views, SQLite for local development, and Pest for tests.
                    Add features from here.
                </p>
            </div>
        </article>

        <article class="card bg-base-100 border border-base-300 shadow-sm">
            <div class="card-body">
                <h2 class="card-title">daisyUI</h2>
                <p class="text-base-content/70">
                    Component classes such as <code class="text-sm">btn</code>,
                    <code class="text-sm">card</code>, and <code class="text-sm">input</code>
                    are available in every Blade view.
                </p>
            </div>
        </article>

        <article class="card bg-base-100 border border-base-300 shadow-sm">
            <div class="card-body">
                <h2 class="card-title">Hotwire</h2>
                <p class="text-base-content/70">
                    Turbo Drive is on for links and forms. Generate a controller with
                    <code class="text-sm">php artisan stimulus:make</code>.
                </p>
            </div>
        </article>
    </section>

    <section class="mt-8 card bg-base-100 border border-base-300 shadow-sm" data-controller="hello">
        <div class="card-body">
            <h2 class="card-title">Try Stimulus</h2>
            <p class="text-base-content/70">
                This greeting runs in the browser. No page reload, no JSON API.
            </p>
            <div class="join w-full max-w-md">
                <input
                    type="text"
                    class="input input-bordered join-item w-full"
                    placeholder="Your name"
                    autocomplete="name"
                    data-hello-target="name"
                    data-action="keydown.enter->hello#greet"
                >
                <button type="button" class="btn btn-primary join-item" data-action="hello#greet">
                    Greet
                </button>
            </div>
            <p
                class="hidden mt-2 font-medium text-success"
                tabindex="-1"
                data-hello-target="output"
            ></p>
        </div>
    </section>
@endsection
