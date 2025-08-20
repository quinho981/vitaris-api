<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DocumentType;

class DocumentTypesController extends Controller
{
    public function index()
    {
        return DocumentType::all();
    }
}
