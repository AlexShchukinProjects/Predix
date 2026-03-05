<style>
    table a {
        color: white !important;
    }
    
</style>
<div class="table-responsive">
    <table class="table table-hover">
        <thead class="table-blue">
            <tr>
                <th>ID</th>
                <th>
                    <a href="#" class="text-decoration-none sort-link" data-sort="NameRus">
                        Название (RU)
                        <span class="sort-indicator" data-for="NameRus"></span>
                    </a>
                </th>
                <th>Название (EN)</th>
                <th>IATA</th>
                <th>ICAO</th>
                <th>Город</th>
                <th>Страна</th>
                
            </tr>
        </thead>
        <tbody>
            @forelse($airports as $airport)
            <tr class="airport-row" data-href="{{ route('airports.edit', $airport) }}" style="cursor:pointer;">
                <td>{{ $airport->id }}</td>
                <td>{{ $airport->NameRus ?? '-' }}</td>
                <td>{{ $airport->NameEng ?? '-' }}</td>
                <td>
                    <span class="badge bg-primary">{{ $airport->iata ?? '-' }}</span>
                </td>
                <td>
                    <span class="badge bg-info">{{ $airport->icao ?? '-' }}</span>
                </td>
                <td>{{ $airport->City ?? '-' }}</td>
                <td>{{ $airport->Country ?? '-' }}</td>
            
                
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center text-muted py-4">
                    <i class="fas fa-plane fa-2x mb-2"></i>
                    <br>
                    Аэропорты не найдены
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
