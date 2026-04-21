<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShortUrlController extends Controller
{
    private function isAlreadySigned(object $row): bool
    {
        return strtolower(trim((string) ($row->signed ?? 'No'))) === 'yes';
    }

    public function show(string $short)
    {
        $row = DB::table('url')->where('short_url', $short)->first();

        if (!$row) {
            return redirect()->route('short.error');
        }

        // Si ya está firmado => mostrar vista signed_ready
        if ($this->isAlreadySigned($row)) {
            return view('sign.signed_ready', [
                'customerName' => $row->name ?? 'Customer',
            ]);
        }

        // +1 click solo al entrar al short link
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

        // Si ya está firmado => mostrar vista signed_ready
        if ($this->isAlreadySigned($row)) {
            return view('sign.signed_ready', [
                'customerName' => $row->name ?? 'Customer',
            ]);
        }

        $request->validate([
            'codigo' => ['required', 'digits:6'],
        ]);

        if ((string) $request->codigo !== (string) $row->rand) {
            return back()->withErrors([
                'codigo' => 'Wrong Access Code.'
            ])->withInput();
        }

        $dest = trim((string) ($row->original_url ?? ''));
        if ($dest === '') {
            return redirect()->route('short.error');
        }

        return redirect()->to($dest);
    }
}