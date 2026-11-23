<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">История</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <p class="mb-4 rounded-lg border border-ok-line bg-ok-bg p-4 text-ok-text">{{ session('success') }}</p>
            @endif

            @if ($entries->isEmpty())
                <p class="rounded-lg bg-card p-6 text-ink shadow-sm">
                    История пуста. Нажмите «Случайное занятие» на <a class="text-accent-text underline" href="{{ route('home') }}">главной</a>, и выпавшее занятие появится здесь.
                </p>
            @else
                <div class="overflow-hidden rounded-lg bg-card shadow-sm">
                    <ul class="divide-y divide-line">
                        @foreach ($entries as $entry)
                            <li class="flex items-start justify-between gap-4 p-4">
                                <div>
                                    <p class="font-semibold text-ink">{{ $entry->idea->title }}</p>
                                    @if ($entry->idea->description)
                                        <p class="mt-1 text-sm text-ink-soft">{{ \Illuminate\Support\Str::limit($entry->idea->description, 140) }}</p>
                                    @endif
                                </div>
                                <time class="shrink-0 text-xs text-mute">{{ $entry->created_at?->format('d.m.Y H:i') }}</time>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="mt-6">{{ $entries->links() }}</div>

                <form method="POST" action="{{ route('history.clear') }}" class="mt-6"
                      x-data
                      x-on:submit="if (! confirm('Очистить всю историю?')) $event.preventDefault()">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-lg border border-err-line px-4 py-2 text-sm font-medium text-err-text hover:bg-err-bg">
                        Очистить историю
                    </button>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
