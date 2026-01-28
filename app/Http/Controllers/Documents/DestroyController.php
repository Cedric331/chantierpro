<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\UseCases\Documents\DeleteDocument;
use Illuminate\Http\RedirectResponse;

class DestroyController extends Controller
{
    public function __construct(private readonly DeleteDocument $deleteDocument)
    {
    }

    public function __invoke(Document $document): RedirectResponse
    {
        $this->deleteDocument->handle($document);

        return redirect()->back();
    }
}

