<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SigningController extends Controller
{
   public function show(string $short, int $docId)
{
    $urlRow = DB::table('url')->where('short_url', $short)->first();
    if (!$urlRow) abort(404);

    $doc = DB::table('documents')->where('id', $docId)->first();
    if (!$doc) abort(404);

    if (trim((string)$doc->insured_name) !== trim((string)$urlRow->name)) {
        abort(403);
    }

    return view('sign.sign', [
        'short' => $short,
        'docId' => $docId,
        'customerName' => $urlRow->name,
        'docsignOverlay' => json_decode($doc->docsign_overlay ?? 'null', true),
    ]);
}

public function pdf(string $short, int $docId)
{
    $urlRow = DB::table('url')->where('short_url', $short)->first();
    if (!$urlRow) abort(404);

    $doc = DB::table('documents')->where('id', $docId)->first();
    if (!$doc) abort(404);

    // Seguridad: el doc debe corresponder al mismo customer
    if (trim((string)$doc->insured_name) !== trim((string)$urlRow->name)) {
        abort(403);
    }

    // documents.path viene como: private/customerdocs/...
    $rel = str_replace('\\', '/', (string)$doc->path);
    $rel = ltrim($rel, '/'); // por si viene con /

    // ✅ Verificar existencia con Storage (más confiable en Windows)
    if (!Storage::disk('local')->exists($rel)) {
        // Si quieres depurar rápido, descomenta:
        // dd(['path_db' => $doc->path, 'rel' => $rel, 'full' => Storage::disk('local')->path($rel)]);
        abort(404);
    }

    $full = Storage::disk('local')->path($rel);

    return response()->file($full, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="document.pdf"',
    ]);
}

  public function saveSignature(Request $request, string $short, int $docId)
{
    $urlRow = DB::table('url')->where('short_url', $short)->first();
    if (!$urlRow) {
        return response()->json(['ok' => false, 'error' => 'Not found'], 404);
    }

    $doc = DB::table('documents')->where('id', $docId)->first();
    if (!$doc) {
        return response()->json(['ok' => false, 'error' => 'Doc not found'], 404);
    }

    if (trim((string)$doc->insured_name) !== trim((string)$urlRow->name)) {
        return response()->json(['ok' => false, 'error' => 'Forbidden'], 403);
    }

    $request->validate([
        'pdf' => 'required|file|mimes:pdf|max:20480',
    ]);

    $relativePdfPath = ltrim(str_replace('\\', '/', (string)$doc->path), '/');

    if (!Storage::disk('local')->exists($relativePdfPath)) {
        return response()->json(['ok' => false, 'error' => 'Original PDF not found'], 404);
    }

    $newPdfContents = file_get_contents($request->file('pdf')->getRealPath());
    Storage::disk('local')->put($relativePdfPath, $newPdfContents);

    // ✅ marcar documento como firmado
    DB::table('documents')
        ->where('id', $docId)
        ->update([
            'signed' => 1,
        ]);

    // ✅ marcar short url como firmado
    DB::table('url')
        ->where('short_url', $short)
        ->update([
            'signed' => 'Yes',
        ]);

    return response()->json([
        'ok' => true,
        'message' => 'PDF firmado guardado correctamente.',
    ]);
}
}

