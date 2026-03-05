@extends("layout.main")
@section('content')
<div class="container" style="width: 500px">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="">
                <div style="margin-bottom: 10px">
                    <h5 class="mb-0">Должности</h5>
                </div>

                <div class="efds-table-header">
                    <div class="efds-table-header__stats text-muted">
                        <span>Всего записей: {{ count($positions) }}</span>
                    </div>
                    <div class="efds-table-header__actions">
                        <a href="{{ route('Position.create') }}" class="btn efds-btn efds-btn--primary">Добавить должность</a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-blue">
                            <thead>
                                <tr>
                                    <th>Название</th>
                                    <th>Сокращение</th>
                                    <th style="text-align: center;">Активность</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($positions as $position)
                                    <tr class="position-row" data-href="{{ route('Position.edit', $position->id) }}" style="cursor:pointer;">
                                        <td>{{ $position->Name }}</td>
                                        <td>{{ $position->short_name }}</td>
                                        <td style="text-align: center;">
                                            @if($position->Active)
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
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function(){
  document.querySelectorAll('.position-row').forEach(function(row){
    row.addEventListener('click', function(){
      const href = this.getAttribute('data-href');
      if (href) window.location.href = href;
    });
  });
});
</script>
@endsection
