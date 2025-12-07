<?php

namespace App\Http\Controllers;

use App\Models\WaitlistEntry;
use Illuminate\Http\Request;

class WaitlistController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        // Evito errori se esiste già
        WaitlistEntry::firstOrCreate(
            ['email' => $validated['email']],
            ['source' => 'landing']
        );

        return back()->with(
            'waitlist_success',
            __('welcome.waitlist_thanks')
        );
    }
}
