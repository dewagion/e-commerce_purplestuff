<?php

namespace App\Http\Controllers;

class PageController extends Controller
{
    public function contact()
    {
        return view('pages.contact');
    }

    public function shipping()
    {
        return view('pages.shipping');
    }

    public function returns()
    {
        return view('pages.returns');
    }

    public function faq()
    {
        return view('pages.faq');
    }
}