@props(['tree' => [], 'activeSlug' => null])

<aside class="unyl-shop-sidebar">
    <div class="unyl-shop-sidebar__card">
        <h4 class="unyl-shop-sidebar__title">Browse</h4>
        <ul class="unyl-shop-sidebar__list">
            @foreach ($tree as $cat)
                <li class="{{ $activeSlug === $cat['slug'] ? 'is-active' : '' }}">
                    <a href="{{ url('/product-category/' . $cat['slug']) }}">{{ $cat['name'] }}</a>
                    @if (!empty($cat['children']))
                        <ul>
                            @foreach ($cat['children'] as $child)
                                <li class="{{ $activeSlug === $child['slug'] ? 'is-active' : '' }}">
                                    <a href="{{ url('/product-category/' . $child['slug']) }}">{{ $child['name'] }}</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
</aside>
