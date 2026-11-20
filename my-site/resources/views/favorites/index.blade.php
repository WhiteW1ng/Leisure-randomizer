<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">Избранное</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @if ($ideas->isEmpty())
                <p class="rounded-lg bg-card p-6 text-ink shadow-sm">
                    В избранном пока пусто. Нажмите «☆ В избранное» у занятия на
                    <a class="text-accent-text underline" href="{{ route('home') }}">главной</a> или в
                    <a class="text-accent-text underline" href="{{ route('catalog') }}">каталоге</a>.
                </p>
            @else
                <div class="grid gap-5 sm:grid-cols-2">
                    @foreach ($ideas as $idea)
                        @include('partials.idea-card', ['idea' => $idea, 'favoriteIds' => $favoriteIds])
                    @endforeach
                </div>
                <div class="mt-8">{{ $ideas->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
