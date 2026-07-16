@extends('layout.main')

@section('content')
<div class="container-fluid mt-3 formatting-page">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h2 class="mb-1 formatting-title">Card formatting rules</h2>
            <p class="text-muted small mb-0">
                Rules used for <strong>Normalised</strong> in Analysis → Add from Excel and for <strong>CUST. CARD NORM</strong> in Master data.
                Built-in rules are read-only. Custom rules are applied first.
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success py-2 px-3">{{ session('success') }}</div>
    @endif

    <div class="row g-4">
        <div class="col-lg-5">
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
                        One example per SRC. CUST. CARD structure. Format keeps fixed characters (e.g. 4N…C) and marks varying digits as d.
                    </p>
                    <div class="table-responsive formatting-unformatted-wrap">
                        <table class="table table-sm align-middle formatting-rules-table mb-0">
                            <thead>
                                <tr>
                                    <th>SRC. CUST. CARD</th>
                                    <th>Aircraft</th>
                                    <th>Format</th>
                                    <th class="text-end">Count</th>
                                </tr>
                            </thead>
                            <tbody id="unformattedTableBody">
                                <tr>
                                    <td colspan="4" class="text-muted small">Press the button to scan master data.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div id="unformattedMessage" class="small mt-2 text-muted"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-2 formatting-add-rule-col">
            <div class="formatting-card">
                <div class="formatting-card__head">
                    <h5 class="mb-0">Add new rule</h5>
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
                        <label class="form-label">OEM (optional)</label>
                        <select class="form-select" id="formatOem">
                            <option value="">Auto</option>
                            <option value="airbus">Airbus</option>
                            <option value="boeing">Boeing</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Document type (optional)</label>
                        <select class="form-select" id="formatDocumentType">
                            <option value="">Auto</option>
                            <option value="task_card">Task card</option>
                            <option value="mpd">MPD</option>
                            <option value="easa">EASA</option>
                            <option value="faa">FAA</option>
                            <option value="any">Any</option>
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

                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn efds-btn efds-btn--outline-primary btn-sm" id="formatRecheckBtn">Recheck mask</button>
                            <button type="button" class="btn efds-btn efds-btn--primary btn-sm" id="formatSaveBtn">Add to rules</button>
                        </div>
                    </div>

                    <div id="formatWizardMessage" class="small mt-3"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="formatting-card">
                <div class="formatting-card__head">
                    <h5 class="mb-0">Existing rules</h5>
                    <span class="text-muted small">{{ $rules->count() }} total</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle formatting-rules-table mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Mask</th>
                                <th>Context</th>
                                <th>Example</th>
                                <th>Source</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rules as $rule)
                                <tr class="{{ !($rule['is_active'] ?? true) ? 'text-muted' : '' }}">
                                    <td>{{ $rule['name'] }}</td>
                                    <td><code>{{ $rule['mask'] }}</code></td>
                                    <td>
                                        <div class="small">
                                            <div>{{ strtoupper((string) ($rule['document_type'] ?? 'any')) }}</div>
                                            @if(!empty($rule['oem']))
                                                <div class="text-muted">{{ ucfirst((string) $rule['oem']) }}</div>
                                            @endif
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
                                    <td>
                                        @if($rule['is_builtin'])
                                            <span class="badge formatting-badge formatting-badge--builtin">Built-in</span>
                                        @else
                                            <span class="badge formatting-badge formatting-badge--custom">Custom</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if(!$rule['is_builtin'] && !empty($rule['id']))
                                            <form method="POST" action="{{ route('modules.reliability.formatting.rules.toggle', $rule['id']) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-link px-1">
                                                    {{ ($rule['is_active'] ?? true) ? 'Disable' : 'Enable' }}
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('modules.reliability.formatting.rules.destroy', $rule['id']) }}" class="d-inline" onsubmit="return confirm('Delete this formatting rule?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-link text-danger px-1">Delete</button>
                                            </form>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-muted">No rules found.</td></tr>
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
.formatting-add-rule-col {
    flex: 0 0 calc(16.666667% + 50px);
    max-width: calc(16.666667% + 50px);
}
.formatting-card { background:#fff; border:1px solid var(--fmt-border); border-radius:8px; overflow:hidden; }
.formatting-card__head { display:flex; justify-content:space-between; align-items:center; padding:14px 16px; background:var(--fmt-bg); border-bottom:1px solid var(--fmt-border); }
.formatting-card__body { padding:16px; }
.formatting-rules-table thead th { font-size:12px; text-transform:uppercase; letter-spacing:.03em; color:#64748b; background:var(--fmt-bg); }
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
.formatting-unformatted-wrap { max-height: calc(100vh - 320px); overflow:auto; }
.formatting-unformatted-row { cursor:pointer; }
.formatting-unformatted-row:hover { background:#eef4ff; }
.formatting-unformatted-row.is-selected { background:#e8eef8; }
.formatting-unformatted-example { color:#1E64D4; font-family:Consolas, monospace; font-size:12px; word-break:break-all; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    var inferUrl = @json(route('modules.reliability.formatting.infer'));
    var previewUrl = @json(route('modules.reliability.formatting.preview'));
    var storeUrl = @json(route('modules.reliability.formatting.rules.store'));
    var unformattedUrl = @json(route('modules.reliability.formatting.unformatted'));

    var rawInput = document.getElementById('formatRawExample');
    var expectedInput = document.getElementById('formatExpectedOutput');
    var oemSelect = document.getElementById('formatOem');
    var docTypeSelect = document.getElementById('formatDocumentType');
    var analyzeBtn = document.getElementById('formatAnalyzeBtn');
    var recheckBtn = document.getElementById('formatRecheckBtn');
    var saveBtn = document.getElementById('formatSaveBtn');
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

    var lastMapping = [];

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

    function fillRawFromUnformatted(example, oem) {
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

    function findUnformatted() {
        findUnformattedBtn.disabled = true;
        unformattedMessage.textContent = 'Scanning master data…';
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
                unformattedMessage.textContent = result.data.message || 'Scan failed.';
                return;
            }

            renderUnformattedRows(result.data.rows || []);
            unformattedCountLabel.textContent = (result.data.format_groups || 0) + ' formats';
            unformattedMessage.textContent =
                'Scanned ' + (result.data.scanned || 0) +
                ' distinct SRC. CUST. CARD values; ' +
                (result.data.unformatted_total || 0) + ' unformatted → ' +
                (result.data.format_groups || 0) + ' format examples.';
        })
        .catch(function() {
            findUnformattedBtn.disabled = false;
            unformattedMessage.textContent = 'Network error while scanning.';
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
            document_type: docTypeSelect.value || null,
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

            if (!docTypeSelect.value && result.data.document_type) {
                docTypeSelect.value = result.data.document_type;
            }
            if (!oemSelect.value && result.data.oem) {
                oemSelect.value = result.data.oem;
            }

            if (!ruleNameInput.value.trim() && result.data.mask) {
                ruleNameInput.value = 'Custom: ' + result.data.mask;
            }

            setMessage('Pattern detected. Review the mask and mapping, then add the rule.', false);
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
                document_type: docTypeSelect.value || null,
            }),
        })
        .then(function(r) { return r.json().then(function(data) { return { ok: r.ok, data: data }; }); })
        .then(function(result) {
            if (!result.ok) {
                setMessage(result.data.message || 'Recheck failed.', true);
                return;
            }

            previewValue.textContent = result.data.preview_normalized || '—';
            expectedValue.textContent = expected;
            var matches = (result.data.preview_normalized || '').toUpperCase() === expected.toUpperCase();
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

        saveBtn.disabled = true;
        fetch(storeUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify({
                name: ruleNameInput.value.trim() || null,
                raw_example: raw,
                expected_output: expected,
                mask: mask,
                oem: oemSelect.value || null,
                document_type: docTypeSelect.value || null,
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

    analyzeBtn.addEventListener('click', function() { analyze(false); });
    recheckBtn.addEventListener('click', recheckMask);
    saveBtn.addEventListener('click', saveRule);
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
    maskInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            recheckMask();
        }
    });
});
</script>
@endsection
