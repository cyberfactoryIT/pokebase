<?php
namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use App\Models\RememberToken;

class RememberTokenService
{
    // Issue a new remember token and cookie
    public function issue(User $user, Request $request)
    {
        Log::info('DEBUG-remember-issue', [
            'user_id' => $user->id,
            'request_remember' => $request->boolean('remember'),
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip(),
        ]);
        $selector = Str::random(32);
        $verifier = Str::random(64);
        $tokenHash = hash('sha256', $verifier);
        $expires = Carbon::now()->addDays(30);
        $token = RememberToken::create([
            'user_id' => $user->id,
            'selector' => $selector,
            'token_hash' => $tokenHash,
            'user_agent' => substr($request->userAgent(), 0, 255),
            'ip' => $request->ip(),
            'expires_at' => $expires,
            'last_used_at' => Carbon::now(),
        ]);
        $cookieValue = $selector . ':' . $verifier;
        Log::info('remember-create', ['user_id' => $user->id, 'selector' => $selector, 'cookie_value' => $cookieValue]);
        return [
            'token' => $token,
            'cookie_value' => $cookieValue,
            'expires' => $expires,
        ];
    }

    // Validate and re-authenticate from cookie
    public function validateAndReauth(Request $request)
    {
        $cookie = $request->cookie('remember_me');
        if (!$cookie || !Str::contains($cookie, ':')) return false;
        [$selector, $verifier] = explode(':', $cookie, 2);
        $token = RememberToken::where('selector', $selector)->first();
        if (!$token) return false;
        if ($token->expires_at < Carbon::now()) return false;
        if (!hash_equals($token->token_hash, hash('sha256', $verifier))) return false;
        // Optionally check UA/IP
        if ($token->user_agent && $token->user_agent !== substr($request->userAgent(), 0, 255)) return false;
        // Optionally check IP prefix
        // Rotate token
        $this->rotate($token, $request);
        Log::info('remember-reauth', ['user_id' => $token->user_id, 'selector' => $selector]);
        return User::find($token->user_id);
    }

    // Rotate token (invalidate old, create new)
    public function rotate(RememberToken $oldToken, Request $request)
    {
        DB::transaction(function () use ($oldToken, $request) {
            $oldToken->delete();
            $this->issue(User::find($oldToken->user_id), $request);
        });
        Log::info('remember-rotate', ['user_id' => $oldToken->user_id, 'old_selector' => $oldToken->selector]);
    }

    // Revoke current token
    public function revokeCurrent(Request $request)
    {
        $cookie = $request->cookie('remember_me');
        if (!$cookie || !Str::contains($cookie, ':')) return;
        [$selector] = explode(':', $cookie, 2);
        RememberToken::where('selector', $selector)->delete();
        $this->clearRememberCookie();
        Log::info('remember-revoke', ['selector' => $selector]);
    }

    // Revoke all tokens for user
    public function revokeAll(User $user)
    {
        RememberToken::where('user_id', $user->id)->delete();
        $this->clearRememberCookie();
        Log::info('remember-revoke-all', ['user_id' => $user->id]);
    }

    // Purge expired tokens
    public function purgeExpired()
    {
        $count = RememberToken::where('expires_at', '<', Carbon::now())->delete();
        Log::info('remember-purge-expired', ['count' => $count]);
    }

    // Set remember cookie
    public function setRememberCookie($value, $expires)
    {
        Cookie::queue('remember_me', $value, $expires->diffInMinutes(Carbon::now()), null, null, true, true, false, 'lax');
    }

    // Clear remember cookie
    public function clearRememberCookie()
    {
        Cookie::queue(Cookie::forget('remember_me'));
    }
}
