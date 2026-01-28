<?php

namespace App\Http\Controllers\Contractors;

use App\Http\Controllers\Controller;
use App\Models\Contractor;
use App\UseCases\Contractors\DeleteContractor;
use Illuminate\Http\RedirectResponse;

class DestroyController extends Controller
{
    public function __construct(private readonly DeleteContractor $deleteContractor)
    {
    }

    public function __invoke(Contractor $contractor): RedirectResponse
    {
        $this->deleteContractor->handle($contractor);

        return redirect()->to('/contractors');
    }
}

