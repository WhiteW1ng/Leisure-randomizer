<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Модерация предложенных идей
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Сообщение об успехе --}}
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Сообщение об ошибке --}}
            @if (session('error'))
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold mb-4 text-gray-900 dark:text-gray-100">Всего идей: {{ $ideas->count() }}</h3>

                @if($ideas->isEmpty())
                    <p class="text-gray-500 dark:text-gray-400">Пока нет предложенных идей.</p>
                @else
                    <div class="space-y-4">
                        @foreach($ideas as $idea)
                            <div class="border rounded-lg p-4 
                                @if($idea->status === 'approved') bg-green-50 dark:bg-green-900/20 border-green-300 dark:border-green-700
                                @elseif($idea->status === 'rejected') bg-red-50 dark:bg-red-900/20 border-red-300 dark:border-red-700
                                @else bg-yellow-50 dark:bg-yellow-900/20 border-yellow-300 dark:border-yellow-700 @endif">
                                
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h4 class="font-bold text-lg text-black">{{ $idea->title }}</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            От: {{ $idea->user->name ?? 'Неизвестно' }} 
                                            ({{ $idea->user->email ?? '' }})
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-500">
                                            {{ $idea->created_at->format('d.m.Y H:i') }}
                                        </p>
                                    </div>
                                    
                                    {{-- Бейдж статуса --}}
                                    <span class="px-3 py-1 rounded-full text-xs font-bold
                                        @if($idea->status === 'approved') bg-green-200 text-green-800
                                        @elseif($idea->status === 'rejected') bg-red-200 text-red-800
                                        @else bg-yellow-200 text-yellow-800 @endif">
                                        @if($idea->status === 'approved') Одобрено
                                        @elseif($idea->status === 'rejected') Отклонено
                                        @else На рассмотрении @endif
                                    </span>
                                </div>

                                <p class="text-gray-700 dark:text-gray-300 mb-3">{{ $idea->description }}</p>

                                {{-- Фильтры идеи (отображаем массивы из JSON) --}}
                                <div class="flex flex-wrap gap-2 mb-3 text-sm">
                                    @if(!empty($idea->seasons))
                                        @foreach($idea->seasons as $season)
                                            <span class="bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-2 py-1 rounded">
                                                {{ $season }}
                                            </span>
                                        @endforeach
                                    @endif
                                    
                                    @if(!empty($idea->companies))
                                        @foreach($idea->companies as $company)
                                            <span class="bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-2 py-1 rounded">
                                                {{ $company }}
                                            </span>
                                        @endforeach
                                    @endif
                                    
                                    @if(!empty($idea->activity_levels))
                                        @foreach($idea->activity_levels as $level)
                                            <span class="bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-2 py-1 rounded">
                                                {{ $level }}
                                            </span>
                                        @endforeach
                                    @endif
                                    
                                    @if(!empty($idea->budgets))
                                        @foreach($idea->budgets as $budget)
                                            <span class="bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-2 py-1 rounded">
                                                 {{ $budget }}
                                            </span>
                                        @endforeach
                                    @endif
                                    
                                    @if(!empty($idea->durations))
                                        @foreach($idea->durations as $duration)
                                            <span class="bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-2 py-1 rounded">
                                                {{ $duration }}
                                            </span>
                                        @endforeach
                                    @endif
                                    
                                    @if(!empty($idea->location_types))
                                        @foreach($idea->location_types as $locationType)
                                            <span class="bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-2 py-1 rounded">
                                                {{ $locationType }}
                                            </span>
                                        @endforeach
                                    @endif
                                </div>

                                {{-- Кнопки действий (только если на рассмотрении) --}}
                                @if($idea->status === 'pending')
                                    <div class="flex gap-2">
                                        <form method="POST" action="{{ route('admin.suggested-ideas.approve', $idea->id) }}">
                                            @csrf
                                            <button type="submit" 
                                                    style="background-color: #16a34a; color: white; font-weight: bold; padding: 8px 16px; border-radius: 6px; border: none; cursor: pointer;"
                                                    onmouseover="this.style.backgroundColor='#15803d'" 
                                                    onmouseout="this.style.backgroundColor='#16a34a'">
                                                Одобрить
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.suggested-ideas.reject', $idea->id) }}">
                                            @csrf
                                            <button type="submit" 
                                                    style="background-color: #dc2626; color: white; font-weight: bold; padding: 8px 16px; border-radius: 6px; border: none; cursor: pointer;"
                                                    onmouseover="this.style.backgroundColor='#b91c1c'" 
                                                    onmouseout="this.style.backgroundColor='#dc2626'">
                                                Отклонить
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>