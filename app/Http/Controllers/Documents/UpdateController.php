<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\UseCases\Documents\UpdateDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    public function __construct(private readonly UpdateDocument $updateDocument)
    {
    }

    public function __invoke(Request $request, Document $document): RedirectResponse
    {
        $this->updateDocument->handle($document, $request->all(), $request->file('file'));

        return redirect()->back();
    }
}

