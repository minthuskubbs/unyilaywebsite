<?php

namespace App\Http\Controllers;

use App\Services\CategoryService;
use App\Services\ProductService;

class HomeController extends Controller
{
    public function __construct(
        private CategoryService $categories,
        private ProductService $products,
    ) {
    }

    public function index()
    {
        return view('home', [
            'categories' => $this->categories->megaMenuGroups(),
            'popularProducts' => $this->products->popular(8),
        ]);
    }
}
