<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use App\Services\TwoFactorService;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Writer;

class TwoFactorController extends Controller
{
    public function show(Request $request, TwoFactorService $service)
    {
        $user = Auth::user();
        if (!$user->two_factor_secret) {
            $secret = $service->generateSecret();
            $user->two_factor_secret = $service->encryptSecret($secret);
            $user->save();
        } else {
            $secret = $service->decryptSecret($user->two_factor_secret);
        }
        $otpUrl = $service->makeOtpAuthUrl(config('app.name'), $user->email, $secret);
    $renderer = new ImageRenderer(new RendererStyle(280), new SvgImageBackEnd());
    $writer   = new Writer($renderer);
    $qr = 'data:image/svg+xml;base64,' . base64_encode($writer->writeString($otpUrl));
        return view('auth.2fa-setup', [
            'qr' => $qr,
            'secret' => $secret,
            'enabled' => $user->two_factor_enabled,
            'confirmed' => $user->two_factor_confirmed_at,
        ]);
    }

    public function confirm(Request $request, TwoFactorService $service)
    {
        $request->validate(['code' => 'required|string']);
        $user = Auth::user();
        $secret = $service->decryptSecret($user->two_factor_secret);
        if (!$service->verifyCode($secret, $request->code)) {
            return back()->withErrors(['code' => 'Invalid code']);
        }
        [$plain, $hashed] = $service->generateRecoveryCodes();
        $user->two_factor_recovery_codes = $hashed;
        $user->two_factor_enabled = true;
        $user->two_factor_confirmed_at = now();
        $user->save();
        Log::info('2FA enabled for user '.$user->id);
        return view('auth.2fa-recovery', ['codes' => $plain]);
    }

    public function regenerateRecovery(Request $request, TwoFactorService $service)
    {
        $user = Auth::user();
        [$plain, $hashed] = $service->generateRecoveryCodes();
        $user->two_factor_recovery_codes = $hashed;
        $user->save();
        Log::info('2FA recovery codes regenerated for user '.$user->id);
        return view('auth.2fa-recovery', ['codes' => $plain]);
    }

    public function disable(Request $request)
    {
        $request->validate(['password' => 'required|current_password']);
        $user = Auth::user();
        $user->two_factor_enabled = false;
        $user->two_factor_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->two_factor_confirmed_at = null;
        $user->save();
        Log::info('2FA disabled for user '.$user->id);
        return redirect()->route('2fa.show');
    }

    public function challengeView()
    {
        return view('auth.2fa-challenge');
    }

    public function challenge(Request $request, TwoFactorService $service)
    {
    // Log::debug('Challenge method called');
        $user = Auth::user();
        if (!$user->two_factor_enabled) {
            session(['2fa_passed' => true]);
            return redirect()->intended(route('dashboard'));
        }
    // Log::debug('Controller: recovery_code = ' . $request->input('recovery_code'));
        $request->validate([
            'code' => 'nullable|string',
            'recovery_code' => 'nullable|string',
        ]);
        $secret = $service->decryptSecret($user->two_factor_secret);
        if ($request->filled('code') && $service->verifyCode($secret, $request->code)) {
            session(['2fa_passed' => true]);
            //Log::info('2FA challenge success (TOTP) for user '.$user->id);
            return redirect()->intended(route('dashboard'));
        }
        
        if ($request->filled('recovery_code')) {
            $codes = $user->two_factor_recovery_codes ?? [];
            $idx = $service->matchRecoveryCode($codes, $request->recovery_code);
            if ($idx !== null) {
                unset($codes[$idx]);
                $user->two_factor_recovery_codes = array_values($codes);
                $user->save();
                session(['2fa_passed' => true]);
                //Log::info('2FA challenge success (recovery) for user '.$user->id);
                return redirect()->intended(route('dashboard'));
            }
        }
        Log::warning('2FA challenge failed for user '.$user->id);
        return back()->withErrors(['code' => 'Invalid code or recovery code']);
    }
}
