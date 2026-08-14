<?php

namespace App\Http\Controllers;

use App\Services\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PageController extends Controller
{
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
        return view('pages.news', [
            'categories' => $this->categories->megaMenuGroups(),
        ]);
    }
}
