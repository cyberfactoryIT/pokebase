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
        
        // Organize FAQs into categories for the contact page
        $faqsByCategory = [
            'getting_started' => [],
            'account_billing' => [],
            'features_tools' => [],
        ];

        // Group FAQs by category (you can customize this based on your actual FAQ categories)
        foreach ($faqs as $category => $items) {
            $categoryLower = strtolower($category);
            if (str_contains($categoryLower, 'start') || str_contains($categoryLower, 'begin')) {
                $faqsByCategory['getting_started'] = $items->take(4);
            } elseif (str_contains($categoryLower, 'account') || str_contains($categoryLower, 'billing') || str_contains($categoryLower, 'payment')) {
                $faqsByCategory['account_billing'] = $items->take(4);
            } elseif (str_contains($categoryLower, 'feature') || str_contains($categoryLower, 'tool')) {
                $faqsByCategory['features_tools'] = $items->take(4);
            }
        }

        return view('pages.contact', compact('faqsByCategory', 'lang'));
    }
}

