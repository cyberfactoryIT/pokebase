# System Architecture - Dual Monetization

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                        BASECARD DUAL MONETIZATION                            │
└─────────────────────────────────────────────────────────────────────────────┘

┌────────────────────────────────┐  ┌─────────────────────────────────────────┐
│   MEMBERSHIP SUBSCRIPTION      │  │   DECK EVALUATION PACKAGES              │
│   (Recurring - Existing)       │  │   (One-shot - New Implementation)       │
├────────────────────────────────┤  ├─────────────────────────────────────────┤
│                                │  │                                         │
│ Controls Access To:            │  │ Controls Access To:                     │
│ • Catalog browsing             │  │ • Evaluation beyond 10 cards            │
│ • Collection management        │  │ • Evaluation results persistence        │
│ • Deck saving/management       │  │ • Evaluation availability timeframe     │
│ • Other app features           │  │                                         │
│                                │  │ Packages:                               │
│ Plans:                         │  │ • FREE: 10 cards (no purchase)          │
│ • FREE (default)               │  │ • EVAL_100: 100 cards, 30 days, €9.99   │
│ • ADVANCED (monthly/yearly)    │  │ • EVAL_600: 600 cards, 30 days, €49.99  │
│ • PREMIUM (monthly/yearly)     │  │ • EVAL_UNLIMITED: ∞ cards, 1 year,€99.99│
│                                │  │                                         │
│ Storage:                       │  │ Storage:                                │
│ • pricing_plans                │  │ • deck_evaluation_packages              │
│ • organizations                │  │ • deck_evaluation_purchases             │
│ • invoices                     │  │ • deck_evaluation_sessions              │
│                                │  │ • deck_evaluation_runs                  │
└────────────────────────────────┘  └─────────────────────────────────────────┘
         ⬇️                                      ⬇️
    INDEPENDENT                            INDEPENDENT
    Do NOT merge                           Do NOT merge
         ⬇️                                      ⬇️
┌────────────────────────────────────────────────────────────────────────────┐
│                              USER SCENARIOS                                 │
├────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│ Scenario A: Guest Evaluator                                                │
│ • Not registered                                                            │
│ • Evaluates 10 cards FREE                                                   │
│ • Sees paywall → buys EVAL_100 package                                      │
│ • Can evaluate up to 100 cards for 30 days                                  │
│ • Does NOT have access to catalog/collection (not a member)                │
│                                                                             │
│ Scenario B: Premium Member                                                 │
│ • Has PREMIUM membership (recurring)                                        │
│ • Full access to catalog, collection, decks                                │
│ • Wants deck evaluation → still needs to buy EVAL package                  │
│ • Membership does NOT grant evaluation credits                             │
│                                                                             │
│ Scenario C: Guest → Registered                                             │
│ • Starts as guest, buys EVAL_600 package                                   │
│ • Later registers/logs in                                                  │
│ • System automatically claims guest purchase                                │
│ • Purchase remains valid until expiry                                      │
│ • Still has FREE membership (can upgrade separately)                        │
│                                                                             │
│ Scenario D: Full User                                                      │
│ • Has PREMIUM membership (catalog/collection access)                        │
│ • Has EVAL_UNLIMITED package (evaluation access)                            │
│ • Both active independently                                                │
│ • Renew separately on different schedules                                  │
│                                                                             │
└────────────────────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────────────────────┐
│                          GUEST FLOW ARCHITECTURE                            │
└────────────────────────────────────────────────────────────────────────────┘

   GUEST VISITS SITE
         │
         ├──► Starts deck evaluation
         │
         ├──► Add cards (1-10) ✅ FREE
         │
         ├──► Try to add 11th card ❌
         │    └──► Paywall shown
         │         │
         │         ├──► Buy EVAL_100 (€9.99)
         │         │    └──► guest_token stored in cookie
         │         │         └──► deck_evaluation_purchase created
         │         │              └──► Can now evaluate 100 cards
         │         │
         │         └──► Or register first → then buy
         │
         └──► Guest registers/logs in
              │
              └──► System detects guest_token in cookie
                   └──► Automatically claims purchases
                        └──► User now owns all guest data

┌────────────────────────────────────────────────────────────────────────────┐
│                       ENTITLEMENT CHECK FLOW                                │
└────────────────────────────────────────────────────────────────────────────┘

