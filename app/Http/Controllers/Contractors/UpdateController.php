<?php

namespace App\Http\Controllers\Contractors;

use App\Http\Controllers\Controller;
use App\Models\Contractor;
use App\UseCases\Contractors\UpdateContractor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    public function __construct(private readonly UpdateContractor $updateContractor)
    {
    }

    public function __invoke(Request $request, Contractor $contractor): RedirectResponse
    {
        $this->updateContractor->handle($contractor, $request->all());

        return redirect()->to('/contractors');
    }
}

