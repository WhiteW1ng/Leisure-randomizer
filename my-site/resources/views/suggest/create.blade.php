<x-guest-layout>
    <div class="max-w-3xl mx-auto mt-10 p-6 bg-white rounded-lg shadow-md">
        <h2 class="text-2xl font-bold mb-6 text-gray-800">Предложить свою идею досуга</h2>

        @if (session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('suggest.store') }}">
            @csrf

            <!-- Название идеи -->
            <div class="mb-4">
                <label for="title" class="block text-sm font-medium text-gray-700">Название идеи *</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                @error('title')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Описание -->
            <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-gray-700">Описание</label>
                <textarea name="description" id="description" rows="4" 
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description') }}</textarea>
            </div>

            <!-- Сезоны -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Сезон *</label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    @foreach($seasons as $season)
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="seasons[]" value="{{ $season->name }}" 
                                   {{ in_array($season->name, old('seasons', [])) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm">
                            <span class="text-sm text-gray-700">{{ $season->name }}</span>
                        </label>
                    @endforeach
                </div>
                @error('seasons')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Компания -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Компания *</label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    @foreach($companies as $company)
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="companies[]" value="{{ $company->name }}" 
                                   {{ in_array($company->name, old('companies', [])) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm">
                            <span class="text-sm text-gray-700">{{ $company->name }}</span>
                        </label>
                    @endforeach
                </div>
                @error('companies')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Уровень активности -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Уровень активности *</label>
                <div class="grid grid-cols-3 gap-2">
                    @foreach($activityLevels as $level)
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="activity_levels[]" value="{{ $level->name }}" 
                                   {{ in_array($level->name, old('activity_levels', [])) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm">
                            <span class="text-sm text-gray-700">{{ $level->name }}</span>
                        </label>
                    @endforeach
                </div>
                @error('activity_levels')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Бюджет -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Бюджет *</label>
                <div class="grid grid-cols-3 gap-2">
                    @foreach($budgets as $budget)
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="budgets[]" value="{{ $budget->name }}" 
                                   {{ in_array($budget->name, old('budgets', [])) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm">
                            <span class="text-sm text-gray-700">{{ $budget->name }}</span>
                        </label>
                    @endforeach
                </div>
                @error('budgets')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Длительность -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Длительность *</label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    @foreach($durations as $duration)
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="durations[]" value="{{ $duration->name }}" 
                                   {{ in_array($duration->name, old('durations', [])) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm">
                            <span class="text-sm text-gray-700">{{ $duration->name }}</span>
                        </label>
                    @endforeach
                </div>
                @error('durations')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Тип локации -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Тип локации *</label>
                <div class="grid grid-cols-3 gap-2">
                    @foreach($locationTypes as $locationType)
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="location_types[]" value="{{ $locationType->name }}" 
                                   {{ in_array($locationType->name, old('location_types', [])) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm">
                            <span class="text-sm text-gray-700">{{ $locationType->name }}</span>
                        </label>
                    @endforeach
                </div>
                @error('location_types')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Кнопка отправки -->
            <div class="flex items-center justify-end">
                <button type="submit" 
                        style="background-color: #2563eb; color: white; font-weight: bold; padding: 12px 24px; border-radius: 8px; border: none; cursor: pointer;"
                        onmouseover="this.style.backgroundColor='#1d4ed8'" 
                        onmouseout="this.style.backgroundColor='#2563eb'">
                    Отправить идею
                </button>
            </div>
        </form>
    </div>
</x-guest-layout>