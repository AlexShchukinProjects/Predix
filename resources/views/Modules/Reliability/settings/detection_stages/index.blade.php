@extends('layout.main')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Failure detection stage</h1>
        <a href="{{ route('modules.reliability.settings.detection-stages.create') }}" class="btn efds-btn efds-btn--primary">Add</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card" style="max-width: 600px;">
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead class="table-blue">
                    <tr>
                        <th>Name</th>
                        <th>Active</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr class="clickable-row"
                            data-href="{{ route('modules.reliability.settings.detection-stages.edit', $item) }}">
                            <td><strong>{{ $item->name }}</strong></td>
                            <td>{{ $item->active ? 'Yes' : 'No' }}</td>
                        </tr>

                        @foreach($item->children as $child)
                            <tr class="clickable-row"
                                data-href="{{ route('modules.reliability.settings.detection-stages.edit', $child) }}">
                                <td>&nbsp;&nbsp;&nbsp;— {{ $child->name }}</td>
                                <td>{{ $child->active ? 'Yes' : 'No' }}</td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-3">No records</td>
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


