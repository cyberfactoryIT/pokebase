<?php
// app/Observers/HelpObserver.php
namespace App\Observers;

use App\Models\Help;
use App\Support\HelpRegistry;

class HelpObserver
{
    public function saved(Help $help) { app(HelpRegistry::class)->forget($help->key); }
    public function deleted(Help $help){ app(HelpRegistry::class)->forget($help->key); }
}
