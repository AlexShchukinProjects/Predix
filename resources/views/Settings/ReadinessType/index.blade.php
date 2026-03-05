@extends("layout.main")
@section('content')
<div class="container" style="width: 500px">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="">
                <div style="margin-bottom: 10px">
                    <h5 class="mb-0">Типы готовности</h5>
                </div>

                <div class="efds-table-header">
                    <div class="efds-table-header__stats text-muted">
                        <span>Всего записей: {{ count($readinessTypes) }}</span>
                    </div>
                    <div class="efds-table-header__actions">
                        <a href="{{ route('ReadinessType.create') }}" class="btn efds-btn efds-btn--primary">Добавить тип готовности</a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-blue">
                            <thead>
                                <tr>
                                    <th>Название</th>
                                    <th style="text-align: center !important;">Активность</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($readinessTypes as $type)
                                    <tr class="rt-row" data-href="{{ route('ReadinessType.edit', $type->id) }}" style="cursor:pointer;">
                                        <td>{{ $type->name }}</td>
                                        <td style="text-align: center;">
                                            <form action="{{ route('ReadinessType.update', $type->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <div class="form-check d-inline-block">
                                                    <input type="checkbox" class="form-check-input" name="active" 
                                                           {{ $type->active ? 'checked' : '' }} 
                                                           onchange="this.form.submit()">
                                                </div>
                                            </form>
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
  document.querySelectorAll('.rt-row').forEach(function(row){
    row.addEventListener('click', function(){
      const href = this.getAttribute('data-href');
      if (href) window.location.href = href;
    });
  });
});
</script>
@endsection 