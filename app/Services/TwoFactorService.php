<?php
namespace App\Services;

use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class TwoFactorService
{
    public function generateSecret(): string
    {
        return (new Google2FA())->generateSecretKey();
    }

    public function encryptSecret(string $secret): string
    {
        return Crypt::encryptString($secret);
    }

    public function decryptSecret(string $encrypted): string
    {
        return Crypt::decryptString($encrypted);
    }

    public function makeOtpAuthUrl(string $company, string $email, string $secret): string
    {
        return (new Google2FA())->getQRCodeUrl($company, $email, $secret);
    }

    public function verifyCode(string $secret, string $code): bool
    {
        $code = preg_replace('/\s+/', '', $code);
        return (new Google2FA())->verifyKey($secret, $code, 8);
    }

    public function generateRecoveryCodes(int $count = 8): array
    {
        $plain = [];
        $hashed = [];
        for ($i = 0; $i < $count; $i++) {
            $code = strtoupper(bin2hex(random_bytes(5))) . '-' . strtoupper(bin2hex(random_bytes(5)));
            $plain[] = $code;
            $hashed[] = Hash::make($code);
        }
        return [$plain, $hashed];
    }

    public function matchRecoveryCode(array $hashedCodes, string $input): ?int
    {
    // \Log::debug('matchRecoveryCode: input = ' . $input);
        foreach ($hashedCodes as $i => $hash) {
            // \Log::debug('matchRecoveryCode: checking index ' . $i . ' hash = ' . $hash);
            if (Hash::check($input, $hash)) {
                // \Log::debug('matchRecoveryCode: MATCH at index ' . $i);
                return $i;
            }
        }
    // \Log::debug('matchRecoveryCode: NO MATCH');
        return null;
    }
}
