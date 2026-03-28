<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DocumentTemplate;
use Illuminate\Support\Facades\Auth;

class DocumentTemplateController extends Controller
{
    public function index()
    {
        return DocumentTemplate::orderBy('id')->get();
    }

    public function userTemplatesWithDocumentsCount()
    {
        $userId = Auth::id();

        return DocumentTemplate::select('id', 'name', 'description')
            ->withCount([
                'documents as total' => function ($query) use ($userId) {
                    $query->withTrashed()
                        ->whereHas('transcript', function ($q) use ($userId) {
                            $q->withTrashed()
                            ->where('user_id', $userId);
                        });
                }
            ])
            ->get();
    }

    public function getIdNameTemplate()
    {
        return DocumentTemplate::select('id', 'name')
            ->get();
    }
}
