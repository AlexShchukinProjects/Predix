@extends("layout.main")

@section('content')

<div class="template-container">
    <div class="row">
        <div class="col-12">
            <div class="page-header mb-4">
                <h2 class="page-title">Логи отправки сообщений</h2>
                <p class="page-subtitle">Отправленные e-mail уведомления</p>
            </div>

            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">№</th>
                                    <th>Тема сообщения</th>
                                    <th>Кому (e-mail)</th>
                                    <th style="width: 140px;">Статус</th>
                                    <th style="width: 180px;">Дата отправки</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                    <tr>
                                        <td>{{ $log->id }}</td>
                                        <td>{{ $log->subject }}</td>
                                        <td>{{ $log->recipient_email }}</td>
                                        <td>
                                            @if($log->success)
                                                <span class="badge bg-success">Успешно</span>
                                            @else
                                                <span class="badge bg-danger">Ошибка</span>
                                            @endif
                                        </td>
                                        <td>{{ $log->created_at?->format('d.m.Y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            Пока нет записей о отправленных письмах.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>

<style>
.template-container {
    background: white;
    min-height: calc(100vh - 80px);
    padding: 20px;
}

.page-header {
    margin-bottom: 20px;
}

.page-title {
    font-size: 24px;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 8px;
}

.page-subtitle {
    font-size: 16px;
    color: #6c757d;
    margin-bottom: 0;
}

.table thead th {
    background-color: #1E64D4;
    font-weight: 600;
    font-size: 14px;
}

.table tbody td {
    font-size: 14px;
    vertical-align: middle;
}
</style>

@endsection

