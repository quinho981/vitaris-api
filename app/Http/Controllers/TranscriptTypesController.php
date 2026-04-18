<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\TranscriptType;

class TranscriptTypesController extends Controller
{
    public function index()
    {
        return TranscriptType::all();
    }

    public function listMinimal() 
    {
        return TranscriptType::select('id', 'type')
            ->orderBy('type')
            ->get();
    }
}
