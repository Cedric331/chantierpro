<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\UseCases\Reports\GetReportData;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PrintController extends Controller
{
    public function __construct(private readonly GetReportData $getReportData)
    {
    }

    public function __invoke(Request $request): Response
    {
        $account = $request->user()->currentAccount;
        $filters = $request->only(['project', 'from', 'to']);

        $data = $this->getReportData->handle($account, $filters);

        return Inertia::render('reports/Print', [
            ...$data,
            'projects' => Project::query()
                ->where('account_id', $account->id)
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }
}



