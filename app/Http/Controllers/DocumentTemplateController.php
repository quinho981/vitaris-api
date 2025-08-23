<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DocumentTemplate;
use Illuminate\Http\Request;

class DocumentTemplateController extends Controller
{
    public function index()
    {
        return DocumentTemplate::all();
    }
}
