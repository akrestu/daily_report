{{--
    Tab sticky (Bootstrap tab). :items = ['desc' => 'Deskripsi', 'comments' => ['Komentar', 4]]
    Pane ditulis di halaman: <div class="tab-pane fade" id="{prefix}-{key}" role="tabpanel">…</div> di dalam .tab-content
--}}
@props(['items' => [], 'active' => null, 'prefix' => 'tab'])
@php($active ??= array_key_first($items))
<ul {{ $attributes->class(['nav nav-underline sg-tabs px-3']) }} role="tablist">
    @foreach ($items as $key => $item)
        @php([$text, $count] = is_array($item) ? [$item[0], $item[1] ?? null] : [$item, null])
        <li class="nav-item" role="presentation">
            <button type="button" role="tab" id="{{ $prefix }}-{{ $key }}-tab"
                    @class(['nav-link', 'active' => $key === $active])
                    data-bs-toggle="tab" data-bs-target="#{{ $prefix }}-{{ $key }}"
                    aria-controls="{{ $prefix }}-{{ $key }}" aria-selected="{{ $key === $active ? 'true' : 'false' }}">
                {{ $text }}
                @if ($count !== null)
                    <span class="badge rounded-pill sg-num">{{ $count }}</span>
                @endif
            </button>
        </li>
    @endforeach
</ul>
