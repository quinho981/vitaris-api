<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Spatie\LaravelPdf\Facades\Pdf;

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
        return $this->documentService->createDocumentAndDispatchInsights($request->all());
    }

    public function refine(Request $request)
    {
        $refined = $this->documentService->refineDocument($request->all());

        return response()->json([
            'content' => $refined
        ]);
    }
     
    public function generatePdf(Document $document)
    {
        $this->authorize('update', $document);

        return Pdf::view('pdf.clinical_document', [
            'content' => $document->result,
            'patient_name' => $document->transcript->user->name,
            'template_name' => $document->documentTemplate->name,
            'created_at' => \Carbon\Carbon::parse($document->created_at)->format('d/m/Y H:i'),
        ])
        ->format('A4')
        ->name("document_{$document->id}.pdf")
        ->download();
    }
}
