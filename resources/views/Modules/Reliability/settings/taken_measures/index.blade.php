@extends('layout.main')

@section('content')
<div class="container-fluid">
    <div class="mb-3">
        <h2 class="mb-0">Taken measures</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="efds-table-header">
        @if(method_exists($items, 'total'))
            <div class="efds-table-header__stats text-muted">
                <span class="me-2">Per page:</span>
                @php $currentPerPage = request()->get('per_page', $items->perPage()); @endphp
                <select class="form-select form-select-sm d-inline-block" style="width: auto;" onchange="window.location.href='{{ request()->url() }}?per_page='+this.value" aria-label="Records per page">
                    <option value="10" {{ $currentPerPage == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ $currentPerPage == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ $currentPerPage == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $currentPerPage == 100 ? 'selected' : '' }}>100</option>
                </select>
                <span class="ms-2">Total records: {{ $items->total() }}</span>
            </div>
        @else
            <div class="efds-table-header__stats text-muted"></div>
        @endif
        <div class="efds-table-header__actions">
            <a href="{{ route('modules.reliability.settings.taken-measures.create') }}" class="btn efds-btn efds-btn--primary">Add</a>
        </div>
    </div>

    <div class="card" style="max-width: 600px;">
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead class="table-blue">
                    <tr>
                        <th>Name</th>
                        <th style="text-align: center;">Active</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr class="clickable-row"
                            data-href="{{ route('modules.reliability.settings.taken-measures.edit', $item) }}">
                            <td><strong>{{ $item->name }}</strong></td>
                            <td style="text-align: center;">
                                @if($item->active)
                                    <span style="color: #28a745; font-size: 20px; font-weight: bold;">✓</span>
                                @else
                                    <span style="color: #dc3545; font-size: 20px; font-weight: bold;">✗</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="text-center text-muted py-3">No records</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <style>
        .clickable-row {
            cursor: pointer;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.clickable-row').forEach(function (row) {
                row.addEventListener('click', function () {
                    const href = this.dataset.href;
                    if (href) {
                        window.location.href = href;
                    }
                });
            });
        });
    </script>
</div>
@endsection

<style>
    .main_screen
    {width:600px;}
    </style>


