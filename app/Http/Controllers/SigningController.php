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

        // Si luego quieres bloquear si ya firmó:
        // if (($urlRow->signed ?? 'No') === 'Yes') return view('short.signed');

        $doc = DB::table('documents')->where('id', $docId)->first();
        if (!$doc) abort(404);

        // Seguridad mínima: que el doc pertenezca al mismo customer
        if (trim((string)$doc->insured_name) !== trim((string)$urlRow->name)) {
            abort(403);
        }

        return view('sign.sign', [
            'short' => $short,
            'docId' => $docId,
            'customerName' => $urlRow->name,
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
        if (!$urlRow) return response()->json(['ok' => false, 'error' => 'Not found'], 404);

        $doc = DB::table('documents')->where('id', $docId)->first();
        if (!$doc) return response()->json(['ok' => false, 'error' => 'Doc not found'], 404);

        if (trim((string)$doc->insured_name) !== trim((string)$urlRow->name)) {
            return response()->json(['ok' => false, 'error' => 'Forbidden'], 403);
        }

        $request->validate([
            'imgBase64' => 'required|string',
        ]);

        $img = $request->input('imgBase64');

        // Limpieza base64 (igual a tu lógica)
        $img = str_replace('data:image/png;base64,', '', $img);
        $img = str_replace(' ', '+', $img);

        $fileData = base64_decode($img, true);
        if ($fileData === false) {
            return response()->json(['ok' => false, 'error' => 'Invalid base64'], 422);
        }

        // Guardar en storage/app/private/firmas
        $fileName = 'firma-' . now()->format('Y-m-d_H-i-s') . '-' . $short . '-' . $docId . '.png';
        $path = 'private/firmas/' . $fileName;

        Storage::disk('local')->put($path, $fileData);

        // Por ahora SOLO guardamos la imagen temporal.
        // Luego: incrustar en PDF y set signed='Yes'.

        return response()->json([
            'ok' => true,
            'path' => $path,
        ]);
    }
}
