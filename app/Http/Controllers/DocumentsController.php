<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DocumentsController extends Controller
{
    public function index()
{
    $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
    if (!$authUser) {
        return redirect()->route('login');
    }

    // Subconsulta para extraer el document_id desde original_url:
    // ejemplo: /sign/abc123/15  =>  15
    $urlSub = DB::table('url')
        ->select(
            'original_url',
            'hash',
            DB::raw("CAST(SUBSTRING_INDEX(original_url, '/', -1) AS UNSIGNED) AS document_id")
        );

    // Subconsulta para saber si ese hash ya quedó firmado en signing
    $signingSub = DB::table('signing')
        ->select(
            'hash_id',
            DB::raw("
                MAX(
                    CASE
                        WHEN (date_2 IS NOT NULL AND date_2 <> '')
                             OR LOWER(COALESCE(status, '')) IN ('signed', 'completed', 'complete', 'done')
                        THEN 1
                        ELSE 0
                    END
                ) AS is_signed
            ")
        )
        ->groupBy('hash_id');

    $documents = DB::table('documents as d')
        ->leftJoin('pdf_overlays as p', 'p.id', '=', 'd.template_id')
        ->leftJoinSub($urlSub, 'u', function ($join) {
            $join->on('u.document_id', '=', 'd.id');
        })
        ->leftJoinSub($signingSub, 's', function ($join) {
            $join->on('s.hash_id', '=', 'u.hash');
        })
        ->select(
            'd.id',
            DB::raw("COALESCE(d.insured_name, '') as customer_name"),
            DB::raw("COALESCE(d.phone, '') as phone"),
            DB::raw("COALESCE(p.template_name, '') as template_name"),
            DB::raw("COALESCE(d.policy_number, '') as policy_number"),
            'd.date',
            'u.original_url',
            DB::raw("COALESCE(s.is_signed, 0) as is_signed")
        )
        ->orderByDesc('d.id')
        ->get();

    $totalDocuments = $documents->count();

    return view('documents', compact('totalDocuments', 'documents'));
}

    public function createDocument()
    {
        return view('documents.create_document');
    }

    // 1) Opciones del select Template (pdf_overlays)
    public function templateOptions()
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
        if (!$authUser) {
            return response()->json(['ok' => false], 401);
        }

        $templates = DB::table('pdf_overlays')
            ->select('id', 'template_name')
            ->orderBy('template_name', 'asc')
            ->get();

        return response()->json([
            'ok' => true,
            'templates' => $templates
        ]);
    }

    // 2) Data del template
    public function templateData($id)
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
        if (!$authUser) {
            return response()->json(['ok' => false], 401);
        }

        $tpl = DB::table('pdf_overlays')
            ->select('id', 'template_name', 'original_file_path', 'overlay_data')
            ->where('id', (int) $id)
            ->first();

        if (!$tpl) {
            return response()->json([
                'ok' => false,
                'error' => 'Template not found'
            ], 404);
        }

        $overlay = [];
        if (!empty($tpl->overlay_data)) {
            $decoded = json_decode($tpl->overlay_data, true);
            $overlay = is_array($decoded) ? $decoded : [];
        }

        return response()->json([
            'ok' => true,
            'id' => $tpl->id,
            'template_name' => $tpl->template_name ?? '',
            'original_file_path' => $tpl->original_file_path,
            'overlay_data' => $overlay,
        ]);
    }

    // 3) Search customers
    public function searchCustomers(Request $request)
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
        if (!$authUser) {
            return response()->json(['ok' => false], 401);
        }

        $q = trim((string) $request->query('q', ''));
        if ($q === '') {
            return response()->json(['ok' => true, 'customers' => []]);
        }

        $customers = DB::table('customers')
            ->select('ID', 'Name', 'Phone', 'Phone2', 'Email1', 'Email2')
            ->where(function ($w) use ($q) {
                $w->where('Name', 'like', "%{$q}%")
                    ->orWhere('Phone', 'like', "%{$q}%")
                    ->orWhere('Phone2', 'like', "%{$q}%");
            })
            ->limit(12)
            ->get();

        return response()->json([
            'ok' => true,
            'customers' => $customers
        ]);
    }

    // 4) Policies por customer
    public function customerPolicies($customerId)
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
        if (!$authUser) {
            return response()->json(['ok' => false], 401);
        }

        $policies = DB::table('policies')
            ->select('id', 'pol_number')
            ->where('customer_id', (string) $customerId)
            ->orderBy('pol_number', 'asc')
            ->get();

        return response()->json([
            'ok' => true,
            'policies' => $policies
        ]);
    }

    // 5) Guardar PDF generado
    public function saveGeneratedPdf(Request $request)
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
        if (!$authUser) {
            return response()->json(['ok' => false], 401);
        }

        $request->validate([
            'template_id' => 'required|integer',
            'customer_id' => 'required',
            'customer_name' => 'required|string',
            'customer_phone' => 'required|string',
            'customer_email' => 'nullable|string',
            'policy_number' => 'nullable|string',
            'doc_type' => 'required|integer',
            'pdf' => 'required|file|mimes:pdf|max:20480',

            // info agent
            'browser_agent' => 'nullable|string',
            'os_agent' => 'nullable|string',
            'dName_agent' => 'nullable|string',
            'device_agent' => 'nullable|string',
            'coordinates_agent' => 'nullable|string',

            // info ip
            'ip_agent' => 'nullable|string',
            'city_agent' => 'nullable|string',
            'country_agent' => 'nullable|string',
            'agent_region' => 'nullable|string',
        ]);

        $template = DB::table('pdf_overlays')
            ->select('id', 'template_name', 'overlay_data')
            ->where('id', (int) $request->template_id)
            ->first();

        if (!$template) {
            return response()->json([
                'ok' => false,
                'error' => 'Template not found'
            ], 404);
        }

        $docSignOverlay = $this->extractDocSignOverlay($template->overlay_data ?? null);

        $customerId = trim((string) $request->customer_id);
        $customerName = trim((string) $request->customer_name);
        $templateName = trim((string) $template->template_name);

        $safeCustomerName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $customerName);
        $safeTemplateName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $templateName);

        $folderName = $customerId . '_' . $safeCustomerName;
        $baseDir = "private/customerdocs/{$folderName}";

        $datePart = now()->format('Ymd');
        $timePart = now()->format('His');

        $fileName = "{$safeTemplateName}_{$safeCustomerName}_{$datePart}_{$timePart}.pdf";
        $storedPath = $request->file('pdf')->storeAs($baseDir, $fileName);

        $createdBy = $authUser->username ?? $authUser->name ?? $authUser->email ?? 'unknown';

        $shortUrl = $this->generateUniqueShortUrl(8);
        $rand6 = $this->generateRand6();
        $hash = md5($shortUrl);

        try {
            DB::beginTransaction();

            $documentId = DB::table('documents')->insertGetId([
    'type' => (int) $request->doc_type,
    'template_id' => (int) $request->template_id,
    'policy_number' => $request->policy_number ?? 'N/A',
    'id_customer' => $customerId,
    'insured_name' => $customerName,
    'phone' => $request->customer_phone,
    'email' => $request->customer_email ?? '',
    'user' => $createdBy,
    'date' => now()->toDateString(),
    'time' => now()->format('H:i:s'),
    'path' => $storedPath,
    'docsign_overlay' => $docSignOverlay ? json_encode($docSignOverlay) : null,
    'signed' => 0, 
                ]);

            $originalUrl = url("/sign/{$shortUrl}/{$documentId}");

            DB::table('url')->insert([
                'name' => $customerName,
                'type' => (int) $request->doc_type,
                'created_by' => $createdBy,
                'signed_by' => $customerName,
                'short_url' => $shortUrl,
                'original_url' => $originalUrl,
                'clicks' => 0,
                'signed' => 'No',
                'date' => now()->toDateString(),
                'time' => now()->format('H:i:s'),
                'rand' => $rand6,
                'hash' => $hash,
            ]);

            DB::table('signing')->insert([
                'name' => $customerName,
                'client' => $customerName,
                'agent' => $createdBy,
                'type' => (string) $request->doc_type,
                'path' => $storedPath,

                'date_1' => now()->toDateString(),
                'time_1' => now()->format('H:i:s'),

                'date_2' => null,
                'time_2' => null,

                'city_client' => '',
                'country_client' => '',
                'ip_client' => '',
                'device_client' => '',
                'browser_client' => '',
                'os_client' => '',
                'dName_client' => '',
                'coordinates_client' => '',

                'city_agent' => substr((string) ($request->city_agent ?? ''), 0, 50),
                'country_agent' => substr((string) ($request->country_agent ?? ''), 0, 80),
                'ip_agent' => substr((string) ($request->ip_agent ?? $request->ip() ?? ''), 0, 80),
                'device_agent' => substr((string) ($request->device_agent ?? ''), 0, 120),
                'browser_agent' => substr((string) ($request->browser_agent ?? ''), 0, 150),
                'os_agent' => substr((string) ($request->os_agent ?? ''), 0, 50),
                'dName_agent' => substr((string) ($request->dName_agent ?? ''), 0, 260),
                'coordinates_agent' => substr((string) ($request->coordinates_agent ?? ''), 0, 80),
                'agent_region' => substr((string) ($request->agent_region ?? ''), 0, 120),

                'last_seen' => '',
                'status' => 'Pending',
                'hash_id' => $hash,
                'opened' => 0,
                'client_region' => '',
                'agent_region' => substr((string) ($request->agent_region ?? ''), 0, 120),
            ]);

            DB::commit();

            $publicShortLink = url('/s/' . $shortUrl);

            return response()->json([
                'ok' => true,
                'file' => $fileName,
                'path' => $storedPath,
                'short_url' => $shortUrl,
                'short_link' => $publicShortLink,
                'rand' => $rand6,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($storedPath && Storage::disk('local')->exists($storedPath)) {
                Storage::disk('local')->delete($storedPath);
            }

            return response()->json([
                'ok' => false,
                'error' => 'Error saving generated document.',
                'detail' => $e->getMessage(),
            ], 500);
        }
    }

    public function streamTemplatePdf($id)
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
        if (!$authUser) {
            abort(401);
        }

        $tpl = DB::table('pdf_overlays')
            ->select('id', 'original_file_path')
            ->where('id', (int) $id)
            ->first();

        if (!$tpl || !$tpl->original_file_path) {
            abort(404);
        }

        $rel = str_replace('\\', '/', $tpl->original_file_path);
        $full = storage_path('app/private/' . ltrim($rel, '/'));

        if (!file_exists($full)) {
            abort(404);
        }

        return response()->file($full, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="template.pdf"',
        ]);
    }

    private function generateRandomString(int $length = 8): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $max = strlen($characters) - 1;
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $max)];
        }

        return $randomString;
    }

    private function generateUniqueShortUrl(int $length = 8): string
    {
        do {
            $short = $this->generateRandomString($length);
            $exists = DB::table('url')->where('short_url', $short)->exists();
        } while ($exists);

        return $short;
    }

    private function generateRand6(): int
    {
        return random_int(100000, 999999);
    }

    private function extractDocSignOverlay(?string $overlayJson): ?array
{
    if (!$overlayJson) {
        return null;
    }

    $items = json_decode($overlayJson, true);
    if (!is_array($items)) {
        return null;
    }

    foreach ($items as $item) {
        $rawText = (string) ($item['text'] ?? '');

        // Normalizar:
        // 1) quitar saltos de línea
        // 2) quitar llaves {}
        // 3) quitar espacios
        $normalized = preg_replace('/\s+/', '', $rawText);
        $normalized = str_replace(['{', '}'], '', $normalized);

        if ($normalized === 'DocSign@') {
            return [
                'page'   => (int) ($item['page'] ?? 1),
                'x'      => (float) ($item['x'] ?? 0),
                'y'      => (float) ($item['y'] ?? 0),
                'width'  => 160,
                'height' => 55,
            ];
        }
    }

    return null;
}
}
