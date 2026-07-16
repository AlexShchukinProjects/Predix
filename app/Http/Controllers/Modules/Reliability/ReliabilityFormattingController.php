<?php

declare(strict_types=1);

namespace App\Http\Controllers\Modules\Reliability;

use App\Http\Controllers\Controller;
use App\Models\ReliabilityCardFormatRule;
use App\Support\CardFormatMask;
use App\Support\CardFormatRuleInference;
use App\Support\CardFormatRuleRepository;
use App\Support\CardFormatValue;
use App\Support\ReliabilityTaskCardNormalizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReliabilityFormattingController extends Controller
{
    public function index(): View
    {
        return view('Modules.Reliability.formatting.index', [
            'rules' => CardFormatRuleRepository::allForDisplay(),
        ]);
    }

    public function infer(Request $request): JsonResponse
    {
        $data = $request->validate([
            'raw_example' => ['required', 'string', 'max:255'],
            'expected_output' => ['required', 'string', 'max:255'],
            'oem' => ['nullable', 'string', 'in:airbus,boeing'],
            'document_type' => ['nullable', 'string', 'in:task_card,easa,faa,mpd,any'],
            'mask' => ['nullable', 'string', 'max:128'],
        ]);

        try {
            $result = CardFormatRuleInference::infer(
                $data['raw_example'],
                $data['expected_output'],
                $data['oem'] ?? null,
                $data['document_type'] ?? null,
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        if (!empty($data['mask'])) {
            $blocks = CardFormatMask::digitBlocksFromMask($data['mask']);
            if ($blocks === null) {
                return response()->json(['message' => 'Invalid mask format. Use d for digits, A for letters, and literals like -. Example: dd-ddd-dd-dd'], 422);
            }

            $result['mask'] = trim($data['mask']);
            $result['digit_blocks'] = $blocks;
            $result['preview_normalized'] = CardFormatMask::apply($data['raw_example'], $blocks);
            $result['matches_expected'] = strtoupper(trim((string) $result['preview_normalized'])) === strtoupper(trim($data['expected_output']));
        }

        return response()->json($result);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:120'],
            'raw_example' => ['required', 'string', 'max:255'],
            'expected_output' => ['required', 'string', 'max:255'],
            'mask' => ['required', 'string', 'max:128'],
            'document_type' => ['nullable', 'string', 'in:task_card,easa,faa,mpd,any'],
            'oem' => ['nullable', 'string', 'in:airbus,boeing'],
            'mapping' => ['nullable', 'array'],
        ]);

        $blocks = CardFormatMask::digitBlocksFromMask($data['mask']);
        if ($blocks === null) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Invalid mask format.'], 422);
            }

            return back()->withErrors(['mask' => 'Invalid mask format.'])->withInput();
        }

        $preview = CardFormatMask::apply($data['raw_example'], $blocks);
        if ($preview === null || strtoupper(trim($preview)) !== strtoupper(trim($data['expected_output']))) {
            $message = 'Mask does not produce the expected output for the raw example.';
            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->withErrors(['mask' => $message])->withInput();
        }

        $inferred = CardFormatRuleInference::infer(
            $data['raw_example'],
            $data['expected_output'],
            $data['oem'] ?? null,
            $data['document_type'] ?? null,
        );

        $priority = (int) (ReliabilityCardFormatRule::max('priority') ?? 0) + 10;

        $rule = ReliabilityCardFormatRule::create([
            'name' => $data['name'] ?: 'Custom: ' . $data['mask'],
            'document_type' => $data['document_type'] ?? $inferred['document_type'],
            'oem' => $data['oem'] ?? $inferred['oem'],
            'mask' => $data['mask'],
            'digit_blocks' => $blocks,
            'is_builtin' => false,
            'is_active' => true,
            'priority' => max(80, $priority),
            'example_raw' => $data['raw_example'],
            'example_normalized' => $data['expected_output'],
            'mapping' => $data['mapping'] ?? $inferred['mapping'],
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Formatting rule added.',
                'rule' => $rule,
            ], 201);
        }

        return redirect()
            ->route('modules.reliability.formatting')
            ->with('success', 'Formatting rule added.');
    }

    public function destroy(ReliabilityCardFormatRule $rule): RedirectResponse|JsonResponse
    {
        if ($rule->is_builtin) {
            abort(403, 'Built-in rules cannot be deleted.');
        }

        $rule->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Rule deleted.']);
        }

        return redirect()
            ->route('modules.reliability.formatting')
            ->with('success', 'Rule deleted.');
    }

    public function toggle(ReliabilityCardFormatRule $rule): RedirectResponse|JsonResponse
    {
        if ($rule->is_builtin) {
            abort(403, 'Built-in rules cannot be toggled.');
        }

        $rule->is_active = !$rule->is_active;
        $rule->save();

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Rule updated.',
                'is_active' => $rule->is_active,
            ]);
        }

        return redirect()
            ->route('modules.reliability.formatting')
            ->with('success', 'Rule updated.');
    }

    public function preview(Request $request): JsonResponse
    {
        $data = $request->validate([
            'raw_example' => ['required', 'string', 'max:255'],
            'mask' => ['required', 'string', 'max:128'],
            'oem' => ['nullable', 'string', 'in:airbus,boeing'],
            'document_type' => ['nullable', 'string', 'in:task_card,easa,faa,mpd,any'],
        ]);

        $blocks = CardFormatMask::digitBlocksFromMask($data['mask']);
        if ($blocks === null) {
            return response()->json(['message' => 'Invalid mask format.'], 422);
        }

        $fromMask = CardFormatMask::apply($data['raw_example'], $blocks);
        $fromEngine = ReliabilityTaskCardNormalizer::normalize(
            $data['raw_example'],
            $data['oem'] ?? null,
            $data['document_type'] ?? null,
        );

        return response()->json([
            'preview_normalized' => $fromMask,
            'engine_normalized' => $fromEngine,
        ]);
    }

    /**
     * Find SRC. CUST. CARD values that current rules cannot normalize,
     * grouped by structural format (one example per format).
     */
    public function findUnformatted(): JsonResponse
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('work_cards_master')) {
            return response()->json([
                'rows' => [],
                'scanned' => 0,
                'unformatted_total' => 0,
                'message' => 'Master data table not found.',
            ]);
        }

        $values = DB::table('work_cards_master')
            ->selectRaw('TRIM(src_cust_card) as src_cust_card')
            ->selectRaw('TRIM(COALESCE(aircraft_type, \'\')) as aircraft_type')
            ->selectRaw('COUNT(*) as occurrences')
            ->whereRaw("TRIM(COALESCE(src_cust_card, '')) <> ''")
            ->groupBy(DB::raw('TRIM(src_cust_card)'), DB::raw('TRIM(COALESCE(aircraft_type, \'\'))'))
            ->orderBy('src_cust_card')
            ->get();

        $bySignature = [];
        $unformattedTotal = 0;

        foreach ($values as $row) {
            $raw = trim((string) ($row->src_cust_card ?? ''));
            if ($raw === '') {
                continue;
            }

            $aircraftType = trim((string) ($row->aircraft_type ?? ''));
            $oem = $this->detectOemFromAircraftType($aircraftType);
            $normalized = ReliabilityTaskCardNormalizer::normalize($raw, $oem);
            if ($normalized !== null && trim($normalized) !== '') {
                continue;
            }

            $unformattedTotal++;
            $signature = CardFormatValue::structureSignature($raw);
            if ($signature === '') {
                $signature = '__empty__';
            }

            // One example per format + OEM (Boeing / Airbus / unknown)
            $groupKey = $signature . '|' . ($oem ?? 'unknown');
            $count = (int) ($row->occurrences ?? 1);
            if (!isset($bySignature[$groupKey])) {
                $bySignature[$groupKey] = [
                    'example' => $raw,
                    'signature' => $signature === '__empty__' ? '—' : $signature,
                    'oem' => $oem,
                    'oem_label' => $oem === 'boeing' ? 'Boeing' : ($oem === 'airbus' ? 'Airbus' : '—'),
                    'aircraft_type' => $aircraftType !== '' ? $aircraftType : null,
                    'distinct_values' => 1,
                    'occurrences' => $count,
                ];
                continue;
            }

            $bySignature[$groupKey]['distinct_values']++;
            $bySignature[$groupKey]['occurrences'] += $count;
            // Prefer a row that already has a known OEM / aircraft type
            if (empty($bySignature[$groupKey]['oem']) && $oem !== null) {
                $bySignature[$groupKey]['example'] = $raw;
                $bySignature[$groupKey]['oem'] = $oem;
                $bySignature[$groupKey]['oem_label'] = $oem === 'boeing' ? 'Boeing' : 'Airbus';
                $bySignature[$groupKey]['aircraft_type'] = $aircraftType !== '' ? $aircraftType : null;
            }
        }

        $rows = array_values($bySignature);
        usort($rows, static function (array $a, array $b): int {
            return ($b['occurrences'] <=> $a['occurrences'])
                ?: strcmp((string) $a['signature'], (string) $b['signature']);
        });

        return response()->json([
            'rows' => $rows,
            'scanned' => $values->count(),
            'unformatted_total' => $unformattedTotal,
            'format_groups' => count($rows),
        ]);
    }

    private function detectOemFromAircraftType(?string $aircraftType): ?string
    {
        $raw = trim((string) ($aircraftType ?? ''));
        if ($raw === '') {
            return null;
        }

        $normalized = strtolower(preg_replace('/\s+/', ' ', $raw) ?? $raw);
        $upper = strtoupper($normalized);

        if (str_contains($normalized, 'boeing') || preg_match('/\bB[-\s]?\d{3,4}\b/', $upper) === 1) {
            return 'boeing';
        }

        if (str_contains($normalized, 'airbus') || preg_match('/\bA[-\s]?\d{3,4}\b/', $upper) === 1) {
            return 'airbus';
        }

        return null;
    }
}
