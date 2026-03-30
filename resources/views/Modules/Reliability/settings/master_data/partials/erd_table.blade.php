{{-- $name (string), $fields array of ['name','pk'=>bool,'note'=>string,'hint'=>string for title] --}}
<div class="master-db-erd-table @if(!empty($wide)) master-db-erd-table--wide @endif">
    <div class="master-db-erd-table__head">{{ $name }}</div>
    <div class="master-db-erd-table__body">
        @foreach($fields as $f)
            <div class="master-db-erd-field @if(!empty($f['pk'])) master-db-erd-field--pk @endif"
                 @if(!empty($f['hint'])) title="{{ $f['hint'] }}" @endif>
                @if(!empty($f['pk']))
                    <i class="fas fa-key master-db-erd-pk-icon" aria-hidden="true"></i>
                @endif
                <span class="master-db-erd-field-name">{{ $f['name'] }}</span>
                @if(!empty($f['note']))
                    <span class="master-db-erd-note">{{ $f['note'] }}</span>
                @endif
            </div>
        @endforeach
    </div>
</div>
