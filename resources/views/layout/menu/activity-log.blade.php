@extends('dashboard')

@section('title', 'Activity Log - Admin')

@section('content')
    @php
        $dashboardMenuLocation = in_array($menuBarLocation ?? 'left', ['top', 'right', 'bottom', 'left'], true)
            ? $menuBarLocation
            : 'left';

        $actsExpanded = [];
        foreach ($auditLogs as $date => $logs) {
            foreach ($logs as $log) {
                $actsExpanded[] = [
                    'date' => $date,
                    'color' => $log->getColor(),
                    'category' => $log->category,
                    'user' => $log->user?->name ?? 'System',
                    'txt' => $log->action,
                    'target' => $log->target_type ? class_basename($log->target_type) : null,
                    'targetId' => $log->target_id,
                    'targetType' => $log->target_type,
                    'reason' => $log->reason,
                    'time' => $log->created_at->diffForHumans(),
                    'timeAbsolute' => $log->created_at->format('H:i'),
                ];
            }
        }
    @endphp

    <x-layout.nav />

    <div class="dashboard-page dashboard-page--{{ $dashboardMenuLocation }}">
        <x-layout.menu :menu-bar-location="$dashboardMenuLocation" />

        <main class="dashboard-content dashboard-home-content" aria-labelledby="activityLogTitle">
            @push('styles')
                @vite('resources/css/admin-activity.css')
            @endpush
            
            @push('scripts')
                <script>
                    window.AdminActivityData = {
                        actsExpanded: @json($actsExpanded),
                        selectedYear: {{ $selectedYear }},
                        selectedMonth: {{ $selectedMonth }},
                        selectedDate: '{{ now()->format("Y-m-d") }}',
                        postSlugs: @json($postSlugs ?? []),
                        userSlugs: @json($userSlugs ?? []),
                        commentPostSlugs: @json($commentPostSlugs ?? [])
                    };
                </script>
                @vite('resources/js/admin-activity.js')
            @endpush

            <div class="admin-shell">
              <nav class="breadcrumb" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}">
                  <span class="act-legend-dot" style="background: var(--accent-color);"></span><span>Admin Dashboard</span>
                </a>
                <span class="breadcrumb-sep">&#9654;</span>
                <span class="breadcrumb-current">Activity Log</span>
              </nav>

              <header class="page-header">
                <div class="page-header-label">
                  <span class="act-legend-dot" style="background: var(--accent-color);"></span> Activity History
                </div>
                <h1>Admin Activity Log</h1>
                <p>Track administrative actions, content updates, and platform events across time.</p>
              </header>

              <section class="expanded-card">
                <div class="expanded-head">
                  <div class="expanded-head-left">
                    <div class="card-icon" style="background: color-mix(in srgb, var(--accent-color) 12%, transparent); color: var(--accent-color)">
                      <span class="act-legend-dot" style="background: var(--accent-color); width: 10px; height: 10px;"></span>
                    </div>
                    <span class="card-title">Select Activity Date</span>
                  </div>
                </div>

                <div class="act-filter-bar" id="act-filter-bar">
                  <button class="act-filter-btn active" data-filter="all" type="button">
                    <span class="act-filter-dot" style="background: var(--text-color);"></span>
                    All
                  </button>
                  <button class="act-filter-btn" data-filter="announcement" type="button">
                    <span class="act-filter-dot" style="background: #7c3aed;"></span>
                    Announcement
                  </button>
                  <button class="act-filter-btn" data-filter="check" type="button">
                    <span class="act-filter-dot" style="background: #3b82f6;"></span>
                    Check
                  </button>
                  <button class="act-filter-btn" data-filter="moderation" type="button">
                    <span class="act-filter-dot" style="background: #ef4444;"></span>
                    Moderation
                  </button>
                  <button class="act-filter-btn" data-filter="resolved" type="button">
                    <span class="act-filter-dot" style="background: #10b981;"></span>
                    Resolved
                  </button>
                </div>

                <div class="expanded-body">
                  <div class="cal-widget">
                    <div class="cal-header">
                      <button class="cal-nav" type="button" id="cal-prev">&#9664;</button>
                      <div class="cal-header-mid" style="position: relative">
                        <button class="cal-month-btn" id="cal-month-btn" type="button">
                          <span id="cal-month-label"></span>
                          <span class="mc">&#9662;</span>
                        </button>
                        <span class="cal-term-label">Platform Activity</span>
                        <div class="cal-month-picker" id="cal-month-picker">
                          <div class="cmp-year-row">
                            <button class="cmp-ynav" type="button" id="picker-prev-year">&#9664;</button>
                            <span class="cmp-year-label" id="picker-year"></span>
                            <button class="cmp-ynav" type="button" id="picker-next-year">&#9654;</button>
                          </div>
                          <div class="cmp-month-grid" id="picker-month-grid"></div>
                        </div>
                      </div>
                      <button class="cal-nav" type="button" id="cal-next">&#9654;</button>
                    </div>
                    <div class="cal-grid" id="cal-grid"></div>
                  </div>

                  <div class="act-log-card">
                    <div class="alc-header">
                      <div>
                        <div class="alc-weekday" id="alc-weekday"></div>
                        <div class="alc-date-big" id="alc-date-big"></div>
                      </div>
                      <span class="alc-badge" id="alc-badge"></span>
                    </div>
                    <div class="alc-body" id="alc-body"></div>
                  </div>
                </div>
              </section>
            </div>

            <div id="log-modal">
              <div class="modal-card" onclick="event.stopPropagation()">
                <div class="modal-head">
                  <div class="modal-head-left">
                    <div class="card-icon" style="background: color-mix(in srgb, var(--accent-color) 12%, transparent); color: var(--accent-color)">
                      <span class="act-legend-dot" style="background: var(--accent-color); width: 10px; height: 10px;"></span>
                    </div>
                    <div>
                      <div class="modal-title" id="modal-title">Detailed Admin Log</div>
                      <div class="modal-sub" id="modal-sub">All actions</div>
                    </div>
                  </div>
                  <button class="modal-close" type="button">&#10005;</button>
                </div>
                <div class="modal-body" id="modal-body"></div>
              </div>
            </div>
        </main>
    </div>
@endsection
