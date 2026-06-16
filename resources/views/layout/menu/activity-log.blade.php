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
                $iconStr = str_replace('fa-', '', $log->getIcon());
                $actsExpanded[] = [
                    'date' => $date,
                    'color' => $log->getColor(),
                    'icon' => $iconStr,
                    'txt' => $log->action,
                    'time' => $log->created_at->diffForHumans(),
                ];
            }
        }
    @endphp

    <x-layout.nav />

    <div class="dashboard-page dashboard-page--{{ $dashboardMenuLocation }}">
        <x-layout.menu :menu-bar-location="$dashboardMenuLocation" />

        <main class="dashboard-content dashboard-home-content" aria-labelledby="activityLogTitle">
            @push('styles')
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
                @vite('resources/css/admin-activity.css')
            @endpush
            
            @push('scripts')
                <script>
                    window.AdminActivityData = {
                        actsExpanded: @json($actsExpanded),
                        selectedYear: {{ $selectedYear }},
                        selectedMonth: {{ $selectedMonth }},
                        selectedDate: '{{ now()->format("Y-m-d") }}'
                    };
                </script>
                @vite('resources/js/admin-activity.js')
            @endpush

            <div class="admin-shell">
              <nav class="breadcrumb" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}">
                  <i class="fa-solid fa-shield-halved"></i><span>Admin Dashboard</span>
                </a>
                <span class="breadcrumb-sep"><i class="fa-solid fa-chevron-right"></i></span>
                <span class="breadcrumb-current">Activity Log</span>
              </nav>

              <section class="expanded-card">
                <div class="expanded-head">
                  <div class="expanded-head-left">
                    <div class="card-icon" style="background: color-mix(in srgb, var(--accent-color) 12%, transparent); color: var(--accent-color)">
                      <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                    <span class="card-title">Select Activity Date</span>
                  </div>
                </div>

                <div class="expanded-body">
                  <div class="cal-widget">
                    <div class="cal-header">
                      <button class="cal-nav" type="button" id="cal-prev"><i class="fa-solid fa-chevron-left"></i></button>
                      <div class="cal-header-mid" style="position: relative">
                        <button class="cal-month-btn" id="cal-month-btn" type="button">
                          <span id="cal-month-label"></span>
                          <i class="fa-solid fa-chevron-down mc"></i>
                        </button>
                        <span class="cal-term-label">Platform Activity</span>
                        <div class="cal-month-picker" id="cal-month-picker">
                          <div class="cmp-year-row">
                            <button class="cmp-ynav" type="button" id="picker-prev-year"><i class="fa-solid fa-chevron-left"></i></button>
                            <span class="cmp-year-label" id="picker-year"></span>
                            <button class="cmp-ynav" type="button" id="picker-next-year"><i class="fa-solid fa-chevron-right"></i></button>
                          </div>
                          <div class="cmp-month-grid" id="picker-month-grid"></div>
                        </div>
                      </div>
                      <button class="cal-nav" type="button" id="cal-next"><i class="fa-solid fa-chevron-right"></i></button>
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

              <header class="page-header">
                <div class="page-header-label">
                  <i class="fa-solid fa-clock-rotate-left"></i> Activity History
                </div>
                <h1>Admin Activity Log</h1>
                <p>Track administrative actions, content updates, and platform events across time.</p>
              </header>
            </div>

            <div id="log-modal">
              <div class="modal-card" onclick="event.stopPropagation()">
                <div class="modal-head">
                  <div class="modal-head-left">
                    <div class="card-icon" style="background: color-mix(in srgb, var(--accent-color) 12%, transparent); color: var(--accent-color)">
                      <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                    <div>
                      <div class="modal-title" id="modal-title">Detailed Admin Log</div>
                      <div class="modal-sub" id="modal-sub">All actions</div>
                    </div>
                  </div>
                  <button class="modal-close" type="button"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="modal-body" id="modal-body"></div>
              </div>
            </div>
        </main>
    </div>
@endsection
