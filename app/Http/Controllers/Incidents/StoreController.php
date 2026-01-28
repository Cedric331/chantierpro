<?php

namespace App\Http\Controllers\Incidents;

use App\Http\Controllers\Controller;
use App\UseCases\Incidents\CreateIncident;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function __construct(private readonly CreateIncident $createIncident)
    {
    }

    public function __invoke(Request $request): RedirectResponse
    {
        $account = $request->user()->currentAccount;
        $this->createIncident->handle($account, $request->all());

        return redirect()->back();
    }
}

