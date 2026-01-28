<?php

namespace App\UseCases\Documents;

use App\Models\Document;

class DeleteDocument
{
    public function handle(Document $document): void
    {
        $document->delete();
    }
}

