<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DocumentsController extends Controller
{
    public function index()
    {
        // Por ahora no hay inserción de datos, contador en 0
        $totalDocuments = 0;
        $documents = collect(); // vacío

        return view('documents', compact('totalDocuments', 'documents'));
    }
}
