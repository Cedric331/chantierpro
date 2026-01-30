<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\FeatureUsage;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndexController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $account = $request->user()?->currentAccount;
        $usageSummary = [];

        if ($account) {
            $labels = [
                'portfolio' => 'Portefeuille',
                'reporting' => 'Reporting',
                'budgets' => 'Budget',
                'planning' => 'Planning',
            ];

            $usageSummary = FeatureUsage::query()
                ->where('account_id', $account->id)
                ->where('used_at', '>=', now()->subDays(30))
                ->get()
                ->groupBy('feature_key')
                ->map(fn ($items, $key) => [
                    'feature_key' => $labels[$key] ?? $key,
                    'days_used' => $items->count(),
                    'last_used_at' => $items->max('used_at'),
                ])
                ->values()
                ->all();
        }

        return Inertia::render('billing/Index', [
            'account' => $account,
            'subscription' => $account?->subscription('default'),
            'usageSummary' => $usageSummary,
        ]);
    }
}

