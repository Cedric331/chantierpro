<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndexController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $account = $request->user()?->currentAccount;

        return Inertia::render('billing/Index', [
            'account' => $account,
            'subscription' => $account?->subscription('default'),
        ]);
    }
}

