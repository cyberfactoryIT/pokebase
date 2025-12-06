<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Carbon;

class EmailVerificationController extends Controller
{
    public function verify(Request $request, $token)
    {
        $user = User::where('email_verification_token', $token)->first();
        if (!$user) {
            return view('auth.verify-email-result', ['status' => 'invalid']);
        }
        if ($user->email_verified_at) {
            return view('auth.verify-email-result', ['status' => 'already_verified']);
        }
        if ($user->email_verification_expires_at < now()) {
            return view('auth.verify-email-result', ['status' => 'expired']);
        }
        $user->email_verified_at = now();
        $user->email_verification_token = null;
        $user->email_verification_expires_at = null;
        $user->save();
        return view('auth.verify-email-result', ['status' => 'success']);
    }
}