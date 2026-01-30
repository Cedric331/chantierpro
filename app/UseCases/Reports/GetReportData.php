<?php

namespace App\UseCases\Reports;

use App\Models\Account;
use App\Services\ReportService;

class GetReportData
{
    public function __construct(private readonly ReportService $reportService)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function handle(Account $account, array $filters): array
    {
        return $this->reportService->build($account, $filters);
    }
}



