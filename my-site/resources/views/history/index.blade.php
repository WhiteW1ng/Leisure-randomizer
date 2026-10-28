<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">История</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <p class="mb-4 rounded-lg border border-green-200 bg-green-50 p-4 text-green-700">{{ session('success') }}</p>
            @endif

            @if ($entries->isEmpty())
                <p class="rounded-lg bg-white p-6 text-gray-700 shadow-sm dark:bg-gray-800 dark:text-gray-200">
                    История пуста. Нажмите «Случайное занятие» на <a class="text-indigo-600 underline" href="{{ route('dashboard') }}">дашборде</a>, и выпавшее занятие появится здесь.
                </p>
            @else
                <div class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($entries as $entry)
                            <li class="flex items-start justify-between gap-4 p-4">
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $entry->idea->title }}</p>
                                    @if ($entry->idea->description)
                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ \Illuminate\Support\Str::limit($entry->idea->description, 140) }}</p>
                                    @endif
                                </div>
                                <time class="shrink-0 text-xs text-gray-500 dark:text-gray-400">{{ $entry->created_at?->format('d.m.Y H:i') }}</time>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="mt-6">{{ $entries->links() }}</div>

                <form method="POST" action="{{ route('history.clear') }}" class="mt-6"
                      onsubmit="return confirm('Очистить всю историю?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-lg border border-red-300 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50">
                        Очистить историю
                    </button>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
