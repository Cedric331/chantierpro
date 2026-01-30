<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\UsageTrackingService;
use App\UseCases\Reports\GetReportData;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndexController extends Controller
{
    public function __construct(
        private readonly GetReportData $getReportData,
        private readonly UsageTrackingService $usageTrackingService,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $account = $request->user()->currentAccount;
        $this->usageTrackingService->track($account, 'reporting');
        $filters = $request->only(['project', 'from', 'to']);

        $data = $this->getReportData->handle($account, $filters);

        return Inertia::render('reports/Index', [
            ...$data,
            'projects' => Project::query()
                ->where('account_id', $account->id)
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }
}



