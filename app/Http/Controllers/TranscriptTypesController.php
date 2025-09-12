<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\TranscriptType;
use Illuminate\Http\Request;

class TranscriptTypesController extends Controller
{
    public function index()
    {
        return TranscriptType::all();
    }
}
