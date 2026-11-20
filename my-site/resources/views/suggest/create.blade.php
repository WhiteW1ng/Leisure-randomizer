<x-app-layout>
    <div class="max-w-3xl mx-auto mt-10 p-6 bg-card rounded-2xl shadow-md">
        <h2 class="text-2xl font-semibold mb-6 text-ink">Предложить свою идею досуга</h2>

        @if (session('success'))
            <div class="mb-4 p-4 bg-ok-bg text-ok-text rounded">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('suggest.store') }}">
            @csrf

            {{-- Все ошибки проверки одним списком: у описания и у групп галочек своих сообщений в форме нет --}}
            @if ($errors->any())
                <div class="mb-4 rounded-xl border border-err-line bg-err-bg p-4 text-sm text-err-text" role="alert">
                    <p class="font-semibold">Идея не отправлена. Исправьте:</p>
                    <ul class="mt-1 list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Название идеи -->
            <div class="mb-4">
                <label for="title" class="block text-sm font-medium text-ink">Название идеи *</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" minlength="3" maxlength="200"
                       class="mt-1 block w-full rounded-md border-line shadow-sm focus:border-accent focus:ring-accent" required>
                @error('title')
                    <p class="text-err-text text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Описание -->
            <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-ink">Описание</label>
                <textarea name="description" id="description" rows="4" maxlength="2000"
                          class="mt-1 block w-full rounded-md border-line shadow-sm focus:border-accent focus:ring-accent">{{ old('description') }}</textarea>
            </div>

            <!-- Сезоны -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-ink mb-2">Сезон *</label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    @foreach($seasons as $season)
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="seasons[]" value="{{ $season->name }}" 
                                   {{ in_array($season->name, old('seasons', [])) ? 'checked' : '' }}
                                   class="rounded border-line bg-field text-accent shadow-sm">
                            <span class="text-sm text-ink">{{ $season->name }}</span>
                        </label>
                    @endforeach
                </div>
                @error('seasons')
                    <p class="text-err-text text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Компания -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-ink mb-2">Компания *</label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    @foreach($companies as $company)
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="companies[]" value="{{ $company->name }}" 
                                   {{ in_array($company->name, old('companies', [])) ? 'checked' : '' }}
                                   class="rounded border-line bg-field text-accent shadow-sm">
                            <span class="text-sm text-ink">{{ $company->name }}</span>
                        </label>
                    @endforeach
                </div>
                @error('companies')
                    <p class="text-err-text text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Уровень активности -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-ink mb-2">Уровень активности *</label>
                <div class="grid grid-cols-3 gap-2">
                    @foreach($activityLevels as $level)
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="activity_levels[]" value="{{ $level->name }}" 
                                   {{ in_array($level->name, old('activity_levels', [])) ? 'checked' : '' }}
                                   class="rounded border-line bg-field text-accent shadow-sm">
                            <span class="text-sm text-ink">{{ $level->name }}</span>
                        </label>
                    @endforeach
                </div>
                @error('activity_levels')
                    <p class="text-err-text text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Бюджет -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-ink mb-2">Бюджет *</label>
                <div class="grid grid-cols-3 gap-2">
                    @foreach($budgets as $budget)
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="budgets[]" value="{{ $budget->name }}" 
                                   {{ in_array($budget->name, old('budgets', [])) ? 'checked' : '' }}
                                   class="rounded border-line bg-field text-accent shadow-sm">
                            <span class="text-sm text-ink">{{ $budget->name }}</span>
                        </label>
                    @endforeach
                </div>
                @error('budgets')
                    <p class="text-err-text text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Длительность -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-ink mb-2">Длительность *</label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    @foreach($durations as $duration)
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="durations[]" value="{{ $duration->name }}" 
                                   {{ in_array($duration->name, old('durations', [])) ? 'checked' : '' }}
                                   class="rounded border-line bg-field text-accent shadow-sm">
                            <span class="text-sm text-ink">{{ $duration->name }}</span>
                        </label>
                    @endforeach
                </div>
                @error('durations')
                    <p class="text-err-text text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Тип локации -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-ink mb-2">Тип локации *</label>
                <div class="grid grid-cols-3 gap-2">
                    @foreach($locationTypes as $locationType)
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="location_types[]" value="{{ $locationType->name }}" 
                                   {{ in_array($locationType->name, old('location_types', [])) ? 'checked' : '' }}
                                   class="rounded border-line bg-field text-accent shadow-sm">
                            <span class="text-sm text-ink">{{ $locationType->name }}</span>
                        </label>
                    @endforeach
                </div>
                @error('location_types')
                    <p class="text-err-text text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Кнопка отправки -->
            <div class="flex items-center justify-end">
                <button type="submit"
                        class="rounded-full bg-accent px-6 py-3 font-medium text-accent-ink shadow-sm hover:bg-accent-hover">
                    Отправить идею
                </button>
            </div>
        </form>
    </div>
</x-app-layout>