<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Đăng nhập' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[var(--surface-muted)]">
    <main class="mx-auto flex min-h-screen max-w-md items-center px-4">
        <section class="panel w-full p-6 sm:p-8">
            {{ $slot }}
        </section>
    </main>

    @stack('scripts')
</body>

</html>


