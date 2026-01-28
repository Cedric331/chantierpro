<?php

namespace App\UseCases\Dashboard;

use App\Models\Account;
use App\Services\DashboardService;

class GetDashboardSummary
{
    public function __construct(private readonly DashboardService $dashboardService)
    {
    }

    public function handle(Account $account): array
    {
        return $this->dashboardService->summary($account);
    }
}

