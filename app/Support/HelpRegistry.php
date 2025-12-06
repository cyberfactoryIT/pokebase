<?php
// app/Support/HelpRegistry.php
namespace App\Support;

use App\Models\Help;
use Illuminate\Support\Facades\Cache;

class HelpRegistry
{
    public function get(string $key, ?string $locale = null): ?array
    {
        $entry = Cache::remember("help:raw:$key", 3600, function() use ($key) {
            return Help::active()->where('key',$key)->first();
        });

        return $entry?->resolve($locale);
    }

    public function forget(string $key): void
    {
        Cache::forget("help:raw:$key");
    }
}
