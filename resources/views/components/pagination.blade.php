@props([
    'paginator' => null,
])

@if ($paginator && method_exists($paginator, 'hasPages') && $paginator->hasPages())
    <div {{ $attributes->merge(['class' => 'mt-12']) }}>
        {{ $paginator->onEachSide(1)->links() }}
    </div>
@endif
