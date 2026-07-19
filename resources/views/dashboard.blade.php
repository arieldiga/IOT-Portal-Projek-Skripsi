@extends('layouts.app')

@section('title', 'Dashboard - Lautan Air Indonesia')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="#"><i class="fas fa-home me-1"></i>Home</a></li>
<li class="breadcrumb-item active">Dashboard</li>
@endsection

@push('styles')
<style>
/* Dashboard specific styles */
.user-info {
    background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
    color: white;
    padding: 24px;
    border-radius: 20px;
    margin-top: 24px;
    box-shadow: var(--shadow-medium);
    position: relative;
    overflow: hidden;
}

.user-info::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 100%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
    transform: rotate(45deg);
    animation: shimmer 3s infinite;
}

@keyframes shimmer {
    0% { transform: translateX(-100%) rotate(45deg); }
    100% { transform: translateX(200%) rotate(45deg); }
}

.company-name {
    font-size: 1.5rem;
    font-weight: 600;
    text-align: center;
    position: relative;
    z-index: 2;
}

/* Customer Dashboard specific styles */
.customer-welcome {
    background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
    color: white;
    padding: 32px;
    border-radius: 20px;
    box-shadow: var(--shadow-medium);
    position: relative;
    overflow: hidden;
    margin-bottom: 24px;
}

.customer-welcome::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 100%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
    transform: rotate(45deg);
    animation: shimmer 3s infinite;
}

.customer-welcome h1 {
    font-size: 2.2rem;
    font-weight: 700;
    margin-bottom: 8px;
    position: relative;
    z-index: 2;
}

.customer-welcome p {
    font-size: 1.1rem;
    margin-bottom: 0;
    position: relative;
    z-index: 2;
    opacity: 0.9;
}

.btn-outline-primary,
.btn-outline-success,
.btn-outline-danger,
.btn-outline-info {
    background: white;
    border: 2px solid transparent;
    border-radius: 16px;
    padding: 24px;
    transition: all 0.3s ease;
    box-shadow: var(--shadow-light);
    position: relative;
    overflow: hidden;
}

.btn-outline-primary { border-color: var(--primary-blue); color: var(--primary-blue); }
.btn-outline-success { border-color: var(--success-green); color: var(--success-green); }
.btn-outline-danger { border-color: var(--danger-red); color: var(--danger-red); }
.btn-outline-info { border-color: #17a2b8; color: #17a2b8; }

.btn-outline-primary:hover,
.btn-outline-success:hover,
.btn-outline-danger:hover,
.btn-outline-info:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-medium);
    border-color: var(--accent-cyan);
    color: var(--primary-blue);
}

.chart-container {
    background: white;
    border-radius: 20px;
    padding: 32px;
    box-shadow: var(--shadow-medium);
    margin: 32px 0;
    border: 1px solid var(--border-color);
}

.table-responsive {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: var(--shadow-medium);
    border: 1px solid var(--border-color);
}

.pagination-wrapper {
    background: var(--glass-bg);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 24px;
    margin-top: 24px;
    box-shadow: var(--shadow-medium);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.ajax-loading {
    display: none;
    text-align: center;
    padding: 20px;
}

.ajax-loading .spinner-border {
    color: var(--primary-blue);
}
</style>
@endpush

@section('content')
<!-- Welcome Card untuk Superadmin -->
@if(Auth::user()->role === 'superadmin')
<div class="welcome-card mb-4">
    <h1 class="welcome-title">Selamat Datang, {{ Auth::user()->display_name ?? Auth::user()->username }}!</h1>
    <p class="text-muted">Role: <span class="badge bg-primary">{{ ucfirst(Auth::user()->role) }}</span></p>
    <div class="user-info">
        <p class="company-name mb-0">
            <i class="fas fa-building me-2"></i> PT Lautan Air Indonesia
        </p>
    </div>
</div>
@endif

<!-- Quick Actions (Only for Superadmin) -->
@if(Auth::user()->role === 'superadmin')
<div class="welcome-card">
    <h3 class="text-primary mb-3"><i class="fas fa-bolt me-2"></i> Quick Actions</h3>
    <div class="row">
        @include('dashboard.partials.admin-actions')
    </div>
</div>

{{-- DELETE USER MODAL --}}
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteUserModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirm User Deletion
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-warning border-0 bg-warning bg-opacity-10">
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                    <strong>Peringatan!</strong> Tindakan ini tidak dapat dibatalkan.
                </div>
                
                <div class="text-center mb-4">
                    <i class="fas fa-user-times text-danger fa-3x mb-3"></i>
                    <p class="mb-2">Apakah Anda yakin ingin menghapus user:</p>
                    <h5 class="text-primary mb-1" id="deleteUserName">-</h5>
                    <span id="deleteUserRole" class="badge bg-secondary">-</span>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Batal
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn" onclick="executeDeleteNow()">
                    <i class="fas fa-trash-alt me-1"></i>Ya, Hapus User
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- CUSTOMIZE COLUMNS MODAL --}}
<div class="modal fade" id="customizeColumnsModal" tabindex="-1" aria-labelledby="customizeColumnsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="customizeColumnsModalLabel">
                    <i class="fas fa-cog me-2"></i>Customize Sensor Columns
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info border-0 bg-info bg-opacity-10">
                    <i class="fas fa-info-circle text-info me-2"></i>
                    Atur nama custom dan visibilitas kolom sensor untuk user: <strong id="customizeUserName">-</strong>
                </div>
                
                <div id="columnsConfigContainer">
                    <div class="text-center py-4">
                        <div class="spinner-border text-info" role="status"></div>
                        <p class="mt-2 text-muted">Loading columns...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Tutup
                </button>
                <button type="button" class="btn btn-info" onclick="saveColumnConfigs()">
                    <i class="fas fa-save me-1"></i>Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Customer Dashboard Section (Only for read_export) -->
