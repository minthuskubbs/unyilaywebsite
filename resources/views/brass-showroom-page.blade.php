<!doctype html>
<html lang="my">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  @php
    $isBrass = ($gallery ?? 'silver') === 'brass';
    $pageTitle = $isBrass ? 'ကြေးဝါအနုပညာပြခန်း — Brass Art Gallery' : 'ငွေထည်အနုပညာပြခန်း — Silver Art Gallery';
    $pageDescription = $isBrass
        ? 'မြန်မာ့ကြေးဝါပန်းချီလက်ရာများကို လှည့်လည်ကြည့်ရှုနိုင်သော 3D ပြခန်း။'
        : 'မြန်မာ့ငွေပန်းချီလက်ရာများကို လှည့်လည်ကြည့်ရှုနိုင်သော 3D ပြခန်း။';
    $pageImage = asset('images/brand/android-chrome-512x512.png');
  @endphp
  <meta name="description" content="{{ $pageDescription }}">
  <title>{{ $pageTitle }}</title>
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
</head>
<body class="bs-page">
  @include('brass-showroom')
</body>
</html>
