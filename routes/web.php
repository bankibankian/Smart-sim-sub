<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/palmpay/webhook', [\App\Http\Controllers\PaymentWebhookController::class, 'handleWebhook'])->name('palmpay.webhook');

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/kyc', [\App\Http\Controllers\KycController::class, 'submit'])->name('kyc.submit');

    // Wallet Routes
    Route::get('/wallet', [\App\Http\Controllers\WalletController::class, 'index'])->name('wallet');
    Route::post('/wallet/claim-bonus', [\App\Http\Controllers\WalletController::class, 'claimBonus'])->name('wallet.claimBonus');
    Route::post('/virtual-account/create', [\App\Http\Controllers\WalletController::class, 'createWallet'])->name('virtual.account.create');
    Route::get('/wallet/balance', [\App\Http\Controllers\WalletController::class, 'getBalance'])->name('wallet.balance');

    // P2P Transfer Routes
    Route::get('/transfer', [\App\Http\Controllers\WalletController::class, 'p2p'])->name('transfer');
    Route::post('/transfer/verify', [\App\Http\Controllers\WalletController::class, 'verifyUser'])->name('transfer.verify');
    Route::post('/transfer/process', [\App\Http\Controllers\WalletController::class, 'processTransfer'])->name('transfer.process');
    Route::post('/verify/pin', [\App\Http\Controllers\WalletController::class, 'verifyPin'])->name('verify.pin')->middleware('throttle:5,1');

    // Withdraw Routes
    Route::get('/withdraw', [\App\Http\Controllers\Action\WithdrawController::class, 'index'])->name('withdraw');
    Route::get('/withdraw/sync-banks', [\App\Http\Controllers\Action\WithdrawController::class, 'syncBanks'])->name('withdraw.syncBanks');
    Route::post('/withdraw/verify-account', [\App\Http\Controllers\Action\WithdrawController::class, 'verifyAccount'])->name('withdraw.verifyAccount');
    Route::post('/withdraw/process', [\App\Http\Controllers\Action\WithdrawController::class, 'processWithdrawal'])->name('withdraw.process');

    // Transactions Routes
    Route::get('/transactions', [\App\Http\Controllers\TransactionController::class, 'index'])->name('transactions');
    Route::get('/receipt', [\App\Http\Controllers\TransactionController::class, 'receipt'])->name('transactions.receipt');
    Route::get('/thankyou', [\App\Http\Controllers\TransactionController::class, 'receipt'])->name('thankyou');

    // Airtime Routes
    Route::get('/airtime', [\App\Http\Controllers\Action\AirtimeController::class, 'airtime'])->name('airtime');
    Route::post('/airtime', [\App\Http\Controllers\Action\AirtimeController::class, 'buyAirtime'])->name('buyairtime');

    // SME Data Routes
    Route::get('/buy-sme-data', [\App\Http\Controllers\Action\SmeDataController::class, 'index'])->name('buy-sme-data');
    Route::post('/buy-sme-data', [\App\Http\Controllers\Action\SmeDataController::class, 'buySMEdata'])->name('buy-sme-data.submit');
    Route::get('/sme/fetch-type', [\App\Http\Controllers\Action\SmeDataController::class, 'fetchDataType'])->name('sme.fetch.type');
    Route::get('/sme/fetch-plan', [\App\Http\Controllers\Action\SmeDataController::class, 'fetchDataPlan'])->name('sme.fetch.plan');
    Route::get('/sme/fetch-price', [\App\Http\Controllers\Action\SmeDataController::class, 'fetchSmeBundlePrice'])->name('sme.fetch.price');

    // Profile PIN update requires verified email (financial action)
    Route::post('/profile/pin', [ProfileController::class, 'updatePin'])->name('profile.pin.update');

    // Support Tickets Routes
    Route::get('/support', [\App\Http\Controllers\SupportController::class, 'index'])->name('support');
    Route::post('/support', [\App\Http\Controllers\SupportController::class, 'store'])->name('support.store');
    Route::get('/support/{ticket}', [\App\Http\Controllers\SupportController::class, 'show'])->name('support.show');
    Route::post('/support/{ticket}/reply', [\App\Http\Controllers\SupportController::class, 'reply'])->name('support.reply');

    // SIM Services Routes
    Route::get('/sims', [\App\Http\Controllers\smartsim\SimsController::class, 'index'])->name('sims.index');
    Route::get('/sims/check', [\App\Http\Controllers\smartsim\SimsController::class, 'checkNumber'])->name('sims.check');
    Route::get('/sims/available-numbers', [\App\Http\Controllers\smartsim\SimsController::class, 'getAvailableNumbers'])->name('sims.available');
    Route::post('/sims/request', [\App\Http\Controllers\smartsim\SimsController::class, 'requestSim'])->name('sims.request');
    Route::post('/sims/activate', [\App\Http\Controllers\smartsim\SimsController::class, 'activateSim'])->name('sims.activate');
    Route::post('/partner/sims/assign', [\App\Http\Controllers\smartsim\SimsController::class, 'partnerAssignSim'])->name('partner.sims.assign');
});

