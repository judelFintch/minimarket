<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=manrope:300,400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased">
        <div class="relative min-h-screen overflow-hidden bg-slate-50">
            <div class="pointer-events-none absolute -left-20 -top-40 h-96 w-96 rounded-full bg-amber-200/40 blur-3xl"></div>
            <div class="pointer-events-none absolute right-0 top-20 h-[28rem] w-[28rem] rounded-full bg-cyan-200/30 blur-3xl"></div>

            <div class="relative z-10 min-h-screen">
                <livewire:layout.navigation />

                <div class="flex min-w-0 flex-1 flex-col">
                    <!-- Page Heading -->
                    @if (isset($header))
                        <header class="border-b border-slate-200/70 bg-white/70 backdrop-blur">
                            <div class="mx-auto flex max-w-6xl items-center gap-4 px-4 py-5 sm:px-6 lg:px-8">
                                <div class="flex-1">
                                    {{ $header }}
                                </div>
                            </div>
                        </header>
                    @endif

                    <!-- Page Content -->
                    <main class="flex-1 px-4 pb-16 pt-6 sm:px-6 lg:px-8 lg:pt-8">
                        {{ $slot }}
                    </main>
                </div>
            </div>
        </div>
    </body>
</html>
