@extends('layout.main')

@section('content')
<style>
    .card {
        border: none;
    }
    .card-body {
        background-color: white;
        padding: 0px;
    }
</style>
<div class="container-fluid">
    <div class="mb-3">
        <h1 class="h4 mb-0">Справочник сообщений</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="efds-table-header">
        <div class="efds-table-header__stats text-muted">
            <span>Всего записей: {{ $messages->count() }}</span>
        </div>
        <div class="efds-table-header__actions">
            <a href="{{ route('excel.export-messages') }}" class="btn efds-btn efds-btn--primary"><i class="fas fa-file-excel"></i> Экспорт в Excel</a>
            <a href="{{ route('messages.create') }}" class="btn efds-btn efds-btn--primary">Добавить сообщение</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead class="table-blue">
                        <tr>
                            <th>ID</th>
                            <th>Название услуги</th>
                            <th>Группа</th>
                            <th>Шаблон</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($messages as $message)
                            <tr class="msg-row" data-href="{{ route('messages.edit', $message) }}" style="cursor:pointer;">
                                <td>{{ $message->id }}</td>
                                <td>{{ $message->Service }}</td>
                                <td>{{ $message->Group ?? '-' }}</td>
                                <td>
                                    @if($message->Template)
                                        <div class="text-truncate" style="max-width: 300px;" title="{{ $message->Template }}">
                                            {{ Str::limit($message->Template, 100) }}
                                        </div>
                                    @else
                                        <span class="text-muted">Шаблон не задан</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function(){
  document.querySelectorAll('.msg-row').forEach(function(row){
    row.addEventListener('click', function(){
      const href = this.getAttribute('data-href');
      if (href) window.location.href = href;
    });
  });
});
</script>
@endsection 