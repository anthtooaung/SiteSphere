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
         x-data="activityLogController()">
        <x-layout.menu :menu-bar-location="$dashboardMenuLocation" />

        <main class="dashboard-content dashboard-home-content" aria-labelledby="activityLogTitle">
            <section class="dashboard-panel mb-4">
                <p class="dashboard-kicker">Admin Records</p>
                <h1 id="activityLogTitle" class="text-2xl font-black text-slate-900 uppercase tracking-tight">Activity Log</h1>
                <p class="text-slate-500">Track platform changes and user actions via an interactive calendar.</p>
            </section>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                {{-- Calendar Pane --}}
                <section class="dashboard-panel bg-white p-6 rounded-lg border border-slate-200">
                    <div class="mb-6 flex items-center justify-between">
                        <h2 class="text-lg font-black text-slate-900 uppercase tracking-tight">{{ $monthName }} {{ $selectedYear }}</h2>
                        <div class="flex gap-2">
                            <button @click="prevMonth()" class="h-9 w-9 flex items-center justify-center rounded-lg bg-slate-50 border border-slate-200 text-slate-600 hover:bg-slate-100 transition-colors">
                                <i class="fa-solid fa-chevron-left text-xs"></i>
                            </button>
                            <button @click="nextMonth()" class="h-9 w-9 flex items-center justify-center rounded-lg bg-slate-50 border border-slate-200 text-slate-600 hover:bg-slate-100 transition-colors">
                                <i class="fa-solid fa-chevron-right text-xs"></i>
                            </button>
                        </div>
                    </div>

                    <div class="activity-calendar-grid">
                        @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                            <div class="calendar-header-day">{{ $day }}</div>
                        @endforeach

                        @for($i = 0; $i < $firstDayOfWeek; $i++)
                            <div></div>
                        @endfor

                        @for($day = 1; $day <= $daysInMonth; $day++)
                            @php
                                $date = \Carbon\Carbon::createFromDate($selectedYear, $selectedMonth, $day)->format('Y-m-d');
                                $hasLogs = $auditLogs->has($date);
                                $isToday = $date === $today;
                            @endphp
                            <div
                                @click="selectDate('{{ $date }}')"
                                :class="{ 'is-selected': selectedDate === '{{ $date }}', 'is-today': '{{ $isToday ? 'true' : 'false' }}' === 'true' }"
                                class="calendar-day-cell text-sm font-bold text-slate-700"
                            >
                                {{ $day }}
                                @if($hasLogs)
                                    <span class="activity-indicator" style="background: {{ $auditLogs[$date]->first()->getColor() }}"></span>
                                @endif
                            </div>
                        @endfor
                    </div>
                </section>

                {{-- Activity Feed Pane --}}
                <section class="dashboard-panel bg-white p-6 rounded-lg border border-slate-200">
                    <div class="mb-6">
                        <p class="dashboard-kicker">Activity Feed</p>
                        <h2 class="text-lg font-black text-slate-900 uppercase tracking-tight" x-text="formatDate(selectedDate)"></h2>
                    </div>

                    <div id="activityFeed" class="relative min-h-[300px]">
                        <div x-show="loading" class="absolute inset-0 bg-white/50 backdrop-blur-[2px] z-10 flex items-center justify-center rounded-lg">
                            <div class="flex flex-col items-center gap-3">
                                <div class="h-10 w-10 border-4 border-indigo-100 border-t-indigo-600 rounded-full animate-spin"></div>
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Fetching logs...</span>
                            </div>
                        </div>

                        {{-- Initially empty, filled by AJAX --}}
                        <div class="flex flex-col items-center justify-center h-full py-20 text-slate-300" x-show="!loading && !selectedDate">
                            <i class="fa-solid fa-calendar-day text-5xl mb-4 opacity-20"></i>
                            <p class="text-sm font-bold uppercase tracking-widest">Select a date to view activity</p>
                        </div>
                    </div>
                </section>
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