User wants to evaluate X cards
         │
         ├──► DeckEvaluationEntitlementService::canEvaluate(userId, guestToken, X)
         │
         ├──► Check for active purchase
         │    │
         │    ├──► Found active purchase?
         │    │    │
         │    │    ├──► YES → Check if expired
         │    │    │    │
         │    │    │    ├──► Expired? → Block ❌
         │    │    │    └──► Not expired → Check card limit
         │    │    │         │
         │    │    │         ├──► Unlimited? → Allow ✅
         │    │    │         └──► Limited? → X <= remaining? 
         │    │    │              │
         │    │    │              ├──► YES → Allow ✅
         │    │    │              └──► NO → Block ❌
         │    │    │
         │    └──► NO → Check free tier
         │         │
         │         └──► Free cards remaining?
         │              │
         │              ├──► YES → X <= remaining? 
         │              │    │
         │              │    ├──► YES → Allow ✅
         │              │    └──► NO → Block ❌
         │              │
         │              └──► NO → Require purchase ❌

┌────────────────────────────────────────────────────────────────────────────┐
│                       IDEMPOTENCY MECHANISM                                 │
└────────────────────────────────────────────────────────────────────────────┘

Evaluation Request: [Card IDs: 1, 5, 23, 45, 67]
         │
         ├──► Generate hash: SHA256("1,5,23,45,67") = "abc123..."
         │
         ├──► Check deck_evaluation_runs for existing run_hash
         │    │
         │    ├──► Found? → Return existing result (no charge) ✅
         │    │
         │    └──► Not found? → Process evaluation
         │         │
         │         ├──► Create deck_evaluation_run with run_hash
         │         ├──► Increment purchase.cards_used OR session.free_cards_used
         │         └──► Return evaluation results ✅
         │
         └──► Re-run same cards? → Hash matches → No double charge ✅

┌────────────────────────────────────────────────────────────────────────────┐
│                       DATABASE RELATIONSHIPS                                │
└────────────────────────────────────────────────────────────────────────────┘

users
  ├──► deck_evaluation_sessions (user_id)
  └──► deck_evaluation_purchases (user_id)

deck_evaluation_packages
  └──► deck_evaluation_purchases (package_id)

deck_evaluation_purchases
  └──► deck_evaluation_runs (purchase_id)

deck_evaluation_sessions
  ├──► guest_decks (guest_deck_id)
  ├──► deck_valuations (deck_valuation_id)
  └──► deck_evaluation_runs (session_id)

┌────────────────────────────────────────────────────────────────────────────┐
│                         SCHEDULED TASKS                                     │
└────────────────────────────────────────────────────────────────────────────┘

Daily at midnight:
  php artisan deck-evaluation:mark-expired
         │
         └──► Query: deck_evaluation_purchases
              WHERE status = 'active'
              AND expires_at <= NOW()
              │
              └──► Update status = 'expired'
                   └──► Results no longer accessible ❌

┌────────────────────────────────────────────────────────────────────────────┐
│                      SECURITY FEATURES                                      │
└────────────────────────────────────────────────────────────────────────────┘

✅ Guest tokens: 40 random characters (cryptographically secure)
✅ Ownership verification: guest_token OR user_id match required
✅ Idempotency hash: Prevents card limit manipulation
✅ Foreign key constraints: No orphaned records
✅ Status enums: Only valid states allowed
✅ Timestamp tracking: Audit trail for all actions
✅ Automatic expiry: Scheduled cleanup task

┌────────────────────────────────────────────────────────────────────────────┐
│                         KEY PRINCIPLES                                      │
└────────────────────────────────────────────────────────────────────────────┘

1️⃣ TWO INDEPENDENT SYSTEMS
   Membership ≠ Deck Evaluation
   Must check entitlements separately

2️⃣ GUEST-FIRST DESIGN
   No registration required to purchase
   Automatic claiming on registration

3️⃣ USAGE LIMITS ENFORCED
   Free: 10 cards
   100/600: Hard limits
   Unlimited: Track usage but no limit

4️⃣ EXPIRY ENFORCED
   Scheduled task marks expired daily
   Results inaccessible after expiry

5️⃣ IDEMPOTENCY GUARANTEED
   Hash-based deduplication
   No double charging

6️⃣ i18n COMPLIANT
   All strings translated (EN/IT/DA)
   Consistent namespacing
```