@if(Auth::user()->role === 'read_export')
<div class="welcome-card">
    <!-- Customer Welcome di dalam Customer Dashboard -->
    <div class="customer-welcome">
        <h1><i class="fas fa-wave-square me-2"></i>Selamat Datang, {{ Auth::user()->display_name ?? Auth::user()->username }}!</h1>
        <p><i class="fas fa-desktop me-3"></i>Pantau dan kelola data sensor Anda dengan mudah melalui grafik interaktif dan tabel detail.</p>
    </div>
    
    <h3 class="text-primary mb-3"><i class="fas fa-chart-line me-2"></i> Sensor Data Overview</h3>
    
    @include('dashboard.partials.sensor-filters')
    @include('dashboard.partials.sensor-chart')
    @include('dashboard.partials.sensor-table')
</div>
@endif

<!-- Modals -->
@if(Auth::user()->role === 'superadmin')
    @include('dashboard.modals.add-user')
@endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Add User Modal
    const addUserModal = document.getElementById('addUserModal');
    const apiIdSelect = document.getElementById('apiIdSelect');
    const customerField = document.getElementById('customerField');
    const toggleManualBtn = document.getElementById('toggleManualBtn');
    let manualMode = false;

    if (addUserModal) {
        // Load API users saat modal dibuka
        addUserModal.addEventListener('show.bs.modal', function() {
            fetch('{{ route("api.users") }}')
                .then(response => response.json())
                .then(data => {
                    apiIdSelect.innerHTML = '<option value="">Pilih ID</option>';
                    data.forEach(user => {
                        apiIdSelect.innerHTML += `<option value="${user.id}" data-username="${user.username}">${user.id} - ${user.username}</option>`;
                    });
                })
                .catch(error => {
                    console.error('Error loading API users:', error);
                });
        });

        // Update customer field saat API ID berubah (jika bukan manual mode)
        apiIdSelect.addEventListener('change', function() {
            if (!manualMode) {
                const selectedOption = this.options[this.selectedIndex];
                customerField.value = selectedOption.getAttribute('data-username') || '';
            }
        });

        // Toggle manual input
        toggleManualBtn.addEventListener('click', function() {
            manualMode = !manualMode;
            if (manualMode) {
                customerField.removeAttribute('readonly');
                customerField.focus();
                toggleManualBtn.textContent = "Auto Mode";
                toggleManualBtn.classList.remove('btn-outline-secondary');
                toggleManualBtn.classList.add('btn-outline-primary');
            } else {
                customerField.setAttribute('readonly', true);
                const selectedOption = apiIdSelect.options[apiIdSelect.selectedIndex];
                customerField.value = selectedOption ? (selectedOption.getAttribute('data-username') || '') : '';
                toggleManualBtn.textContent = "Manual Input";
                toggleManualBtn.classList.remove('btn-outline-primary');
                toggleManualBtn.classList.add('btn-outline-secondary');
            }
        });
    }

    // Initialize dashboard based on role
    @if(Auth::user()->role === 'read_export')
        initializeSensorDashboard();
    @endif
    
    @if(Auth::user()->role === 'superadmin')
        initializeAdminFunctions();
    @endif
});

