<?php

namespace App\Http\Controllers\Portfolio;

use App\Http\Controllers\Controller;
use App\Services\UsageTrackingService;
use App\UseCases\Portfolio\GetPortfolioOverview;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndexController extends Controller
{
    public function __construct(
        private readonly GetPortfolioOverview $getPortfolioOverview,
        private readonly UsageTrackingService $usageTrackingService,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $account = $request->user()->currentAccount;
        $this->usageTrackingService->track($account, 'portfolio');
        $data = $this->getPortfolioOverview->handle($account);

        return Inertia::render('portfolio/Index', [
            'stats' => $data['stats'],
            'projects' => $data['projects'],
        ]);
    }
}

