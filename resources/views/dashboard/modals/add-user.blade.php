<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('users.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">
                    <i class="fas fa-user-plus me-2"></i>Add User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- Tampilkan error validation jika ada --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Field Customer (Autoread from API) --}}
                <div class="mb-3">
                    <label for="customerField" class="form-label">
                        <i class="fas fa-building me-1"></i>Customer
                    </label>
                    <input type="text" name="username" id="customerField" class="form-control bg-light" 
                           readonly required value="{{ old('username') }}">
                </div>

                {{-- Field ID - API --}}
                <div class="mb-3">
                    <label for="apiIdSelect" class="form-label">
                        <i class="fas fa-key me-1"></i>ID
                    </label>
                    <select name="name" id="apiIdSelect" class="form-select" required>
                        <option value="">Pilih ID</option>
                    </select>
                </div>

                {{-- NEW: Field Display Name (Manual Input) --}}
                <div class="mb-3">
                    <label for="displayNameField" class="form-label">
                        <i class="fas fa-user me-1"></i>Username (Display)
                        <small class="text-danger">*</small>
                    </label>
                    <input type="text" name="display_name" id="displayNameField" class="form-control" 
                           placeholder="Nama yang akan muncul di dashboard" 
                           required value="{{ old('display_name') }}">
                </div>

                {{-- Field Password --}}
                <div class="mb-3">
                    <label for="passwordField" class="form-label">
                        <i class="fas fa-lock me-1"></i>Password
                    </label>
                    <input type="password" name="password" id="passwordField" class="form-control" 
                           placeholder="Minimal 3 karakter" required>
                </div>

                {{-- Field Role --}}
                <div class="mb-3">
                    <label for="roleSelect" class="form-label">
                        <i class="fas fa-user-shield me-1"></i>Role
                    </label>
                    <select name="role" id="roleSelect" class="form-select" required>
                        <option value="superadmin" {{ old('role') == 'superadmin' ? 'selected' : '' }}>Super Admin</option>
                        <option value="read_export" {{ old('role') == 'read_export' ? 'selected' : '' }}>Read & Export(Vendor)</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Add User
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addUserModal = document.getElementById('addUserModal');
    const apiIdSelect = document.getElementById('apiIdSelect');
    const customerField = document.getElementById('customerField');
    const displayNameField = document.getElementById('displayNameField');
    let apiUsersLoaded = false;

    if (addUserModal) {
        // Load API users HANYA sekali (bukan tiap modal dibuka)
        addUserModal.addEventListener('show.bs.modal', function() {
            if (apiUsersLoaded) return;

            fetch('{{ route("api.users") }}')
                .then(response => response.json())
                .then(data => {
                    apiIdSelect.innerHTML = '<option value="">Pilih ID</option>';
                    data.forEach(user => {
                        apiIdSelect.innerHTML += `<option value="${user.id}" data-username="${user.username}">${user.id} - ${user.username}</option>`;
                    });
                    apiUsersLoaded = true;
                })
                .catch(error => {
                    console.error('Error loading API users:', error);
                    showToast('Gagal memuat daftar API users', 'danger');
                });
        });

        // Auto-fill customer field saat API ID berubah
        apiIdSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const username = selectedOption.getAttribute('data-username') || '';
            customerField.value = username;

            if (displayNameField.value === '') {
                displayNameField.value = username;
            }
        });

        // Reset FORM saja saat modal ditutup, JANGAN reset dropdown API
        addUserModal.addEventListener('hidden.bs.modal', function() {
            document.querySelector('#addUserModal form').reset();
            // dropdown apiIdSelect TIDAK di-reset, biar nggak fetch ulang tiap buka modal
        });
    }
});
</script>