@if(Auth::user()->role === 'read_export')
// ========================================
// SENSOR DASHBOARD FUNCTIONS
// ========================================
function initializeSensorDashboard() {
    loadParameterOptions();
    setupEventListeners();
}

let sensorChart = null;
let currentFilters = {
    parameter: null,
    from: null,
    to: null
};

let isDataLoaded = false;

function loadParameterOptions() {
    fetch('/api/sensor-data/initial', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.availableColumns) {
            populateParameterOptions(data.availableColumns);
        }
    })
    .catch(error => {
        console.error('Error loading parameters:', error);
    });
}

function setupEventListeners() {
    document.getElementById('filterForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const fromDate = document.getElementById('fromDate').value;
        const toDate = document.getElementById('toDate').value;
        
        if (!fromDate || !toDate) {
            Swal.fire({
                icon: 'error',
                title: 'Oops, Ada Kesalahan!',
                text: 'Harap pilih rentang tanggal terlebih dahulu',
                confirmButtonColor: '#dc3545'
            });
            return;
        }
        
        if (new Date(fromDate) > new Date(toDate)) {
            Swal.fire({
                icon: 'error',
                title: 'Tanggal Tidak Valid!',
                text: 'Tanggal awal tidak boleh lebih besar dari tanggal akhir',
                confirmButtonColor: '#dc3545'
            });
            return;
        }

        // ✅ FIX: Validasi maksimal 30 hari (hitung inklusif)
        const msPerDay = 24 * 60 * 60 * 1000;
        const diffDaysCheck = Math.round((new Date(toDate) - new Date(fromDate)) / msPerDay) + 1;

        if (diffDaysCheck > 30) {
            Swal.fire({
                icon: 'error',
                title: 'Rentang Terlalu Panjang!',
                text: 'Maksimal rentang tanggal adalah 30 hari',
                confirmButtonColor: '#dc3545'
            });
            return;
        }
        
        applyFilters();
    });
    
    document.getElementById('paramSelect').addEventListener('change', function() {
        if (sensorChart && isDataLoaded) {
            updateChartParameter(this.value);
        }
    });
    
    const toggleBtn = document.getElementById('toggleTableBtn');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', toggleTable);
    }
}

function applyFilters() {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    
    currentFilters = {
        parameter: formData.get('parameter'),
        from: formData.get('from'),
        to: formData.get('to')
    };

    // ✅ FIX: Validasi rentang maksimal 30 hari (hitung inklusif)
    const start = new Date(currentFilters.from);
    const end   = new Date(currentFilters.to);
    const diffDays = Math.round((end - start) / (1000 * 60 * 60 * 24)) + 1;

    if (diffDays > 30) {
        Swal.fire({
            icon: 'warning',
            title: 'Rentang Terlalu Panjang!',
            text: 'Maksimal rentang tanggal adalah 30 hari'
        });
        return; // 
    }

    showLoading('.chart-container');

    const placeholder = document.getElementById('chartPlaceholder');
    if (placeholder) {
        placeholder.style.display = 'none';
    }

    // ✅ Lanjut fetch setelah lolos validasi tanggal
    fetch('/api/sensor-data/filtered', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.chartData && data.chartData.length > 0) {
            isDataLoaded = true;
            updateChart(data.chartData, data.availableColumns);

            const toggleBtn = document.getElementById('toggleTableBtn');
            if (toggleBtn) {
                toggleBtn.style.display = 'inline-block';
            }

            showToast('Data berhasil dimuat!', 'success');
        } else {
            isDataLoaded = false;
            showNoDataMessage();
            showToast('Tidak ada data pada rentang tanggal yang dipilih', 'warning');
        }
    })
    .catch(error => {
        console.error('Error applying filters:', error);
        isDataLoaded = false;
    })
    .finally(() => {
        hideLoading('.chart-container');
    });
}

