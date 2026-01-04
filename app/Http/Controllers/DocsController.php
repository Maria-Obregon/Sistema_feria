<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocsController extends Controller
{
    public function pronafecyt(): StreamedResponse
    {
        $path = 'public/forms/Pronafecyt-2025-formularios-f1-al-f6-y-f16-al-f18.docx';
        abort_unless(Storage::exists($path), 404, 'Archivo no disponible');
        return Storage::download($path, 'PRONAFECYT-2025-Formularios.docx');
    }
}
