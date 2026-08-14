@props(['categories' => [], 'title' => null, 'description' => null, 'bodyClass' => null])

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    @if ($description)
        <meta name="description" content="{{ $description }}">
    @endif

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