function updateChart(chartData, availableColumns) {
    const ctx = document.getElementById('sensorChart');
    if (!ctx) return;
    
    ctx.style.display = 'block';
    
    if (sensorChart) {
        sensorChart.destroy();
    }
    
    if (!chartData || chartData.length === 0) {
        showNoDataMessage();
        return;
    }
    
    const currentParam = currentFilters.parameter || Object.keys(availableColumns)[0];
    const color = getParameterColor(currentParam);
    
    sensorChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.map(d => formatDateTime(d.datetime)),
            datasets: [{
                label: availableColumns[currentParam] || currentParam,
                data: chartData.map(d => d[currentParam] || 0),
                borderColor: color,
                backgroundColor: color + '20',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: { family: 'Inter', size: 14, weight: '600' }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: '#333',
                    borderWidth: 1,
                    cornerRadius: 12
                }
            },
            scales: {
                x: { 
                    title: { display: true, text: 'Waktu' },
                    grid: { color: 'rgba(0, 0, 0, 0.1)' }
                },
                y: { 
                    title: { display: true, text: 'Nilai' }, 
                    beginAtZero: true,
                    grid: { color: 'rgba(0, 0, 0, 0.1)' }
                }
            },
            animation: { duration: 1000, easing: 'easeInOutQuart' }
        }
    });
}

function loadTableData(page = 1) {
    if (!currentFilters.from || !currentFilters.to) {
        Swal.fire({
            icon: 'warning',
            title: 'Filter Belum Diatur!',
            text: 'Silakan pilih rentang tanggal terlebih dahulu',
            confirmButtonColor: '#ff9800'
        });
        return;
    }
    
    showLoading('#sensorTable');
    
    const params = new URLSearchParams({
        page: page,
        ...currentFilters
    });
    
    fetch(`/api/sensor-data/table?${params}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('tableContent').innerHTML = data.html;
            setupPaginationListeners();
        }
    })
    .catch(error => {
        console.error('Error loading table data:', error);
    })
    .finally(() => {
        hideLoading('#sensorTable');
    });
}

function setupPaginationListeners() {
    document.querySelectorAll('.pagination a').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const url = new URL(this.href);
            const page = url.searchParams.get('page');
            loadTableData(page);
        });
    });
}

function toggleTable() {
    const table = document.getElementById('sensorTable');
    const btn = document.getElementById('toggleTableBtn');
    const icon = btn.querySelector('i');
    
    if (table.style.display === 'none' || table.style.display === '') {
        if (!isDataLoaded) {
            Swal.fire({
                icon: 'warning',
                title: 'Belum Ada Data!',
                text: 'Silakan lakukan filter data terlebih dahulu',
                confirmButtonColor: '#ff9800'
            });
            return;
        }
        
        table.style.display = 'block';
        btn.className = 'btn btn-outline-danger';
        icon.className = 'fas fa-eye-slash me-2';
        btn.innerHTML = '<i class="fas fa-eye-slash me-2"></i> Sembunyikan Data Tabel';
        loadTableData();
    } else {
        table.style.display = 'none';
        btn.className = 'btn btn-outline-primary';
        icon.className = 'fas fa-eye me-2';
        btn.innerHTML = '<i class="fas fa-eye me-2"></i> Tampilkan Data Tabel';
    }
}

function exportToExcel() {
    if (!currentFilters.from || !currentFilters.to) {
        Swal.fire({
            icon: 'error',
            title: 'Filter Belum Diatur!',
            text: 'Silakan lakukan filter data terlebih dahulu sebelum export',
            confirmButtonColor: '#dc3545'
        });
        return;
    }
    
    Swal.fire({
        title: 'Export Data ke Excel?',
        html: `
            <div class="text-start">
                <p class="mb-2"><i class="fas fa-calendar text-primary me-2"></i>
                   <strong>Periode:</strong> ${formatDateDisplay(currentFilters.from)} - ${formatDateDisplay(currentFilters.to)}</p>
                <p class="mb-2"><i class="fas fa-chart-line text-info me-2"></i>
                   <strong>Parameter:</strong> ${currentFilters.parameter || 'Semua Parameter'}</p>
                <p class="mb-0 text-muted small"><i class="fas fa-info-circle me-2"></i>
                   File akan disimpan dalam format Excel (.xlsx)</p>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-file-excel me-2"></i>Export Excel',
        cancelButtonText: '<i class="fas fa-times me-2"></i>Batal',
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        customClass: {
            confirmButton: 'btn btn-success me-2',
            cancelButton: 'btn btn-secondary'
        },
        buttonsStyling: false,
        width: '500px'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Memproses Export...',
                html: `
                    <div class="text-center">
                        <div class="spinner-border text-primary mb-3" role="status"></div>
                        <p class="mb-0">Sedang mempersiapkan file Excel...</p>
                    </div>
                `,
                allowOutsideClick: false,
                showConfirmButton: false
            });

            const params = new URLSearchParams({
                from: currentFilters.from || '',
                to: currentFilters.to || '',
                parameter: currentFilters.parameter || ''
            });

            const exportUrl = `/sensor/export-excel?${params}`;
            window.location.href = exportUrl;
            
            setTimeout(() => {
                Swal.fire({
                    title: 'Export Berhasil!',
                    html: `
                        <div class="text-center">
                            <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                            <p class="mb-2">File Excel (.xlsx) sedang diunduh</p>
                        </div>
                    `,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#28a745',
                    timer: 4000,
                    timerProgressBar: true
                });
            }, 2000);
        }
    });
}

