<nav x-data="{ open: false }" class="bg-card border-b border-line">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 font-serif text-xl text-ink">
                        <span class="text-accent" aria-hidden="true">✦</span>
                        <span>Досуг-Рандом</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('home')" :active="request()->routeIs('home')">
                        Главная
                    </x-nav-link>
                    <x-nav-link :href="route('catalog')" :active="request()->routeIs('catalog')">
                        Каталог
                    </x-nav-link>
                    <x-nav-link :href="route('suggest.create')" :active="request()->routeIs('suggest.*')">
                        Предложить идею
                    </x-nav-link>
                    @auth
                        <x-nav-link :href="route('favorites.index')" :active="request()->routeIs('favorites.*')">
                            Избранное
                        </x-nav-link>
                        <x-nav-link :href="route('history.index')" :active="request()->routeIs('history.*')">
                            История
                        </x-nav-link>
                        @if (Auth::user()->isAdmin())
                            <x-nav-link :href="route('admin.suggested-ideas.index')" :active="request()->routeIs('admin.*')">
                                Модерация
                            </x-nav-link>
                        @endif
                    @endauth
                </div>
            </div>

            <!-- Right side: профиль для вошедших, вход и регистрация для гостей -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-mute bg-card hover:text-ink focus:outline-none transition ease-in-out duration-150">
                                <div>{{ Auth::user()->name }}</div>

                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                Профиль
                            </x-dropdown-link>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    Выйти
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <a href="{{ route('login') }}"
                       class="rounded-md px-4 py-2 text-sm font-medium text-ink hover:text-ink">
                        Войти
                    </a>
                    <a href="{{ route('register') }}"
                       class="ms-2 rounded-lg bg-accent px-4 py-2 text-sm font-semibold text-accent-ink shadow-sm hover:bg-accent-hover">
                        Регистрация
                    </a>
                @endauth
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-mute hover:text-mute hover:bg-chip focus:outline-none focus:bg-chip focus:text-mute transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('home')" :active="request()->routeIs('home')">
                Главная
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('catalog')" :active="request()->routeIs('catalog')">
                Каталог
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('suggest.create')" :active="request()->routeIs('suggest.*')">
                Предложить идею
            </x-responsive-nav-link>
            @auth
                <x-responsive-nav-link :href="route('favorites.index')" :active="request()->routeIs('favorites.*')">
                    Избранное
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('history.index')" :active="request()->routeIs('history.*')">
                    История
                </x-responsive-nav-link>
                @if (Auth::user()->isAdmin())
                    <x-responsive-nav-link :href="route('admin.suggested-ideas.index')" :active="request()->routeIs('admin.*')">
                        Модерация
                    </x-responsive-nav-link>
                @endif
            @endauth
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-3 border-t border-line">
            @auth
                <div class="px-4">
                    <div class="font-medium text-base text-ink">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-mute">{{ Auth::user()->email }}</div>
                </div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')">
                        Профиль
                    </x-responsive-nav-link>

                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <x-responsive-nav-link :href="route('logout')"
                                onclick="event.preventDefault();
                                            this.closest('form').submit();">
                            Выйти
                        </x-responsive-nav-link>
                    </form>
                </div>
            @else
                <div class="space-y-1">
                    <x-responsive-nav-link :href="route('login')">
                        Войти
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('register')">
                        Регистрация
                    </x-responsive-nav-link>
                </div>
            @endauth
        </div>
    </div>
</nav>
