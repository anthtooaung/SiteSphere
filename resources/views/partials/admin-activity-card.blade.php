@forelse($logs as $log)
    <div class="alc-entry">
        <div class="alc-icon" style="background: {{ $log->getColor() }}18">
            <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: {{ $log->getColor() }};"></span>
        </div>
        <div class="alc-info">
            <div class="alc-txt">{{ $log->action }}</div>
            <div class="alc-time">
                <i class="fa-regular fa-user"></i> {{ $log->user->name }}
                <span style="margin: 0 4px opacity: 0.5">·</span>
                <i class="fa-regular fa-clock"></i> {{ $log->created_at->format('H:i') }}
            </div>
        </div>
    </div>
    @if(!$loop->last)
        <div class="alc-divider"></div>
    @endif
@empty
    <div class="alc-empty">
        <i class="fa-regular fa-calendar-xmark"></i>
        <p>No activity recorded for this day</p>
    </div>
@endforelse

@if(!$isFullList && $totalCount > 3)
    <div class="alc-see-more" onclick="selectDate('{{ $date }}', true)">
        <i class="fa-solid fa-arrow-up-right-from-square"></i>
        See all {{ $totalCount }} actions
    </div>
@endif
