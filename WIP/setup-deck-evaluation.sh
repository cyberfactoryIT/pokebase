#!/bin/bash

# Quick Start Script - Deck Evaluation Monetization System
# This script sets up the new dual monetization system

set -e

echo "🚀 Starting Deck Evaluation Monetization System Setup..."
echo ""

# Step 1: Run migrations
echo "📦 Step 1/5: Running migrations..."
php artisan migrate
echo "✅ Migrations completed"
echo ""

# Step 2: Seed packages
echo "💰 Step 2/5: Seeding evaluation packages..."
php artisan db:seed --class=DeckEvaluationPackageSeeder
echo "✅ Packages seeded (EVAL_100, EVAL_600, EVAL_UNLIMITED)"
echo ""

# Step 3: Verify tables
echo "🔍 Step 3/5: Verifying database tables..."
php artisan tinker --execute="
echo 'Deck Evaluation Packages: ' . App\Models\DeckEvaluationPackage::count() . PHP_EOL;
\$packages = App\Models\DeckEvaluationPackage::all();
foreach (\$packages as \$pkg) {
    echo '  - ' . \$pkg->name . ' (' . \$pkg->code . '): ' . \$pkg->formatted_price . PHP_EOL;
}
"
echo "✅ Tables verified"
echo ""

# Step 4: Run tests
echo "🧪 Step 4/5: Running feature tests..."
php artisan test --filter DeckEvaluationEntitlementTest
echo "✅ Tests passed"
echo ""

# Step 5: Summary
echo "📋 Step 5/5: Installation summary"
echo ""
echo "✅ INSTALLATION COMPLETE!"
echo ""
echo "📚 Next Steps:"
echo "1. Review INTEGRATION_GUIDE_DeckValuationFlowController.php"
echo "2. Update DeckValuationFlowController with entitlement checks"
echo "3. Add guest claiming to login handler"
echo "4. Add route for account/deck-evaluations"
echo "5. Schedule daily task in app/Console/Kernel.php:"
echo "   \$schedule->command('deck-evaluation:mark-expired')->daily();"
echo "6. Integrate payment provider in DeckEvaluationPurchaseController"
echo ""
echo "📖 Full Documentation:"
echo "- DECK_EVALUATION_MONETIZATION.md (complete guide)"
echo "- IMPLEMENTATION_SUMMARY.md (what was implemented)"
echo ""
echo "🎉 System is ready for integration!"
