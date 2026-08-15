<?php

namespace App\Http\Controllers;

use App\Services\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PageController extends Controller
{
    /**
     * Articles are static/hand-authored (source content lives in the old
     * WordPress theme's Elementor pages, not a queryable "posts" list), so
     * they're kept here as data rather than read from the WordPress DB.
     */
    private const ARTICLES = [
        'what-is-925' => [
            'title' => 'What is 925?',
            'subtitle' => 'Silver? Or other metal?',
            'excerpt' => '"Do you have the 925? And not the silver?" We hear that question many times — most customers think 925 is a different kind of metal from silver.',
            'image' => 'https://unyilaysilver.com/wp-content/uploads/2020/07/pocket-watch-560937_1280.jpg',
            'date' => '2021-07-12',
            'body' => [
                ['type' => 'p', 'text' => '"Do you have the 925? And not the silver?" We hear that kind of question many times, because most customers ask it. They think 925 is a different kind of metal.'],
                ['type' => 'p', 'text' => 'As the first topic in this blog, we\'ll explain the difference between silver and 925 that confuses many customers.'],
                ['type' => 'p', 'text' => 'When jewelry is made with 100% silver, it\'s difficult to achieve some product designs because the silver is too soft. So it needs to be mixed with another kind of metal. Mixing standards differ between countries — the world standard is Britain\'s Sterling Standard, mixing silver (92.5%) with copper or another alloy. That\'s what people casually call "925".'],
                ['type' => 'image', 'src' => 'https://unyilaysilver.com/wp-content/uploads/2020/07/Silver.png', 'alt' => 'Ref: antiquesilver.org'],
                ['type' => 'p', 'text' => 'Actually, 925 is not a different metal — it\'s silver. Most jewelry shops don\'t explain this well, so people get confused. Silver product designs vary by country of origin, but the quality itself doesn\'t differ much between them.'],
                ['type' => 'p', 'text' => 'To prevent tarnishing easily, some pieces are soaked in rhodium for a protective top layer — but after about a year the color fades and it needs to be re-plated.'],
                ['type' => 'p', 'text' => 'Rhodium\'s downside is that it\'s expensive, which is why well-known silverware stores\' products cost more. It also only works for jewelry-scale pieces, not larger items.'],
                ['type' => 'p', 'text' => 'Can silver content go higher than 92.5%? Yes — almost 100% is achievable for some products, as silversmithing techniques improve designs that weren\'t possible before. But at that purity it becomes very soft and needs to be handled carefully. That\'s some silver knowledge for you.'],
                ['type' => 'p', 'text' => 'In conclusion, silver and 925 aren\'t different kinds of metal. If you want to buy silverware, check the mixing percentage — many Myanmar silver shops won\'t tell you the truth about it, so buy from a shop you trust. U Nyi Lay Silver Shop has kept the best silver quality for over 60 years, so you can buy with confidence.'],
            ],
        ],
    ];

    public function __construct(private CategoryService $categories)
    {
    }

    public function about()
    {
        return view('pages.about', [
            'categories' => $this->categories->megaMenuGroups(),
        ]);
    }

    public function contact()
    {
        return view('pages.contact', [
            'categories' => $this->categories->megaMenuGroups(),
        ]);
    }

    public function contactSubmit(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:40',
            'message' => 'required|string|max:2000',
        ]);

        try {
            Mail::raw(
                "From: {$validated['name']} <{$validated['email']}>\nPhone: " . ($validated['phone'] ?? '-') . "\n\n{$validated['message']}",
                function ($mail) use ($validated) {
                    $mail->to('unyilaysilver@gmail.com')
                        ->subject('Website inquiry from ' . $validated['name'])
                        ->replyTo($validated['email'], $validated['name']);
                }
            );
        } catch (\Throwable $e) {
            Log::error('Contact form mail failed', ['exception' => $e->getMessage()]);

            return back()->withInput()->withErrors([
                'message' => 'Sorry, your message could not be sent right now. Please call or email us directly — see the contact details below.',
            ]);
        }

        return back()->with('success', "Thanks for reaching out — we'll get back to you soon.");
    }

    public function news()
    {
        $articles = collect(self::ARTICLES)
            ->map(fn ($a, $slug) => [
                'slug' => $slug,
                'title' => $a['title'],
                'excerpt' => $a['excerpt'],
                'image' => $a['image'],
                'date' => $a['date'],
            ])
            ->values()
            ->all();

        return view('pages.news', [
            'categories' => $this->categories->megaMenuGroups(),
            'articles' => $articles,
        ]);
    }

    public function newsShow(string $slug)
    {
        $article = self::ARTICLES[$slug] ?? null;
        abort_if(!$article, 404);

        return view('pages.news-show', [
            'categories' => $this->categories->megaMenuGroups(),
            'article' => $article,
        ]);
    }
}
