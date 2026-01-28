<?php

namespace App\UseCases\Documents;

use App\Models\Document;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

class UpdateDocument
{
    /**
     * @param array<string, mixed> $input
     */
    public function handle(Document $document, array $input, ?UploadedFile $file): Document
    {
        $data = Validator::validate($input, [
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'version' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        $document->update($data);

        if ($file) {
            $document->addMedia($file)->toMediaCollection('file');
        }

        return $document;
    }
}

