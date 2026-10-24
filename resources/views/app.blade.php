<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title inertia>{{ config('app.name', 'crow.asia') }}</title>
        <link rel="stylesheet" href="/fonts/crow/fonts.css">
        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.tsx'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased text-[#111113] bg-[#FAF8F5]">
        @inertia
    </body>
</html>
