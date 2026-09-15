@extends('layouts.app')

@section('title', 'Today')

@section('content')
    @php
        $eatingPass = $day['eating'] === 'pass';
        $walkingPass = $day['walking'] === 'pass';
    @endphp

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="card bg-base-100 border border-base-300 shadow-sm lg:col-span-2">
            <div class="card-body">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-sm text-base-content/60">{{ $day['date'] }}</p>
                        <h1 class="text-3xl font-semibold">{{ $day['calories_remaining'] }} left</h1>
                        <p class="text-base-content/70">
                            {{ $day['calories_eaten'] }} / {{ $day['calories_goal'] }} calories
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <span class="badge {{ $eatingPass ? 'badge-success' : 'badge-error' }}">
                            Eating {{ $eatingPass ? 'pass' : 'fail' }}
                        </span>
                        @if ($day['walking'] === 'rest')
                            <span class="badge badge-ghost">Walking rest</span>
                        @else
                            <span class="badge {{ $walkingPass ? 'badge-success' : 'badge-error' }}">
                                Walking {{ $walkingPass ? 'pass' : 'fail' }}
                            </span>
                        @endif
                    </div>
                </div>

                <form method="POST" action="{{ route('food-logs.store') }}" class="join w-full max-w-xl mt-4">
                    @csrf
                    <input
                        type="text"
                        name="input"
                        class="input input-bordered join-item w-full"
                        placeholder="What did you eat?"
                        required
                        autofocus
                    >
                    <button type="submit" class="btn btn-primary join-item">Log food</button>
                </form>
                @error('input')
                    <p class="text-error text-sm mt-2">{{ $message }}</p>
                @enderror

                <ul class="mt-4 divide-y divide-base-300">
                    @forelse ($day['food_logs'] as $log)
                        <li class="py-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <form method="POST" action="{{ route('food-logs.update', $log['id']) }}" class="flex flex-wrap items-center gap-2 flex-1">
                                    @csrf
                                    @method('PATCH')
                                    <input
                                        type="text"
                                        name="name"
                                        value="{{ $log['name'] }}"
                                        class="input input-bordered input-sm flex-1 min-w-40"
                                        required
                                    >
                                    <input
                                        type="number"
                                        name="calories"
                                        value="{{ $log['calories'] }}"
                                        min="1"
                                        class="input input-bordered input-sm w-24 tabular-nums"
                                        required
                                    >
                                    <button type="submit" class="btn btn-sm">Save</button>
                                </form>
                                <form method="POST" action="{{ route('food-logs.duplicate', $log['id']) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm">Again</button>
                                </form>
                                <form method="POST" action="{{ route('food-logs.destroy', $log['id']) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-sm text-error">Delete</button>
                                </form>
                            </div>
                        </li>
                    @empty
                        <li class="py-2 text-base-content/60">Nothing logged yet — that's a fail until you do.</li>
                    @endforelse
                </ul>
            </div>
        </section>

        <section class="card bg-base-100 border border-base-300 shadow-sm">
            <div class="card-body">
                <h2 class="card-title">Walks</h2>
                <p class="text-base-content/70">
                    @if ($day['walking'] === 'rest')
                        Sunday rest — no walk required.
                    @else
                        {{ $day['miles_walked'] }} / {{ $day['miles_goal'] }} miles
                    @endif
                </p>
                <form method="POST" action="{{ route('walks.store') }}" class="join w-full">
                    @csrf
                    <input
                        type="number"
                        name="miles"
                        step="0.01"
                        min="0.01"
                        class="input input-bordered join-item w-full"
                        placeholder="Miles"
                        required
                    >
                    <button type="submit" class="btn btn-secondary join-item">Log walk</button>
                </form>
                @error('miles')
                    <p class="text-error text-sm mt-2">{{ $message }}</p>
                @enderror
                <ul class="mt-4 divide-y divide-base-300">
                    @forelse ($day['walks'] as $walk)
                        <li class="py-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <form method="POST" action="{{ route('walks.update', $walk['id']) }}" class="join flex-1">
                                    @csrf
                                    @method('PATCH')
                                    <input
                                        type="number"
                                        name="miles"
                                        value="{{ $walk['miles'] }}"
                                        step="0.01"
                                        min="0.01"
                                        class="input input-bordered input-sm join-item w-full"
                                        required
                                    >
                                    <button type="submit" class="btn btn-sm join-item">Save</button>
                                </form>
                                <form method="POST" action="{{ route('walks.destroy', $walk['id']) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-sm text-error">Delete</button>
                                </form>
                            </div>
                        </li>
                    @empty
                        <li class="py-2 text-base-content/60">No walks yet.</li>
                    @endforelse
                </ul>
            </div>
        </section>
    </div>

    <section class="card bg-base-100 border border-base-300 shadow-sm mt-6">
        <div class="card-body">
            <form method="POST" action="{{ route('day-notes.store') }}">
                @csrf
                <label class="text-sm text-base-content/60" for="note">Note</label>
                <textarea
                    id="note"
                    name="note"
                    rows="2"
                    class="textarea textarea-bordered w-full mt-1"
                    placeholder="{{ ($day['calories_remaining'] ?? 0) < 0 ? 'What happened? Birthday, dinner out…' : 'Birthday, travel, sick…' }}"
                >{{ $day['note'] }}</textarea>
                <button type="submit" class="btn btn-sm mt-2">Save note</button>
            </form>
        </div>
    </section>

    <div class="grid gap-6 lg:grid-cols-3 mt-6">
        <section class="card bg-base-100 border border-base-300 shadow-sm lg:col-span-3">
            <div class="card-body">
                <div class="grid gap-6 lg:grid-cols-3">
                    <div>
                        <h2 class="card-title">Weigh-in</h2>
                        <p class="text-base-content/70">
                            Goal {{ number_format($weighIns['goal_pounds'], 0) }} lb
                            @if (! is_null($weighIns['remaining_pounds']))
                                ·
                                @if ($weighIns['remaining_pounds'] > 0)
                                    {{ number_format($weighIns['remaining_pounds'], 1) }} lb to go
                                @elseif ($weighIns['remaining_pounds'] < 0)
                                    {{ number_format(abs($weighIns['remaining_pounds']), 1) }} lb under goal
                                @else
                                    at goal
                                @endif
                            @endif
                        </p>
                        <p class="text-base-content/70 mt-1">
                            @if (count($weighIns['data']))
                                Latest: {{ $weighIns['data'][0]['pounds'] }} lb on {{ $weighIns['data'][0]['date'] }}
                            @else
                                No weigh-in yet.
                            @endif
                            @if (! is_null($weighIns['this_week']) && ! is_null($weighIns['last_week']))
                                · Friday {{ $weighIns['this_week'] }} lb
                                ({{ $weighIns['delta'] > 0 ? '+' : '' }}{{ $weighIns['delta'] }} vs last)
                            @endif
                        </p>
                        <form method="POST" action="{{ route('weigh-ins.store') }}" class="join w-full mt-4">
                            @csrf
                            <input
                                type="number"
                                name="pounds"
                                step="0.1"
                                min="0.1"
                                class="input input-bordered join-item w-full"
                                placeholder="Pounds"
                                required
                            >
                            <button type="submit" class="btn join-item">Save</button>
                        </form>
                        @error('pounds')
                            <p class="text-error text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div
                        class="lg:col-span-2"
                        data-controller="weight-chart"
                        data-weight-chart-goal-value="{{ $weighIns['goal_pounds'] }}"
                        data-weight-chart-points-value='@json($weighIns['series'])'
                    >
                        @if (count($weighIns['series']))
                            <svg data-weight-chart-target="canvas" class="w-full h-64" role="img" aria-label="Weight toward 205 pounds"></svg>
                        @else
                            <p class="text-base-content/60 h-64 flex items-center">Log weigh-ins to see the trend to 205 lb.</p>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <section class="card bg-base-100 border border-base-300 shadow-sm lg:col-span-2">
            <div class="card-body">
                <h2 class="card-title">{{ $calendar['month'] }}</h2>
                <div class="grid grid-cols-7 gap-2 text-center text-xs text-base-content/60">
                    <span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
                </div>
                <div class="grid grid-cols-7 gap-2 mt-2">
                    @php
                        $first = \Carbon\CarbonImmutable::parse($calendar['days'][0]['date'], config('health.timezone'));
                        $pad = (int) $first->dayOfWeek;
                    @endphp
                    @for ($i = 0; $i < $pad; $i++)
                        <div></div>
                    @endfor
                    @foreach ($calendar['days'] as $calDay)
                        <div class="rounded-box border border-base-300 p-2 min-h-16">
                            <div class="text-sm font-medium">{{ \Carbon\CarbonImmutable::parse($calDay['date'])->day }}</div>
                            <div class="text-lg leading-none mt-1">
                                <span class="{{ $calDay['eating'] === 'pass' ? 'text-success' : 'text-error' }}" title="Eating {{ $calDay['eating'] }}">
                                    {{ $calDay['eating'] === 'pass' ? '✓' : '✕' }}
                                </span>
                                @if ($calDay['walking'] !== 'rest')
                                    <span class="{{ $calDay['walking'] === 'pass' ? 'text-success' : 'text-error' }}" title="Walking {{ $calDay['walking'] }}">
                                        {{ $calDay['walking'] === 'pass' ? '✓' : '✕' }}
                                    </span>
                                @endif
                                @if ($calDay['weigh_in'])
                                    <span title="Weigh-in">⚖</span>
                                @endif
                                @if ($calDay['note'])
                                    <span title="Note">·</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    </div>
@endsection
