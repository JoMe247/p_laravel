<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentsController extends Controller
{
    public function index()
    {
        // Por ahora no hay inserción de datos, contador en 0
        $totalDocuments = 0;
        $documents = collect(); // vacío

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
        if (!$authUser) return response()->json(['ok' => false], 401);

        $agency = $authUser->agency;

        // Ajusta columnas si tu tabla difiere
        $templates = DB::table('pdf_overlays')
            ->select('id', 'template_name')
            ->orderBy('template_name', 'asc')
            ->get();

        return response()->json(['ok' => true, 'templates' => $templates]);
    }

    // 2) Data del template (original_original + overlay_data)
    public function templateData($id)
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
        if (!$authUser) return response()->json(['ok' => false], 401);

        $tpl = DB::table('pdf_overlays')
            ->select('id', 'template_name', 'original_file_path', 'overlay_data')
            ->where('id', (int)$id)
            ->first();

        if (!$tpl) {
            return response()->json(['ok' => false, 'error' => 'Template not found'], 404);
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
            'original_file_path' => $tpl->original_file_path, // ✅ correcto
            'overlay_data' => $overlay,
        ]);
    }

    // 3) Search customers (para sugerencias)
    public function searchCustomers(Request $request)
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
        if (!$authUser) return response()->json(['ok' => false], 401);

        $q = trim((string)$request->query('q', ''));
        if ($q === '') return response()->json(['ok' => true, 'customers' => []]);

        $customers = DB::table('customers')
            ->select('ID', 'Name', 'Phone', 'Phone2', 'Email1', 'Email2')
            ->where(function ($w) use ($q) {
                $w->where('Name', 'like', "%{$q}%")
                    ->orWhere('Phone', 'like', "%{$q}%")
                    ->orWhere('Phone2', 'like', "%{$q}%");
            })
            ->limit(12)
            ->get();

        return response()->json(['ok' => true, 'customers' => $customers]);
    }

    // 4) Policies por customer
    public function customerPolicies($customerId)
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
        if (!$authUser) return response()->json(['ok' => false], 401);

        $agency = $authUser->agency;

        $policies = DB::table('policies')
            ->select('id', 'pol_number')
            ->where('customer_id', (string)$customerId) // ✅ confirmado
            ->orderBy('pol_number', 'asc')
            ->get();

        return response()->json(['ok' => true, 'policies' => $policies]);
    }

    // 5) Guardar PDF generado (recibe Blob desde JS)
    public function saveGeneratedPdf(Request $request)
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
        if (!$authUser) return response()->json(['ok' => false], 401);

        $request->validate([
            'template_id' => 'required|integer',
            'customer_id' => 'required',
            'customer_name' => 'required|string',
            'customer_phone' => 'required|string',
            'customer_email' => 'nullable|string',
            'policy_number' => 'nullable|string',
            'doc_type' => 'required|integer',
            'pdf' => 'required|file|mimes:pdf|max:20480',
        ]);

        $template = DB::table('pdf_overlays')
            ->select('template_name')
            ->where('id', (int)$request->template_id)
            ->first();

        if (!$template) {
            return response()->json(['ok' => false, 'error' => 'Template not found']);
        }

        // ----------------------------
        // 🔹 Variables base
        // ----------------------------
        $customerId   = trim($request->customer_id);
        $customerName = trim($request->customer_name);
        $templateName = trim($template->template_name);

        // Sanitizar nombres (quitar caracteres raros)
        $safeCustomerName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $customerName);
        $safeTemplateName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $templateName);

        // ----------------------------
        // 🔹 Carpeta destino
        // customerdocs/{id}_{name}
        // ----------------------------
        $folderName = $customerId . '_' . $safeCustomerName;
        $baseDir = "private/customerdocs/{$folderName}";

        // ----------------------------
        // 🔹 Nombre archivo
        // TemplateName_CustomerName_YYYYMMDD_HHMMSS.pdf
        // ----------------------------
        $datePart = now()->format('Ymd');
        $timePart = now()->format('His');

        $fileName = "{$safeTemplateName}_{$safeCustomerName}_{$datePart}_{$timePart}.pdf";

        // ----------------------------
        // 🔹 Guardar archivo
        // ----------------------------
        $storedPath = $request->file('pdf')->storeAs($baseDir, $fileName);

        // ----------------------------
        // 🔹 Insertar en tabla documents
        // ----------------------------
        DB::table('documents')->insert([
            'type'          => (int)$request->doc_type,
            'policy_number' => $request->policy_number ?? 'N/A',
            'insured_name'  => $customerName,
            'phone'         => $request->customer_phone,
            'email'         => $request->customer_email ?? '',
            'user'          => $authUser->username ?? $authUser->name ?? $authUser->email,
            'date'          => now()->toDateString(),
            'time'          => now()->format('H:i:s'),
            'path'          => $storedPath,
            'signed'        => 0,
        ]);

        // ==========================================================
        // ✅ NUEVO: Generar short_url + rand y guardarlo en tabla url
        // ==========================================================
        $createdBy = $authUser->username ?? $authUser->name ?? $authUser->email ?? 'unknown';

        $shortUrl = $this->generateUniqueShortUrl(8);
        $rand6    = $this->generateRand6();

        DB::table('url')->insert([
            'name'         => $customerName,           // mismo name del customer
            'type'         => (int)$request->doc_type, // o 1 fijo si quieres
            'created_by'   => $createdBy,
            'signed_by'    => $customerName,
            'short_url'    => $shortUrl,
            'original_url' => '',                      // por ahora lo llenas manual
            'clicks'       => 0,
            'signed'       => 'No',
            'date'         => now()->toDateString(),
            'time'         => now()->format('H:i:s'),
            'rand'         => $rand6,
        ]);

        $publicShortLink = url('/s/' . $shortUrl);

        return response()->json([
            'ok' => true,
            'file' => $fileName,
            'path' => $storedPath,

            // ✅ opcional: para mostrarlo en tu UI
            'short_url' => $shortUrl,
            'short_link' => $publicShortLink,
            'rand' => $rand6,
        ]);
    }

    public function streamTemplatePdf($id)
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
        if (!$authUser) abort(401);

        $tpl = DB::table('pdf_overlays')
            ->select('id', 'original_file_path')
            ->where('id', (int)$id)
            ->first();

        if (!$tpl || !$tpl->original_file_path) abort(404);

        // Normaliza slashes por si viene con "\"
        $rel = str_replace('\\', '/', $tpl->original_file_path);

        // Tus templates están en: storage/app/private/templates/...
        $full = storage_path('app/private/' . ltrim($rel, '/'));

        if (!file_exists($full)) abort(404);

        return response()->file($full, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="template.pdf"',
        ]);
    }

    // 6) Generar URL

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
        // 100000 - 999999 (6 dígitos reales)
        return random_int(100000, 999999);
    }
}
