@props(['categories' => [], 'title' => null, 'description' => null, 'bodyClass' => null, 'image' => null])

@php
    $pageTitle = $title ?? config('app.name');
    $pageDescription = $description ?? 'U Nyi Lay Silver Shop — handcrafted Burmese silverware, brass and jewelry for gifts and decorations. Loon Gu Kyaung Street, Yankin Tsp, Yangon, Myanmar. 09 506-2583, 09 512-4920, 09 509-9843, 09 501-6665.';
    $pageImage = $image ?? asset('images/brand/android-chrome-512x512.png');
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <link rel="canonical" href="{{ url()->current() }}">

    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ $pageImage }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <meta name="twitter:image" content="{{ $pageImage }}">

    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/brand/favicon-16x16.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/brand/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="48x48" href="{{ asset('images/brand/favicon-48x48.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/brand/apple-touch-icon.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Karla:wght@400;500;600&display=swap">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body @class([$bodyClass])>
    <x-layout.header :categories="$categories" />

    <main>
        {{ $slot }}
    </main>

    <x-layout.footer />
</body>
</html>
