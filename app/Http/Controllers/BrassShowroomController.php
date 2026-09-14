<?php

namespace App\Http\Controllers;

use App\Services\BrassShowroomManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class BrassShowroomController extends Controller
{
    public function show(BrassShowroomManager $showroom): View
    {
        return view('pages.brass', ['showroomConfig' => $showroom->view('published')['config']]);
    }

    public function preview(Request $request, BrassShowroomManager $showroom): View
    {
        abort_unless($request->hasValidSignature(), 403);

        return view('pages.brass', ['showroomConfig' => $showroom->view('draft')['config']]);
    }
}