// Utility functions
function showToast(message, type = 'success') {
    const iconMap = {
        'success': 'success',
        'error': 'error',
        'danger': 'error',
        'warning': 'warning',
        'info': 'info'
    };

    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 5000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    Toast.fire({
        icon: iconMap[type] || 'info',
        title: message
    });
}

function getParameterColor(column) {
    const colorMap = {
        'suhu': '#FF6B6B', 'ph': '#4ECDC4', 'salinitas': '#3B82F6',
        'tds': '#F59E0B', 'cod': '#8B5CF6', 'tss': '#10B981',
        'nh3n': '#F43F5E', 'debit': '#06B6D4', 'conductivity': '#6366F1'
    };
    return colorMap[column] || '#9CA3AF';
}

function formatDateDisplay(date) {
    if (!date) return null;
    try {
        return new Date(date).toLocaleDateString('id-ID');
    } catch (e) {
        return date;
    }
}

function hideLoading(target) {
    if (typeof target === 'string') {
        const element = document.querySelector(target);
        if (element) {
            const loadingOverlay = element.querySelector('.loading-overlay');
            if (loadingOverlay) {
                loadingOverlay.remove();
            }
        }
    }
}

function formatDateTime(datetime) {
    return new Date(datetime).toLocaleString('id-ID', {
        day: '2-digit', month: '2-digit', 
        hour: '2-digit', minute: '2-digit'
    });
}

function populateParameterOptions(availableColumns) {
    const select = document.getElementById('paramSelect');
    select.innerHTML = '';
    
    Object.entries(availableColumns).forEach(([key, label]) => {
        const option = document.createElement('option');
        option.value = key;
        option.textContent = label;
        select.appendChild(option);
    });
    
    if (Object.keys(availableColumns).length > 0) {
        currentFilters.parameter = Object.keys(availableColumns)[0];
    }
}

function showLoading(target) {
    if (typeof target === 'string') {
        const element = document.querySelector(target);
        if (element) {
            element.style.position = 'relative';
            const loadingOverlay = document.createElement('div');
            loadingOverlay.className = 'loading-overlay d-flex justify-content-center align-items-center';
            loadingOverlay.innerHTML = `
                <div class="text-center">
                    <div class="spinner-border text-primary mb-2" role="status"></div>
                    <div class="small text-muted">Loading...</div>
                </div>
            `;
            element.appendChild(loadingOverlay);
        }
    }
}

function showNoDataMessage() {
    const canvas = document.getElementById('sensorChart');
    canvas.style.display = 'none';
    
    const placeholder = document.getElementById('chartPlaceholder');
    if (placeholder) {
        placeholder.innerHTML = `
            <i class="fas fa-chart-line fa-4x text-muted mb-3"></i>
            <h5 class="text-muted">Tidak ada data pada periode yang dipilih</h5>
            <p class="text-muted">Coba pilih rentang tanggal yang berbeda</p>
        `;
        placeholder.style.display = 'block';
    }
}
@endif

@if(Auth::user()->role === 'superadmin')
// ========================================
// ADMIN FUNCTIONS - SIMPLIFIED MAXIMUM
// ========================================
let userToDelete = null;
let userToDeleteName = null;
let currentCustomizeUserId = null;

// Dipanggil dari onclick di table
function openDeleteModal(userId, userName, userRole) {
    console.log('✓ openDeleteModal:', { userId, userName, userRole });
    
    userToDelete = userId;
    userToDeleteName = userName;
    
    document.getElementById('deleteUserName').textContent = userName;
    const roleElement = document.getElementById('deleteUserRole');
    roleElement.textContent = userRole === 'superadmin' ? 'Super Admin' : 'Read & Export';
    roleElement.className = userRole === 'superadmin' ? 'badge bg-danger' : 'badge bg-success';
    
    const modalElement = document.getElementById('deleteUserModal');
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
}

