<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    {{-- Блок с сообщением об успехе (появляется только если оно есть) --}}
       {{-- Блок с сообщением об успехе --}}
    @if (session('success'))
        <div id="success-toast" class="mb-4 p-4 bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200 rounded-lg shadow-sm flex justify-between items-center transition-opacity duration-500 ease-in-out">
            <span class="font-medium">{{ session('success') }}</span>
            <button onclick="hideToast()" class="ml-4 text-green-700 dark:text-green-200 hover:text-green-900 dark:hover:text-green-100 font-bold text-xl leading-none focus:outline-none" title="Закрыть">
                &times;
            </button>
        </div>

        <script>
            // Функция для скрытия сообщения
            function hideToast() {
                const toast = document.getElementById('success-toast');
                if (toast) {
                    toast.style.opacity = '0'; // Запускаем анимацию исчезновения
                    setTimeout(() => {
                        toast.style.display = 'none'; // Полностью убираем из потока
                    }, 500); // Ждем 500мс, пока пройдет CSS-анимация
                }
            }

            // Запускаем таймер автоматически при загрузке страницы
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(hideToast, 5000); // 5000 мс = 5 секунд
            });
        </script>
    @endif

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @include('partials.random-idea', ['action' => route('dashboard')])

                    @if (auth()->user()->isAdmin())
                        <div class="mt-8 border-t border-gray-200 pt-4 dark:border-gray-700">
                            <a href="{{ route('admin.suggested-ideas.index') }}" class="text-sm font-medium text-indigo-600 hover:underline dark:text-indigo-300">
                                Модерация предложенных идей →
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Плавающая кнопка "Предложить идею" --}}
    <a href="{{ route('suggest.create') }}" 
       class="fixed bottom-8 right-8 bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-6 rounded-full shadow-lg hover:shadow-xl transition-all duration-200 flex items-center gap-2 z-50"
       style="position: fixed; bottom: 2rem; right: 2rem;">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
        </svg>
        Предложить идею
    </a>
</x-app-layout>
