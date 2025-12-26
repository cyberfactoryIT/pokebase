<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;

class TranslateArticles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'articles:translate 
                            {--locale= : Target locale (en, it, da)}
                            {--all : Translate to all available locales}
                            {--force : Regenerate existing translations}
                            {--article= : Specific article ID to translate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pre-generate translations for articles using DeepL API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $availableLocales = ['en', 'it', 'da'];
        
        // Determine target locales
        if ($this->option('all')) {
            $targetLocales = $availableLocales;
        } elseif ($locale = $this->option('locale')) {
            if (!in_array($locale, $availableLocales)) {
                $this->error("Invalid locale: {$locale}. Available: " . implode(', ', $availableLocales));
                return 1;
            }
            $targetLocales = [$locale];
        } else {
            $this->error('Please specify --locale=XX or --all');
            return 1;
        }
        
        // Get articles to translate
        $articlesQuery = Article::published();
        if ($articleId = $this->option('article')) {
            $articlesQuery->where('id', $articleId);
        }
        
        $articles = $articlesQuery->get();
        
        if ($articles->isEmpty()) {
            $this->info('No articles found to translate.');
            return 0;
        }
        
        $this->info("Found {$articles->count()} article(s) to translate.");
        $this->newLine();
        
        $force = $this->option('force');
        $totalTranslations = 0;
        $skippedTranslations = 0;
        
        foreach ($articles as $article) {
            $this->info("Processing: {$article->title} (ID: {$article->id}, Original: {$article->original_locale})");
            
            foreach ($targetLocales as $targetLocale) {
                // Skip if same as original
                if ($article->original_locale === $targetLocale) {
                    continue;
                }
                
                // Check if translation already exists
                $existingTranslation = $article->getTranslation($targetLocale);
                
                if ($existingTranslation && !$force) {
                    $this->line("  → {$targetLocale}: Already exists (use --force to regenerate)");
                    $skippedTranslations++;
                    continue;
                }
                
                try {
                    $this->line("  → {$targetLocale}: Translating...");
                    
                    if ($existingTranslation && $force) {
                        // Delete existing and recreate
                        $existingTranslation->delete();
                    }
                    
                    $translation = $article->createTranslation($targetLocale);
                    $totalTranslations++;
                    
                    $this->line("  → {$targetLocale}: ✓ Done");
                    
                } catch (\Exception $e) {
                    $this->error("  → {$targetLocale}: Failed - " . $e->getMessage());
                }
            }
            
            $this->newLine();
        }
        
        $this->info("Translation complete!");
        $this->info("Created: {$totalTranslations} translations");
        if ($skippedTranslations > 0) {
            $this->info("Skipped: {$skippedTranslations} (already exist)");
        }
        
        return 0;
    }
}
