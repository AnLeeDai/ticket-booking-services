<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Quản lý đặt vé' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <div class="app-shell">
        <header class="sticky top-0 z-30 border-b border-[var(--line)] bg-white/90 backdrop-blur">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-3">
                    <button type="button" class="btn-secondary md:hidden" data-sidebar-toggle>Menu</button>
                    <a href="{{ route('dashboard') }}" class="text-base font-semibold text-[var(--text)]">Quản lý đặt vé</a>
                </div>

                <nav class="hidden items-center gap-1 md:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard') || request()->routeIs('home')">Tổng quan</x-nav-link>
                    <x-nav-link :href="route('categories.index')" :active="request()->routeIs('categories.*')">Thể loại</x-nav-link>
                    <x-nav-link :href="route('movies.index')" :active="request()->routeIs('movies.*')">Phim</x-nav-link>
                    <x-nav-link :href="route('cinemas.index')" :active="request()->routeIs('cinemas.*')">Rạp chiếu</x-nav-link>
                    <x-nav-link :href="route('showtimes.index')" :active="request()->routeIs('showtimes.*')">Suất chiếu</x-nav-link>
                    <x-nav-link :href="route('combos.index')" :active="request()->routeIs('combos.*')">Combo</x-nav-link>
                    <x-nav-link :href="route('booking.index')" :active="request()->routeIs('booking.*')">Đặt vé</x-nav-link>
                    <x-nav-link :href="route('profile.index')" :active="request()->routeIs('profile.*')">Hồ sơ</x-nav-link>
                </nav>
            </div>
        </header>

        <div class="mx-auto grid max-w-7xl grid-cols-1 gap-6 px-4 py-6 sm:px-6 lg:grid-cols-[240px_1fr] lg:px-8">
            <aside class="panel hidden p-3 md:block" data-sidebar>
                <div class="space-y-1">
                    <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard') || request()->routeIs('home')">Tổng quan</x-sidebar-link>
                    <x-sidebar-link :href="route('categories.index')" :active="request()->routeIs('categories.*')">Thể loại</x-sidebar-link>
                    <x-sidebar-link :href="route('movies.index')" :active="request()->routeIs('movies.*')">Phim</x-sidebar-link>
                    <x-sidebar-link :href="route('cinemas.index')" :active="request()->routeIs('cinemas.*')">Rạp chiếu</x-sidebar-link>
                    <x-sidebar-link :href="route('showtimes.index')" :active="request()->routeIs('showtimes.*')">Suất chiếu</x-sidebar-link>
                    <x-sidebar-link :href="route('combos.index')" :active="request()->routeIs('combos.*')">Combo</x-sidebar-link>
                    <x-sidebar-link :href="route('booking.index')" :active="request()->routeIs('booking.*')">Đặt vé</x-sidebar-link>
                    <x-sidebar-link :href="route('profile.index')" :active="request()->routeIs('profile.*')">Hồ sơ</x-sidebar-link>
                    <x-sidebar-link :href="route('login')" :active="request()->routeIs('login')">Đăng nhập</x-sidebar-link>
                </div>
            </aside>

            <main class="min-w-0 space-y-6">
                {{ $slot }}
            </main>
        </div>
    </div>

    @stack('scripts')
</body>

</html>


