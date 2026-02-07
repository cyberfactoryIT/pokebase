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
            
            $contactSubject = $request->subject;
            $emailSubject = $contactSubject 
                ? '[Basecard] ' . $contactSubject 
                : '[Basecard] Richiesta di supporto da ' . $request->name;
            
            Mail::send('emails.support', [
                'subject' => $emailSubject,
                'body' => $request->message,
                'contactName' => $request->name,
                'contactEmail' => $request->email,
                'contactSubject' => $contactSubject,
                'actionUrl' => null,
                'actionText' => null,
            ], function($mail) use ($request, $emailSubject) {
                $mail->to(config('mail.support_address', 'support@example.com'))
                    ->subject($emailSubject)
                    ->replyTo($request->email, $request->name)
                    ->from(config('mail.from.address'), config('mail.from.name'))
                    // Add headers to improve deliverability
                    ->withSymfonyMessage(function ($message) use ($request) {
                        $headers = $message->getHeaders();
                        $headers->addTextHeader('X-Mailer', 'Basecard Support System');
                        $headers->addTextHeader('X-Contact-Form', 'true');
                        $headers->addTextHeader('X-Sender-IP', $request->ip());
                    });
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
