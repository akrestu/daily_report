{{-- Skeleton loading (bukan spinner penuh layar). type: list | kpi | table --}}
@props(['type' => 'list', 'count' => 3])
<div {{ $attributes }} aria-busy="true" role="status">
    @if ($type === 'kpi')
        <div class="sg-kpi-grid">
            @for ($i = 0; $i < $count; $i++)
                <div class="card sg-kpi"><div class="sg-skeleton" style="height:13px;width:60%"></div><div class="sg-skeleton mt-2" style="height:32px;width:40%"></div></div>
            @endfor
        </div>
    @elseif ($type === 'table')
        <div class="sg-table-wrap">
            @for ($i = 0; $i < $count; $i++)
                <div class="d-flex gap-3 align-items-center px-3 border-bottom" style="height:48px">
                    <div class="sg-skeleton" style="height:14px;flex:3"></div><div class="sg-skeleton" style="height:14px;flex:1"></div><div class="sg-skeleton" style="height:22px;width:90px;border-radius:999px"></div>
                </div>
            @endfor
        </div>
    @else
        <div class="sg-list">
            @for ($i = 0; $i < $count; $i++)
                <div class="sg-list-card">
                    <div class="sg-list-card__body">
                        <div class="d-flex justify-content-between"><div class="sg-skeleton" style="height:22px;width:90px;border-radius:999px"></div><div class="sg-skeleton" style="height:13px;width:70px"></div></div>
                        <div class="sg-skeleton mt-2" style="height:16px;width:85%"></div>
                        <div class="sg-skeleton mt-2" style="height:13px;width:55%"></div>
                    </div>
                </div>
            @endfor
        </div>
    @endif
    <span class="visually-hidden">Memuat…</span>
</div>
