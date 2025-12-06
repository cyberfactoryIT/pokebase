<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class SetLocale
{
    public function handle($request, Closure $next)
    {
        $supported = ['da', 'en', 'it'];
        $carbonMap = ['en' => 'en', 'da' => 'da', 'it' => 'it'];
        $locale = config('app.locale');

        if (Auth::check() && in_array(Auth::user()->locale, $supported)) {
            $locale = Auth::user()->locale;
        } elseif (Session::has('locale') && in_array(Session::get('locale'), $supported)) {
            $locale = Session::get('locale');
        }

        App::setLocale($locale);
        setlocale(LC_TIME, $carbonMap[$locale] . '_' . strtoupper($carbonMap[$locale]) . '.UTF-8');
        Carbon::setLocale($carbonMap[$locale]);

        return $next($request);
    }
}
