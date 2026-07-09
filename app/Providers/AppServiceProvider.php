<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (!defined('K_PATH_FONTS')) {
            define('K_PATH_FONTS', base_path('vendor/tecnickcom/tc-lib-pdf-font/target/fonts/'));
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Enforce strong password rules globally
        \Illuminate\Validation\Rules\Password::defaults(function () {
            return \Illuminate\Validation\Rules\Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols();
        });

        // Force HTTPS in production — protects session cookies and data in transit
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Set default pagination views
        Paginator::defaultView('vendor.pagination.custom');
        Paginator::defaultSimpleView('vendor.pagination.custom');

        // Share recent transactions with header layout as notifications
        view()->composer('layouts.partials.header', function ($view) {
            if (auth()->check()) {
                $user = auth()->user();
                
                // If staff, get last 5 system transactions, else get user's last 5 transactions
                if ($user->isStaff()) {
                    $transactions = \App\Models\Transaction::latest()
                        ->with('user')
                        ->limit(5)
                        ->get();
                } else {
                    $transactions = \App\Models\Transaction::where('user_id', $user->id)
                        ->latest()
                        ->limit(5)
                        ->get();
                }
                
                $notifications = $transactions->map(function ($tx) use ($user) {
                    $title = $tx->description;
                    if ($user->isStaff() && $tx->user) {
                        $title = "{$tx->user->email}: {$tx->description}";
                    }
                    
                    return [
                        'id' => $tx->id,
                        'title' => $title,
                        'desc' => "Ref: {$tx->transaction_ref} • Amount: ₦" . number_format($tx->amount, 2) . " • Status: " . strtoupper($tx->status),
                        'time' => $tx->created_at->diffForHumans(),
                        'read' => false, // Start unread so user can clear them
                    ];
                });
                
                $view->with('headerNotifications', $notifications);
            } else {
                $view->with('headerNotifications', collect([]));
            }
        });
    }
}
