<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShortUrlController extends Controller
{
    public function show(string $short)
    {
        $row = DB::table('url')->where('short_url', $short)->first();

        if (!$row) {
            return redirect()->route('short.error');
        }

        // Si ya está firmado => pantalla "Already Signed"
        if (($row->signed ?? 'No') === 'Yes') {
            return redirect()->route('short.signed');
        }

        // +1 click (solo en GET)
        DB::table('url')->where('id', $row->id)->increment('clicks');

        return view('short.form', [
            'short' => $short,
        ]);
    }

    public function verify(Request $request, string $short)
    {
        $row = DB::table('url')->where('short_url', $short)->first();

        if (!$row) {
            return redirect()->route('short.error');
        }

        if (($row->signed ?? 'No') === 'Yes') {
            return redirect()->route('short.signed');
        }

        // tu formulario manda "codigo" (string de 6)
        $request->validate([
            'codigo' => ['required','digits:6'],
        ]);

        if ((string)$request->codigo !== (string)$row->rand) {
            return back()->withErrors(['codigo' => 'Wrong Access Code.'])->withInput();
        }

        // Si aún no tienes original_url, manda a error por ahora
        $dest = trim($row->original_url ?? '');
        if ($dest === '') {
            return redirect()->route('short.error');
        }

        // Redirige al URL guardado en tabla (tal cual)
        return redirect()->away($dest);
    }
}