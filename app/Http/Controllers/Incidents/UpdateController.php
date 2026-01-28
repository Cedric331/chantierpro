<?php

namespace App\Http\Controllers\Incidents;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\UseCases\Incidents\UpdateIncident;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    public function __construct(private readonly UpdateIncident $updateIncident)
    {
    }

    public function __invoke(Request $request, Incident $incident): RedirectResponse
    {
        $this->updateIncident->handle($incident, $request->all());

        return redirect()->to('/incidents');
    }
}

