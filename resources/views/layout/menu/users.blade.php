@extends('dashboard')

@section('title')
Users
@endsection

@section('content')
@php
$dashboardMenuLocation = in_array($menuBarLocation ?? 'left', ['top', 'right', 'bottom', 'left'], true)
? $menuBarLocation
: 'left';
$userFilters = $userFilters ?? [
'search' => '',
'role' => 'all',
'status' => 'all',
'joined_date' => '',
];

$statusFor = function ($user): string {
if ($user->trashed() || $user->report_count >= 3) {
return 'restricted';
}

return $user->report_count > 0 ? 'warning' : 'safe';
};
@endphp

<x-layout.nav />

<div class="dashboard-page dashboard-page--{{ $dashboardMenuLocation }} admin-users-page">
    <x-layout.menu :menu-bar-location="$dashboardMenuLocation" />

    <main class="dashboard-content admin-users-content" aria-labelledby="adminUsersTitle">
        <section class="admin-users-shell" data-users-page
            x-data="{
                    isFiltering: false,
                    isClearing: false,
                    isLoading: false,
                    role: '{{ $userFilters['role'] }}',
                    status: '{{ $userFilters['status'] }}',
                    async submitForm(e) {
                        this.isFiltering = true;
                        this.isLoading = true;
                        const form = this.$refs.filterForm || document.querySelector('[data-users-filter-form]');
                        await this.fetchData(new FormData(form));
                        this.isFiltering = false;
                    },
                    async clearForm() {
                        this.isClearing = true;
                        this.isLoading = true;
                        this.role = 'all';
                        this.status = 'all';
                        const form = document.querySelector('[data-users-filter-form]');
                        form.reset();
                        form.querySelector('[name=search]').value = '';
                        await this.$nextTick();
                        await this.fetchData(new FormData(form));
                        this.isClearing = false;
                    },
                    async confirmAction(event, message, confirmText) {
                        event.preventDefault();
                        const form = event.target;
                        const result = await window.sitesphereSwal.confirm({
                            title: 'Are you sure?',
                            text: message,
                            icon: 'warning',
                            confirmButtonText: confirmText
                        });
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    },
                    async confirmBan(event, userName) {
                        event.preventDefault();
                        const form = event.target;
                        const result = await window.sitesphereSwal.confirm({
                            title: 'Ban this user?',
                            text: `Ban ${userName}'s account? This will soft-delete their account.`,
                            icon: 'warning',
                            input: 'text',
                            inputPlaceholder: 'Enter reason for banning...',
                            confirmButtonColor: 'var(--ui-danger)',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Yes, ban',
                            inputValidator: (value) => {
                                if (!value) {
                                    return 'You need to provide a reason!'
                                }
                            }
                        });
                        if (result.isConfirmed) {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'reason';
                            input.value = result.value;
                            form.appendChild(input);
                            form.submit();
                        }
                    },
                    async fetchData(formData) {
                        const url = new URL('{{ route('users') }}');
                        for (const [key, value] of formData.entries()) {
                            if (value && value !== 'all') {
                                url.searchParams.set(key, value);
                            }
                        }
                        window.history.replaceState({}, '', url);
                        try {
                            const response = await fetch(url.toString(), {
                                headers: { 'X-Requested-With': 'XMLHttpRequest' }
                            });
                            const html = await response.text();
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            
                            document.querySelector('.users-real-body').innerHTML = doc.querySelector('.users-real-body').innerHTML;
                            if(doc.querySelector('.admin-users-pagination-row')) {
                                document.querySelector('.admin-users-pagination-row').innerHTML = doc.querySelector('.admin-users-pagination-row').innerHTML;
                            }
                            if(doc.querySelector('[data-users-table-count]')) {
                                document.querySelector('[data-users-table-count]').innerHTML = doc.querySelector('[data-users-table-count]').innerHTML;
                            }
                        } catch (error) {
                            console.error(error);
                        } finally {
                            this.isLoading = false;
                        }
                    }
                }">
            <header class="admin-users-header">
                <div>
                    <p class="dashboard-kicker">Admin Panel</p>
                    <h1 id="adminUsersTitle">User Management Directory</h1>
                    <p>Monitor platform registrations, assign privileges, and review account safety flags. Use the
                        multi-filter system below to quickly find specific groups or individuals.</p>
                </div>
            </header>

            <section class="admin-users-summary" aria-label="User summary metrics">
                <article class="admin-users-summary-card">
                    <span class="admin-users-summary-icon total" aria-hidden="true">
                        <x-fas-users />
                    </span>
                    <div>
                        <p>Total Users</p>
                        <strong>{{ $userSummary['total'] }}</strong>
                    </div>
                </article>
                <article class="admin-users-summary-card">
                    <span class="admin-users-summary-icon safe" aria-hidden="true">
                        <x-fas-shield-halved />
                    </span>
                    <div>
                        <p>Safe Accounts</p>
                        <strong>{{ $userSummary['safe'] }}</strong>
                    </div>
                </article>
                <article class="admin-users-summary-card">
                    <span class="admin-users-summary-icon warning" aria-hidden="true">
                        <x-fas-triangle-exclamation />
                    </span>
                    <div>
                        <p>Warning</p>
                        <strong>{{ $userSummary['warning'] }}</strong>
                    </div>
                </article>
                <article class="admin-users-summary-card">
                    <span class="admin-users-summary-icon restricted" aria-hidden="true">
                        <x-fas-ban />
                    </span>
                    <div>
                        <p>Restricted</p>
                        <strong>{{ $userSummary['restricted'] }}</strong>
                    </div>
                </article>
            </section>

            <div class="admin-users-actions-card">
                <div class="admin-users-table-actions">
                    <div class="admin-users-title-wrapper">
                        <h2>All Users</h2>
                        <p data-users-table-count>
                            Showing {{ $users->firstItem() ?? 0 }}-{{ $users->lastItem() ?? 0 }} of {{ $users->total() }} users
                        </p>
                    </div>
                </div>

                <div class="admin-users-filter-container">
                    <form method="GET" action="{{ route('users') }}" class="admin-users-controls"
                        data-users-filter-form x-ref="filterForm" @submit.prevent="submitForm">
                        <label class="admin-users-search">
                            <x-fas-search class="admin-users-search-icon" aria-hidden="true" />
                            <span class="sr-only">Search users</span>
                            <input type="search" name="search" value="{{ $userFilters['search'] }}"
                                @input.debounce.500ms="submitForm()"
                                placeholder="Search name, email, phone" data-users-search>
                        </label>

                        <div class="admin-users-control-wrapper relative">
                            <span class="sr-only">Filter by role</span>
                            <button type="button" class="admin-users-select justify-between w-full" id="adminUsersRoleButton"
                                data-dropdown-toggle="adminUsersRoleDropdown" data-dropdown-placement="bottom-start"
                                aria-expanded="false" style="min-width: 140px; outline: none !important; cursor: pointer;">
                                <span class="admin-users-control-label truncate" x-text="role === 'all' ? 'All roles' : role.charAt(0).toUpperCase() + role.slice(1)">
                                    {{ $userFilters['role'] === 'all' ? 'All roles' : ucfirst($userFilters['role']) }}
                                </span>
                                <x-fas-chevron-down class="admin-users-search-icon ml-2" aria-hidden="true" />
                            </button>

                            <div id="adminUsersRoleDropdown" class="account-menu-dropdown hidden"
                                aria-labelledby="adminUsersRoleButton">
                                <ul class="account-menu-list">
                                    <li>
                                        <button type="button" class="account-menu-link" :class="role === 'all' ? 'active' : ''"
                                            @click="role = 'all'; document.getElementById('adminUsersRoleButton').click(); $nextTick(() => submitForm());">All roles</button>
                                    </li>
                                    <li>
                                        <button type="button" class="account-menu-link" :class="role === 'admin' ? 'active' : ''"
                                            @click="role = 'admin'; document.getElementById('adminUsersRoleButton').click(); $nextTick(() => submitForm());">Admin</button>
                                    </li>
                                    <li>
                                        <button type="button" class="account-menu-link" :class="role === 'user' ? 'active' : ''"
                                            @click="role = 'user'; document.getElementById('adminUsersRoleButton').click(); $nextTick(() => submitForm());">User</button>
                                    </li>
                                </ul>
                            </div>
                            <input type="hidden" name="role" x-model="role" data-users-role-filter>
                        </div>

                        <div class="admin-users-control-wrapper relative">
                            <span class="sr-only">Filter by account status</span>
                            <button type="button" class="admin-users-select justify-between w-full" id="adminUsersStatusButton"
                                data-dropdown-toggle="adminUsersStatusDropdown" data-dropdown-placement="bottom-start"
                                aria-expanded="false" style="min-width: 140px; outline: none !important; cursor: pointer;">
                                <span class="admin-users-control-label truncate" x-text="status === 'all' ? 'All status' : status.charAt(0).toUpperCase() + status.slice(1)">
                                    {{ $userFilters['status'] === 'all' ? 'All status' : ucfirst($userFilters['status']) }}
                                </span>
                                <x-fas-chevron-down class="admin-users-search-icon ml-2" aria-hidden="true" />
                            </button>

                            <div id="adminUsersStatusDropdown" class="account-menu-dropdown hidden"
                                aria-labelledby="adminUsersStatusButton">
                                <ul class="account-menu-list">
                                    <li>
                                        <button type="button" class="account-menu-link" :class="status === 'all' ? 'active' : ''"
                                            @click="status = 'all'; document.getElementById('adminUsersStatusButton').click(); $nextTick(() => submitForm());">All status</button>
                                    </li>
                                    <li>
                                        <button type="button" class="account-menu-link" :class="status === 'safe' ? 'active' : ''"
                                            @click="status = 'safe'; document.getElementById('adminUsersStatusButton').click(); $nextTick(() => submitForm());">Safe</button>
                                    </li>
                                    <li>
                                        <button type="button" class="account-menu-link" :class="status === 'warning' ? 'active' : ''"
                                            @click="status = 'warning'; document.getElementById('adminUsersStatusButton').click(); $nextTick(() => submitForm());">Warning</button>
                                    </li>
                                    <li>
                                        <button type="button" class="account-menu-link" :class="status === 'restricted' ? 'active' : ''"
                                            @click="status = 'restricted'; document.getElementById('adminUsersStatusButton').click(); $nextTick(() => submitForm());">Restricted</button>
                                    </li>
                                </ul>
                            </div>
                            <input type="hidden" name="status" x-model="status" data-users-status-filter>
                        </div>

                        <div class="admin-users-control-wrapper relative">
                            <button type="button" class="admin-users-select justify-between w-full" id="usersDateBtn"
                                data-dropdown-toggle="usersDateDropdown" data-dropdown-placement="bottom-start"
                                aria-expanded="false" style="min-width: 140px; outline: none !important; cursor: pointer;">
                                <x-fas-calendar class="admin-users-search-icon" aria-hidden="true" />
                                <span class="admin-users-control-label truncate" id="usersDateLabel">{{ $userFilters['joined_date'] ? \Carbon\Carbon::parse($userFilters['joined_date'])->format('M Y') : 'All dates' }}</span>
                                <x-fas-chevron-down class="admin-users-search-icon ml-2" aria-hidden="true" />
                            </button>
                            <div id="usersDateDropdown" class="account-menu-dropdown hidden saved-post-date-dropdown"
                                aria-labelledby="usersDateBtn">
                                <div class="saved-post-date-picker">
                                    <div class="saved-post-date-year-row">
                                        <button type="button" class="saved-post-date-nav" id="usersPrevYear">&#9664;</button>
                                        <span class="saved-post-date-year-label" id="usersYearLabel">{{ now()->year }}</span>
                                        <button type="button" class="saved-post-date-nav" id="usersNextYear">&#9654;</button>
                                    </div>
                                    <div class="saved-post-date-month-grid" id="usersMonthGrid"></div>
                                    <button type="button" class="saved-post-date-clear" id="usersDateClear">All dates</button>
                                </div>
                            </div>
                            <input type="hidden" name="joined_date" value="{{ $userFilters['joined_date'] }}" data-users-joined-date>
                        </div>
                    </form>
                </div>
            </div>

            <section class="admin-users-table-card">

                <div class="admin-users-table-wrap">
                    <table class="admin-users-table">
                        <thead>
                            <tr>
                                <th>Username & Profile</th>
                                <th>Email</th>
                                <th>Phone Number</th>
                                <th>Role</th>
                                <th>Account Status</th>
                                <th>Report</th>
                                <th>Joined On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody x-show="isLoading" x-cloak>
                            @for($i = 0; $i < 5; $i++)
                                <tr>
                                <td colspan="8">
                                    <div class="admin-users-skeleton-bar"></div>
                                </td>
                                </tr>
                                @endfor
                        </tbody>
                        <tbody class="users-real-body" x-show="!isLoading">
                            @forelse ($users as $listedUser)
                            @php
                            $status = $statusFor($listedUser);
                            $isSelf = auth()->id() === $listedUser->id;
                            @endphp
                            <tr @class(['is-restricted'=> $listedUser->trashed()])
                                style="cursor: pointer;"
                                onclick="if(!event.target.closest('.admin-users-action-btn') && !event.target.closest('a')) window.location='{{ route('profile-detail', $listedUser->slug) }}'">
                                <td data-label="User">
                                    <span class="admin-users-profile-link" aria-label="View details coming soon">
                                        @if ($listedUser->user_image)
                                        <img src="{{ $listedUser->getAvatarUrl() }}" alt="{{ $listedUser->name }}"
                                            class="admin-users-avatar">
                                        @else
                                        <span class="admin-users-avatar">
                                            {{ \Illuminate\Support\Str::of($listedUser->name)->explode(' ')->map(fn ($part) => \Illuminate\Support\Str::substr($part, 0, 1))->join('') ?: '?' }}
                                        </span>
                                        @endif
                                        <span class="min-w-0" style="max-width: 150px;">
                                            <span class="admin-users-name truncate" title="{{ $listedUser->name }}">{{ $listedUser->name }}</span>
                                            <span class="admin-users-sub truncate" title="{{ $listedUser->trashed() ? 'Restricted account' : 'Active account' }}">
                                                {{ $listedUser->trashed() ? 'Restricted account' : 'Active account' }}
                                            </span>
                                        </span>
                                    </span>
                                </td>
                                <td data-label="Email">
                                    <a class="admin-users-contact-link"
                                        href="mailto:{{ $listedUser->email }}">{{ $listedUser->email }}</a>
                                </td>
                                <td data-label="Phone">
                                    @if ($listedUser->user_phone)
                                    <a class="admin-users-contact-link"
                                        href="tel:{{ preg_replace('/\s+/', '', $listedUser->user_phone) }}">+95{{ $listedUser->user_phone }}</a>
                                    @else
                                    <span class="admin-users-muted">Not provided</span>
                                    @endif
                                </td>
                                <td data-label="Role">
                                    <span @class(['admin-users-role-pill', 'admin'=> $listedUser->role === 'admin'])>
                                        {{ ucfirst($listedUser->role) }}
                                    </span>
                                </td>
                                <td data-label="Account Status" style="text-align: center;">
                                    <span class="admin-users-status {{ $status }}">
                                        {{ ucfirst($status) }}
                                    </span>
                                    @if ($listedUser->status === 'unsecure' && $status !== 'warning')
                                        <span style="display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 600; background: color-mix(in srgb, #d97706 15%, transparent); color: #d97706; margin-top: 2px;">Unsecure</span>
                                    @endif
                                </td>
                                <td data-label="Report">
                                    <span class="admin-users-report-count">{{ $listedUser->report_count }}</span>
                                </td>
                                <td data-label="Joined On">{{ $listedUser->created_at?->format('M d, Y') }}</td>
                                <td data-label="Actions">
                                    <div class="admin-users-action-group">
                                        @if ($listedUser->trashed() && !$listedUser->is_permanently_banned)
                                        <form method="POST" action="{{ route('users.restore', $listedUser->id) }}"
                                            @submit="confirmAction($event, 'Restore {{ addslashes($listedUser->name) }} account?', 'Yes, restore')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="admin-users-action-btn restore-action"
                                                @disabled($isSelf) title="Restore user" aria-label="Restore {{ $listedUser->name }}">
                                                <x-fas-rotate-left aria-hidden="true" />
                                            </button>
                                        </form>
                                        @elseif ($listedUser->is_permanently_banned)
                                        <span class="admin-users-status restricted" style="font-size: 0.7rem; padding: 2px 6px;">Perm. Banned</span>
                                        @else
                                        <form method="POST" action="{{ route('users.toggle-unsecure', $listedUser->id) }}"
                                            @submit="confirmAction($event, '{{ $listedUser->isUnsecure() ? 'Mark ' . addslashes($listedUser->name) . ' as secure?' : 'Mark ' . addslashes($listedUser->name) . ' as unsecure?' }}', '{{ $listedUser->isUnsecure() ? 'Yes, mark secure' : 'Yes, mark unsecure' }}')">
                                            @csrf
                                            <button type="submit"
                                                class="admin-users-action-btn {{ $listedUser->isUnsecure() ? 'restore-action' : 'delete-action' }}"
                                                @disabled($isSelf) title="{{ $listedUser->isUnsecure() ? 'Mark secure' : 'Mark unsecure' }}"
                                                aria-label="{{ $listedUser->isUnsecure() ? 'Mark ' . $listedUser->name . ' secure' : 'Mark ' . $listedUser->name . ' unsecure' }}">
                                                <x-fas-shield-halved aria-hidden="true" />
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('users.role', $listedUser) }}"
                                            @submit="confirmAction($event, 'Change {{ addslashes($listedUser->name) }} role?', 'Yes, change')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="admin-users-action-btn role-action {{ $listedUser->role === 'admin' ? 'to-user' : 'to-admin' }}"
                                                @disabled($isSelf) title="Change role"
                                                aria-label="Change {{ $listedUser->name }} role">
                                                <x-fas-user-shield aria-hidden="true" />
                                            </button>
                                        </form>
                                        @endif

                                        @unless ($listedUser->trashed())
                                        <form method="POST" action="{{ route('users.destroy', $listedUser) }}"
                                            @submit="confirmBan($event, '{{ addslashes($listedUser->name) }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-users-action-btn delete-action"
                                                @disabled($isSelf) title="Ban user"
                                                aria-label="Ban {{ $listedUser->name }}">
                                                <x-fas-ban aria-hidden="true" />
                                            </button>
                                        </form>
                                        @endunless
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="admin-users-empty-row" style="padding: 60px 20px;">
                                    <div class="saved-post-empty" style="min-height: auto; margin: 0 auto; border: none; background: transparent; box-shadow: none;">
                                        <div class="saved-post-empty-icon">
                                            <x-fas-users aria-hidden="true" />
                                        </div>
                                        <h2>No users found</h2>
                                        <p>Try another name, email, phone number, or date.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="admin-users-pagination-row">
                    <span>Page {{ $users->currentPage() }} of {{ max(1, $users->lastPage()) }}</span>
                    <div style="display: flex; gap: 8px;">
                        @if ($users->onFirstPage())
                            <button disabled class="admin-users-secondary-button" style="opacity: 0.5; cursor: not-allowed; padding: 0 12px; min-height: 28px;">Previous</button>
                        @else
                            <a href="{{ $users->previousPageUrl() }}" class="admin-users-secondary-button" style="padding: 0 12px; min-height: 28px; text-decoration: none;">Previous</a>
                        @endif

                        @if ($users->hasMorePages())
                            <a href="{{ $users->nextPageUrl() }}" class="admin-users-secondary-button" style="padding: 0 12px; min-height: 28px; text-decoration: none;">Next</a>
                        @else
                            <button disabled class="admin-users-secondary-button" style="opacity: 0.5; cursor: not-allowed; padding: 0 12px; min-height: 28px;">Next</button>
                        @endif
                    </div>
                </div>
            </section>
        </section>
    </main>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    function initUsersDatePicker() {
        const yearLabel = document.getElementById('usersYearLabel');
        const monthGrid = document.getElementById('usersMonthGrid');
        const prevYearBtn = document.getElementById('usersPrevYear');
        const nextYearBtn = document.getElementById('usersNextYear');
        const dateLabel = document.getElementById('usersDateLabel');
        const dateClearBtn = document.getElementById('usersDateClear');
        const joinedDateInput = document.querySelector('[data-users-joined-date]');

        if (!yearLabel || !monthGrid) return;

        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        let currentYear = parseInt(yearLabel.textContent) || new Date().getFullYear();

        function renderMonths() {
            yearLabel.textContent = currentYear;
            monthGrid.innerHTML = '';
            months.forEach((month, idx) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'saved-post-date-month-btn';
                btn.textContent = month;
                btn.dataset.month = idx;

                if (joinedDateInput.value) {
                    const selectedDate = new Date(joinedDateInput.value);
                    if (selectedDate.getFullYear() === currentYear && selectedDate.getMonth() === idx) {
                        btn.classList.add('active');
                    }
                }

                btn.addEventListener('click', () => {
                    const formatDate = (date) => {
                        const year = date.getFullYear();
                        const month = String(date.getMonth() + 1).padStart(2, '0');
                        const day = String(date.getDate()).padStart(2, '0');
                        return `${year}-${month}-${day}`;
                    };
                    const selectedDate = new Date(currentYear, idx, 1);
                    joinedDateInput.value = formatDate(selectedDate);
                    dateLabel.textContent = `${months[idx]} ${currentYear}`;

                    const dropdown = document.getElementById('usersDateDropdown');
                    if (dropdown) dropdown.classList.add('hidden');

                    const form = joinedDateInput.closest('form');
                    if (form) {
                        if (typeof form.requestSubmit === 'function') {
                            form.requestSubmit();
                        } else {
                            form.dispatchEvent(new Event('submit', { cancelable: true }));
                        }
                    }
                });

                monthGrid.appendChild(btn);
            });
        }

        prevYearBtn.addEventListener('click', () => {
            currentYear--;
            renderMonths();
        });

        nextYearBtn.addEventListener('click', () => {
            currentYear++;
            renderMonths();
        });

        dateClearBtn.addEventListener('click', () => {
            joinedDateInput.value = '';
            dateLabel.textContent = 'All dates';

            const dropdown = document.getElementById('usersDateDropdown');
            if (dropdown) dropdown.classList.add('hidden');

            const form = joinedDateInput.closest('form');
            if (form) {
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    form.dispatchEvent(new Event('submit', { cancelable: true }));
                }
            }
        });

        renderMonths();
    }

    initUsersDatePicker();
});
</script>
@endpush