// Dipanggil dari onclick di button modal
function executeDeleteNow() {
    console.log('✓ executeDeleteNow called!');
    console.log('✓ userToDelete:', userToDelete);
    console.log('✓ userToDeleteName:', userToDeleteName);
    
    if (!userToDelete) {
        alert('ERROR: User ID tidak ditemukan!');
        return;
    }
    
    const deleteUrl = '/users/' + userToDelete;
    console.log('✓ Delete URL:', deleteUrl);
    
    // Tutup modal
    const modalElement = document.getElementById('deleteUserModal');
    const modal = bootstrap.Modal.getInstance(modalElement);
    if (modal) {
        modal.hide();
    }
    
    // Show loading
    Swal.fire({
        title: 'Menghapus User...',
        html: '<div class="spinner-border text-danger"></div>',
        showConfirmButton: false,
        allowOutsideClick: false
    });
    
    // Create form
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = deleteUrl;
    
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';
    
    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'DELETE';
    
    form.appendChild(csrfInput);
    form.appendChild(methodInput);
    document.body.appendChild(form);
    
    console.log('✓ Submitting form...');
    form.submit();
}

function initializeAdminFunctions() {
    console.log('✓ Admin functions ready (using onclick handlers)');
}

function openCustomizeModal(userId, userName) {
    console.log('Opening customize modal for:', userId, userName);
    
    currentCustomizeUserId = userId;
    document.getElementById('customizeUserName').textContent = userName;
    
    // Buka modal
    const modalElement = document.getElementById('customizeColumnsModal');
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
    
    // Load columns config
    loadColumnConfigs(userId);
}

function loadColumnConfigs(userId) {
    const container = document.getElementById('columnsConfigContainer');
    container.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-info" role="status"></div>
            <p class="mt-2 text-muted">Loading columns...</p>
        </div>
    `;
    
    fetch(`/api/users/${userId}/sensor-columns`, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderColumnConfigs(data.columns);
        } else {
            container.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    ${data.message || 'Gagal memuat data kolom'}
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error loading columns:', error);
        container.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Error: ${error.message}
            </div>
        `;
    });
}

function renderColumnConfigs(columns) {
    const container = document.getElementById('columnsConfigContainer');
    
    let html = '<div class="list-group">';
    
    Object.entries(columns).forEach(([columnName, config]) => {
        html += `
            <div class="list-group-item">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <label class="form-label fw-bold mb-0">
                            ${config.original_label}
                        </label>
                        <small class="d-block text-muted">${columnName}</small>
                    </div>
                    <div class="col-md-6">
                        <input type="text" 
                               class="form-control" 
                               id="custom_${columnName}"
                               value="${config.custom_label || config.original_label}"
                               placeholder="Nama custom kolom">
                    </div>
                    <div class="col-md-2 text-end">
                        <div class="form-check form-switch">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="visible_${columnName}"
                                   ${config.is_visible ? 'checked' : ''}>
                            <label class="form-check-label" for="visible_${columnName}">
                                <small>Tampilkan</small>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
}

function saveColumnConfigs() {
    if (!currentCustomizeUserId) {
        alert('User ID tidak ditemukan');
        return;
    }
    
    // Collect all configurations
    const configs = [];
    const container = document.getElementById('columnsConfigContainer');
    const inputs = container.querySelectorAll('input[id^="custom_"]');
    
    inputs.forEach(input => {
        const columnName = input.id.replace('custom_', '');
        const customLabel = input.value.trim();
        const isVisible = document.getElementById(`visible_${columnName}`).checked;
        
        configs.push({
            column_name: columnName,
            custom_label: customLabel,
            is_visible: isVisible
        });
    });
    
    // Show loading
    Swal.fire({
        title: 'Menyimpan...',
        html: '<div class="spinner-border text-info"></div>',
        showConfirmButton: false,
        allowOutsideClick: false
    });
    
    // Save to server
    fetch(`/api/users/${currentCustomizeUserId}/sensor-columns`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ configs })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Konfigurasi kolom berhasil disimpan',
                timer: 2000
            });
            
            // Close modal
            const modalElement = document.getElementById('customizeColumnsModal');
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                modal.hide();
            }
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: data.message || 'Gagal menyimpan konfigurasi'
            });
        }
    })
    .catch(error => {
        console.error('Error saving configs:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: error.message
        });
    });
}
@endif
</script>
@endpush