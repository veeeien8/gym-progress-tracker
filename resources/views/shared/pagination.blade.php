@if($items->hasPages())
<nav aria-label="Pagination" class="mt-6 flex flex-wrap items-center justify-between gap-3">
 <p class="text-sm text-slate-500">Page {{ $items->currentPage() }} of {{ $items->lastPage() }} · {{ $items->total() }} results</p>
 <div class="flex gap-2">
@if($items->previousPageUrl())<a class="btn-secondary" rel="prev" href="{{ $items->previousPageUrl() }}">Previous</a>
@endif

 @if($items->nextPageUrl())<a class="btn-secondary" rel="next" href="{{ $items->nextPageUrl() }}">Next</a>
@endif
</div>
</nav>
@endif

