@extends('layout.main')

@section('content')
<div class="container mt-4" style="width: 700px">
    <div class="mb-3">
        <h2 class="mb-0">Справочник типов ТОиР</h2>
    </div>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="efds-table-header">
        <div class="efds-table-header__stats text-muted">
            <span>Всего записей: {{ $maintenanceTypes->total() }}</span>
        </div>
        <div class="efds-table-header__actions">
            <a href="{{ route('maintenance-types.create') }}" class="btn efds-btn efds-btn--primary">Добавить тип ТОиР</a>
        </div>
    </div>

    <table class="table table-blue table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Название</th>
                <th style="text-align: center;">Активно</th>
                
            </tr>
        </thead>
        <tbody>
            @foreach($maintenanceTypes as $maintenanceType)
                <tr class="mt-row" data-href="{{ route('maintenance-types.edit', $maintenanceType) }}" style="cursor:pointer;">
                    <td>{{ $maintenanceType->id }}</td>
                    <td>{{ $maintenanceType->name }}</td>
                    <td style="text-align: center;">
                        @if($maintenanceType->active)
                            <span style="color: #28a745; font-size: 20px; font-weight: bold;">✓</span>
                        @else
                            <span style="color: #dc3545; font-size: 20px; font-weight: bold;">✗</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $maintenanceTypes->onEachSide(1)->links('vendor.pagination.bootstrap-4') }}
</div>
<script>
document.addEventListener('DOMContentLoaded', function(){
  document.querySelectorAll('.mt-row').forEach(function(row){
    row.addEventListener('click', function(){
      const href = this.getAttribute('data-href');
      if (href) window.location.href = href;
    });
  });
});
</script>
@endsection
