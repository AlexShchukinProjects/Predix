@if($airports->hasPages())
    <div class="mt-4 airports-pagination-wrap">
        <div class="pagination-links">
            {{ $airports->links('vendor.pagination.bootstrap-4') }}
        </div>
    </div>
@endif
