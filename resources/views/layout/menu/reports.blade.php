@extends('dashboard')

@section('title')
    Reports
@endsection

@push('styles')
    @vite('resources/css/reports.css')
@endpush

@section('content')
    @php
        $dashboardMenuLocation = in_array($menuBarLocation ?? 'left', ['top', 'right', 'bottom', 'left'], true)
            ? $menuBarLocation
            : 'left';
        $reportFilters = $reportFilters ?? [
            'search' => '',
            'status' => 'unread',
            'reported_date' => '',
        ];
    @endphp

    <x-layout.nav />

    <div class="dashboard-page dashboard-page--{{ $dashboardMenuLocation }} reports-page">
        <x-layout.menu :menu-bar-location="$dashboardMenuLocation" />

        <main class="dashboard-content reports-content" aria-labelledby="reportsTitle">
            <section class="reports-shell" data-admin-reports-page>
                <header class="reports-header">
                    <div>
                        <p class="dashboard-kicker">Database Administration Core</p>
                        <h1 id="reportsTitle">System Report Log Console</h1>
                        <p>Live post report queue mapped to the existing reports and notification tables.</p>
                    </div>
                </header>

                <section class="reports-summary" aria-label="Report summary">
                    <article class="reports-summary-card">
                        <span>Total Post Reports</span>
                        <strong>{{ $reportSummary['total'] }}</strong>
                    </article>
                    <article class="reports-summary-card unread">
                        <span>Unread</span>
                        <strong>{{ $reportSummary['unread'] }}</strong>
                    </article>
                    <article class="reports-summary-card read">
                        <span>Read</span>
                        <strong>{{ $reportSummary['read'] }}</strong>
                    </article>
                </section>

                <section class="reports-tabs-card">
                    <div class="reports-tabs" role="tablist" aria-label="Report type tabs">
                        <button type="button" class="reports-tab active" id="posts-tab" role="tab"
                            aria-selected="true" data-report-tab="posts">
                            POST
                        </button>
                        <button type="button" class="reports-tab disabled" id="comments-tab" role="tab"
                            aria-selected="false" disabled data-report-tab="comments">
                            COMMENT <span>Coming soon</span>
                        </button>
                        <button type="button" class="reports-tab disabled" id="users-tab" role="tab"
                            aria-selected="false" disabled data-report-tab="users">
                            USER <span>Coming soon</span>
                        </button>
                    </div>
                </section>

                <section class="reports-table-card" id="posts-view" role="tabpanel" aria-labelledby="posts-tab"
                    data-report-posts-panel>
                    <div class="reports-table-toolbar">
                        <div>
                            <span class="reports-table-kicker">Post Audit Queue</span>
                            <p data-reports-table-count>
                                Showing {{ $reports->firstItem() ?? 0 }}-{{ $reports->lastItem() ?? 0 }} of {{ $reports->total() }} reports
                            </p>
                        </div>

                        <form method="GET" action="{{ route('reports') }}" class="reports-filter-form"
                            data-report-filter-form>
                            <label class="reports-search">
                                <x-fas-search class="reports-search-icon" aria-hidden="true" />
                                <span class="sr-only">Search reports</span>
                                <input type="search" name="search" value="{{ $reportFilters['search'] }}"
                                    placeholder="Search post, reporter, reason..." data-report-search>
                            </label>

                            <label class="reports-select">
                                <span class="sr-only">Filter report read status</span>
                                <select name="status" data-report-status-filter>
                                    <option value="unread" @selected($reportFilters['status'] === 'unread')>Unread Only</option>
                                    <option value="read" @selected($reportFilters['status'] === 'read')>Read Only</option>
                                    <option value="all" @selected($reportFilters['status'] === 'all')>All Reports</option>
                                </select>
                            </label>

                            <label class="reports-date">
                                <span>Reported date</span>
                                <input type="date" name="reported_date" value="{{ $reportFilters['reported_date'] }}"
                                    data-report-date-filter>
                            </label>

                            <div class="reports-filter-actions">
                                <button type="submit" class="reports-primary-button">Apply</button>
                                <a href="{{ route('reports') }}" class="reports-secondary-button">Clear</a>
                            </div>
                        </form>
                    </div>

                    <div class="reports-table-wrap">
                        <table class="reports-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Post Content Header</th>
                                    <th>Reason Log</th>
                                    <th>Reported By</th>
                                    <th>Reported Date</th>
                                    <th class="reports-actions-heading">Administrative Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($reports as $report)
                                    @php
                                        $isUnread = ! $report->admin_read;
                                        $isHighlighted = $highlightPostId > 0 && $report->target_id === $highlightPostId;
                                    @endphp
                                    <tr @class([
                                        'reports-row',
                                        'unread-state' => $isUnread,
                                        'read-state' => ! $isUnread,
                                        'is-highlighted' => $isHighlighted,
                                    ]) data-report-row data-report-id="{{ $report->id }}">
                                        <td data-label="No">
                                            <span class="reports-row-number">
                                                @if ($isUnread)
                                                    <span class="reports-ping-wrap" aria-label="Unread report">
                                                        <span class="reports-ping animate-ping" aria-hidden="true"></span>
                                                        <span class="reports-ping-core" aria-hidden="true"></span>
                                                    </span>
                                                @endif
                                                #{{ $report->id }}
                                            </span>
                                        </td>
                                        <td data-label="Post">
                                            <span class="reports-post-title">
                                                {{ $report->post?->title ?? 'Deleted or unavailable post' }}
                                            </span>
                                            <span class="reports-post-meta">
                                                target_id: {{ $report->target_id }}
                                                @if ($report->post?->url)
                                                    &bull; {{ $report->post->url }}
                                                @endif
                                            </span>
                                        </td>
                                        <td data-label="Reason">
                                            <span class="reports-reason">{{ $report->reason }}</span>
                                        </td>
                                        <td data-label="Reported By">
                                            <span class="reports-reporter">
                                                {{ $report->reporter?->name ?? 'Unknown user' }}
                                            </span>
                                            @if ($report->reporter?->email)
                                                <span class="reports-post-meta">{{ $report->reporter->email }}</span>
                                            @endif
                                        </td>
                                        <td data-label="Reported Date">
                                            <span class="reports-date-stack">
                                                <span>{{ $report->created_at?->format('M d, Y') }}</span>
                                                <span>{{ $report->created_at?->format('H:i') }}</span>
                                            </span>
                                        </td>
                                        <td data-label="Actions">
                                            <div class="reports-action-group">
                                                <a href="{{ route('home') }}" class="reports-action-button">
                                                    View Post
                                                </a>

                                                @if ($isUnread)
                                                    <form method="POST" action="{{ route('reports.read', $report) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="reports-action-button mark-read">
                                                            Mark Read
                                                        </button>
                                                    </form>
                                                @else
                                                    <button type="button" class="reports-action-button is-disabled" disabled>
                                                        Read
                                                    </button>
                                                @endif

                                                <button type="button" class="reports-action-button is-disabled" disabled>
                                                    Delete Post Soon
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="reports-empty-row">
                                            No post reports match the current filters.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="reports-pagination-row">
                        <span>Page {{ $reports->currentPage() }} of {{ $reports->lastPage() }}</span>
                        {{ $reports->links() }}
                    </div>
                </section>

                <section class="reports-coming-soon" aria-label="Coming soon report types">
                    <article>
                        <strong>Comment reports</strong>
                        <span>Coming soon</span>
                    </article>
                    <article>
                        <strong>User reports</strong>
                        <span>Coming soon</span>
                    </article>
                </section>
            </section>
        </main>
    </div>
@endsection
