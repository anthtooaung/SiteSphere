@forelse($logs as $log)
    <div class="alc-entry">
        <div class="alc-icon" style="background: {{ $log->getColor() }}18">
            <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: {{ $log->getColor() }};"></span>
        </div>
        <div class="alc-info">
            <div class="alc-txt">{{ $log->getActionLabel() }}</div>
            <div class="alc-time">
                <x-far-user /> {{ $log->user->name }}
                <span style="margin: 0 4px; opacity: 0.5;">·</span>
                <x-far-clock /> {{ $log->created_at->format('H:i') }}
            </div>
            @if($log->reason)
                <div class="alc-reason" style="font-size: 12px; color: var(--muted); margin-top: 4px;">{{ Str::limit($log->reason, 80) }}</div>
            @endif
        </div>
    </div>
    @if(!$loop->last)
        <div class="alc-divider"></div>
    @endif
@empty
    <div class="alc-empty">
        <x-far-calendar-xmark />
        <p>No activity recorded for this day</p>
    </div>
@endforelse

@if(!$isFullList && $totalCount > 3)
    <div class="alc-see-more" onclick="selectDate('{{ $date }}', true)">
        <x-fas-arrow-up-right-from-square />
        See all {{ $totalCount }} actions
    </div>
@endif
