<?php

namespace App\Http\Controllers\Incidents;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\UseCases\Incidents\DeleteIncident;
use Illuminate\Http\RedirectResponse;

class DestroyController extends Controller
{
    public function __construct(private readonly DeleteIncident $deleteIncident)
    {
    }

    public function __invoke(Incident $incident): RedirectResponse
    {
        $this->deleteIncident->handle($incident);

        return redirect()->to('/incidents');
    }
}

