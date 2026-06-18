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
                        await this.fetchData(new FormData(e.target));
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
                        form.querySelector('[name=joined_date]').value = '';
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
                    async fetchData(formData) {
                        const url = new URL('{{ route('users') }}');
                        for (const [key, value] of formData.entries()) {
                            if (value && value !== 'all') {
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
                        data-users-filter-form @submit.prevent="submitForm">
                        <label class="admin-users-search">
                            <x-fas-search class="admin-users-search-icon" aria-hidden="true" />
                            <span class="sr-only">Search users</span>
                            <input type="search" name="search" value="{{ $userFilters['search'] }}"
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
                                            @click="role = 'all'; document.getElementById('adminUsersRoleButton').click();">All roles</button>
                                    </li>
                                    <li>
                                        <button type="button" class="account-menu-link" :class="role === 'admin' ? 'active' : ''"
                                            @click="role = 'admin'; document.getElementById('adminUsersRoleButton').click();">Admin</button>
                                    </li>
                                    <li>
                                        <button type="button" class="account-menu-link" :class="role === 'user' ? 'active' : ''"
                                            @click="role = 'user'; document.getElementById('adminUsersRoleButton').click();">User</button>
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
                                            @click="status = 'all'; document.getElementById('adminUsersStatusButton').click();">All status</button>
                                    </li>
                                    <li>
                                        <button type="button" class="account-menu-link" :class="status === 'safe' ? 'active' : ''"
                                            @click="status = 'safe'; document.getElementById('adminUsersStatusButton').click();">Safe</button>
                                    </li>
                                    <li>
                                        <button type="button" class="account-menu-link" :class="status === 'warning' ? 'active' : ''"
                                            @click="status = 'warning'; document.getElementById('adminUsersStatusButton').click();">Warning</button>
                                    </li>
                                    <li>
                                        <button type="button" class="account-menu-link" :class="status === 'restricted' ? 'active' : ''"
                                            @click="status = 'restricted'; document.getElementById('adminUsersStatusButton').click();">Restricted</button>
                                    </li>
                                </ul>
                            </div>
                            <input type="hidden" name="status" x-model="status" data-users-status-filter>
                        </div>

                        <label class="admin-users-date">
                            <span>Joined date</span>
                            <input type="date" name="joined_date" value="{{ $userFilters['joined_date'] }}"
                                placeholder="mm/dd/yyyy" data-users-joined-date>
                        </label>

                        <div class="admin-users-filter-actions">
                            <button type="submit" class="admin-users-primary-button save-btn" :class="{ 'is-loading': isFiltering }" :disabled="isFiltering || isClearing">
                                <span class="button-label">Apply</span>
                                <span class="button-loader" aria-hidden="true">
                                    <i></i><i></i><i></i>
                                </span>
                            </button>
                            <button type="button" class="admin-users-secondary-button save-btn" @click="clearForm" :class="{ 'is-loading': isClearing }" :disabled="isFiltering || isClearing">
                                <span class="button-label">Clear</span>
                                <span class="button-loader" aria-hidden="true">
                                    <i></i><i></i><i></i>
                                </span>
                            </button>
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
                                onclick="if(!event.target.closest('.admin-users-action-btn') && !event.target.closest('a')) window.location='{{ route('profile-detail', $listedUser->name) }}'">
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
                                        <span>
                                            <span class="admin-users-name">{{ $listedUser->name }}</span>
                                            <span class="admin-users-sub">
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
                                </td>
                                <td data-label="Report">
                                    <span class="admin-users-report-count">{{ $listedUser->report_count }}</span>
                                </td>
                                <td data-label="Joined On">{{ $listedUser->created_at?->format('M d, Y') }}</td>
                                <td data-label="Actions">
                                    <div class="admin-users-action-group">
                                        @if ($listedUser->trashed())
                                        <form method="POST" action="{{ route('users.restore', $listedUser->id) }}"
                                            @submit="confirmAction($event, 'Restore {{ addslashes($listedUser->name) }} account?', 'Yes, restore')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="admin-users-action-btn restore-action"
                                                @disabled($isSelf) title="Restore user" aria-label="Restore {{ $listedUser->name }}">
                                                <x-fas-rotate-left aria-hidden="true" />
                                            </button>
                                        </form>
                                        @else
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
                                            @submit="confirmAction($event, 'Restrict {{ addslashes($listedUser->name) }} account?', 'Yes, restrict')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-users-action-btn delete-action"
                                                @disabled($isSelf) title="Restrict user"
                                                aria-label="Restrict {{ $listedUser->name }}">
                                                <x-fas-trash aria-hidden="true" />
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