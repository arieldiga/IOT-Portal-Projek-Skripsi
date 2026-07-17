{{-- Quick Actions --}}
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 mb-3">
                <button class="btn btn-outline-primary w-100 py-3"
                        data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="fas fa-plus-circle d-block mb-2"></i>
                    Add New User
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Daftar User --}}
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center">
            <i class="fas fa-users text-primary me-2"></i>
            <h5 class="mb-0">Daftar User Terdaftar</h5>
        </div>
        <div class="text-muted small">
            <i class="fas fa-info-circle me-1"></i>
            Total: {{ $users->count() }} users
        </div>
    </div>

    <div class="card-body table-responsive">
        <table class="table table-striped table-hover align-middle user-table">
            <thead class="table-primary">
                <tr>
                    <th>Username (Display)</th>
                    <th>Customer (API)</th>
                    <th>ID</th>
                    <th>Role</th>
                    <th>Dibuat Pada</th>
                    <th class="text-center" width="150">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td data-label="Username (Display)">
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm me-2">
                                    <span class="badge bg-{{ $user->role === 'superadmin' ? 'danger' : 'success' }} rounded-circle p-2">
                                        {{ strtoupper(substr($user->display_name ?? $user->username, 0, 2)) }}
                                    </span>
                                </div>
                                <div>
                                    <strong>{{ $user->display_name ?? $user->username }}</strong>
                                </div>
                            </div>
                        </td>
                        <td data-label="Customer (API)">
                            <span class="badge bg-secondary">
                                <i class="fas fa-building me-1"></i>{{ $user->username }}
                            </span>
                        </td>
                        <td data-label="ID">
                            <code class="text-primary">{{ $user->name }}</code>
                        </td>
                        <td data-label="Role">
                            <span class="badge {{ $user->role === 'superadmin' ? 'bg-danger' : 'bg-success' }}">
                                {{ $user->role === 'superadmin' ? 'Super Admin' : 'Read & Export' }}
                            </span>
                        </td>
                        <td data-label="Dibuat Pada">
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                {{ $user->created_at->format('d M Y H:i') }}
                            </small>
                        </td>
                        <td class="text-center" data-label="Actions">
                            @if($user->role === 'read_export')
                                <button class="btn btn-outline-info btn-sm me-1"
                                        onclick="openCustomizeModal({{ $user->id }}, '{{ addslashes($user->display_name ?? $user->username) }}')"
                                        title="Customize Columns">
                                    <i class="fas fa-cog"></i>
                                </button>
                            @endif

                            @if($user->id !== Auth::user()->id)
                                <button class="btn btn-outline-danger btn-sm"
                                        onclick="openDeleteModal('{{ $user->id }}', '{{ addslashes($user->display_name ?? $user->username) }}', '{{ $user->role }}')"
                                        title="Delete User">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            @else
                                <span class="badge bg-secondary small">
                                    <i class="fas fa-user-check me-1"></i>Current User
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-users fa-3x mb-3 d-block"></i>
                                <h6>No users found</h6>
                                <small>Click "Add New User" to create the first user</small>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->count() > 0)
    <div class="card-footer bg-light">
        <div class="row">
            <div class="col-sm-6">
                <small class="text-muted">
                    <i class="fas fa-chart-pie me-1"></i>
                    <strong>Statistics:</strong>
                    Superadmins: {{ $users->where('role', 'superadmin')->count() }} |
                    Read & Export: {{ $users->where('role', 'read_export')->count() }}
                </small>
            </div>
            <div class="col-sm-6 text-end">
                <small class="text-muted">
                    <i class="fas fa-clock me-1"></i>
                    Last updated: {{ now()->format('d M Y H:i') }}
                </small>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- Responsive: tabel jadi "card list" di layar kecil, tetap tabel normal di desktop --}}
<style>
.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.user-table {
    min-width: 1000px;
    white-space: nowrap;
}

.user-table th,
.user-table td {
    vertical-align: middle;
}
</style>