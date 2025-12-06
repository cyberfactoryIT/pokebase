<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SupportController extends Controller
{
    public function index()
    {
        $faqs = \App\Models\Faq::published()
            ->ordered()
            ->get()
            ->groupBy('category');
        return view('support.index', compact('faqs'));
    }

    public function contact(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:64',
            'email' => 'required|email',
            'message' => 'required|string|max:2000',
        ]);
        // Invio mail di supporto con template Blade
        \Log::info('Sending support email', ['from' => $request->email, 'name' => $request->name]);
        Mail::send('emails.support', [
            'subject' => 'Support request from ' . $request->name,
            'body' => $request->message,
            'actionUrl' => null,
            'actionText' => null,
        ], function($mail) use ($request) {
            $mail->to(config('mail.support_address', 'support@example.com'))
                ->subject('Support request from ' . $request->name)
                ->replyTo($request->email);
        });
        \Log::info('Support email sent', ['from' => $request->email, 'name' => $request->name]);
        return back()->with('success', __('messages.support_sent'));
    }
}
