<?php
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use Illuminate\Http\Request; 
use App\Http\Controllers\Admin\HelpController;
use App\Models\WaitlistEntry;


Route::get('/', function () {
    $waitlistCount = WaitlistEntry::count()+49;
    return view('welcome', compact('waitlistCount'));
});
// routes/web.php
Route::view('/terms', 'legal.terms')->name('terms');
Route::view('/privacy', 'legal.privacy')->name('privacy');


// Route di test per invalidare la sessione e fare logout
Route::get('/test-session-invalidate', function (\Illuminate\Http\Request $request) {
    \Illuminate\Support\Facades\Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('test.session.invalidate');

Route::get('/test-cookie', function () {
    return response('Cookie test')->withCookie(
        cookie('remember_me', 'testselector:testverifier', 60 * 24 * 30, null, null, true, true, false, 'lax')
    );
});
Route::get('/verify-email/{token}', [\App\Http\Controllers\Auth\EmailVerificationController::class, 'verify'])->name('verification.custom');
use App\Http\Controllers\WaitlistController;

Route::post('/waitlist', [WaitlistController::class, 'store'])->name('waitlist.store');


// Public FAQ route
Route::get('/faq', [\App\Http\Controllers\PublicFaqController::class, 'index']);
// Company info lookup (used by registration form CVR lookup)
Route::post('/company-info/lookup', [\App\Http\Controllers\CompanyInfoController::class, 'lookup'])->name('company.info.lookup');
// Simple test endpoint to verify route/controller reachable
Route::get('/company-info/test', function(){
    return response()->json(['ok' => true]);
});
// FAQ admin routes
Route::middleware(['auth'])->group(function () {
    Route::get('/2fa', [\App\Http\Controllers\Auth\TwoFactorController::class, 'show'])->name('2fa.show');
    Route::post('/2fa/confirm', [\App\Http\Controllers\Auth\TwoFactorController::class, 'confirm'])->name('2fa.confirm');
    Route::post('/2fa/recovery', [\App\Http\Controllers\Auth\TwoFactorController::class, 'regenerateRecovery'])->name('2fa.recovery');
    Route::post('/2fa/disable', [\App\Http\Controllers\Auth\TwoFactorController::class, 'disable'])->name('2fa.disable');
    Route::get('/2fa/challenge', [\App\Http\Controllers\Auth\TwoFactorController::class, 'challengeView'])->name('2fa.challenge.show');
    Route::post('/2fa/challenge', [\App\Http\Controllers\Auth\TwoFactorController::class, 'challenge'])->middleware('throttle:6,1')->name('2fa.challenge.do');
    Route::resource('faqs', \App\Http\Controllers\FaqController::class)->except(['show']);
    Route::post('faqs/reorder', [\App\Http\Controllers\FaqController::class, 'reorder'])->name('faqs.reorder');
    Route::post('faqs/{faq}/toggle', [\App\Http\Controllers\FaqController::class, 'togglePublish'])->name('faqs.toggle');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Billing
    Route::get('/billing', [\App\Http\Controllers\BillingController::class,'index'])->name('billing.index');
    Route::post('/billing/change-plan', [\App\Http\Controllers\BillingController::class,'changePlan'])->name('billing.changePlan');
    Route::post('/billing/cancel-subscription', [\App\Http\Controllers\BillingController::class,'cancelSubscription'])->name('billing.cancelSubscription');
    Route::post('/billing/reactivate-subscription', [\App\Http\Controllers\BillingController::class,'reactivateSubscription'])->name('billing.reactivateSubscription');
    Route::get('/billing/invoices/{invoice}', [\App\Http\Controllers\BillingController::class,'showInvoice'])->name('billing.invoice.show');
    Route::get('/billing/invoices/{invoice}/receipt', [\App\Http\Controllers\BillingController::class,'downloadReceipt'])->name('billing.invoice.receipt');
    Route::post('/billing/confirm-change-plan', [\App\Http\Controllers\BillingController::class,'showChangePlanConfirmation'])->name('billing.confirmChangePlan');
});

Route::middleware(['web', 'auth'])->prefix('admin')->group(function () {
    // Gestione utenti
    Route::resource('users', App\Http\Controllers\Admin\AdminUserController::class)->except(['show']);

    // Activity Log
    Route::get('activity-log', [App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('admin.activitylog.index');

    // Gestione organizzazione
    Route::get('organization/edit', [App\Http\Controllers\Admin\OrganizationController::class, 'edit'])->name('admin.organization.edit');
    Route::patch('organization/update', [App\Http\Controllers\Admin\OrganizationController::class, 'update'])->name('admin.organization.update');
});

Route::middleware(['auth'])->group(function () {
    Route::resource('projects', \App\Http\Controllers\ProjectController::class);
});

// Superadmin area
Route::prefix('superadmin')->middleware(['auth'])->group(function () {
    Route::post('/promotions/{promotion}', [\App\Http\Controllers\Superadmin\PromotionsController::class, 'update'])->name('superadmin.promotions.update');
    
    Route::get('/organizations', [\App\Http\Controllers\Superadmin\OrganizationsController::class, 'index'])->name('superadmin.organizations.index');
    Route::get('/plans', [\App\Http\Controllers\Superadmin\PlansController::class, 'index'])->name('superadmin.plans.index');
    Route::get('/plans/create', [\App\Http\Controllers\Superadmin\PlansController::class, 'create'])->name('superadmin.plans.create');
    Route::post('/plans', [\App\Http\Controllers\Superadmin\PlansController::class, 'store'])->name('superadmin.plans.store');
    Route::get('/plans/{plan}/edit', [\App\Http\Controllers\Superadmin\PlansController::class, 'edit'])->name('superadmin.plans.edit');
    Route::put('/plans/{plan}', [\App\Http\Controllers\Superadmin\PlansController::class, 'update'])->name('superadmin.plans.update');
    Route::delete('/plans/{plan}', [\App\Http\Controllers\Superadmin\PlansController::class, 'destroy'])->name('superadmin.plans.destroy');
    Route::get('/promotions', [\App\Http\Controllers\Superadmin\PromotionsController::class, 'index'])->name('superadmin.promotions.index');
    Route::get('/promotions/create', [\App\Http\Controllers\Superadmin\PromotionsController::class, 'create'])->name('superadmin.promotions.create');
    Route::get('/promotions/{promotion}/edit', [\App\Http\Controllers\Superadmin\PromotionsController::class, 'edit'])->name('superadmin.promotions.edit');
    Route::delete('/promotions/{promotion}', [\App\Http\Controllers\Superadmin\PromotionsController::class, 'destroy'])->name('superadmin.promotions.destroy');
    Route::post('/promotions', [\App\Http\Controllers\Superadmin\PromotionsController::class, 'store'])->name('superadmin.promotions.store');
      Route::get('invoices', [\App\Http\Controllers\Admin\AllInvoicesController::class, 'index'])->name('admin.invoices.index');
    Route::get('/billing/invoices/{invoice}', [\App\Http\Controllers\SuperAdminBillingController::class, 'showInvoice'])->name('superadmin.billing.invoice.show');
    Route::get('/superadmin/billing/invoices/export', [\App\Http\Controllers\SuperAdminBillingController::class, 'exportInvoices'])->name('superadmin.billing.invoices.export');
    Route::resource('helps', HelpController::class)->except(['show']);
    
});

Route::get('/faq', [\App\Http\Controllers\FaqController::class, 'index'])->name('faq.index');
Route::get('/faq/create', [\App\Http\Controllers\FaqController::class, 'create'])->name('faq.create');
Route::post('/faq', [\App\Http\Controllers\FaqController::class, 'store'])->name('faq.store');
Route::get('/faq/{faq}/edit', [\App\Http\Controllers\FaqController::class, 'edit'])->name('faq.edit');
Route::put('/faq/{faq}', [\App\Http\Controllers\FaqController::class, 'update'])->name('faq.update');
Route::delete('/faq/{faq}', [\App\Http\Controllers\FaqController::class, 'destroy'])->name('faq.destroy');

require __DIR__.'/auth.php';

// Locale switch route
Route::post('/language-change', [\App\Http\Controllers\LocaleController::class, 'switch'])->name('language.change');
Route::post('/locale-switch', [\App\Http\Controllers\LocaleController::class, 'switch'])->name('locale.switch');

// Public support route
Route::get('/support', [\App\Http\Controllers\SupportController::class, 'index'])->name('support.index');
Route::post('/support/contact', [\App\Http\Controllers\SupportController::class, 'contact'])->name('support.contact');
