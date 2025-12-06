<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocaleController extends Controller
{
    public function switch(Request $request)
    {
        $locale = $request->input('locale');
        $supported = ['da', 'en', 'it'];
        if (in_array($locale, $supported)) {
            session(['locale' => $locale]);
            app()->setLocale($locale);
            if (Auth::check()) {
                $user = Auth::user();
                $user->setLocale($locale);
            }
        }
        return back();
    }
}
