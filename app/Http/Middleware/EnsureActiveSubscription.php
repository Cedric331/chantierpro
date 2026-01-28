<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if ($user->hasRole('Admin')) {
            return $next($request);
        }

        $account = $user->currentAccount;
        if (! $account) {
            return $next($request);
        }

        if (! Schema::hasTable('subscriptions') || ! Schema::hasColumn('subscriptions', 'account_id')) {
            return $next($request);
        }

        $isOnTrial = $account->trial_ends_at && $account->trial_ends_at->isFuture();
        $hasSubscription = $account->subscribed('default');

        if (! $isOnTrial && ! $hasSubscription) {
            return redirect()->route('billing.index');
        }

        return $next($request);
    }
}

