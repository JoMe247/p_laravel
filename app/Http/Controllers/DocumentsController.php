<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Twilio\Rest\Client;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
class DocumentsController extends Controller
{
    public function index()
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
        if (!$authUser) {
            return redirect()->route('login');
        }

        $urlSub = DB::table('url')
            ->select(
                'short_url',
                'original_url',
                'hash',
                DB::raw("
                CASE
                    WHEN original_url LIKE '%id=%'
                        THEN CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(original_url, 'id=', -1), '&', 1) AS UNSIGNED)
                    ELSE CAST(SUBSTRING_INDEX(original_url, '/', -1) AS UNSIGNED)
                END AS document_id
            ")
            );

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
                'u.short_url',
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

        $docDate = now()->toDateString();
        $docTime = now()->format('H:i:s');

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
                'date' => $docDate,
                'time' => $docTime,
                'path' => $storedPath,
                'docsign_overlay' => $docSignOverlay ? json_encode($docSignOverlay) : null,
                'signed' => 0,
            ]);

            $publicBaseUrl = $this->getPublicBaseUrl();

            $originalUrl = $publicBaseUrl
                . '/sign?id=' . $documentId
                . '&name=' . urlencode($customerName)
                . '&hash=' . $hash
                . '&date=' . $docDate
                . '&time=' . $docTime;


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

            $publicShortLink = $this->getPublicBaseUrl() . '/s/' . $shortUrl;

            // Mensaje actual para firma de documento
            $smsBody = "Hello {$customerName}, you can now sign the document\n\n"
                . $publicShortLink . "\n\n"
                . "Use the following code to access: {$rand6}";

            // Ejemplo alternativo:
            // $smsBody = "Hello {$customerName}, here is your document link:\n\n{$publicShortLink}";

            $smsResult = [
                'ok' => false,
                'message' => 'SMS no enviado',
            ];

            try {
                $smsResult = $this->sendDocumentShortSms(
                    (string) ($request->customer_phone ?? ''),
                    $smsBody
                );
            } catch (\Throwable $e) {
                $smsResult = [
                    'ok' => false,
                    'message' => $e->getMessage(),
                ];
            }

            return response()->json([
                'ok' => true,
                'file' => $fileName,
                'path' => $storedPath,
                'short_url' => $shortUrl,
                'short_link' => $publicShortLink,
                'rand' => $rand6,
                'sms_sent' => (bool) ($smsResult['ok'] ?? false),
                'sms_message' => $smsResult['message'] ?? '',
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

    public function viewPdf($id)
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
        if (!$authUser) {
            abort(401);
        }

        $doc = DB::table('documents')
            ->where('id', (int) $id)
            ->first();

        if (!$doc) {
            abort(404);
        }

        $relativePath = ltrim(str_replace('\\', '/', (string) $doc->path), '/');

        if (!$relativePath || !Storage::disk('local')->exists($relativePath)) {
            abort(404);
        }

        $fullPath = Storage::disk('local')->path($relativePath);

        return response()->file($fullPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($relativePath) . '"',
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

    private function getPublicBaseUrl(): string
    {
        return rtrim(env('SHORT_PUBLIC_BASE_URL', config('app.url')), '/');
    }

    private function getAgencyTwilioNumber(): string
    {
        $user = Auth::guard('web')->user() ?? Auth::guard('sub')->user();

        if (!$user) {
            throw new \Exception('Usuario no autenticado');
        }

        $agency = $user->agency ?? null;

        $agencyUser = User::where('agency', $agency)->first();

        if (!$agencyUser || !$agencyUser->twilio_number) {
            throw new \Exception('No se encontró número Twilio asignado para esta agencia');
        }

        return $agencyUser->twilio_number;
    }

    private function normalizePhoneForSms(string $phone): string
    {
        $phone = trim($phone);

        if ($phone === '') {
            return '';
        }

        // Si ya viene con +
        if (str_starts_with($phone, '+')) {
            return '+' . preg_replace('/\D+/', '', substr($phone, 1));
        }

        $digits = preg_replace('/\D+/', '', $phone);

        if ($digits === '') {
            return '';
        }

        // 10 dígitos -> asumir +1
        if (strlen($digits) === 10) {
            return '+1' . $digits;
        }

        // 11 dígitos iniciando con 1 -> +1XXXXXXXXXX
        if (strlen($digits) === 11 && str_starts_with($digits, '1')) {
            return '+' . $digits;
        }

        // Cualquier otro caso internacional
        return '+' . $digits;
    }

    private function sendDocumentShortSms(string $to, string $body): array
    {
        $to = $this->normalizePhoneForSms($to);

        if ($to === '') {
            return [
                'ok' => false,
                'message' => 'Número del customer vacío o inválido',
            ];
        }

        $user = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
        if (!$user) {
            return [
                'ok' => false,
                'message' => 'Usuario no autenticado',
            ];
        }

        $agencyCode = $user->agency ?? null;
        if (!$agencyCode) {
            return [
                'ok' => false,
                'message' => 'El usuario no tiene agency asignada',
            ];
        }

        $agency = DB::table('agency')
            ->where('agency_code', $agencyCode)
            ->first();

        if (!$agency) {
            return [
                'ok' => false,
                'message' => 'No se encontró la agency vinculada al usuario',
            ];
        }

        $plan = DB::connection('doc_config')
            ->table('limits')
            ->where('account_type', $agency->account_type)
            ->first();

        if (!$plan) {
            return [
                'ok' => false,
                'message' => 'No se encontró el plan asignado a la agency',
            ];
        }

        $twilioNumber = $this->getAgencyTwilioNumber();

        $startMonth = Carbon::now()->startOfMonth();
        $endMonth   = Carbon::now()->endOfMonth();

        $monthlySmsCount = DB::table('sms')
            ->where('from', $twilioNumber)
            ->where('direction', 'outbound-api')
            ->whereBetween('created_at', [$startMonth, $endMonth])
            ->count();

        if ($monthlySmsCount >= (int) $plan->msg_limit) {
            return [
                'ok' => false,
                'limit_error' => true,
                'message' => 'Has alcanzado tu límite mensual de mensajes',
            ];
        }

        $twilioSid = config('services.twilio.sid') ?: env('TWILIO_ACCOUNT_SID');
        $twilioToken = config('services.twilio.token') ?: env('TWILIO_AUTH_TOKEN');

        if (!$twilioSid || !$twilioToken) {
            return [
                'ok' => false,
                'message' => 'Faltan credenciales de Twilio SMS en .env',
            ];
        }

        $client = new Client($twilioSid, $twilioToken);

        $message = $client->messages->create($to, [
            'from' => $twilioNumber,
            'body' => $body,
        ]);

        DB::table('sms')->updateOrInsert(
            ['sid' => $message->sid],
            [
                'from' => $message->from ?? $twilioNumber,
                'to' => $message->to ?? $to,
                'body' => $message->body ?? $body,
                'sent_by_id' => $user->id ?? null,
                'sent_by_name' => $user->name ?? $user->username ?? 'Usuario',
                'direction' => $message->direction ?? 'outbound-api',
                'status' => $message->status ?? null,
                'num_media' => intval($message->numMedia ?? 0),
                'media_urls' => json_encode([]),
                'date_sent' => isset($message->dateSent)
                    ? Carbon::parse($message->dateSent)->setTimezone('America/Mexico_City')
                    : now(),
                'date_created' => now(),
                'created_at' => now(),
                'updated_at' => now(),
                'deleted' => null,
            ]
        );

        return [
            'ok' => true,
            'sid' => $message->sid,
            'message' => 'SMS enviado correctamente',
            'to' => $to,
        ];
    }

    public function resendToPhone($id)
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
        if (!$authUser) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        $doc = DB::table('documents')->where('id', (int) $id)->first();
        if (!$doc) {
            return response()->json(['ok' => false, 'message' => 'Document not found'], 404);
        }

        $urlRow = $this->getUrlRowByDocumentId((int) $doc->id);
        if (!$urlRow) {
            return response()->json(['ok' => false, 'message' => 'URL record not found'], 404);
        }

        $customerName = trim((string) ($doc->insured_name ?? $urlRow->name ?? 'Customer'));
        $publicShortLink = $this->getPublicBaseUrl() . '/s/' . $urlRow->short_url;
        $rand6 = (string) ($urlRow->rand ?? '');

        $smsBody = "Hello {$customerName}, you can now sign the document\n\n"
            . $publicShortLink . "\n\n"
            . "Use the following code to access: {$rand6}";

        try {
            $smsResult = $this->sendDocumentShortSms((string) ($doc->phone ?? ''), $smsBody);

            if (!($smsResult['ok'] ?? false)) {
                return response()->json([
                    'ok' => false,
                    'message' => $smsResult['message'] ?? 'SMS could not be sent'
                ], 422);
            }

            return response()->json([
                'ok' => true,
                'message' => 'SMS resent successfully.'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function resendByEmail($id)
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
        if (!$authUser) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        $doc = DB::table('documents')->where('id', (int) $id)->first();
        if (!$doc) {
            return response()->json(['ok' => false, 'message' => 'Document not found'], 404);
        }

        $urlRow = $this->getUrlRowByDocumentId((int) $doc->id);
        if (!$urlRow) {
            return response()->json(['ok' => false, 'message' => 'URL record not found'], 404);
        }

        $customerName = trim((string) ($doc->insured_name ?? $urlRow->name ?? 'Customer'));
        $customerEmail = trim((string) ($doc->email ?? ''));
        $publicShortLink = $this->getPublicBaseUrl() . '/s/' . $urlRow->short_url;
        $rand6 = (string) ($urlRow->rand ?? '');

        $emailBody = "Hello {$customerName}, you can now sign the document\n\n"
            . $publicShortLink . "\n\n"
            . "Use the following code to access: {$rand6}";

        Log::info('DOCUMENT RESEND EMAIL SIMULATION', [
            'document_id' => (int) $doc->id,
            'to_email' => $customerEmail,
            'customer_name' => $customerName,
            'subject' => 'Document signature access',
            'body' => $emailBody,
            'sent_by' => $authUser->username ?? $authUser->name ?? $authUser->email ?? 'unknown',
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Email simulated. Check laravel.log.'
        ]);
    }

    public function destroy($id)
    {
        $authUser = Auth::guard('web')->user() ?? Auth::guard('sub')->user();
        if (!$authUser) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        $doc = DB::table('documents')->where('id', (int) $id)->first();
        if (!$doc) {
            return response()->json(['ok' => false, 'message' => 'Document not found'], 404);
        }

        $urlRow = $this->getUrlRowByDocumentId((int) $doc->id);

        try {
            DB::beginTransaction();

            if (!empty($doc->path) && Storage::disk('local')->exists($doc->path)) {
                Storage::disk('local')->delete($doc->path);
            }

            if ($urlRow) {
                DB::table('signing')
                    ->where('hash_id', $urlRow->hash)
                    ->delete();

                DB::table('url')
                    ->where('hash', $urlRow->hash)
                    ->delete();
            }

            DB::table('documents')
                ->where('id', (int) $doc->id)
                ->delete();

            DB::commit();

            return response()->json([
                'ok' => true,
                'message' => 'Document deleted successfully.'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'ok' => false,
                'message' => 'Error deleting document.',
                'detail' => $e->getMessage(),
            ], 500);
        }
    }

    private function getUrlRowByDocumentId(int $documentId): ?object
    {
        return DB::table('url')
            ->where(function ($q) use ($documentId) {
                $q->where('original_url', 'like', '%id=' . $documentId . '&%')
                    ->orWhere('original_url', 'like', '%id=' . $documentId);
            })
            ->orderByDesc('id')
            ->first();
    }
}