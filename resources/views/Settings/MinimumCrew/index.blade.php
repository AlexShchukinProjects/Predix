@extends("layout.main")
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex align-items-center mb-3">
                <h1 class="h4 mb-0 flex-grow-1">Минимальный состав экипажа</h1>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="minimumCrewTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="position: sticky; left: 0; background: #1E64D4; z-index: 10;">Типы ВС</th>
                                    @foreach($positions as $position)
                                        <th>{{ $position->Name }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($aircraftTypes as $aircraftType)
                                    <tr>
                                        <td style="position: sticky; left: 0; background: #1E64D4; z-index: 10; font-weight: 600;">
                                            {{ $aircraftType->icao }}
                                        </td>
                                        @foreach($positions as $position)
                                            <td>
                                                <input type="number" 
                                                       class="form-control form-control-sm quantity-input" 
                                                       min="0" 
                                                       value="{{ $crewMap[$aircraftType->id][$position->id] ?? 0 }}"
                                                       data-aircraft-type-id="{{ $aircraftType->id }}"
                                                       data-position-id="{{ $position->id }}"
                                                       style="width: 80px; text-align: center;">
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mt-3 d-flex justify-content-end">
                <button type="button" class="btn efds-btn efds-btn--primary" style="margin-right: 0;" onclick="saveAll()">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<style>
    .table-responsive {
        max-height: 80vh;
        overflow-x: auto;
        overflow-y: auto;
    }
    
    #minimumCrewTable thead th {
        position: sticky;
        top: 0;
        background: #1E64D4;
        z-index: 5;
        border-bottom: 2px solid #dee2e6;
    }
    
    #minimumCrewTable thead th:first-child {
        z-index: 15;
    }
    
    #minimumCrewTable tbody td:first-child {
        z-index: 10;
    }
    
    .quantity-input {
        border: 1px solid #ced4da;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    
    .quantity-input:focus {
        border-color: #80bdff;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
</style>

<script>
function saveAll() {
    const inputs = document.querySelectorAll('.quantity-input');
    const data = [];
    
    inputs.forEach(input => {
        const aircraftTypeId = input.getAttribute('data-aircraft-type-id');
        const positionId = input.getAttribute('data-position-id');
        const quantity = parseInt(input.value) || 0;
        
        data.push({
            aircraft_type_id: parseInt(aircraftTypeId),
            position_id: parseInt(positionId),
            quantity: quantity
        });
    });
    
    fetch('{{ route("minimum_crew.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ data: data })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert('Данные успешно сохранены');
        } else {
            alert('Ошибка при сохранении данных');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ошибка при сохранении данных');
    });
}
</script>
@endsection

