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
            <section class="admin-users-shell" data-users-page>
                <header class="admin-users-header">
                    <div>
                        <p class="dashboard-kicker">Admin Panel</p>
                        <h1 id="adminUsersTitle">User Accounts</h1>
                        <p>
                            Review user profiles, contact details, roles, join dates, and account safety status.
                        </p>
                    </div>
                </header>

                <section class="admin-users-summary" aria-label="User summary">
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
                            <p>Safe</p>
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

                <section class="admin-users-table-card">
                    <div class="admin-users-table-actions">
                        <div>
                            <h2>All Users</h2>
                            <p data-users-table-count>
                                Showing {{ $users->firstItem() ?? 0 }}-{{ $users->lastItem() ?? 0 }} of {{ $users->total() }} users
                            </p>
                        </div>

                        <form method="GET" action="{{ route('users') }}" class="admin-users-controls"
                            data-users-filter-form>
                            <label class="admin-users-search">
                                <x-fas-search class="admin-users-search-icon" aria-hidden="true" />
                                <span class="sr-only">Search users</span>
                                <input type="search" name="search" value="{{ $userFilters['search'] }}"
                                    placeholder="Search name, email, phone" data-users-search>
                            </label>

                            <label class="admin-users-select">
                                <span class="sr-only">Filter by role</span>
                                <select name="role" data-users-role-filter>
                                    <option value="all" @selected($userFilters['role'] === 'all')>All roles</option>
                                    <option value="admin" @selected($userFilters['role'] === 'admin')>Admin</option>
                                    <option value="user" @selected($userFilters['role'] === 'user')>User</option>
                                </select>
                            </label>

                            <label class="admin-users-select">
                                <span class="sr-only">Filter by account status</span>
                                <select name="status" data-users-status-filter>
                                    <option value="all" @selected($userFilters['status'] === 'all')>All status</option>
                                    <option value="safe" @selected($userFilters['status'] === 'safe')>Safe</option>
                                    <option value="warning" @selected($userFilters['status'] === 'warning')>Warning</option>
                                    <option value="restricted" @selected($userFilters['status'] === 'restricted')>Restricted</option>
                                </select>
                            </label>

                            <label class="admin-users-date">
                                <span>Joined date</span>
                                <input type="date" name="joined_date" value="{{ $userFilters['joined_date'] }}"
                                    data-users-joined-date>
                            </label>

                            <div class="admin-users-filter-actions">
                                <button type="submit" class="admin-users-primary-button">Apply</button>
                                <a href="{{ route('users') }}" class="admin-users-secondary-button">Clear</a>
                            </div>
                        </form>
                    </div>

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
                            <tbody>
                                @forelse ($users as $listedUser)
                                    @php
                                        $status = $statusFor($listedUser);
                                        $isSelf = auth()->id() === $listedUser->id;
                                    @endphp
                                    <tr @class(['is-restricted' => $listedUser->trashed()])>
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
                                            <span @class(['admin-users-role-pill', 'admin' => $listedUser->role === 'admin'])>
                                                {{ ucfirst($listedUser->role) }}
                                            </span>
                                        </td>
                                        <td data-label="Account Status">
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
                                                        onsubmit="return confirm('Restore {{ $listedUser->name }} account?')">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="admin-users-action-btn restore-action"
                                                            @disabled($isSelf) title="Restore user" aria-label="Restore {{ $listedUser->name }}">
                                                            <x-fas-rotate-left aria-hidden="true" />
                                                        </button>
                                                    </form>
                                                @else
                                                    <form method="POST" action="{{ route('users.role', $listedUser) }}"
                                                        onsubmit="return confirm('Change {{ $listedUser->name }} role?')">
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

                                                <button type="button" class="admin-users-action-btn view-action" disabled
                                                    title="User details coming soon" aria-label="User details coming soon">
                                                    <x-fas-eye aria-hidden="true" />
                                                </button>

                                                @unless ($listedUser->trashed())
                                                    <form method="POST" action="{{ route('users.destroy', $listedUser) }}"
                                                        onsubmit="return confirm('Restrict {{ $listedUser->name }} account?')">
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
                                        <td colspan="8" class="admin-users-empty-row">
                                            No users found. Try another name, email, phone number, or date.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="admin-users-pagination-row">
                        <span>Page {{ $users->currentPage() }} of {{ $users->lastPage() }}</span>
                        {{ $users->links() }}
                    </div>
                </section>
            </section>
        </main>
    </div>
@endsection
