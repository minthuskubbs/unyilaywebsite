<x-layouts.app :categories="$categories" :title="$article['title'] . ' — U Nyi Lay Silver Shop'">
    <div class="unyl-page unyl-news-article">
        <a href="{{ url('/news-articles') }}" class="unyl-news-article__back">&larr; News &amp; Articles</a>

        <header class="unyl-news-article__header">
            <h1>{{ $article['title'] }}</h1>
            @if (!empty($article['subtitle']))
                <p class="unyl-news-article__subtitle">{{ $article['subtitle'] }}</p>
            @endif
            <div class="unyl-divider"><img src="{{ asset('images/home/divider-small-leaf.svg') }}" alt="" loading="lazy" /></div>
        </header>

        <div class="unyl-news-article__image">
            <img src="{{ $article['image'] }}" alt="{{ $article['title'] }}" loading="lazy" />
        </div>

        <div class="unyl-news-article__body">
            @foreach ($article['body'] as $block)
                @if ($block['type'] === 'p')
                    <p>{{ $block['text'] }}</p>
                @elseif ($block['type'] === 'image')
                    <div class="unyl-news-article__inline-image">
                        <img src="{{ $block['src'] }}" alt="{{ $block['alt'] ?? '' }}" loading="lazy" />
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</x-layouts.app>
