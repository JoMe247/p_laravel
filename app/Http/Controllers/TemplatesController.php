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
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();

        if (!$authUser) {
            return redirect()->route('login');
        }

        $limitData = $this->getDocsLimitData();

        if ($limitData['isDocsOverLimit']) {
            return redirect()->route('documents.index')
                ->with('doc_limit_error', 'Has alcanzado el límite mensual de documentos de tu plan.');
        }

        return view('create_template');
    }

    public function store(Request $request)
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();

        if (!$authUser) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $limitData = $this->getDocsLimitData();

        if ($limitData['isDocsOverLimit']) {
            return response()->json([
                'ok' => false,
                'limit_error' => true,
                'message' => 'Has alcanzado el límite mensual de documentos de tu plan.',
            ], 422);
        }

        $request->validate([
            'pdf' => ['required', 'file', 'mimes:pdf', 'max:20480'],
            'pdfModified' => ['required', 'file', 'mimes:pdf', 'max:20480'],
            'overlayData' => ['required', 'string'],
            'templateName' => ['required', 'string', 'max:255'],
        ]);

        $userId = (string) ($authUser->id ?? $authUser->ID ?? '');

        $templateName = trim((string) $request->templateName);
        $stamp = now()->format('m-d-Y_H_i_s');
        $safeName = Str::slug($templateName, '-');

        $baseDir = "templates/{$safeName}-{$stamp}";
        Storage::disk('local')->makeDirectory($baseDir);

        $originalPath = $request->file('pdf')->storeAs(
            $baseDir,
            'original_original.pdf',
            'local'
        );

        $modifiedPath = $request->file('pdfModified')->storeAs(
            $baseDir,
            'modified_original.pdf',
            'local'
        );

        DB::table('pdf_overlays')->insert([
            'user_id' => $userId,
            'template_name' => $templateName,
            'original_file_path' => $originalPath,
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

    private function getDocsLimitData(): array
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();

        if (!$authUser) {
            return [
                'agencyCode' => null,
                'docLimit' => 0,
                'monthlyDocCount' => 0,
                'isDocsOverLimit' => false,
            ];
        }

        $agencyCode = $authUser->agency ?? null;

        if (!$agencyCode) {
            return [
                'agencyCode' => null,
                'docLimit' => 0,
                'monthlyDocCount' => 0,
                'isDocsOverLimit' => false,
            ];
        }

        $agency = DB::table('agency')
            ->where('agency_code', $agencyCode)
            ->first();

        if (!$agency) {
            return [
                'agencyCode' => $agencyCode,
                'docLimit' => 0,
                'monthlyDocCount' => 0,
                'isDocsOverLimit' => false,
            ];
        }

        $plan = DB::connection('doc_config')
            ->table('limits')
            ->where('account_type', $agency->account_type)
            ->first();

        $docLimit = (int) ($plan->doc_limit ?? 0);

        $monthlyDocCount = DB::table('documents as d')
            ->join('customers as c', 'c.ID', '=', 'd.id_customer')
            ->where('c.agency', $agencyCode)
            ->whereBetween('d.date', [
                now()->startOfMonth()->toDateString(),
                now()->endOfMonth()->toDateString(),
            ])
            ->count();

        return [
            'agencyCode' => $agencyCode,
            'docLimit' => $docLimit,
            'monthlyDocCount' => $monthlyDocCount,
            'isDocsOverLimit' => $docLimit > 0
                ? $monthlyDocCount >= $docLimit
                : false,
        ];
    }
}
