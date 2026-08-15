<x-layouts.app :categories="$categories" title="News &amp; Articles — U Nyi Lay Silver Shop">
    <div class="unyl-page unyl-news">
        <div class="unyl-news__header">
            <h1>News &amp; Articles</h1>
            <div class="unyl-divider"><img src="{{ asset('images/home/divider-small-leaf.svg') }}" alt="" loading="lazy" /></div>
        </div>

        <div class="unyl-news__grid">
            @foreach ($articles as $article)
                <a href="{{ url('/news-articles/' . $article['slug']) }}" class="unyl-news-card">
                    <div class="unyl-news-card__image">
                        <img src="{{ $article['image'] }}" alt="{{ $article['title'] }}" loading="lazy" />
                    </div>
                    <h2 class="unyl-news-card__title">{{ $article['title'] }}</h2>
                    <p class="unyl-news-card__excerpt">{{ $article['excerpt'] }}</p>
                    <span class="unyl-news-card__link">Read more &rarr;</span>
                </a>
            @endforeach
        </div>
    </div>
</x-layouts.app>
