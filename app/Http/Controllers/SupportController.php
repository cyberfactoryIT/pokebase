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
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|max:2000',
        ]);
        
        try {
            // Invio mail di supporto con template Blade
            \Log::info('Sending support email', ['from' => $request->email, 'name' => $request->name]);
            
            $subject = $request->subject ?? 'Support request from ' . $request->name;
            
            Mail::send('emails.support', [
                'subject' => $subject,
                'body' => $request->message,
                'actionUrl' => null,
                'actionText' => null,
            ], function($mail) use ($request, $subject) {
                $mail->to(config('mail.support_address', 'support@example.com'))
                    ->subject($subject)
                    ->replyTo($request->email);
            });
            
            \Log::info('Support email sent', ['from' => $request->email, 'name' => $request->name]);
            
            return back()->with('contact_success', __('messages.support_sent'));
        } catch (\Exception $e) {
            \Log::error('Failed to send support email', [
                'error' => $e->getMessage(),
                'from' => $request->email,
                'name' => $request->name
            ]);
            
            return back()->with('contact_error', __('messages.support_error'))
                ->withInput();
        }
    }
}
