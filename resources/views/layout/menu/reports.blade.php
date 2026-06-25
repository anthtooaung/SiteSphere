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
        <section class="reports-shell" data-admin-reports-page
            x-data="{
                activeTab: '{{ $activeTab }}',
                isFiltering: false,
                isClearing: false,
                isLoading: false,
                status: '{{ $reportFilters['status'] }}',
                reportSummary: @js($reportSummary),
                async submitForm(e) {
                    this.isFiltering = true;
                    this.isLoading = true;
                    await this.fetchData(new FormData(e.target));
                    this.isFiltering = false;
                },
                async clearForm() {
                    this.isClearing = true;
                    this.isLoading = true;
                    this.status = 'all';
                    const form = document.querySelector('[data-report-filter-form]');
                    form.reset();
                    form.querySelector('[name=search]').value = '';
                    form.querySelector('[name=reported_date]').value = '';
                    await this.$nextTick();
                    await this.fetchData(new FormData(form));
                    this.isClearing = false;
                },
                async fetchData(formData) {
                    const url = new URL('{{ route('reports') }}');
                    for (const [key, value] of formData.entries()) {
                        if (value) {
                            url.searchParams.set(key, value);
                        }
                    }
                    try {
                        const response = await fetch(url.toString(), {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        const html = await response.text();
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        
                        ['posts', 'comments', 'users', 'user_posts'].forEach(tab => {
                            const tPanel = document.querySelector(`#${tab}-view`);
                            const nPanel = doc.querySelector(`#${tab}-view`);
                            if (tPanel && nPanel) {
                                const tbody = tPanel.querySelector('.reports-real-body');
                                const ntbody = nPanel.querySelector('.reports-real-body');
                                if (tbody && ntbody) tbody.innerHTML = ntbody.innerHTML;
                                
                                const tPag = tPanel.querySelector('.reports-pagination-row');
                                const nPag = nPanel.querySelector('.reports-pagination-row');
                                if (tPag && nPag) tPag.innerHTML = nPag.innerHTML;
                            }
                            
                            const tCount = document.querySelector(`[data-count-${tab}]`);
                            const nCount = doc.querySelector(`[data-count-${tab}]`);
                            if (tCount && nCount) tCount.innerHTML = nCount.innerHTML;
                        });

                        const nData = doc.querySelector('#report-summary-data');
                        if (nData) this.reportSummary = JSON.parse(nData.textContent);
                    } catch (error) {
                        console.error(error);
                    } finally {
                        this.isLoading = false;
                    }
                },
                async confirmAction(e, title, text, confirmButtonText, icon = 'warning') {
                    e.preventDefault();
                    const result = await window.sitesphereSwal.confirm({
                        title: title,
                        text: text,
                        icon: icon,
                        confirmButtonText: confirmButtonText
                    });
                    if (result.isConfirmed) {
                        e.target.submit();
                    }
                }
            }">
            <script id="report-summary-data" type="application/json">{!! json_encode($reportSummary) !!}</script>
            <header class="reports-header">
                <div>
                    <p class="dashboard-kicker">Database Administration Core</p>
                    <h1 id="reportsTitle">System Report Log Console</h1>
                    <p>Live post report queue mapped to the existing reports and notification tables.</p>
                </div>
            </header>

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
                    <button type="button" class="reports-tab" id="user_posts-tab" role="tab"
                        :class="{ 'active': activeTab === 'user_posts' }"
                        :aria-selected="activeTab === 'user_posts'"
                        @click="activeTab = 'user_posts'"
                        data-report-tab="user_posts">
                        DESCRIPTION
                    </button>
                </div>
            </section>

            <section class="reports-summary" aria-label="Report summary">
                <article class="reports-summary-card">
                    <span>Total Reports</span>
                    <strong x-text="reportSummary[activeTab]?.total ?? 0">{{ $reportSummary[$activeTab]['total'] ?? 0 }}</strong>
                </article>
                <article class="reports-summary-card unread">
                    <span>Unread</span>
                    <strong x-text="reportSummary[activeTab]?.unread ?? 0">{{ $reportSummary[$activeTab]['unread'] ?? 0 }}</strong>
                </article>
                <article class="reports-summary-card read">
                    <span>Read</span>
                    <strong x-text="reportSummary[activeTab]?.read ?? 0">{{ $reportSummary[$activeTab]['read'] ?? 0 }}</strong>
                </article>
            </section>

            <div class="reports-actions-card">
                <div class="reports-table-actions">
                    <div class="reports-title-wrapper">
                        <h2 x-show="activeTab === 'posts'">Post Audit Queue</h2>
                        <h2 x-show="activeTab === 'comments'" x-cloak>Comment Audit Queue</h2>
                        <h2 x-show="activeTab === 'users'" x-cloak>User Account Flags</h2>
                        <h2 x-show="activeTab === 'user_posts'" x-cloak>Description Flags</h2>

                        <p x-show="activeTab === 'posts'" data-count-posts>
                            Showing {{ $reports->firstItem() ?? 0 }}-{{ $reports->lastItem() ?? 0 }} of {{ $reports->total() }} reports
                        </p>
                        <p x-show="activeTab === 'comments'" data-count-comments x-cloak>
                            Showing {{ $commentReports->firstItem() ?? 0 }}-{{ $commentReports->lastItem() ?? 0 }} of {{ $commentReports->total() }} reports
                        </p>
                        <p x-show="activeTab === 'users'" data-count-users x-cloak>
                            Showing {{ $userReports->firstItem() ?? 0 }}-{{ $userReports->lastItem() ?? 0 }} of {{ $userReports->total() }} reports
                        </p>
                        <p x-show="activeTab === 'user_posts'" data-count-user_posts x-cloak>
                            Showing {{ $userPostReports->firstItem() ?? 0 }}-{{ $userPostReports->lastItem() ?? 0 }} of {{ $userPostReports->total() }} reports
                        </p>
                    </div>
                </div>

                <div class="reports-filter-container">
                    <form method="GET" action="{{ route('reports') }}" class="reports-controls"
                        data-report-filter-form @submit.prevent="submitForm">
                        <label class="reports-search">
                            <x-fas-search class="reports-search-icon" aria-hidden="true" />
                            <span class="sr-only">Search reports</span>
                            <input type="search" name="search" value="{{ $reportFilters['search'] }}"
                                placeholder="Search post, reporter, reason..." data-report-search class="focus:outline-none outline-none focus:ring-0">
                        </label>

                        <div class="reports-control-wrapper relative">
                            <span class="sr-only">Filter by account status</span>
                            <button type="button" class="reports-select justify-between w-full" id="reportsStatusButton"
                                data-dropdown-toggle="reportsStatusDropdown" data-dropdown-placement="bottom-start"
                                aria-expanded="false" style="min-width: 140px; outline: none !important; cursor: pointer;">
                                <span class="reports-control-label truncate" x-text="status === 'all' ? 'All Reports' : (status === 'unread' ? 'Unread' : 'Read ')">
                                    {{ $reportFilters['status'] === 'all' ? 'All Reports' : ($reportFilters['status'] === 'unread' ? 'Unread ' : 'Read ') }}
                                </span>
                                <x-fas-chevron-down class="reports-search-icon ml-2" aria-hidden="true" />
                            </button>

                            <div id="reportsStatusDropdown" class="account-menu-dropdown hidden"
                                aria-labelledby="reportsStatusButton">
                                <ul class="account-menu-list">
                                    <li>
                                        <button type="button" class="account-menu-link" :class="status === 'all' ? 'active' : ''"
                                            @click="status = 'all'; document.getElementById('reportsStatusButton').click();">All Reports</button>
                                    </li>
                                    <li>
                                        <button type="button" class="account-menu-link" :class="status === 'unread' ? 'active' : ''"
                                            @click="status = 'unread'; document.getElementById('reportsStatusButton').click();">Unread </button>
                                    </li>
                                    <li>
                                        <button type="button" class="account-menu-link" :class="status === 'read' ? 'active' : ''"
                                            @click="status = 'read'; document.getElementById('reportsStatusButton').click();">Read </button>
                                    </li>
                                </ul>
                            </div>
                            <input type="hidden" name="status" x-model="status" data-report-status-filter>
                        </div>

                        <label class="reports-date">
                            <span>Reported date</span>
                            <input type="date" name="reported_date" value="{{ $reportFilters['reported_date'] }}"
                                placeholder="mm/dd/yyyy" data-report-date-filter>
                        </label>

                        <div class="reports-filter-actions">
                            <button type="submit" class="reports-primary-button save-btn" :class="{ 'is-loading': isFiltering }" :disabled="isFiltering || isClearing">
                                <span class="button-label">Apply</span>
                                <span class="button-loader" aria-hidden="true">
                                    <i></i><i></i><i></i>
                                </span>
                            </button>
                            <button type="button" class="reports-secondary-button save-btn" @click="clearForm" :class="{ 'is-loading': isClearing }" :disabled="isFiltering || isClearing">
                                <span class="button-label">Clear</span>
                                <span class="button-loader" aria-hidden="true">
                                    <i></i><i></i><i></i>
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
<section class="reports-table-card" id="posts-view" role="tabpanel" aria-labelledby="posts-tab"
                data-report-posts-panel x-show="activeTab === 'posts'">
                <div class="reports-table-wrap">
                    <table class="reports-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Post Content</th>
                                <th>Reason</th>
                                <th>Reported By</th>
                                <th>Reported Date</th>
                                <th class="reports-actions-heading">Actions</th>
                            </tr>
                        </thead>
                        <tbody x-show="isLoading" x-cloak>
                            @for($i = 0; $i < 5; $i++)
                                <tr>
                                <td colspan="6">
                                    <div class="reports-skeleton-bar"></div>
                                </td>
                                </tr>
                                @endfor
                        </tbody>
                        <tbody class="reports-real-body" x-show="!isLoading">
                            @forelse ($reports as $report)
                            @php
                            $isUnread = ! $report->admin_read;
                            $isHighlighted = $highlightPostId > 0 && $report->target_id === $highlightPostId;
                            @endphp
                            <tr @class([ 'reports-row' , 'unread-state'=> $isUnread,
                                'read-state' => ! $isUnread,
                                'is-highlighted' => $isHighlighted,
                                ]) data-report-row data-report-id="{{ $report->id }}"
                                onclick="if (!event.target.closest('a, button, form')) window.location.href='{{ route('reports.open', $report) }}';" style="cursor: pointer;">
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
                                        @if ($report->post?->trashed())
                                            <span class="reports-banned-badge">Banned</span>
                                        @elseif ($report->post?->is_unsecure)
                                            <span style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; background: color-mix(in srgb, #d97706 15%, transparent); color: #d97706;">Unsecure</span>
                                        @endif
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
                                        @if ($report->post?->slug)
                                        <a href="{{ route('reports.open', $report) }}" class="reports-icon-btn view-action" aria-label="View Post">
                                            <x-fas-eye aria-hidden="true" /></a>
                                        @else
                                        <button type="button" class="reports-icon-btn view-action" disabled aria-label="Post unavailable" style="opacity:0.4;cursor:not-allowed;">
                                            <x-fas-eye aria-hidden="true" /> 
                                        </button>
                                        @endif

                                        @if ($isUnread)
                                        <form method="POST" action="{{ route('reports.read', $report) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="reports-icon-btn read-action" aria-label="Mark Read" style="color: var(--ui-success, #28a745);">
                                                <x-fas-check aria-hidden="true" />
                                            </button>
                                        </form>
                                        @else
                                        <form method="POST" action="{{ route('reports.unread', $report) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="reports-icon-btn read-done-action" aria-label="Mark Unread">
                                                <x-fas-check-double aria-hidden="true" /> 
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="reports-empty-row">
                                    <div class="reports-empty-state">
                                        <div class="reports-empty-icon">
                                            <x-fas-flag aria-hidden="true" />
                                        </div>
                                        <h3>No post reports found</h3>
                                        <p>Try adjusting your filters or check back later.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="reports-pagination-row">
                    <span>Page {{ $reports->currentPage() }} of {{ max(1, $reports->lastPage()) }}</span>
                    <div style="display: flex; gap: 8px;">
                        @if ($reports->onFirstPage())
                        <button disabled class="reports-secondary-button" style="opacity: 0.5; cursor: not-allowed; padding: 0 12px; min-height: 28px;">Previous</button>
                        @else
                        <a href="{{ $reports->previousPageUrl() }}" class="reports-secondary-button" style="padding: 0 12px; min-height: 28px; text-decoration: none;">Previous</a>
                        @endif

                        @if ($reports->hasMorePages())
                        <a href="{{ $reports->nextPageUrl() }}" class="reports-secondary-button" style="padding: 0 12px; min-height: 28px; text-decoration: none;">Next</a>
                        @else
                        <button disabled class="reports-secondary-button" style="opacity: 0.5; cursor: not-allowed; padding: 0 12px; min-height: 28px;">Next</button>
                        @endif
                    </div>
                </div>
            </section>

            <section class="reports-table-card" id="comments-view" role="tabpanel" aria-label="Comment reports" aria-labelledby="comments-tab"
                x-show="activeTab === 'comments'" x-cloak>
                <div class="reports-table-wrap">
                    <table class="reports-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Comment Snippet</th>
                                <th>Reason</th>
                                <th>Reported By</th>
                                <th>Reported Date</th>
                                <th class="reports-actions-heading">Actions</th>
                            </tr>
                        </thead>
                        <tbody x-show="isLoading" x-cloak>
                            @for($i = 0; $i < 5; $i++)
                                <tr>
                                <td colspan="6">
                                    <div class="reports-skeleton-bar"></div>
                                </td>
                                </tr>
                                @endfor
                        </tbody>
                        <tbody class="reports-real-body" x-show="!isLoading">
                            @forelse ($commentReports as $report)
                            @php
                            $isUnread = ! $report->admin_read;
                            @endphp
                            <tr @class([ 'reports-row' , 'unread-state'=> $isUnread, 'read-state' => ! $isUnread ]) data-report-row data-report-id="{{ $report->id }}"
                                onclick="if (!event.target.closest('a, button, form')) window.location.href='{{ route('reports.open', $report) }}';" style="cursor: pointer;">
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
                                        @if ($report->comment?->trashed())
                                            <span class="reports-banned-badge">Banned</span>
                                        @endif
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
                                        @if ($report->comment?->post?->slug)
                                        <a href="{{ route('reports.open', $report) }}" class="reports-icon-btn view-action" aria-label="View Comment">
                                            <x-fas-eye aria-hidden="true" /> 
                                        </a>
                                        @else
                                        <button type="button" class="reports-icon-btn view-action" disabled aria-label="Comment unavailable" style="opacity:0.4;cursor:not-allowed;">
                                            <x-fas-eye aria-hidden="true" /> 
                                        </button>
                                        @endif

                                        @if ($isUnread)
                                        <form method="POST" action="{{ route('reports.read', $report) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="reports-icon-btn read-action" aria-label="Mark Read" style="color: var(--ui-success, #28a745);">
                                                <x-fas-check aria-hidden="true" />
                                            </button>
                                        </form>
                                        @else
                                        <form method="POST" action="{{ route('reports.unread', $report) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="reports-icon-btn read-done-action" aria-label="Mark Unread">
                                                <x-fas-check-double aria-hidden="true" /> 
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="reports-empty-row">
                                    <div class="reports-empty-state">
                                        <div class="reports-empty-icon">
                                            <x-fas-comment-slash aria-hidden="true" />
                                        </div>
                                        <h3>No comment reports found</h3>
                                        <p>Try adjusting your filters or check back later.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="reports-pagination-row">
                    <span>Page {{ $commentReports->currentPage() }} of {{ max(1, $commentReports->lastPage()) }}</span>
                    <div style="display: flex; gap: 8px;">
                        @if ($commentReports->onFirstPage())
                        <button disabled class="reports-secondary-button" style="opacity: 0.5; cursor: not-allowed; padding: 0 12px; min-height: 28px;">Previous</button>
                        @else
                        <a href="{{ $commentReports->previousPageUrl() }}" class="reports-secondary-button" style="padding: 0 12px; min-height: 28px; text-decoration: none;">Previous</a>
                        @endif

                        @if ($commentReports->hasMorePages())
                        <a href="{{ $commentReports->nextPageUrl() }}" class="reports-secondary-button" style="padding: 0 12px; min-height: 28px; text-decoration: none;">Next</a>
                        @else
                        <button disabled class="reports-secondary-button" style="opacity: 0.5; cursor: not-allowed; padding: 0 12px; min-height: 28px;">Next</button>
                        @endif
                    </div>
                </div>
            </section>

            <section class="reports-table-card" id="users-view" role="tabpanel" aria-label="User reports" aria-labelledby="users-tab"
                x-show="activeTab === 'users'" x-cloak>
                <div class="reports-table-wrap">
                    <table class="reports-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>User Identity</th>
                                <th>Reason</th>
                                <th>Reported By</th>
                                <th>Reported Date</th>
                                <th class="reports-actions-heading">Actions</th>
                            </tr>
                        </thead>
                        <tbody x-show="isLoading" x-cloak>
                            @for($i = 0; $i < 5; $i++)
                                <tr>
                                <td colspan="6">
                                    <div class="reports-skeleton-bar"></div>
                                </td>
                                </tr>
                                @endfor
                        </tbody>
                        <tbody class="reports-real-body" x-show="!isLoading">
                            @forelse ($userReports as $report)
                            @php
                            $isUnread = ! $report->admin_read;
                            $targetUser = $report->targetUser;
                            $initials = $targetUser ? \Illuminate\Support\Str::of($targetUser->name)->explode(' ')->map(fn ($part) => \Illuminate\Support\Str::substr($part, 0, 1))->join('') ?: '?' : '??';
                            @endphp
                            <tr @class([ 'reports-row' , 'unread-state'=> $isUnread, 'read-state' => ! $isUnread ]) data-report-row data-report-id="{{ $report->id }}"
                                onclick="if (!event.target.closest('a, button, form')) window.location.href='{{ route('reports.open', $report) }}';" style="cursor: pointer;">
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
                                    <div class="reports-user-identity">
                                        @if ($targetUser && $targetUser->user_image)
                                        <img src="{{ $targetUser->getAvatarUrl() }}" alt="{{ $targetUser->name }}" class="reports-user-avatar" style="object-fit: cover;">
                                        @else
                                        <div class="reports-user-avatar">
                                            {{ $initials }}
                                        </div>
                                        @endif
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
                                        @if ($targetUser?->slug)
                                        <a href="{{ route('reports.open', $report) }}" class="reports-icon-btn view-action" aria-label="View Profile">
                                            <x-fas-eye aria-hidden="true" /> 
                                        </a>
                                        @else
                                        <button type="button" class="reports-icon-btn view-action" disabled aria-label="User unavailable" style="opacity:0.4;cursor:not-allowed;">
                                            <x-fas-eye aria-hidden="true" /> 
                                        </button>
                                        @endif

                                        @if ($isUnread)
                                        <form method="POST" action="{{ route('reports.read', $report) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="reports-icon-btn read-action" aria-label="Mark Read" style="color: var(--ui-success, #28a745);">
                                                <x-fas-check aria-hidden="true" /></button>
                                        </form>
                                        @else
                                        <form method="POST" action="{{ route('reports.unread', $report) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="reports-icon-btn read-done-action" aria-label="Mark Unread">
                                                <x-fas-check-double aria-hidden="true" />
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="reports-empty-row">
                                    <div class="reports-empty-state">
                                        <div class="reports-empty-icon">
                                            <x-fas-user-slash aria-hidden="true" />
                                        </div>
                                        <h3>No user reports found</h3>
                                        <p>Try adjusting your filters or check back later.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="reports-pagination-row">
                    <span>Page {{ $userReports->currentPage() }} of {{ max(1, $userReports->lastPage()) }}</span>
                    <div style="display: flex; gap: 8px;">
                        @if ($userReports->onFirstPage())
                        <button disabled class="reports-secondary-button" style="opacity: 0.5; cursor: not-allowed; padding: 0 12px; min-height: 28px;">Previous</button>
                        @else
                        <a href="{{ $userReports->previousPageUrl() }}" class="reports-secondary-button" style="padding: 0 12px; min-height: 28px; text-decoration: none;">Previous</a>
                        @endif

                        @if ($userReports->hasMorePages())
                        <a href="{{ $userReports->nextPageUrl() }}" class="reports-secondary-button" style="padding: 0 12px; min-height: 28px; text-decoration: none;">Next</a>
                        @else
                        <button disabled class="reports-secondary-button" style="opacity: 0.5; cursor: not-allowed; padding: 0 12px; min-height: 28px;">Next</button>
                        @endif
                    </div>
                </div>
            </section>

            <section class="reports-table-card" id="user_posts-view" role="tabpanel" aria-label="Description reports" aria-labelledby="user_posts-tab"
                x-show="activeTab === 'user_posts'" x-cloak>
                <div class="reports-table-wrap">
                    <table class="reports-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Description Snippet</th>
                                <th>Reason</th>
                                <th>Reported By</th>
                                <th>Reported Date</th>
                                <th class="reports-actions-heading">Actions</th>
                            </tr>
                        </thead>
                        <tbody x-show="isLoading" x-cloak>
                            @for($i = 0; $i < 5; $i++)
                                <tr>
                                <td colspan="6">
                                    <div class="reports-skeleton-bar"></div>
                                </td>
                                </tr>
                                @endfor
                        </tbody>
                        <tbody class="reports-real-body" x-show="!isLoading">
                            @forelse ($userPostReports as $report)
                            @php
                            $isUnread = ! $report->admin_read;
                            @endphp
                            <tr @class([ 'reports-row' , 'unread-state'=> $isUnread, 'read-state' => ! $isUnread ]) data-report-row data-report-id="{{ $report->id }}"
                                onclick="if (!event.target.closest('a, button, form')) window.location.href='{{ route('reports.open', $report) }}';" style="cursor: pointer;">
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
                                <td data-label="Description Snippet">
                                    <span class="reports-post-title">
                                        "{{ Str::limit($report->userPost?->description ?? 'Deleted or unavailable description', 60) }}"
                                        @if ($report->userPost?->trashed())
                                            <span class="reports-banned-badge">Banned</span>
                                        @endif
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
                                        @if ($report->userPost?->user?->name)
                                        <a href="{{ route('reports.open', $report) }}" class="reports-icon-btn view-action" aria-label="View Description">
                                            <x-fas-eye aria-hidden="true" /> 
                                        </a>
                                        @else
                                        <button type="button" class="reports-icon-btn view-action" disabled aria-label="Description unavailable" style="opacity:0.4;cursor:not-allowed;">
                                            <x-fas-eye aria-hidden="true" /> 
                                        </button>
                                        @endif

                                        @if ($isUnread)
                                        <form method="POST" action="{{ route('reports.read', $report) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="reports-icon-btn read-action" aria-label="Mark Read" style="color: var(--ui-success, #28a745);">
                                                <x-fas-check aria-hidden="true" />
                                            </button>
                                        </form>
                                        @else
                                        <form method="POST" action="{{ route('reports.unread', $report) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="reports-icon-btn read-done-action" aria-label="Mark Unread">
                                                <x-fas-check-double aria-hidden="true" />
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="reports-empty-row">
                                    <div class="reports-empty-state">
                                        <div class="reports-empty-icon">
                                            <x-fas-flag aria-hidden="true" />
                                        </div>
                                        <h3>No description reports found</h3>
                                        <p>Try adjusting your filters or check back later.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="reports-pagination-row">
                    <span>Page {{ $userPostReports->currentPage() }} of {{ max(1, $userPostReports->lastPage()) }}</span>
                    <div style="display: flex; gap: 8px;">
                        @if ($userPostReports->onFirstPage())
                        <button disabled class="reports-secondary-button" style="opacity: 0.5; cursor: not-allowed; padding: 0 12px; min-height: 28px;">Previous</button>
                        @else
                        <a href="{{ $userPostReports->previousPageUrl() }}" class="reports-secondary-button" style="padding: 0 12px; min-height: 28px; text-decoration: none;">Previous</a>
                        @endif

                        @if ($userPostReports->hasMorePages())
                        <a href="{{ $userPostReports->nextPageUrl() }}" class="reports-secondary-button" style="padding: 0 12px; min-height: 28px; text-decoration: none;">Next</a>
                        @else
                        <button disabled class="reports-secondary-button" style="opacity: 0.5; cursor: not-allowed; padding: 0 12px; min-height: 28px;">Next</button>
                        @endif
                    </div>
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
                    <div style="background-color: var(--ui-surface); padding: 16px; border-radius: 6px; border: 1px solid var(--ui-border);">
                        <h4 style="font-weight: bold; color: var(--text-color); margin-bottom: 4px;">Title: ${initials}</h4>
                        <p style="color: color-mix(in srgb, var(--text-color) 70%, transparent); margin: 0; line-height: 1.6;">${detailsText}</p>
                    </div>
                </div>`;
        } else if (type === 'comment') {
            title = `Comment Audit: #${id}`;
            content = `
                <div style="text-align: left; font-size: 0.875rem; font-family: sans-serif; line-height: 1.5;">
                    <div style="border-left: 4px solid var(--ui-border-strong); padding-left: 16px; padding-top: 8px; padding-bottom: 8px; font-style: italic; color: color-mix(in srgb, var(--text-color) 80%, transparent);">
                        "${detailsText}"
                    </div>
                </div>`;
        } else if (type === 'profile') {
            title = `Account Profile View: #${id}`;
            content = `
                <div style="text-align: left; font-size: 0.875rem; font-family: sans-serif; line-height: 1.5;">
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <div style="height: 64px; width: 64px; background-color: var(--ui-border); border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: bold; color: color-mix(in srgb, var(--text-color) 80%, transparent);">${initials}</div>
                        <div>
                            <h4 style="font-size: 1.125rem; font-weight: bold; margin: 0;">${detailsText}</h4>
                            <p style="color: color-mix(in srgb, var(--text-color) 55%, transparent); margin: 4px 0 0;">Member since: ${dateStr}</p>
                        </div>
                    </div>
                </div>`;
        }

        window.sitesphereSwal.fire({
            title: title,
            html: content,
            confirmButtonText: 'Close System View',
            width: '600px'
        });
    }
</script>
@endpush
@endsection