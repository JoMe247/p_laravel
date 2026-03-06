<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class TemplatesController extends Controller
{
    public function create()
    {
        return view('create_template');
    }

    public function store(Request $request)
    {
        $request->validate([
            'pdf' => ['required', 'file', 'mimes:pdf', 'max:20480'],         // 20MB
            'pdfModified' => ['required', 'file', 'mimes:pdf', 'max:20480'], // 20MB
            'overlayData' => ['required', 'string'],
            'templateName' => ['required', 'string', 'max:255'],
        ]);

        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
        if (!$authUser) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // OJO: tu tabla original guarda user_id como varchar.
        // Aquí guardo el ID real del usuario/sub-user.
        $userId = (string)($authUser->id ?? $authUser->ID ?? '');

        $templateName = trim($request->templateName);
        $stamp = now()->format('m-d-Y H_i_s');
        $safeName = Str::slug($templateName, '-');

        // Guardado en storage/app/templates/...
        $baseDir = "templates/{$safeName}-{$stamp}";
        Storage::disk('local')->makeDirectory($baseDir);

        $originalPath = $request->file('pdf')->storeAs($baseDir, 'original_original.pdf', 'local');
        $modifiedPath = $request->file('pdfModified')->storeAs($baseDir, 'modified_original.pdf', 'local');

        DB::table('pdf_overlays')->insert([
            'user_id' => $userId,
            'template_name' => $templateName,
            'original_file_path' => $originalPath,  // ej: templates/1/mi-template-.../original_original.pdf
            'modified_file_path' => $modifiedPath,
            'overlay_data' => $request->overlayData,
            'created_at' => now(),
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Template saved successfully',
            'template_name' => $templateName,
        ]);
    }
}
