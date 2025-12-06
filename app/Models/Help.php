<?php

// app/Models/Help.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Help extends Model
{
    protected $fillable = [
        'key','icon','title','short','long','links','meta','is_active',
    ];
    protected $casts = [
        'title' => 'array',
        'short' => 'array',
        'long'  => 'array',
        'links' => 'array',
        'meta'  => 'array',
        'is_active' => 'boolean',
    ];

    // Scopes
    public function scopeActive($q){ return $q->where('is_active', true); }

    // Risoluzione lingua con fallback (locale -> en -> app.fallback_locale)
    public function resolve(string $locale): array
    {
        $locale = $locale ?: app()->getLocale();
        $fallbacks = array_unique([$locale, 'en', config('app.fallback_locale','en')]);

        $pick = function(?array $field) use ($fallbacks) {
            if (!is_array($field)) return null;
            foreach ($fallbacks as $loc) {
                if (isset($field[$loc]) && filled($field[$loc])) return $field[$loc];
            }
            // primo valorizzato
            foreach ($field as $v) if (filled($v)) return $v;
            return null;
        };

        return [
            'key'   => $this->key,
            'icon'  => $this->icon,
            'title' => $pick($this->title),
            'short' => $pick($this->short),
            'long'  => $pick($this->long),
            'links' => $this->links ?? [],
            'meta'  => $this->meta ?? [],
        ];
    }
}
