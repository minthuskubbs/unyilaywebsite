<x-layouts.app :categories="$categories" :title="$title . ' — U Nyi Lay Silver Shop'" :body-class="($isJewelry ?? false) ? 'theme-light' : null">
    <div class="unyl-shop">
        <div class="unyl-shop__header">
            <x-shop.breadcrumb :breadcrumbs="$breadcrumbs" :current="$title" />
            <h1 class="unyl-shop__title">{{ $category['name'] }}</h1>
        </div>

        <div class="unyl-category-tiles">
            @foreach ($children as $child)
                <a href="{{ url('/product-category/' . $child['slug']) }}" class="unyl-category-tile">
                    @if ($child['image'])
                        <img src="{{ $child['image'] }}" alt="{{ $child['name'] }}" loading="lazy" />
                    @endif
                    <span class="unyl-category-tile__caption">{{ $child['name'] }}</span>
                </a>
            @endforeach
        </div>
    </div>
</x-layouts.app>
