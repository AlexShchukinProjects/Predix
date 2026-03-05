@extends('layout.main')

@section('content')
<style>
.color-preview {
    border: 1px solid #ddd;
    border-radius: 3px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.color-preview:hover {
    transform: scale(1.1);
    transition: transform 0.2s ease;
}
</style>
<div class="container mt-4" style="">
    <div class="mb-3">
        <h2 class="mb-0">Справочник мероприятий</h2>
    </div>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="efds-table-header">
        <div class="efds-table-header__stats text-muted">
            <span>Всего записей: {{ count($events) }}</span>
        </div>
        <div class="efds-table-header__actions">
            <a href="{{ route('events.create') }}" class="btn efds-btn efds-btn--primary">Добавить мероприятие</a>
        </div>
    </div>

    <table class="table table-blue table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Название</th>
                <th>Сокращение</th>
                <th>Цвет</th>
                <th>Тип мероприятия</th>
                <th style="text-align: center;">Активно</th>
                
            </tr>
        </thead>
        <tbody>
            @foreach($events as $event)
                <tr class="event-row" data-href="{{ route('events.edit', $event) }}" style="cursor:pointer;">
                    <td>{{ $event->id }}</td>
                    <td>{{ $event->name }}</td>
                    <td>
                        @if($event->abbreviation)
                            <span class="badge bg-primary">{{ $event->abbreviation }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($event->color)
                            <div class="d-flex align-items-center">
                                <div class="color-preview me-2" style="width: 20px; height: 20px; background-color: {{ $event->color }}; border: 1px solid #ddd; border-radius: 3px;"></div>
                                <span class="text-muted">{{ $event->color }}</span>
                            </div>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($event->work_type)
                            <span class="badge bg-warning text-dark">Рабочее</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        @if($event->active)
                            <span style="color: #28a745; font-size: 20px; font-weight: bold;">✓</span>
                        @else
                            <span style="color: #dc3545; font-size: 20px; font-weight: bold;">✗</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<script>
document.addEventListener('DOMContentLoaded', function(){
  document.querySelectorAll('.event-row').forEach(function(row){
    row.addEventListener('click', function(){
      const href = this.getAttribute('data-href');
      if (href) window.location.href = href;
    });
  });
});
</script>
@endsection 