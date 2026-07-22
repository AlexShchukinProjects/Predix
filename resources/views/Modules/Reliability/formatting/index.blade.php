@extends('layout.main')

@section('content')
<div class="container-fluid mt-3 formatting-page">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h2 class="mb-1 formatting-title">Card formatting rules</h2>
            <p class="text-muted small mb-0">
                Rules used for <strong>Normalised</strong> in Analysis → Add from Excel and for <strong>CUST. CARD NORM</strong> in Master data.
                All rules are stored in the database and can be edited or deleted.
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success py-2 px-3">{{ session('success') }}</div>
    @endif

    <div class="row g-4 formatting-layout-row flex-lg-nowrap">
        <div class="col formatting-unformatted-col">
            <div class="formatting-card">
                <div class="formatting-card__head">
                    <h5 class="mb-0">Unformatted</h5>
                    <span class="text-muted small" id="unformattedCountLabel">—</span>
                </div>
                <div class="formatting-card__body">
                    <button type="button" class="btn efds-btn efds-btn--primary btn-sm w-100 mb-3" id="findUnformattedBtn">
                        Find unformatted
                    </button>
                    <p class="small text-muted mb-2" id="unformattedHint">
                        Scans all master-data RC/NRC rows. Empty SRC. CUST. CARD are skipped. Unique card values are checked; the table shows one example per format structure.
                    </p>
                    <div class="table-responsive formatting-unformatted-wrap">
                        <table class="table table-sm align-middle formatting-rules-table formatting-unformatted-table mb-0">
                            <thead>
                                <tr>
                                    <th class="formatting-unformatted-col-src">SRC. CUST. CARD</th>
                                    <th class="formatting-unformatted-col-aircraft">Aircraft</th>
                                    <th class="formatting-unformatted-col-format">Format</th>
                                    <th class="formatting-unformatted-col-count text-end">Count</th>
                                </tr>
                            </thead>
                            <tbody id="unformattedTableBody">
                                <tr>
                                    <td colspan="4" class="text-muted small">Press the button to scan master data.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div id="unformattedMessage" class="formatting-unformatted-stats small mt-2 text-muted">
                        <div>Checked rows: —</div>
                        <div>Unique SRC. CUST. CARD: —</div>
                        <div>Unformatted: —</div>
                        <div>Different formats: —</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-auto formatting-add-rule-col">
            <div class="formatting-card">
                <div class="formatting-card__head">
                    <h5 class="mb-0" id="formatPanelTitle">Add new rule</h5>
                </div>
                <div class="formatting-card__body">
                    <div class="mb-3">
                        <label class="form-label">1. Raw example (unformatted)</label>
                        <input type="text" class="form-control" id="formatRawExample" placeholder="e.g. TASK 291105210804">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">2. Expected formatted value</label>
                        <input type="text" class="form-control" id="formatExpectedOutput" placeholder="e.g. 29-11-05-210-804">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">OEM</label>
                        <select class="form-select" id="formatOem">
                            <option value="">All</option>
                            <option value="airbus">Airbus</option>
                            <option value="boeing">Boeing</option>
                        </select>
                    </div>

                    <button type="button" class="btn efds-btn efds-btn--outline-primary btn-sm" id="formatAnalyzeBtn">Analyze pattern</button>

                    <div id="formatAnalysisPanel" class="format-analysis-panel d-none mt-4">
                        <div class="mb-2 fw-semibold">3. Detected format</div>
                        <p class="small text-muted mb-2">
                            <code>d</code> = digit to extract, fixed characters (e.g. <code>4N</code>, <code>C</code>) are matched and discarded.
                            Example: <code>4N-dd-ddd-dd-C</code> → <code>21-061-01</code>.
                        </p>
                        <label class="form-label">Mask</label>
                        <input type="text" class="form-control font-monospace mb-3" id="formatMaskInput">

                        <div class="mb-3">
                            <div class="small fw-semibold mb-2">Mapping preview</div>
                            <div id="formatMappingPreview" class="format-mapping-preview"></div>
                        </div>

                        <div class="mb-3">
                            <div class="format-preview-box mb-2">
                                <div class="small text-muted">Preview from mask</div>
                                <div id="formatPreviewValue" class="format-preview-value">—</div>
                            </div>
                            <div class="format-preview-box">
                                <div class="small text-muted">Expected</div>
                                <div id="formatExpectedValue" class="format-preview-value">—</div>
                            </div>
                        </div>

                        <div id="formatMatchStatus" class="small mb-3"></div>

                        <div class="mb-3">
                            <label class="form-label">Rule name (optional)</label>
                            <input type="text" class="form-control" id="formatRuleName" placeholder="Custom mask name">
                        </div>

                        <div class="format-action-bar">
                            <div class="format-action-bar__left">
                                <button type="button" class="btn efds-btn efds-btn--outline-primary btn-sm" id="formatRecheckBtn">Recheck mask</button>
                                <button type="button" class="btn efds-btn efds-btn--primary btn-sm" id="formatSaveBtn">Add to rules</button>
                                <button type="button" class="btn efds-btn efds-btn--outline-primary btn-sm d-none" id="formatCancelEditBtn">Cancel</button>
                            </div>
                            <button type="button" class="btn btn-outline-danger btn-sm d-none format-action-bar__delete" id="formatDeleteBtn">Delete</button>
                        </div>
                    </div>

                    <div id="formatWizardMessage" class="small mt-3"></div>
                </div>
            </div>
        </div>

        <div class="col formatting-existing-col">
            <div class="formatting-card">
                <div class="formatting-card__head">
                    <h5 class="mb-0">Existing rules</h5>
                    <span class="text-muted small">{{ $rules->count() }} total</span>
                </div>
                <div class="formatting-card__body pt-0 pb-2">
                    <p class="small text-muted mb-0">
                        Matching order: longer masks are tried first; among masks of the same length, more specific ones win
                        (e.g. <code>ddd-Addd-dd</code> before <code>ddd-dddd-dd</code>, because <code>A</code> is a letter constraint and fixed characters like <code>4</code>/<code>N</code>/<code>C</code> are even more specific than digit wildcards <code>d</code>).
                        Click a row to edit the rule.
                    </p>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle formatting-rules-table mb-0">
                        <thead>
                            <tr>
                                <th style="width:4.5rem;">Priority</th>
                                <th>Name</th>
                                <th>Mask</th>
                                <th>Context</th>
                                <th>Example</th>
                            </tr>
                        </thead>
                        <tbody id="existingRulesTableBody">
                            @forelse($rules as $rule)
                                <tr
                                    class="formatting-existing-row {{ !($rule['is_active'] ?? true) ? 'text-muted' : '' }}"
                                    data-rule-key="{{ $rule['key'] }}"
                                >
                                    <td class="fw-semibold">{{ $rule['match_priority'] ?? $loop->iteration }}</td>
                                    <td>{{ $rule['name'] }}</td>
                                    <td><code>{{ $rule['mask'] }}</code></td>
                                    <td>
                                        <div class="small">
                                            <div>{{ strtoupper((string) ($rule['document_type'] ?? 'any')) }}</div>
                                            <div class="text-muted">
                                                @if(!empty($rule['oem']))
                                                    {{ ucfirst((string) $rule['oem']) }}
                                                @else
                                                    All aircraft
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="small">
                                        @if(!empty($rule['example_raw']))
                                            <div><span class="text-muted">Raw:</span> {{ $rule['example_raw'] }}</div>
                                            <div><span class="text-muted">Norm:</span> {{ $rule['example_normalized'] }}</div>
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-muted">No rules found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.formatting-page { --fmt-border:#e3e6f0; --fmt-bg:#f5f7fa; }
.formatting-title { font-weight:600; color:#2d3748; font-size:24px; }
.formatting-layout-row {
    align-items: flex-start;
}
.formatting-unformatted-col {
    flex: 0 1 480px;
    width: 480px;
    max-width: 520px;
    min-width: 0;
}
.formatting-existing-col {
    flex: 1 1 0;
    min-width: 0;
}
.formatting-add-rule-col {
    flex: 0 0 630px;
    width: 630px;
    max-width: 630px;
}
@media (max-width: 991.98px) {
    .formatting-layout-row {
        flex-wrap: wrap !important;
    }
    .formatting-unformatted-col,
    .formatting-add-rule-col {
        flex: 1 1 100%;
        width: 100%;
        max-width: 100%;
    }
}
.formatting-card { background:#fff; border:1px solid var(--fmt-border); border-radius:8px; overflow:hidden; }
.formatting-card__head { display:flex; justify-content:space-between; align-items:center; padding:14px 16px; background:var(--fmt-bg); border-bottom:1px solid var(--fmt-border); }
.formatting-card__body { padding:16px; }
.formatting-rules-table thead th { font-size:12px; text-transform:uppercase; letter-spacing:.03em; color:#64748b; background:var(--fmt-bg); }
.formatting-unformatted-table {
    table-layout: fixed;
    width: 100%;
}
.formatting-unformatted-col-src { width: 28%; }
.formatting-unformatted-col-aircraft { width: 22%; }
.formatting-unformatted-col-format { width: 28%; }
.formatting-unformatted-col-count { width: 22%; }
.formatting-unformatted-table td,
.formatting-unformatted-table th {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    vertical-align: middle;
}
.formatting-unformatted-table .formatting-unformatted-example,
.formatting-unformatted-table code {
    display: inline-block;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    vertical-align: bottom;
}
.formatting-badge { font-weight:600; }
.formatting-badge--builtin { background:#e8eef8; color:#1E64D4; }
.formatting-badge--custom { background:#edf7ed; color:#198754; }
.format-analysis-panel { border-top:1px solid var(--fmt-border); padding-top:16px; }
.format-mapping-preview { display:flex; flex-wrap:wrap; gap:8px; }
.format-map-chip { border:1px solid #dbe2ea; border-radius:6px; padding:8px 10px; background:#fafbfc; min-width:120px; }
.format-map-chip__type { font-size:10px; text-transform:uppercase; letter-spacing:.04em; color:#64748b; margin-bottom:4px; }
.format-map-chip__raw { font-family:Consolas, monospace; font-size:12px; color:#334155; }
.format-map-chip__arrow { color:#94a3b8; font-size:11px; margin:2px 0; }
.format-map-chip__formatted { font-family:Consolas, monospace; font-size:12px; color:#1E64D4; font-weight:600; }
.format-map-chip__mask { font-size:11px; color:#64748b; margin-top:4px; }
.format-map-chip--discard { background:#f8fafc; opacity:.85; }
.format-map-chip--discard .format-map-chip__formatted { color:#94a3b8; font-weight:500; }
.format-preview-box { border:1px solid var(--fmt-border); border-radius:6px; padding:10px 12px; background:#fafbfc; min-height:72px; }
.format-preview-value { font-family:Consolas, monospace; font-size:14px; font-weight:600; color:#2d3748; word-break:break-all; }
.format-match-ok { color:#198754; }
.format-match-bad { color:#dc3545; }
.format-action-bar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 5px;
    width: 100%;
}
.format-action-bar__left {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: flex-start;
    gap: 5px;
}
.format-action-bar__delete {
    margin-left: auto;
    margin-right: 0;
}
.formatting-unformatted-wrap { max-height: calc(100vh - 320px); overflow:auto; }
.formatting-unformatted-row { cursor:pointer; }
.formatting-unformatted-row:hover { background:#eef4ff; }
.formatting-unformatted-row.is-selected { background:#e8eef8; }
.formatting-unformatted-example { color:#1E64D4; font-family:Consolas, monospace; font-size:12px; word-break:break-all; }
.formatting-unformatted-stats {
    display: flex;
    flex-direction: column;
    gap: 2px;
    line-height: 1.35;
}
.formatting-unformatted-stats strong { color: #334155; font-weight: 600; }
.formatting-existing-row { cursor:pointer; }
.formatting-existing-row:hover { background:#eef4ff; }
.formatting-existing-row.is-selected { background:#e8eef8; }
</style>

@php
    $rulesByKey = $rules->mapWithKeys(static function (array $rule): array {
        $key = (string) ($rule['key'] ?? ('rule_' . ($rule['id'] ?? uniqid('r', true))));

        return [$key => [
            'id' => $rule['id'] ?? null,
            'name' => $rule['name'] ?? null,
            'mask' => $rule['mask'] ?? null,
            'document_type' => $rule['document_type'] ?? null,
            'oem' => $rule['oem'] ?? null,
            'example_raw' => $rule['example_raw'] ?? null,
            'example_normalized' => $rule['example_normalized'] ?? null,
            'mapping' => $rule['mapping'] ?? [],
        ]];
    })->all();
@endphp
<script>
document.addEventListener('DOMContentLoaded', function() {
    var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    var inferUrl = @json(route('modules.reliability.formatting.infer'));
    var previewUrl = @json(route('modules.reliability.formatting.preview'));
    var storeUrl = @json(route('modules.reliability.formatting.rules.store'));
    var updateUrlTemplate = @json(route('modules.reliability.formatting.rules.update', ['rule' => '__ID__']));
    var deleteUrlTemplate = @json(route('modules.reliability.formatting.rules.destroy', ['rule' => '__ID__']));
    var unformattedUrl = @json(route('modules.reliability.formatting.unformatted'));
    var rulesByKey = @json($rulesByKey);

    var panelTitle = document.getElementById('formatPanelTitle');
    var rawInput = document.getElementById('formatRawExample');
    var expectedInput = document.getElementById('formatExpectedOutput');
    var oemSelect = document.getElementById('formatOem');
    var analyzeBtn = document.getElementById('formatAnalyzeBtn');
    var recheckBtn = document.getElementById('formatRecheckBtn');
    var saveBtn = document.getElementById('formatSaveBtn');
    var cancelEditBtn = document.getElementById('formatCancelEditBtn');
    var deleteBtn = document.getElementById('formatDeleteBtn');
    var panel = document.getElementById('formatAnalysisPanel');
    var maskInput = document.getElementById('formatMaskInput');
    var mappingPreview = document.getElementById('formatMappingPreview');
    var previewValue = document.getElementById('formatPreviewValue');
    var expectedValue = document.getElementById('formatExpectedValue');
    var matchStatus = document.getElementById('formatMatchStatus');
    var ruleNameInput = document.getElementById('formatRuleName');
    var wizardMessage = document.getElementById('formatWizardMessage');
    var findUnformattedBtn = document.getElementById('findUnformattedBtn');
    var unformattedTableBody = document.getElementById('unformattedTableBody');
    var unformattedCountLabel = document.getElementById('unformattedCountLabel');
    var unformattedMessage = document.getElementById('unformattedMessage');
    var existingRulesTableBody = document.getElementById('existingRulesTableBody');

    var lastMapping = [];
    var editingRuleId = null;
    var editingDocumentType = null;

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function setMessage(text, isError) {
        wizardMessage.textContent = text || '';
        wizardMessage.className = 'small mt-3 ' + (isError ? 'text-danger' : 'text-success');
    }

    function updateUrlFor(id) {
        return updateUrlTemplate.replace('__ID__', String(id));
    }

    function deleteUrlFor(id) {
        return deleteUrlTemplate.replace('__ID__', String(id));
    }

    function setEditMode(ruleId) {
        editingRuleId = ruleId;
        panelTitle.textContent = 'Edit rule';
        cancelEditBtn.classList.remove('d-none');
        saveBtn.textContent = 'Save changes';
        deleteBtn.classList.remove('d-none');
    }

    function setCreateMode() {
        editingRuleId = null;
        editingDocumentType = null;
        panelTitle.textContent = 'Add new rule';
        saveBtn.textContent = 'Add to rules';
        cancelEditBtn.classList.add('d-none');
        deleteBtn.classList.add('d-none');
        if (existingRulesTableBody) {
            existingRulesTableBody.querySelectorAll('.formatting-existing-row').forEach(function(row) {
                row.classList.remove('is-selected');
            });
        }
    }

    function resetWizardForm() {
        rawInput.value = '';
        expectedInput.value = '';
        oemSelect.value = '';
        maskInput.value = '';
        ruleNameInput.value = '';
        lastMapping = [];
        renderMapping([]);
        previewValue.textContent = '—';
        expectedValue.textContent = '—';
        matchStatus.innerHTML = '';
        panel.classList.add('d-none');
        setCreateMode();
        setMessage('', false);
    }

    function loadRuleIntoForm(rule) {
        rawInput.value = rule.example_raw || '';
        expectedInput.value = rule.example_normalized || '';
        oemSelect.value = rule.oem || '';
        editingDocumentType = rule.document_type || null;
        maskInput.value = rule.mask || '';
        ruleNameInput.value = rule.name || '';
        lastMapping = Array.isArray(rule.mapping) ? rule.mapping : [];
        renderMapping(lastMapping);

        panel.classList.remove('d-none');
        expectedValue.textContent = rule.example_normalized || '—';
        previewValue.textContent = rule.example_normalized || '—';

        if (rule.id) {
            setEditMode(rule.id);
            updateMatchStatus(true);
            setMessage('Rule loaded for editing. Adjust and save changes.', false);
        } else {
            setCreateMode();
            setMessage('Rule loaded.', false);
        }

        if (rawInput.value && maskInput.value) {
            recheckMask();
        }

        rawInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function fillRawFromUnformatted(example, oem) {
        setCreateMode();
        rawInput.value = example || '';
        if (oem) {
            oemSelect.value = oem;
        }
        expectedInput.focus();
        setMessage('Example loaded into Add new rule. Enter the expected formatted value.', false);
        rawInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function renderUnformattedRows(rows) {
        unformattedTableBody.innerHTML = '';
        if (!rows || !rows.length) {
            unformattedTableBody.innerHTML = '<tr><td colspan="4" class="text-muted small">No unformatted formats found.</td></tr>';
            return;
        }

        rows.forEach(function(row) {
            var tr = document.createElement('tr');
            tr.className = 'formatting-unformatted-row';
            tr.setAttribute('data-example', row.example || '');
            tr.setAttribute('data-oem', row.oem || '');
            var oemLabel = row.oem_label || (row.oem === 'boeing' ? 'Boeing' : (row.oem === 'airbus' ? 'Airbus' : '—'));
            tr.innerHTML =
                '<td><span class="formatting-unformatted-example">' + escapeHtml(row.example) + '</span></td>' +
                '<td class="small">' + escapeHtml(oemLabel) + '</td>' +
                '<td><code class="small">' + escapeHtml(row.signature) + '</code></td>' +
                '<td class="text-end small">' + escapeHtml(String(row.occurrences || 0)) + '</td>';
            unformattedTableBody.appendChild(tr);
        });
    }

    function renderUnformattedStats(data, fallbackText) {
        if (fallbackText) {
            unformattedMessage.className = 'formatting-unformatted-stats small mt-2 text-muted';
            unformattedMessage.textContent = fallbackText;
            return;
        }

        var checked = data.rows_with_src != null ? data.rows_with_src : (data.total_rows || 0);
        var unique = data.scanned || 0;
        var unformatted = data.unformatted_total || 0;
        var formats = data.format_groups || 0;

        unformattedMessage.className = 'formatting-unformatted-stats small mt-2 text-muted';
        unformattedMessage.innerHTML =
            '<div>Checked rows: <strong>' + escapeHtml(String(checked)) + '</strong>' +
                (data.total_rows != null
                    ? ' <span class="text-muted">(of ' + escapeHtml(String(data.total_rows)) + ' RC/NRC; ' +
                      escapeHtml(String(data.rows_without_src || 0)) + ' without SRC. CUST. CARD)</span>'
                    : '') +
            '</div>' +
            '<div>Unique SRC. CUST. CARD: <strong>' + escapeHtml(String(unique)) + '</strong></div>' +
            '<div>Unformatted: <strong>' + escapeHtml(String(unformatted)) + '</strong></div>' +
            '<div>Different formats: <strong>' + escapeHtml(String(formats)) + '</strong></div>';
    }

    function findUnformatted() {
        findUnformattedBtn.disabled = true;
        renderUnformattedStats(null, 'Scanning master data…');
        unformattedCountLabel.textContent = '…';

        fetch(unformattedUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
        })
        .then(function(r) { return r.json().then(function(data) { return { ok: r.ok, data: data }; }); })
        .then(function(result) {
            findUnformattedBtn.disabled = false;
            if (!result.ok) {
                renderUnformattedStats(null, result.data.message || 'Scan failed.');
                return;
            }

            renderUnformattedRows(result.data.rows || []);
            unformattedCountLabel.textContent = (result.data.format_groups || 0) + ' formats';
            renderUnformattedStats(result.data);
        })
        .catch(function() {
            findUnformattedBtn.disabled = false;
            renderUnformattedStats(null, 'Network error while scanning.');
        });
    }

    function renderMapping(items) {
        mappingPreview.innerHTML = '';
        if (!items || !items.length) {
            mappingPreview.innerHTML = '<span class="text-muted small">No mapping generated.</span>';
            return;
        }

        items.forEach(function(item) {
            var chip = document.createElement('div');
            var type = item.type || 'part';
            chip.className = 'format-map-chip' + (type.indexOf('discard') === 0 ? ' format-map-chip--discard' : '');
            chip.innerHTML =
                '<div class="format-map-chip__type">' + type + '</div>' +
                '<div class="format-map-chip__raw">' + (item.raw_part || '—') + '</div>' +
                '<div class="format-map-chip__arrow">→</div>' +
                '<div class="format-map-chip__formatted">' + (item.formatted_part || '—') + '</div>' +
                '<div class="format-map-chip__mask">' + (item.mask_token || '') + '</div>';
            mappingPreview.appendChild(chip);
        });
    }

    function updateMatchStatus(matches) {
        if (matches) {
            matchStatus.innerHTML = '<span class="format-match-ok">Mask matches expected output.</span>';
            saveBtn.disabled = false;
        } else {
            matchStatus.innerHTML = '<span class="format-match-bad">Mask does not match expected output. Adjust the mask and recheck.</span>';
            saveBtn.disabled = true;
        }
    }

    function analyze(useCurrentMask) {
        var raw = rawInput.value.trim();
        var expected = expectedInput.value.trim();
        if (!raw || !expected) {
            setMessage('Enter both raw example and expected formatted value.', true);
            return;
        }

        setMessage('Analyzing…', false);
        analyzeBtn.disabled = true;

        var payload = {
            raw_example: raw,
            expected_output: expected,
            oem: oemSelect.value || null,
            document_type: editingDocumentType || null,
        };

        if (useCurrentMask && maskInput.value.trim()) {
            payload.mask = maskInput.value.trim();
        }

        fetch(inferUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify(payload),
        })
        .then(function(r) { return r.json().then(function(data) { return { ok: r.ok, data: data }; }); })
        .then(function(result) {
            analyzeBtn.disabled = false;
            if (!result.ok) {
                setMessage(result.data.message || 'Analysis failed.', true);
                return;
            }

            panel.classList.remove('d-none');
            maskInput.value = result.data.mask || '';
            lastMapping = result.data.mapping || [];
            renderMapping(lastMapping);
            previewValue.textContent = result.data.preview_normalized || '—';
            expectedValue.textContent = expected;
            updateMatchStatus(!!result.data.matches_expected);

            if (!oemSelect.value && result.data.oem) {
                oemSelect.value = result.data.oem;
            }

            if (!editingDocumentType && result.data.document_type) {
                editingDocumentType = result.data.document_type;
            }

            if (!ruleNameInput.value.trim() && result.data.mask) {
                ruleNameInput.value = 'Custom: ' + result.data.mask;
            }

            setMessage(
                editingRuleId
                    ? 'Pattern detected. Review the mask, then save changes.'
                    : 'Pattern detected. Review the mask and mapping, then add the rule.',
                false
            );
        })
        .catch(function() {
            analyzeBtn.disabled = false;
            setMessage('Network error during analysis.', true);
        });
    }

    function recheckMask() {
        var raw = rawInput.value.trim();
        var mask = maskInput.value.trim();
        var expected = expectedInput.value.trim();
        if (!raw || !mask) {
            setMessage('Raw example and mask are required.', true);
            return;
        }

        fetch(previewUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify({
                raw_example: raw,
                mask: mask,
                oem: oemSelect.value || null,
                document_type: editingDocumentType || null,
            }),
        })
        .then(function(r) { return r.json().then(function(data) { return { ok: r.ok, data: data }; }); })
        .then(function(result) {
            if (!result.ok) {
                setMessage(result.data.message || 'Recheck failed.', true);
                return;
            }

            previewValue.textContent = result.data.preview_normalized || '—';
            expectedValue.textContent = expected || '—';
            var matches = expected !== '' && (result.data.preview_normalized || '').toUpperCase() === expected.toUpperCase();
            updateMatchStatus(matches);
            setMessage(matches ? 'Mask verified.' : 'Mask updated, but output still differs from expected.', !matches);
        })
        .catch(function() {
            setMessage('Network error during recheck.', true);
        });
    }

    function saveRule() {
        var raw = rawInput.value.trim();
        var expected = expectedInput.value.trim();
        var mask = maskInput.value.trim();
        if (!raw || !expected || !mask) {
            setMessage('Complete analysis before saving.', true);
            return;
        }

        var isUpdate = !!editingRuleId;
        var url = isUpdate ? updateUrlFor(editingRuleId) : storeUrl;
        var method = isUpdate ? 'PUT' : 'POST';

        saveBtn.disabled = true;
        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-HTTP-Method-Override': isUpdate ? 'PUT' : 'POST',
            },
            body: JSON.stringify({
                name: ruleNameInput.value.trim() || null,
                raw_example: raw,
                expected_output: expected,
                mask: mask,
                oem: oemSelect.value || null,
                document_type: editingDocumentType || null,
                mapping: lastMapping,
            }),
        })
        .then(function(r) { return r.json().then(function(data) { return { ok: r.ok, data: data }; }); })
        .then(function(result) {
            saveBtn.disabled = false;
            if (!result.ok) {
                setMessage(result.data.message || 'Could not save rule.', true);
                return;
            }

            window.location.reload();
        })
        .catch(function() {
            saveBtn.disabled = false;
            setMessage('Network error while saving.', true);
        });
    }

    function deleteRule() {
        if (!editingRuleId) {
            setMessage('No rule selected for deletion.', true);
            return;
        }

        if (!window.confirm('Delete this formatting rule?')) {
            return;
        }

        deleteBtn.disabled = true;
        fetch(deleteUrlFor(editingRuleId), {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-HTTP-Method-Override': 'DELETE',
            },
        })
        .then(function(r) { return r.json().then(function(data) { return { ok: r.ok, data: data }; }); })
        .then(function(result) {
            deleteBtn.disabled = false;
            if (!result.ok) {
                setMessage(result.data.message || 'Could not delete rule.', true);
                return;
            }

            window.location.reload();
        })
        .catch(function() {
            deleteBtn.disabled = false;
            setMessage('Network error while deleting.', true);
        });
    }

    analyzeBtn.addEventListener('click', function() { analyze(false); });
    recheckBtn.addEventListener('click', recheckMask);
    saveBtn.addEventListener('click', saveRule);
    cancelEditBtn.addEventListener('click', resetWizardForm);
    deleteBtn.addEventListener('click', deleteRule);
    findUnformattedBtn.addEventListener('click', findUnformatted);
    unformattedTableBody.addEventListener('click', function(e) {
        var tr = e.target.closest('.formatting-unformatted-row');
        if (!tr) return;
        unformattedTableBody.querySelectorAll('.formatting-unformatted-row').forEach(function(row) {
            row.classList.remove('is-selected');
        });
        tr.classList.add('is-selected');
        fillRawFromUnformatted(tr.getAttribute('data-example'), tr.getAttribute('data-oem'));
    });
    if (existingRulesTableBody) {
        existingRulesTableBody.addEventListener('click', function(e) {
            var tr = e.target.closest('.formatting-existing-row');
            if (!tr) return;

            var key = tr.getAttribute('data-rule-key');
            var rule = key ? rulesByKey[key] : null;
            if (!rule) {
                setMessage('Could not load this rule.', true);
                return;
            }

            existingRulesTableBody.querySelectorAll('.formatting-existing-row').forEach(function(row) {
                row.classList.remove('is-selected');
            });
            tr.classList.add('is-selected');
            loadRuleIntoForm(rule);
        });
    }
    maskInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            recheckMask();
        }
    });
});
</script>
@endsection
