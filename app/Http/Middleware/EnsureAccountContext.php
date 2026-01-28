<?php

namespace App\Http\Middleware;

use App\Models\Account;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->current_account_id) {
            $accountId = $user->memberships()->value('account_id');
            if ($accountId) {
                $user->forceFill(['current_account_id' => $accountId])->save();
            }
        }

        if ($user && $user->hasRole('Admin') && ! $user->current_account_id) {
            $accountId = Account::query()->value('id');
            if ($accountId) {
                $user->forceFill(['current_account_id' => $accountId])->save();
            }
        }

        if ($user && ! $user->current_account_id) {
            abort(403);
        }

        return $next($request);
    }
}

