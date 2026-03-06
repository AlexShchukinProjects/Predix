@extends('layout.main')

@section('content')
<div class="container-fluid mt-3">
    <h4 class="mb-3">BUF report structure</h4>

    @if(session('success'))
        <div class="alert alert-success py-1 px-2 mb-2">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-body p-2">
            <form method="POST" action="{{ route('modules.reliability.settings.report-structure-buf.update') }}">
                @csrf

                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 45%;">Parameter</th>
                            <th style="width: 30%;">Settings</th>
                            <th style="width: 25%;">Example (print output)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1. Record no. (starting from…)</td>
                            <td>
                                <input
                                    type="number"
                                    name="start_number_prefix"
                                    class="form-control form-control-sm"
                                    min="1"
                                    value="{{ old('start_number_prefix', $bufSetting->start_number_prefix) }}"
                                >
                            </td>
                            <td>{{ $bufSetting->start_number_prefix }}51788</td>
                        </tr>
                        <tr>
                            <td>2. Detection date</td>
                            <td>
                                <input type="text" class="form-control form-control-sm" placeholder="DD.MM.YYYY">
                            </td>
                            <td>05.04.2025</td>
                        </tr>
                        <tr>
                            <td>3. Aircraft registration number</td>
                            <td>
                                <input type="text" class="form-control form-control-sm" placeholder="Registration (e.g. 06012)">
                            </td>
                            <td>06012</td>
                        </tr>
                        <tr>
                            <td>4. Aircraft type modification</td>
                            <td>
                                <input type="text" class="form-control form-control-sm" placeholder="Modification code (e.g. 7600)">
                            </td>
                            <td>7600</td>
                        </tr>
                        <tr>
                            <td>5. «00»</td>
                            <td>
                                <input type="text" class="form-control form-control-sm" value="00">
                            </td>
                            <td>00</td>
                        </tr>
                        <tr>
                            <td>6. Aircraft malfunction manifestation</td>
                            <td>
                                <textarea class="form-control form-control-sm" rows="2" placeholder="Brief malfunction description"></textarea>
                            </td>
                            <td>механическое повреждение антенны CI-205-3 из комплекта KN-62А №29057</td>
                        </tr>
                        <tr>
                            <td>7. Failure detection stage (on ground 10, in flight 20)</td>
                            <td>
                                <input type="text" class="form-control form-control-sm" placeholder="10 or 20">
                            </td>
                            <td>10</td>
                        </tr>
                        <tr>
                            <td>8. Functional system/subsystem</td>
                            <td>
                                <input type="text" class="form-control form-control-sm" placeholder="System/subsystem code (e.g. 110122)">
                            </td>
                            <td>110122</td>
                        </tr>
                        <tr>
                            <td>9. Component malfunction cause</td>
                            <td>
                                <textarea class="form-control form-control-sm" rows="2" placeholder="Component malfunction cause"></textarea>
                            </td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td>10. Component type</td>
                            <td>
                                <input type="text" class="form-control form-control-sm" placeholder="Component type">
                            </td>
                            <td>CI-205-3</td>
                        </tr>
                        <tr>
                            <td>11. Component serial no.</td>
                            <td>
                                <input type="text" class="form-control form-control-sm" placeholder="Serial number">
                            </td>
                            <td>441794</td>
                        </tr>
                        <tr>
                            <td>12. Component TSN (hrs)</td>
                            <td>
                                <input type="number" class="form-control form-control-sm" placeholder="TSN hours">
                            </td>
                            <td>5951.2</td>
                        </tr>
                        <tr>
                            <td>13. Component TSO (hrs)</td>
                            <td>
                                <input type="number" class="form-control form-control-sm" placeholder="TSO hours">
                            </td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td>14. Hours unit (always dash)</td>
                            <td>
                                <input type="text" class="form-control form-control-sm" value="-" readonly>
                            </td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td>15. Aircraft hours</td>
                            <td>
                                <input type="number" class="form-control form-control-sm" placeholder="Aircraft flight hours">
                            </td>
                            <td>3333.1</td>
                        </tr>
                        <tr>
                            <td>16. Aircraft landings</td>
                            <td>
                                <input type="number" class="form-control form-control-sm" placeholder="Aircraft landings">
                            </td>
                            <td>1785</td>
                        </tr>
                        <tr>
                            <td>17. — (always dash)</td>
                            <td>
                                <input type="text" class="form-control form-control-sm" value="-" readonly>
                            </td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td>18. — (always dash)</td>
                            <td>
                                <input type="text" class="form-control form-control-sm" value="-" readonly>
                            </td>
                            <td>-</td>
                        </tr>
                    </tbody>
                </table>
                <div class="mt-2 text-end">
                    <button type="submit" class="btn btn-sm btn-primary">
                        Save prefix
                    </button>
                </div>
                <div class="mt-1 text-muted" style="font-size: 0.8rem;">
                    Final record number = prefix (from Settings column) + failure ID, e.g. {{ $bufSetting->start_number_prefix }} + 51788 → {{ $bufSetting->start_number_prefix }}51788.
                </div>
            </form>
        </div>
    </div>
</div>
@endsection


