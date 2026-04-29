<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\Log;

class SigningController extends Controller
{
    public function show(string $short, int $docId)
    {
        $urlRow = DB::table('url')->where('short_url', $short)->first();
        if (!$urlRow) abort(404);

        $doc = DB::table('documents')->where('id', $docId)->first();
        if (!$doc) abort(404);

        if (trim((string) $doc->insured_name) !== trim((string) $urlRow->name)) {
            abort(403);
        }

        // ✅ Registrar apertura real de la vista sign
        $this->touchSigningOpen($urlRow, $doc);

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

        if (trim((string) $doc->insured_name) !== trim((string) $urlRow->name)) {
            abort(403);
        }

        $rel = str_replace('\\', '/', (string) $doc->path);
        $rel = ltrim($rel, '/');

        if (!Storage::disk('local')->exists($rel)) {
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
        try {
            $urlRow = DB::table('url')->where('short_url', $short)->first();
            if (!$urlRow) {
                return response()->json(['ok' => false, 'error' => 'Not found'], 404);
            }

            $doc = DB::table('documents')->where('id', $docId)->first();
            if (!$doc) {
                return response()->json(['ok' => false, 'error' => 'Doc not found'], 404);
            }

            if (trim((string) $doc->insured_name) !== trim((string) $urlRow->name)) {
                return response()->json(['ok' => false, 'error' => 'Forbidden'], 403);
            }

            $request->validate([
                'pdf' => 'required|file|mimes:pdf|max:20480',
                'signature_data' => 'required|string',
                'browser_client' => 'nullable|string',
                'os_client' => 'nullable|string',
                'dName_client' => 'nullable|string',
                'device_client' => 'nullable|string',
                'coordinates_client' => 'nullable|string',
                // info ip
                'ip_client' => 'nullable|string',
                'city_client' => 'nullable|string',
                'country_client' => 'nullable|string',
                'client_region' => 'nullable|string',
            ]);
            $relativePdfPath = ltrim(str_replace('\\', '/', (string) $doc->path), '/');

            if (!Storage::disk('local')->exists($relativePdfPath)) {
                return response()->json(['ok' => false, 'error' => 'Original PDF not found'], 404);
            }

            $newPdfContents = file_get_contents($request->file('pdf')->getRealPath());
            Storage::disk('local')->put($relativePdfPath, $newPdfContents);

            DB::table('documents')
                ->where('id', $docId)
                ->update([
                    'signed' => 1,
                ]);

            DB::table('url')
                ->where('short_url', $short)
                ->update([
                    'signed' => 'Yes',
                ]);

            $this->ensureSigningRowExists($urlRow, $doc);

            $geo = $this->resolveGeoFromIp($request->ip());

            DB::table('signing')
                ->where('hash_id', $urlRow->hash)
                ->update([
                    'path' => $relativePdfPath,
                    'date_2' => now()->toDateString(),
                    'time_2' => now()->format('H:i:s'),

                    'city_client' => substr((string) ($request->city_client ?? ''), 0, 50),
                    'country_client' => substr((string) ($request->country_client ?? ''), 0, 80),
                    'client_region' => substr((string) ($request->client_region ?? ''), 0, 120),
                    'ip_client' => substr((string) ($request->ip_client ?? $request->ip() ?? ''), 0, 80),
                    'device_client' => substr((string) ($request->device_client ?? ''), 0, 120),
                    'browser_client' => substr((string) ($request->browser_client ?? ''), 0, 150),
                    'os_client' => substr((string) ($request->os_client ?? ''), 0, 50),
                    'dName_client' => substr((string) ($request->dName_client ?? ''), 0, 260),
                    'coordinates_client' => substr((string) ($request->coordinates_client ?? ''), 0, 100),

                    'last_seen' => now()->format('m/d/Y \a\t H:i:s'),
                    'status' => 'Completed',
                ]);

            $signing = DB::table('signing')->where('hash_id', $urlRow->hash)->first();
            if (!$signing) {
                return response()->json(['ok' => false, 'error' => 'Signing row not found'], 500);
            }

            $certificatePdfBinary = $this->generateCertificatePdf($signing, $urlRow, $doc, $request->signature_data);

            $tempCertificatePath = storage_path('app/temp_certificate_' . $docId . '.pdf');
            file_put_contents($tempCertificatePath, $certificatePdfBinary);

            $signedPdfFullPath = Storage::disk('local')->path($relativePdfPath);
            $mergedPath = storage_path('app/temp_merged_' . $docId . '.pdf');

            $this->mergePdfFiles([
                $signedPdfFullPath,
                $tempCertificatePath,
            ], $mergedPath);

            Storage::disk('local')->put($relativePdfPath, file_get_contents($mergedPath));

            @unlink($tempCertificatePath);
            @unlink($mergedPath);

            return response()->json([
                'ok' => true,
                'message' => 'PDF firmado guardado y certificado agregado correctamente.',
            ]);
        } catch (\Throwable $e) {
            Log::error('SIGN SAVE ERROR', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'ok' => false,
                'error' => 'Error al guardar la firma',
                'detail' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    }

    private function touchSigningOpen(object $urlRow, object $doc): void
    {
        $this->ensureSigningRowExists($urlRow, $doc);

        $current = DB::table('signing')->where('hash_id', $urlRow->hash)->first();
        if (!$current) {
            return;
        }

        DB::table('signing')
            ->where('hash_id', $urlRow->hash)
            ->update([
                'opened' => ((int) $current->opened) + 1,
                'last_seen' => now()->format('m/d/Y \a\t H:i:s'),
            ]);
    }

    private function ensureSigningRowExists(object $urlRow, object $doc): void
    {
        $exists = DB::table('signing')->where('hash_id', $urlRow->hash)->exists();
        if ($exists) {
            return;
        }

        DB::table('signing')->insert([
            'name' => $urlRow->name ?? '',
            'client' => $urlRow->name ?? '',
            'agent' => $urlRow->created_by ?? '',
            'type' => (string) ($doc->type ?? $urlRow->type ?? ''),
            'path' => (string) ($doc->path ?? ''),
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

            'city_agent' => '',
            'country_agent' => '',
            'ip_agent' => '',
            'device_agent' => '',
            'browser_agent' => '',
            'os_agent' => '',
            'dName_agent' => '',
            'coordinates_agent' => '',

            'last_seen' => '',
            'status' => 'Pending',
            'hash_id' => $urlRow->hash ?? '',
            'opened' => 0,
            'client_region' => '',
            'agent_region' => '',
        ]);
    }

    private function generateCertificatePdf(object $signing, object $urlRow, object $doc, string $signatureData): string
    {
        $agentInfo = $this->resolveAgentInfo((string) ($urlRow->created_by ?? ''));

        $logoPath = public_path('img/logo.png');
        $logoBase64 = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : '';

        $subject = $this->resolveDocumentSubject($doc);

        $createdDate = $signing->date_1 ? date('m/d/Y', strtotime($signing->date_1)) : '';
        $createdTime = $signing->time_1 ? date('H:i:s', strtotime($signing->time_1)) : '';

        $signedDate = $signing->date_2 ? date('m/d/Y', strtotime($signing->date_2)) : '';
        $signedTime = $signing->time_2 ? date('H:i:s', strtotime($signing->time_2)) : '';

        $viewed = $signing->last_seen ?: '';
        $clicks = (int) ($urlRow->clicks ?? 0);
        $status = $signing->status ?: 'Completed';

        $customerPhone = $this->formatUsPhone((string) ($doc->phone ?? ''));
        $customerEmail = (string) ($doc->email ?? '');

        $montserratRegular = str_replace('\\', '/', storage_path('fonts/Montserrat-Regular.ttf'));
        $montserratBold = str_replace('\\', '/', storage_path('fonts/Montserrat-Bold.ttf'));

        $html = '
    <html>
    <head>
        <meta charset="utf-8">
        <style>
@page{
                margin: 0;
            }

@font-face {
                font-family: "Montserrat";
                font-style: normal;
                font-weight: 400;
                src: url("file:///' . $montserratRegular . '") format("truetype");
            }
@font-face {
                font-family: "Montserrat";
                font-style: normal;
                font-weight: 700;
                src: url("file:///' . $montserratBold . '") format("truetype");
            }

            body {
                margin: 0;
                padding: 0;
                font-family: "Montserrat", sans-serif;
                color: #555;
                font-size: 11px;
                line-height: 1.18;
            }

            .wrap {
                margin: 0;
                padding: 10px 12px 12px 12px;
                border: 1px solid #999;
                box-sizing: border-box;
            }

            .logo {
                width: 205px;
                display: block;
                margin-left: auto;
                margin-right: 3%;
                margin-top: 8px;
                margin-bottom: 8px;
            }

            h2 {
                margin: 0 0 8px 0;
                color: #555;
                font-size: 20px;
                font-weight: 700;
            }

            h3 {
                color: #333;
                background-color: #ddd;
                padding: 5px 8px;
                margin: 8px 0 6px 0;
                font-size: 12px;
                font-weight: 700;
            }

            .row {
                margin: 0 0 2px 0;
                word-break: break-word;
            }

            .row.ua {
                font-size: 9px;
                line-height: 1.1;
            }

            .status-ok {
                color: #45b82f;
                font-weight: 700;
            }

            .grid {
                width: 100%;
                border-collapse: collapse;
                table-layout: fixed;
            }

            .grid td {
                vertical-align: top;
            }

            .signer-left {
                width: 70%;
                padding-right: 8px;
            }

            .signer-right {
                width: 30%;
                text-align: center;
                vertical-align: top;
                padding-top: 4px;
            }

            .sig-label {
                font-size: 10px;
                font-weight: 700;
                color: #555;
                margin-bottom: 6px;
            }

            .sig-img {
                width: 145px;
                height: auto;
            }

            .two-col td {
                width: 50%;
                vertical-align: top;
            }
        </style>
    </head>
    <body>
        <div class="wrap">';

        if ($logoBase64 !== '') {
            $html .= '<img src="' . $logoBase64 . '" class="logo">';
        }

        $html .= '
            <h2>Certificate Of Completion</h2>

            <h3>Document</h3>
            <table class="grid two-col">
                <tr>
                    <td>
                        <div class="row">ID: ' . e((string) $signing->id) . '</div>
                    </td>
                    <td>
                        <div class="row">Operation ID: ' . e((string) ($urlRow->hash ?? '')) . '</div>
                    </td>
                </tr>
            </table>

            <div class="row">Subject: ' . e($subject) . '</div>
            <div class="row">Created: ' . e($createdDate) . ' at ' . e($createdTime) . '</div>
            <div class="row">Viewed: ' . e($viewed) . '</div>
            <div class="row"><strong>Signed: ' . e($signedDate) . ' at ' . e($signedTime) . '</strong></div>
            <div class="row">
                Times Accessed: ' . e((string) $signing->opened) . '
                &nbsp;&nbsp;&nbsp; URL Visits: ' . e((string) $clicks) . '
                &nbsp;&nbsp;&nbsp; Status: <span class="status-ok">' . e($status) . '</span>
            </div>

            <h3>Signer Information</h3>
            <table class="grid">
                <tr>
                    <td class="signer-left">
                        <div class="row">Name: ' . e((string) ($urlRow->name ?? '')) . '</div>
                        <div class="row">Phone: ' . e($customerPhone) . '</div>
                        <div class="row">Email: ' . e($customerEmail) . '</div>
                        <div class="row">Detected Country: ' . e((string) $signing->country_client) . '</div>
                        <div class="row">Detected City: ' . e((string) $signing->city_client) . '</div>
                        <div class="row">Detected Region: ' . e((string) $signing->client_region) . '</div>
                        <div class="row">Using IP Address: ' . e((string) $signing->ip_client) . '</div>
                        <div class="row">Signer Proximate Coordinates: ' . e((string) $signing->coordinates_client) . '</div>
                        <div class="row">Signed Device: ' . e((string) $signing->device_client) . '</div>
                        <div class="row">Signed Operative System: ' . e((string) $signing->os_client) . '</div>
                        <div class="row">Signed Browser: ' . e((string) $signing->browser_client) . '</div>
                        <div class="row ua">Signed Agent Info: ' . e((string) $signing->dName_client) . '</div>
                    </td>
                    <td class="signer-right">
                        <div class="sig-label">Signature ' . e($signedDate) . ' at ' . e($signedTime) . '</div>
                        <img src="' . $signatureData . '" class="sig-img">
                    </td>
                </tr>
            </table>

            <h3>Holder Agent Information</h3>
            
            <div class="row">Agent Name: ' . e((string) $agentInfo['name']) . '</div>
            <div class="row">Agent User: ' . e((string) $signing->agent) . '</div>
            <div class="row">Agent Email: ' . e((string) $agentInfo['email']) . '</div>
            <div class="row">Agent Country: ' . e((string) $signing->country_agent) . '</div>
            <div class="row">Agent City: ' . e((string) $signing->city_agent) . '</div>
            <div class="row">Agent Region: ' . e((string) $signing->agent_region) . '</div>
            <div class="row">Agent IP: ' . e((string) $signing->ip_agent) . '</div>
            <div class="row">Agent Device: ' . e((string) $signing->device_agent) . '</div>
            <div class="row">Agent Operative System: ' . e((string) $signing->os_agent) . '</div>
            <div class="row">Agent Browser: ' . e((string) $signing->browser_agent) . '</div>
            <div class="row ua">Agent Device Name and Version: ' . e((string) $signing->dName_agent) . '</div>
            <div class="row">Agent Coordinates: ' . e((string) $signing->coordinates_agent) . '</div>
        </div>
    </body>
    </html>';

        return Pdf::loadHTML($html)
            ->setPaper('letter', 'portrait')
            ->output();
    }

    private function mergePdfFiles(array $files, string $outputPath): void
    {
        $pdf = new Fpdi();

        foreach ($files as $file) {
            $pageCount = $pdf->setSourceFile($file);

            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($templateId);

                $orientation = $size['width'] > $size['height'] ? 'L' : 'P';
                $pdf->AddPage($orientation, [$size['width'], $size['height']]);
                $pdf->useTemplate($templateId);
            }
        }

        $pdf->Output('F', $outputPath);
    }

    private function resolveAgentInfo(string $createdBy): array
    {
        $user = DB::table('users')
            ->where('username', $createdBy)
            ->orWhere('name', $createdBy)
            ->orWhere('email', $createdBy)
            ->first();

        if ($user) {
            return [
                'name' => $user->name ?? $user->username ?? $createdBy,
                'email' => $user->email ?? '',
            ];
        }

        $subUser = DB::table('sub_users')
            ->where('username', $createdBy)
            ->orWhere('name', $createdBy)
            ->orWhere('email', $createdBy)
            ->first();

        if ($subUser) {
            return [
                'name' => $subUser->name ?? $subUser->username ?? $createdBy,
                'email' => $subUser->email ?? '',
            ];
        }

        return [
            'name' => $createdBy,
            'email' => '',
        ];
    }

    private function resolveDocumentSubject(object $doc): string
    {
        $type = (string) ($doc->type ?? '');

        return match ($type) {
            '1' => 'Endorsement',
            default => 'Document',
        };
    }

    private function formatUsPhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);

        if (strlen($digits) === 10) {
            return '+1 (' . substr($digits, 0, 3) . ') ' . substr($digits, 3, 3) . '-' . substr($digits, 6);
        }

        return $phone;
    }

