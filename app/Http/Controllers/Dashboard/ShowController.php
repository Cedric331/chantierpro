<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\UseCases\Dashboard\GetDashboardSummary;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShowController extends Controller
{
    public function __construct(private readonly GetDashboardSummary $getDashboardSummary)
    {
    }

    public function __invoke(Request $request): Response
    {
        $account = $request->user()?->currentAccount;

        $summary = $account ? $this->getDashboardSummary->handle($account) : [
            'stats' => [
                'activeProjects' => 0,
                'delayedProjects' => 0,
                'totalBudget' => 0,
                'pendingValidations' => 0,
                'openIncidents' => 0,
            ],
            'charts' => [
                'averageProgress' => 0,
                'budget' => [
                    'consumed' => 0,
                    'remaining' => 0,
                ],
                'progressByProject' => [
                    'labels' => [],
                    'series' => [],
                ],
                'statusBreakdown' => [],
            ],
            'recentProjects' => [],
            'urgentValidations' => [],
            'recentDecisions' => [],
        ];

        return Inertia::render('Dashboard', $summary);
    }
}

