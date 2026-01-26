<?php
namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\Request;

class PublicFaqController extends Controller
{
    public function index(Request $request)
    {
        $lang = $request->get('lang', app()->getLocale());
        $faqs = Faq::published()->ordered()->get()->groupBy('category');
        return view('faq', compact('faqs', 'lang'));
    }

    public function contact(Request $request)
    {
        $lang = $request->get('lang', app()->getLocale());
        $faqs = Faq::published()->ordered()->get()->groupBy('category');
        
        return view('pages.contact', compact('faqs', 'lang'));
    }
}

