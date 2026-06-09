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
        <section class="reports-shell" data-admin-reports-page x-data="{ activeTab: '{{ $activeTab }}' }">
            <header class="reports-header">
                <div>
                    <p class="dashboard-kicker">Database Administration Core</p>
                    <h1 id="reportsTitle">System Report Log Console</h1>
                    <p>Live post report queue mapped to the existing reports and notification tables.</p>
                </div>
            </header>

            <section class="reports-summary" aria-label="Report summary">
                <article class="reports-summary-card">
                    <span>Total Reports</span>
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
                    <button type="button" class="reports-tab" id="posts-tab" role="tab"
                        :class="{ 'active': activeTab === 'posts' }"
                        :aria-selected="activeTab === 'posts'"
                        @click="activeTab = 'posts'"
                        data-report-tab="posts">
                        POST
                    </button>
                    <button type="button" class="reports-tab" id="comments-tab" role="tab"
                        :class="{ 'active': activeTab === 'comments' }"
                        :aria-selected="activeTab === 'comments'"
                        @click="activeTab = 'comments'"
                        data-report-tab="comments">
                        COMMENT
                    </button>
                    <button type="button" class="reports-tab" id="users-tab" role="tab"
                        :class="{ 'active': activeTab === 'users' }"
                        :aria-selected="activeTab === 'users'"
                        @click="activeTab = 'users'"
                        data-report-tab="users">
                        USER
                    </button>
                </div>
            </section>

            <section class="reports-table-card" id="posts-view" role="tabpanel" aria-labelledby="posts-tab"
                data-report-posts-panel x-show="activeTab === 'posts'">
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
                                <option value="unread" @selected($reportFilters['status']==='unread' )>Unread Only</option>
                                <option value="read" @selected($reportFilters['status']==='read' )>Read Only</option>
                                <option value="all" @selected($reportFilters['status']==='all' )>All Reports</option>
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
                            <tr @class([ 'reports-row' , 'unread-state'=> $isUnread,
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

                                        <button type="button" class="reports-action-button" onclick="executeDelete('Post', '{{ $report->target_id }}')">
                                            Delete Post
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

            <section class="reports-table-card" id="comments-view" role="tabpanel" aria-label="Comment reports" aria-labelledby="comments-tab"
                x-show="activeTab === 'comments'" x-cloak>
                <div class="reports-table-toolbar">
                    <div>
                        <span class="reports-table-kicker">Comment Audit Queue</span>
                        <p data-reports-table-count>
                            Showing {{ $commentReports->firstItem() ?? 0 }}-{{ $commentReports->lastItem() ?? 0 }} of {{ $commentReports->total() }} reports
                        </p>
                    </div>
                </div>

                <div class="reports-table-wrap">
                    <table class="reports-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Comment Snippet</th>
                                <th>Reason</th>
                                <th>Reported By</th>
                                <th>Reported Date</th>
                                <th class="reports-actions-heading">Administrative Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($commentReports as $report)
                            @php
                            $isUnread = ! $report->admin_read;
                            @endphp
                            <tr @class([ 'reports-row' , 'unread-state'=> $isUnread, 'read-state' => ! $isUnread ]) data-report-row data-report-id="{{ $report->id }}">
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
                                <td data-label="Comment Snippet">
                                    <span class="reports-post-title">
                                        "{{ Str::limit($report->comment?->content ?? 'Deleted or unavailable comment', 60) }}"
                                    </span>
                                    <span class="reports-post-meta">
                                        target_id: {{ $report->target_id }}
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
                                        <button type="button" class="reports-action-button"
                                                onclick="viewDetails('comment', '{{ $report->id }}', '{{ e($report->comment?->content) }}')">
                                            View Comment
                                        </button>

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

                                        <button type="button" class="reports-action-button" onclick="executeDelete('Comment', '{{ $report->target_id }}')">
                                            Delete Comment
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="reports-empty-row">
                                    No comment reports match the current filters.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="reports-pagination-row">
                    <span>Page {{ $commentReports->currentPage() }} of {{ $commentReports->lastPage() }}</span>
                    {{ $commentReports->links() }}
                </div>
            </section>

            <section class="reports-table-card" id="users-view" role="tabpanel" aria-label="User reports" aria-labelledby="users-tab"
                x-show="activeTab === 'users'" x-cloak>
                <div class="reports-table-toolbar">
                    <div>
                        <span class="reports-table-kicker">User Account Flags</span>
                        <p data-reports-table-count>
                            Showing {{ $userReports->firstItem() ?? 0 }}-{{ $userReports->lastItem() ?? 0 }} of {{ $userReports->total() }} reports
                        </p>
                    </div>
                </div>

                <div class="reports-table-wrap">
                    <table class="reports-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>User Identity</th>
                                <th>Reason</th>
                                <th>Reported By</th>
                                <th>Reported Date</th>
                                <th class="reports-actions-heading">Administrative Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($userReports as $report)
                            @php
                            $isUnread = ! $report->admin_read;
                            $targetUser = $report->targetUser;
                            $initials = $targetUser ? strtoupper(substr($targetUser->name, 0, 2)) : '??';
                            @endphp
                            <tr @class([ 'reports-row' , 'unread-state'=> $isUnread, 'read-state' => ! $isUnread ]) data-report-row data-report-id="{{ $report->id }}">
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
                                <td data-label="User Identity">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div style="width: 32px; height: 32px; border-radius: 50%; background: #e2e8f0; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: bold; color: #475569; border: 1px solid #cbd5e1;">
                                            {{ $initials }}
                                        </div>
                                        <div>
                                            <span class="reports-reporter">
                                                {{ $targetUser?->name ?? 'Unknown User' }}
                                            </span>
                                            <span class="reports-post-meta">
                                                @if ($targetUser?->email)
                                                {{ $targetUser->email }}
                                                @else
                                                target_id: {{ $report->target_id }}
                                                @endif
                                            </span>
                                        </div>
                                    </div>
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
                                        <button type="button" class="reports-action-button"
                                                onclick="viewDetails('profile', '{{ $report->id }}', '{{ e($targetUser?->name) }}', '{{ $initials }}', '{{ $targetUser ? $targetUser->created_at?->format('M Y') : 'Unknown' }}')">
                                            View Profile
                                        </button>

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

                                        <button type="button" class="reports-action-button" onclick="executeSuspend('{{ $report->target_id }}', '{{ e($targetUser?->name) }}')">
                                            Suspend Account
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="reports-empty-row">
                                    No user reports match the current filters.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="reports-pagination-row">
                    <span>Page {{ $userReports->currentPage() }} of {{ $userReports->lastPage() }}</span>
                    {{ $userReports->links() }}
                </div>
            </section>
        </section>
    </main>
</div>

@push('scripts')
<script>
    function viewDetails(type, id, detailsText, initials, dateStr) {
        let content = '';
        let title = '';

        if (type === 'post') {
            title = `Post Metadata: #${id}`;
            content = `
                <div style="text-align: left; font-size: 0.875rem; font-family: sans-serif; line-height: 1.5;">
                    <div style="background-color: #f8fafc; padding: 16px; border-radius: 6px; border: 1px solid #e2e8f0;">
                        <h4 style="font-weight: bold; color: #0f172a; margin-bottom: 4px;">Title: ${initials}</h4>
                        <p style="color: #475569; margin: 0; line-height: 1.6;">${detailsText}</p>
                    </div>
                </div>`;
        } else if (type === 'comment') {
            title = `Comment Audit: #${id}`;
            content = `
                <div style="text-align: left; font-size: 0.875rem; font-family: sans-serif; line-height: 1.5;">
                    <div style="border-left: 4px solid #cbd5e1; padding-left: 16px; padding-top: 8px; padding-bottom: 8px; font-style: italic; color: #334155;">
                        "${detailsText}"
                    </div>
                </div>`;
        } else if (type === 'profile') {
            title = `Account Profile View: #${id}`;
            content = `
                <div style="text-align: left; font-size: 0.875rem; font-family: sans-serif; line-height: 1.5;">
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <div style="height: 64px; width: 64px; background-color: #e2e8f0; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: bold; color: #334155;">${initials}</div>
                        <div>
                            <h4 style="font-size: 1.125rem; font-weight: bold; margin: 0;">${detailsText}</h4>
                            <p style="color: #64748b; margin: 4px 0 0;">Member since: ${dateStr}</p>
                        </div>
                    </div>
                </div>`;
        }

        Swal.fire({
            title: title,
            html: content,
            confirmButtonText: 'Close System View',
            confirmButtonColor: '#1e293b',
            width: '600px'
        });
    }

    function executeDelete(type, id) {
        Swal.fire({
            title: `Delete ${type}?`,
            text: `This will completely delete this ${type.toLowerCase()} record (#${id}) from database clusters. This action is completely irreversible.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#be123c',
            confirmButtonText: `Delete ${type}`
        });
    }

    function executeSuspend(id, user) {
        Swal.fire({
            title: 'Suspend User Account?',
            text: `Terminating session tokens and blacklisting authentication keys for user profile ${user} (ID: #${id}).`,
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#be123c',
            confirmButtonText: 'Suspend Account'
        });
    }
</script>
@endpush
@endsection