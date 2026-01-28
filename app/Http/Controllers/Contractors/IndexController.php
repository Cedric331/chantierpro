<?php

namespace App\Http\Controllers\Contractors;

use App\Http\Controllers\Controller;
use App\UseCases\Contractors\ListContractors;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndexController extends Controller
{
    public function __construct(private readonly ListContractors $listContractors)
    {
    }

    public function __invoke(Request $request): Response
    {
        $account = $request->user()->currentAccount;
        $filters = $request->only(['role', 'search']);

        return Inertia::render('contractors/Index', [
            'filters' => $filters,
            'contractors' => $this->listContractors->handle($account, $filters),
        ]);
    }
}

