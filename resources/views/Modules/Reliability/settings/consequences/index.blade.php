@extends('layout.main')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Последствия</h1>
        <a href="{{ route('modules.reliability.settings.consequences.create') }}" class="btn btn-primary">Добавить</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead class="table-blue">
                    <tr>
                        <th>Наименование</th>
                        <th>Активно</th>
                        <th class="text-end">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->active ? 'Да' : 'Нет' }}</td>
                            <td class="text-end">
                                <a href="{{ route('modules.reliability.settings.consequences.edit', $item) }}" class="btn btn-sm btn-outline-primary">Редактировать</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-3">Нет записей</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($items, 'links'))
            <div class="card-footer">
                {{ $items->onEachSide(1)->links('vendor.pagination.bootstrap-4') }}
            </div>
        @endif
    </div>
</div>
@endsection


