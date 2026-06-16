@extends('dashboard')

@section('title', 'Activity Log - Admin')

@section('content')
    @php
        $dashboardMenuLocation = in_array($menuBarLocation ?? 'left', ['top', 'right', 'bottom', 'left'], true)
            ? $menuBarLocation
            : 'left';

        $startOfMonth = \Carbon\Carbon::createFromDate($selectedYear, $selectedMonth, 1);
        $daysInMonth = $startOfMonth->daysInMonth;
        $firstDayOfWeek = $startOfMonth->dayOfWeek; // 0 (Sun) to 6 (Sat)
        $today = now()->format('Y-m-d');

        $monthName = $startOfMonth->format('F');
    @endphp

    <x-layout.nav />

    <div class="dashboard-page dashboard-page--{{ $dashboardMenuLocation }}"
        >
        <x-layout.menu :menu-bar-location="$dashboardMenuLocation" />

                <main class="dashboard-content dashboard-home-content" aria-labelledby="activityLogTitle">
            @php
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
            @push('styles')
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
                @vite('resources/css/admin-activity.css')
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
                      <button class="cal-nav" type="button" @click="prevMonth()"><i class="fa-solid fa-chevron-left"></i></button>
                      <div class="cal-header-mid" style="position: relative">
                        <button class="cal-month-btn" id="cal-month-btn" type="button">
                          <span id="cal-month-label"></span>
                          <i class="fa-solid fa-chevron-down mc"></i>
                        </button>
                        <span class="cal-term-label">Platform Activity</span>
                        <div class="cal-month-picker" id="cal-month-picker">
                          <div class="cmp-year-row">
                            <button class="cmp-ynav" type="button"><i class="fa-solid fa-chevron-left"></i></button>
                            <span class="cmp-year-label" id="picker-year"></span>
                            <button class="cmp-ynav" type="button"><i class="fa-solid fa-chevron-right"></i></button>
                          </div>
                          <div class="cmp-month-grid" id="picker-month-grid"></div>
                        </div>
                      </div>
                      <button class="cal-nav" type="button" @click="nextMonth()"><i class="fa-solid fa-chevron-right"></i></button>
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
                    <div class="card-icon" style="background: #eff6ff; color: #3b82f6">
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

@push('scripts')
<script>
    function activityLogController() {
        return {
            selectedDate: '{{ now()->format('Y-m-d') }}',
            currentMonth: {{ $selectedMonth }},
            currentYear: {{ $selectedYear }},
            loading: false,

            init() {
                this.selectDate(this.selectedDate);
            },

            async selectDate(date, showAll = false) {
                this.selectedDate = date;
                this.loading = true;

                try {
                    const url = showAll ? `/api/admin/activity/${date}?all=true` : `/api/admin/activity/${date}`;
                    const response = await fetch(url);
                    if (response.ok) {
                        const html = await response.text();
                        document.getElementById('activityFeed').innerHTML = html;
                    } else {
                        document.getElementById('activityFeed').innerHTML = `
                            <div class="flex flex-col items-center justify-center py-20 text-slate-400">
                                <i class="fa-solid fa-circle-exclamation text-3xl mb-2 text-rose-500"></i>
                                <p class="text-sm font-bold uppercase">Failed to load activity</p>
                            </div>
                        `;
                    }
                } catch (error) {
                    console.error('Activity fetch error:', error);
                    window.sitesphereSwal.toast({
                        icon: 'error',
                        title: 'Failed to load activity feed'
                    });
                } finally {
                    this.loading = false;
                }
            },

            formatDate(dateStr) {
                if (!dateStr) return 'Select Date';
                // Handle YYYY-MM-DD
                const [year, month, day] = dateStr.split('-');
                const date = new Date(year, month - 1, day);
                return date.toLocaleDateString('en-US', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
            },

            prevMonth() {
                let m = parseInt(this.currentMonth) - 1;
                let y = parseInt(this.currentYear);
                if (m < 1) {
                    m = 12;
                    y--;
                }
                this.updateCalendar(m, y);
            },

            nextMonth() {
                let m = parseInt(this.currentMonth) + 1;
                let y = parseInt(this.currentYear);
                if (m > 12) {
                    m = 1;
                    y++;
                }
                this.updateCalendar(m, y);
            },

            updateCalendar(month, year) {
                window.location.href = `{{ route('admin.activity-log') }}?month=${month}&year=${year}`;
            }
        }
    }
</script>
@endpush
