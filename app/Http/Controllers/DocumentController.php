<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Services\DocumentService;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    protected DocumentService $documentService;

    public function __construct(DocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    public function update(Document $document, Request $request)
    {
        $this->authorize('update', $document);

        $data = $request->all();
        $document->update($data);

        return $document;
    }

    public function generate(Request $request) {
        return $this->documentService->generateDocumentAndStore($request->all());
    }

    public function refine(Request $request)
    {
        $refined = $this->documentService->refineDocument($request->all());

        return response()->json([
            'content' => $refined
        ]);
    }
}