Route::middleware('auth')->group(function () {
    Route::get('/search', [\App\Http\Controllers\SearchController::class, 'search'])->name('search');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/upgrade', [ProfileController::class, 'requestUpgrade'])->name('profile.upgrade.request');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified', 'super_admin'])->prefix('admin/manage')->name('admin.manage.')->group(function () {
    Route::get('/users', [\App\Http\Controllers\Admin\ManageController::class, 'users'])->name('users');
    Route::get('/users/{user}/edit', [\App\Http\Controllers\Admin\ManageController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{user}', [\App\Http\Controllers\Admin\ManageController::class, 'updateUser'])->name('users.update');
    Route::get('/users/{user}', [\App\Http\Controllers\Admin\ManageController::class, 'showUser'])->name('users.show');
    Route::delete('/users/{user}', [\App\Http\Controllers\Admin\ManageController::class, 'destroyUser'])->name('users.destroy');

    Route::get('/upgrades', [\App\Http\Controllers\Admin\ManageController::class, 'upgrades'])->name('upgrades');
    Route::post('/upgrades/{user}/approve', [\App\Http\Controllers\Admin\ManageController::class, 'approveUpgrade'])->name('upgrades.approve');
    Route::post('/upgrades/{user}/reject', [\App\Http\Controllers\Admin\ManageController::class, 'rejectUpgrade'])->name('upgrades.reject');

    Route::get('/access', [\App\Http\Controllers\Admin\ManageController::class, 'access'])->name('access');
    Route::put('/access/{user}', [\App\Http\Controllers\Admin\ManageController::class, 'updateAccess'])->name('access.update');

    // Admin Support Tickets Routes
    Route::get('/support', [\App\Http\Controllers\Admin\SupportController::class, 'index'])->name('support.index');
    Route::get('/support/{ticket}', [\App\Http\Controllers\Admin\SupportController::class, 'show'])->name('support.show');
    Route::post('/support/{ticket}/reply', [\App\Http\Controllers\Admin\SupportController::class, 'reply'])->name('support.reply');
    Route::post('/support/{ticket}/status', [\App\Http\Controllers\Admin\SupportController::class, 'updateStatus'])->name('support.status');
});

Route::middleware(['auth', 'verified', 'super_admin'])->group(function () {
    Route::get('/admin/transactions', [\App\Http\Controllers\Admin\TransactionController::class, 'index'])->name('admin.transactions');

    // Services Management
    Route::prefix('admin/services')->name('admin.services.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ServiceController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Admin\ServiceController::class, 'store'])->name('store');
        Route::get('/{service}', [\App\Http\Controllers\Admin\ServiceController::class, 'show'])->name('show');
        Route::put('/{service}', [\App\Http\Controllers\Admin\ServiceController::class, 'update'])->name('update');
        Route::delete('/{service}', [\App\Http\Controllers\Admin\ServiceController::class, 'destroy'])->name('destroy');

        // Fields
        Route::post('/{service}/fields', [\App\Http\Controllers\Admin\ServiceController::class, 'storeField'])->name('fields.store');
        Route::put('/fields/{field}', [\App\Http\Controllers\Admin\ServiceController::class, 'updateField'])->name('fields.update');
        Route::delete('/fields/{field}', [\App\Http\Controllers\Admin\ServiceController::class, 'destroyField'])->name('fields.destroy');

        // Prices
        Route::post('/{service}/prices', [\App\Http\Controllers\Admin\ServiceController::class, 'storePrice'])->name('prices.store');
        Route::put('/prices/{price}', [\App\Http\Controllers\Admin\ServiceController::class, 'updatePrice'])->name('prices.update');
        Route::delete('/prices/{price}', [\App\Http\Controllers\Admin\ServiceController::class, 'destroyPrice'])->name('prices.destroy');
    });

    // SME Data Plans Management
    Route::prefix('admin/sme-plans')->name('admin.sme-plans.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\SmePlanController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Admin\SmePlanController::class, 'store'])->name('store');
        Route::put('/{plan}', [\App\Http\Controllers\Admin\SmePlanController::class, 'update'])->name('update');
        Route::delete('/{plan}', [\App\Http\Controllers\Admin\SmePlanController::class, 'destroy'])->name('destroy');
    });

    // Admin SIM Plans / SIMs Management
    Route::prefix('admin/sim-plan')->name('admin.sim-plan.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\SimPlanController::class, 'index'])->name('index');
        Route::post('/store', [\App\Http\Controllers\Admin\SimPlanController::class, 'store'])->name('store');
        Route::post('/assign', [\App\Http\Controllers\Admin\SimPlanController::class, 'assign'])->name('assign');
        Route::post('/unassign/{sim}', [\App\Http\Controllers\Admin\SimPlanController::class, 'unassign'])->name('unassign');
        Route::post('/requests/{simRequest}/approve', [\App\Http\Controllers\Admin\SimPlanController::class, 'approveRequest'])->name('requests.approve');
        Route::post('/requests/{simRequest}/reject', [\App\Http\Controllers\Admin\SimPlanController::class, 'rejectRequest'])->name('requests.reject');
        Route::post('/import-excel', [\App\Http\Controllers\Admin\SimPlanController::class, 'importExcel'])->name('import');
        Route::get('/download-sample', [\App\Http\Controllers\Admin\SimPlanController::class, 'downloadSample'])->name('download-sample');
    });
});

// ⚠️  LOCAL DEVELOPMENT ONLY — remove or keep guarded before deploying to production
if (app()->environment('local')) {
    Route::get('/temp-login', function () {
        \Illuminate\Support\Facades\Auth::login(\App\Models\User::find(1));
        return redirect()->route('wallet');
    });
}

require __DIR__.'/auth.php';
