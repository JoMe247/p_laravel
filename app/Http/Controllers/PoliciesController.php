<?php

namespace App\Http\Controllers;

use App\Models\Policy;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PoliciesController extends Controller
{
    public function index($customer_id)
    {
        $customer = Customer::findOrFail($customer_id);

        $policies = Policy::where('customer_id', $customer_id)
            ->orderBy('id', 'desc')
            ->get();

        $fieldLabels = $this->policyFieldLabels();
        $self = $this;

        $policyLog = $policies
            ->flatMap(function ($policy) use ($fieldLabels, $self) {
                $logs = is_array($policy->policy_log) ? $policy->policy_log : [];

                return collect($logs)->map(function ($entry) use ($fieldLabels, $self) {
                    $snapshot = is_array($entry['snapshot'] ?? null) ? $entry['snapshot'] : [];
                    $vehicles = $self->normalizeVehiclesArray($snapshot['vehicules'] ?? []);
                    $snapshot['vehicules'] = $vehicles;

                    $changedFields = collect($entry['changed_fields'] ?? [])
                        ->map(fn($field) => $fieldLabels[$field] ?? $field)
                        ->values()
                        ->all();

                    return [
                        'created_at'           => $entry['created_at'] ?? null,
                        'action'               => $entry['action'] ?? 'updated',
                        'action_text'          => match ($entry['action'] ?? 'updated') {
                            'created' => 'Created',
                            'updated' => 'Updated',
                            default   => ucfirst((string) ($entry['action'] ?? 'updated')),
                        },
                        'changed_by'           => $entry['changed_by'] ?? 'System',
                        'changed_fields'       => $changedFields,
                        'changed_fields_text'  => !empty($changedFields) ? implode(', ', $changedFields) : '-',
                        'vehicle_changes_text' => $entry['vehicle_changes_text'] ?? '-',
                        'vehicles_text'        => $self->formatVehiclesForLog($vehicles),
                        'snapshot'             => $snapshot,
                    ];
                });
            })
            ->sortByDesc(function ($log) {
                return strtotime($log['created_at'] ?? '') ?: 0;
            })
            ->values();

        return view('policies', compact('customer', 'policies', 'policyLog'));
    }

    public function store(Request $request, $customer_id)
    {
        $data = $request->validate([
            'pol_carrier'      => 'nullable|string',
            'pol_number'       => 'nullable|string',
            'pol_url'          => 'nullable|string',
            'pol_expiration'   => 'nullable|date',
            'pol_eff_date'     => 'nullable|date',
            'pol_added_date'   => 'nullable|date',
            'pol_due_day'      => 'nullable|string',
            'pol_status'       => 'nullable|string',
            'pol_agent_record' => 'nullable|string',
            'vehicules'        => 'nullable|string',
        ]);

        $data['customer_id'] = $customer_id;
        $data['pol_status'] = !empty($data['pol_status']) ? $data['pol_status'] : 'Active';

        if (!empty($data['vehicules'])) {
            $decoded = json_decode($data['vehicules'], true);
            $data['vehicules'] = $this->normalizeVehiclesArray(is_array($decoded) ? $decoded : []);
        } else {
            $data['vehicules'] = [];
        }

        $policy = Policy::create($data);

        $createdSnapshot = $this->makePolicySnapshot($policy);

        $policy->policy_log = [
            $this->buildPolicyLogEntry(
                $createdSnapshot,
                'created',
                $this->trackedPolicyFields(),
                $this->buildVehicleChangeSummary([], $createdSnapshot['vehicules'] ?? [])
            ),
        ];

        $policy->save();

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        Policy::where('id', $id)->delete();

        return response()->json(['success' => true]);
    }

    public function show($id)
    {
        $p = Policy::findOrFail($id);

        return response()->json([
            'success' => true,
            'policy'  => $p,
        ]);
    }

    public function update(Request $request, $id)
    {
        $policy = Policy::findOrFail($id);

        $beforeSnapshot = $this->makePolicySnapshot($policy);

        $data = $request->validate([
            'pol_carrier'      => 'nullable|string',
            'pol_number'       => 'nullable|string',
            'pol_url'          => 'nullable|string',
            'pol_expiration'   => 'nullable|date',
            'pol_eff_date'     => 'nullable|date',
            'pol_added_date'   => 'nullable|date',
            'pol_due_day'      => 'nullable|string',
            'pol_status'       => 'nullable|string',
            'pol_agent_record' => 'nullable|string',
            'vehicules'        => 'nullable|string',
        ]);

        if (array_key_exists('pol_status', $data) && $data['pol_status'] === '') {
            $data['pol_status'] = 'Active';
        }

        if (array_key_exists('vehicules', $data)) {
            if (!empty($data['vehicules'])) {
                $decoded = json_decode($data['vehicules'], true);
                $data['vehicules'] = $this->normalizeVehiclesArray(is_array($decoded) ? $decoded : []);
            } else {
                $data['vehicules'] = [];
            }
        }

        $policy->update($data);
        $policy->refresh();

        $afterSnapshot = $this->makePolicySnapshot($policy);

        $changedFields = [];
        foreach ($this->trackedPolicyFields() as $field) {
            if (($beforeSnapshot[$field] ?? null) !== ($afterSnapshot[$field] ?? null)) {
                $changedFields[] = $field;
            }
        }

        if (!empty($changedFields)) {
            $currentLog = is_array($policy->policy_log) ? $policy->policy_log : [];

            $currentLog[] = $this->buildPolicyLogEntry(
                $afterSnapshot,
                'updated',
                $changedFields,
                $this->buildVehicleChangeSummary(
                    $beforeSnapshot['vehicules'] ?? [],
                    $afterSnapshot['vehicules'] ?? []
                )
            );

            $policy->policy_log = array_values($currentLog);
            $policy->save();
        }

        return response()->json(['success' => true]);
    }

    protected function trackedPolicyFields(): array
    {
        return [
            'pol_carrier',
            'pol_number',
            'pol_url',
            'pol_expiration',
            'pol_eff_date',
            'pol_added_date',
            'pol_due_day',
            'pol_status',
            'pol_agent_record',
            'vehicules',
        ];
    }

    protected function policyFieldLabels(): array
    {
        return [
            'pol_carrier'      => 'Carrier',
            'pol_number'       => 'Number',
            'pol_url'          => 'URL',
            'pol_expiration'   => 'Expiration Date',
            'pol_eff_date'     => 'Effective Date',
            'pol_added_date'   => 'Added Date',
            'pol_due_day'      => 'Payment Due Day',
            'pol_status'       => 'Status',
            'pol_agent_record' => 'Agent Record',
            'vehicules'        => 'Vehicles',
        ];
    }

    protected function makePolicySnapshot(Policy $policy): array
    {
        return [
            'pol_carrier'      => $policy->pol_carrier ?? '',
            'pol_number'       => $policy->pol_number ?? '',
            'pol_url'          => $policy->pol_url ?? '',
            'pol_expiration'   => $policy->pol_expiration ?? '',
            'pol_eff_date'     => $policy->pol_eff_date ?? '',
            'pol_added_date'   => $policy->pol_added_date ?? '',
            'pol_due_day'      => $policy->pol_due_day ?? '',
            'pol_status'       => $policy->pol_status ?? 'Active',
            'pol_agent_record' => $policy->pol_agent_record ?? '',
            'vehicules'        => $this->normalizeVehiclesArray($policy->vehicules ?? []),
        ];
    }

    protected function buildPolicyLogEntry(
        array $snapshot,
        string $action,
        array $changedFields = [],
        string $vehicleChangesText = '-'
    ): array {
        $actor = Auth::guard('web')->user() ?? Auth::guard('sub')->user();

        $changedBy = 'System';
        if ($actor) {
            $changedBy = $actor->name ?? $actor->username ?? $actor->email ?? 'System';
        }

        return [
            'created_at'           => now()->format('Y-m-d H:i:s'),
            'action'               => $action,
            'changed_by'           => $changedBy,
            'changed_fields'       => array_values($changedFields),
            'vehicle_changes_text' => $vehicleChangesText,
            'snapshot'             => $snapshot,
        ];
    }

    protected function normalizeVehiclesArray($vehicles): array
    {
        if (is_string($vehicles)) {
            $decoded = json_decode($vehicles, true);
            $vehicles = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($vehicles)) {
            return [];
        }

        return collect($vehicles)
            ->map(function ($vehicle) {
                $vehicle = is_array($vehicle) ? $vehicle : [];

                return [
                    'vin'   => trim((string) ($vehicle['vin'] ?? '')),
                    'year'  => trim((string) ($vehicle['year'] ?? '')),
                    'make'  => trim((string) ($vehicle['make'] ?? '')),
                    'model' => trim((string) ($vehicle['model'] ?? '')),
                ];
            })
            ->filter(function ($vehicle) {
                return $vehicle['vin'] !== ''
                    || $vehicle['year'] !== ''
                    || $vehicle['make'] !== ''
                    || $vehicle['model'] !== '';
            })
            ->values()
            ->all();
    }

    protected function formatSingleVehicleForLog(array $vehicle, ?int $number = null): string
    {
        $prefix = $number ? "Vehicle {$number}" : "Vehicle";

        return $prefix . " (VIN: " . ($vehicle['vin'] ?: '-') .
            " / Year: " . ($vehicle['year'] ?: '-') .
            " / Make: " . ($vehicle['make'] ?: '-') .
            " / Model: " . ($vehicle['model'] ?: '-') . ")";
    }

    protected function formatVehiclesForLog(array $vehicles): string
    {
        $vehicles = $this->normalizeVehiclesArray($vehicles);

        if (empty($vehicles)) {
            return '-';
        }

        return collect($vehicles)
            ->values()
            ->map(function ($vehicle, $index) {
                return $this->formatSingleVehicleForLog($vehicle, $index + 1);
            })
            ->implode('; ');
    }

    protected function buildVehicleSignature(array $vehicle): string
    {
        return strtolower(implode('|', [
            trim((string) ($vehicle['vin'] ?? '')),
            trim((string) ($vehicle['year'] ?? '')),
            trim((string) ($vehicle['make'] ?? '')),
            trim((string) ($vehicle['model'] ?? '')),
        ]));
    }

    protected function formatVehicleInline(array $vehicle): string
    {
        return '(VIN: ' . (($vehicle['vin'] ?? '') !== '' ? $vehicle['vin'] : '-') .
            ' / Year: ' . (($vehicle['year'] ?? '') !== '' ? $vehicle['year'] : '-') .
            ' / Make: ' . (($vehicle['make'] ?? '') !== '' ? $vehicle['make'] : '-') .
            ' / Model: ' . (($vehicle['model'] ?? '') !== '' ? $vehicle['model'] : '-') . ')';
    }

    protected function buildVehicleChangeSummary(array $beforeVehicles = [], array $afterVehicles = []): string
    {
        $beforeVehicles = $this->normalizeVehiclesArray($beforeVehicles);
        $afterVehicles = $this->normalizeVehiclesArray($afterVehicles);

        $parts = [];

        // =========================================================
        // 1) Detectar updates reales solo por VIN
        // =========================================================
        $beforeByVin = [];
        foreach ($beforeVehicles as $vehicle) {
            $vin = strtolower(trim((string) ($vehicle['vin'] ?? '')));
            if ($vin !== '') {
                $beforeByVin[$vin] = $vehicle;
            }
        }

        $afterByVin = [];
        foreach ($afterVehicles as $vehicle) {
            $vin = strtolower(trim((string) ($vehicle['vin'] ?? '')));
            if ($vin !== '') {
                $afterByVin[$vin] = $vehicle;
            }
        }

        foreach ($beforeByVin as $vin => $beforeVehicle) {
            if (isset($afterByVin[$vin]) && $beforeVehicle != $afterByVin[$vin]) {
                $parts[] = 'Vehicle Updated: ' .
                    $this->formatVehicleInline($beforeVehicle) .
                    ' -> ' .
                    $this->formatVehicleInline($afterByVin[$vin]);
            }
        }

        // =========================================================
        // 2) Detectar removidos/agregados por firma completa
        //    evitando falsos "updated" por cambio de índice
        // =========================================================
        $beforeCounts = [];
        foreach ($beforeVehicles as $vehicle) {
            $sig = $this->buildVehicleSignature($vehicle);
            if (!isset($beforeCounts[$sig])) {
                $beforeCounts[$sig] = [
                    'count' => 0,
                    'vehicle' => $vehicle,
                ];
            }
            $beforeCounts[$sig]['count']++;
        }

        $afterCounts = [];
        foreach ($afterVehicles as $vehicle) {
            $sig = $this->buildVehicleSignature($vehicle);
            if (!isset($afterCounts[$sig])) {
                $afterCounts[$sig] = [
                    'count' => 0,
                    'vehicle' => $vehicle,
                ];
            }
            $afterCounts[$sig]['count']++;
        }

        // removidos
        foreach ($beforeCounts as $sig => $info) {
            $beforeCount = $info['count'];
            $afterCount = $afterCounts[$sig]['count'] ?? 0;

            if ($beforeCount > $afterCount) {
                $diff = $beforeCount - $afterCount;
                for ($i = 0; $i < $diff; $i++) {
                    $parts[] = 'Vehicle Removed: ' . $this->formatVehicleInline($info['vehicle']);
                }
            }
        }

        // agregados
        foreach ($afterCounts as $sig => $info) {
            $afterCount = $info['count'];
            $beforeCount = $beforeCounts[$sig]['count'] ?? 0;

            if ($afterCount > $beforeCount) {
                $diff = $afterCount - $beforeCount;
                for ($i = 0; $i < $diff; $i++) {
                    $parts[] = 'Vehicle Added: ' . $this->formatVehicleInline($info['vehicle']);
                }
            }
        }

        return !empty($parts) ? implode(' | ', $parts) : '-';
    }
}
