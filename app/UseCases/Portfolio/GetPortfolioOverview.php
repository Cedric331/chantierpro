<?php

namespace App\UseCases\Portfolio;

use App\Models\Account;
use App\Services\PortfolioService;

class GetPortfolioOverview
{
    public function __construct(private readonly PortfolioService $portfolioService)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function handle(Account $account): array
    {
        return $this->portfolioService->overview($account);
    }
}



