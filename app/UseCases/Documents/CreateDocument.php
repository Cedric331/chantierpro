<?php

namespace App\UseCases\Documents;

use App\Models\Account;
use App\Models\Document;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

class CreateDocument
{
    public function handle(Account $account, array $input, ?UploadedFile $file): Document
    {
        $data = Validator::make($input, [
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'version' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'max:50'],
        ])->validate();

        $document = Document::create([
            ...$data,
            'account_id' => $account->id,
            'version' => $data['version'] ?? 'v1',
            'status' => $data['status'] ?? 'pending',
        ]);

        if ($file) {
            $document->addMedia($file)->toMediaCollection('file');
        }

        return $document;
    }
}