    private function resolveGeoFromIp(?string $ip): array
    {
        // Aquí puedes conectar tu lógica real de GeoIP si ya la tienes.
        // Por ahora no invento ciudad/país/región, para no guardarte basura.
        return [
            'country' => '',
            'city' => '',
            'region' => '',
        ];
    }

    public function showByQuery(Request $request)
    {
        $docId = (int) $request->query('id');
        $hash = trim((string) $request->query('hash', ''));
        $name = trim((string) $request->query('name', ''));
        $date = trim((string) $request->query('date', ''));
        $time = trim((string) $request->query('time', ''));

        if (!$docId || $hash === '' || $name === '' || $date === '' || $time === '') {
            abort(404);
        }

        $urlRow = DB::table('url')
            ->where('hash', $hash)
            ->first();

        if (!$urlRow) {
            abort(404);
        }

        $doc = DB::table('documents')
            ->where('id', $docId)
            ->first();

        if (!$doc) {
            abort(404);
        }

        // Validar coincidencia exacta de todo
        if ((int) $doc->id !== $docId) {
            abort(403);
        }

        if (trim((string) $urlRow->hash) !== $hash) {
            abort(403);
        }

        if (trim((string) $urlRow->name) !== $name) {
            abort(403);
        }

        if (trim((string) $doc->insured_name) !== $name) {
            abort(403);
        }

        if (trim((string) $doc->date) !== $date) {
            abort(403);
        }

        if (trim((string) $doc->time) !== $time) {
            abort(403);
        }

        $this->touchSigningOpen($urlRow, $doc);

        return view('sign.sign', [
            'short' => $urlRow->short_url,
            'docId' => $docId,
            'customerName' => $urlRow->name,
            'docsignOverlay' => json_decode($doc->docsign_overlay ?? 'null', true),
        ]);
    }
}
