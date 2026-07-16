<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard - Lautan Air Indonesia')</title>
    
    <!-- External CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


    <!-- Custom CSS -->
    <style>
        :root {
            --primary-blue: #0066CC;
            --secondary-blue: #004999;
            --accent-cyan: #00B8D4;
            --light-blue: #E3F2FD;
            --success-green: #00C851;
            --warning-orange: #FF6F00;
            --danger-red: #DC3545;
            --dark-gray: #212529;
            --light-gray: #F8F9FA;
            --border-color: #E9ECEF;
            --text-muted: #6C757D;
            --glass-bg: rgba(255, 255, 255, 0.95);
            --shadow-light: 0 4px 20px rgba(0, 0, 0, 0.08);
            --shadow-medium: 0 8px 30px rgba(0, 0, 0, 0.12);
            --shadow-strong: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        * { box-sizing: border-box; }

/* Fix: kecualikan elemen icon SweetAlert2 dari reset box-sizing di atas */
.swal2-icon,
.swal2-icon *,
.swal2-success-ring,
.swal2-success-fix,
.swal2-success-line-tip,
.swal2-success-line-long,
.swal2-x-mark,
.swal2-x-mark-line-left,
.swal2-x-mark-line-right {
    box-sizing: content-box !important;
}

        body { 
            font-family: 'Poppins', sans-serif; 
            background: url('{{ asset('images/bgLogin1.png') }}') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh; 
        }

        /* Enhanced Navbar */
        .navbar-custom {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            box-shadow: var(--shadow-medium);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .logo-navbar {
            height: 40px;
            background: white;
            padding: 4px 8px;
            border-radius: 12px;
            margin-right: 12px;
            box-shadow: var(--shadow-light);
            transition: transform 0.3s ease;
        }

        .logo-navbar:hover { transform: scale(1.05); }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.4rem;
            color: var(--primary-blue) !important;
        }

        .text-muted {
            background: linear-gradient(135deg, var(--primary-blue), var(--accent-cyan));
            color: white !important;
            padding: 8px 16px;
            border-radius: 25px;
            font-weight: 500;
            font-size: 0.9rem;
            box-shadow: var(--shadow-light);
        }

        .btn-logout {
            background: linear-gradient(135deg, #FF416C, #FF4B2B);
            color: white;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 25px;
            border: none;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-light);
        }

        .btn-logout:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 65, 108, 0.4);
            color: white;
        }

        /* Enhanced Cards */
        .welcome-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 40px;
            box-shadow: var(--shadow-strong);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .welcome-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-blue), var(--accent-cyan));
        }

        .welcome-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
        }

        .welcome-title {
            color: var(--primary-blue);
            font-weight: 800;
            font-size: 2.8rem;
            margin-bottom: 16px;
            background: linear-gradient(135deg, var(--primary-blue), var(--accent-cyan));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Breadcrumb */
        .breadcrumb {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 16px 24px;
            box-shadow: var(--shadow-light);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .breadcrumb-item a {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .breadcrumb-item a:hover { color: var(--accent-cyan); }

        /* Loading States */
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            border-radius: inherit;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--primary-blue);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Toast notifications */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1100;
        }

        .toast {
            border-radius: 12px;
            box-shadow: var(--shadow-medium);
            margin-bottom: 10px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .welcome-title { font-size: 2.2rem; }
            .welcome-card { padding: 24px; }
        }

    /* ... CSS yang sudah ada ... */
    
    /* Custom SweetAlert Styles */
    .swal2-popup {
        border-radius: 20px !important;
        font-family: 'Inter', sans-serif !important;
        box-shadow: var(--shadow-strong) !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
    }
    
    .swal2-title {
        color: var(--primary-blue) !important;
        font-weight: 700 !important;
        font-size: 1.5rem !important;
    }
    
    .swal2-content {
        color: var(--dark-gray) !important;
        font-size: 1rem !important;
    }
    
    .swal2-confirm {
        background: linear-gradient(135deg, var(--primary-blue), var(--accent-cyan)) !important;
        border-radius: 12px !important;
        padding: 12px 24px !important;
        font-weight: 600 !important;
        border: none !important;
        box-shadow: var(--shadow-light) !important;
        transition: all 0.3s ease !important;
    }
    
    .swal2-confirm:hover {
        transform: translateY(-2px) !important;
        box-shadow: var(--shadow-medium) !important;
    }
    
    .swal2-cancel {
        background-color: var(--text-muted) !important;
        border-radius: 12px !important;
        padding: 12px 24px !important;
        font-weight: 600 !important;
        border: none !important;
        box-shadow: var(--shadow-light) !important;
        transition: all 0.3s ease !important;
    }
    
    .swal2-cancel:hover {
        background-color: #5a6268 !important;
        transform: translateY(-2px) !important;
    }
    
    .swal2-success .swal2-success-ring {
        border-color: var(--success-green) !important;
    }
    
    .swal2-error .swal2-x-mark {
        border-color: var(--danger-red) !important;
    }
    
    .swal2-warning .swal2-warning {
        border-color: var(--warning-orange) !important;
        color: var(--warning-orange) !important;
    }
    
    .swal2-info .swal2-info {
        border-color: var(--accent-cyan) !important;
        color: var(--accent-cyan) !important;
    }
    
    /* Loading spinner in SweetAlert */
    .swal2-loader {
        border-color: var(--primary-blue) transparent var(--primary-blue) transparent !important;
    }
    
    /* Toast positioning */
    .swal2-toast-shown {
        top: 80px !important; /* Adjust for navbar */
        right: 20px !important;
    }
</style>
    
    @stack('styles')
</head>
<body>
    <!-- Enhanced Navbar -->
<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container">
        <a class="navbar-brand" href="#">
            <img src="https://www.lautanairindonesia.com/wp-content/uploads/2025/05/Logo-LAI-Horizontal_2025-1024x415.png" alt="LAI Logo" class="logo-navbar">
            <span class="fw-bold text-primary">Smart Dashboard</span>
        </a>

        <!-- Tombol hamburger, muncul di layar < 992px -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Konten yang di-collapse di mobile -->
        <div class="collapse navbar-collapse" id="navbarContent">
            <div class="ms-auto d-flex flex-column flex-lg-row align-items-center gap-2 mt-3 mt-lg-0">
                <span class="me-lg-3 text-muted">
                    <i class="fas fa-user-circle me-2"></i>
                    {{ Auth::user()->display_name ?? Auth::user()->username }}
                </span>
                <form id="logoutForm" action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="button" class="btn-logout" onclick="handleLogout(this)">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>

    <!-- Breadcrumb -->
    <div class="container mt-3">
        <nav class="breadcrumb bg-light p-2 rounded">
            <ol class="breadcrumb mb-0">
                @yield('breadcrumbs')
            </ol>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="container my-4">
        @yield('content')
    </div>

    <!-- Toast Container -->
    <div class="toast-container"></div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Global JS Functions -->
    <script>
        // Setup CSRF token for all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

         // Enhanced logout function dengan SweetAlert
    function handleLogout(btn) {
        Swal.fire({
            title: 'Konfirmasi Logout',
            text: 'Yakin ingin keluar dari sistem?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-sign-out-alt me-2"></i>Ya, Logout',
            cancelButtonText: '<i class="fas fa-times me-2"></i>Batal',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            customClass: {
                confirmButton: 'btn btn-danger me-2',
                cancelButton: 'btn btn-secondary'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Logging out...';
                btn.disabled = true;
                
                // Submit form
                document.getElementById('logoutForm').submit();
            }
        });
    }

        // Global loading state management
        function showLoading(element) {
            const container = typeof element === 'string' ? document.querySelector(element) : element;
            if (!container) return;
            
            container.style.position = 'relative';
            const loadingOverlay = document.createElement('div');
            loadingOverlay.className = 'loading-overlay';
            loadingOverlay.innerHTML = '<div class="spinner"></div>';
            container.appendChild(loadingOverlay);
        }

        function hideLoading(element) {
            const container = typeof element === 'string' ? document.querySelector(element) : element;
            if (!container) return;
            
            const loadingOverlay = container.querySelector('.loading-overlay');
            if (loadingOverlay) {
                loadingOverlay.remove();
            }
        }

        
    // Enhanced loading functions dengan SweetAlert
    function showLoadingSwal(title = 'Memuat...', text = 'Mohon tunggu sebentar') {
        Swal.fire({
            title: title,
            text: text,
            allowOutsideClick: false,
            showConfirmButton: false,
            background: 'var(--glass-bg)',
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }

    function hideLoadingSwal() {
        if (Swal.isVisible()) {
            Swal.close();
        }
    }

        // Enhanced showToast function dengan SweetAlert Toast
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
            timer: 4000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            },
            customClass: {
                popup: 'swal2-toast-custom'
            },
            background: 'var(--glass-bg)',
            color: 'var(--dark-gray)'
        });

        Toast.fire({
            icon: iconMap[type] || 'info',
            title: message
        });
    }

         // Enhanced error handler dengan SweetAlert
    function handleAjaxError(xhr, status, error) {
        console.error('AJAX Error:', error);
        let title = 'Oops, Ada Kesalahan!';
        let message = 'Lakukan filter data terlebih dahulu';
        
        if (xhr.responseJSON && xhr.responseJSON.message) {
            message = xhr.responseJSON.message;
        } else if (xhr.status === 404) {
            message = 'Data tidak ditemukan';
        } else if (xhr.status === 500) {
            message = 'Kesalahan server internal';
            title = 'Server Error';
        } else if (xhr.status === 403) {
            message = 'Akses ditolak';
            title = 'Akses Ditolak';
        }
        
        Swal.fire({
            title: title,
            text: message,
            icon: 'error',
            confirmButtonText: 'OK',
            confirmButtonColor: 'var(--danger-red)'
        });
    }

    // Function untuk konfirmasi umum
    function confirmAction(title, text, confirmText = 'Ya', cancelText = 'Batal') {
        return Swal.fire({
            title: title,
            text: text,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: confirmText,
            cancelButtonText: cancelText,
            confirmButtonColor: 'var(--primary-blue)',
            cancelButtonColor: 'var(--text-muted)',
            customClass: {
                confirmButton: 'btn btn-primary me-2',
                cancelButton: 'btn btn-secondary'
            },
            buttonsStyling: false
        });
    }

    // Function untuk success notification
    function showSuccessAlert(title, message, timer = 3000) {
        Swal.fire({
            title: title,
            text: message,
            icon: 'success',
            confirmButtonText: 'OK',
            confirmButtonColor: 'var(--success-green)',
            timer: timer,
            timerProgressBar: true,
            showCloseButton: true
        });
    }

    // Function untuk error notification
    function showErrorAlert(title, message) {
        Swal.fire({
            title: title,
            text: message,
            icon: 'error',
            confirmButtonText: 'OK',
            confirmButtonColor: 'var(--danger-red)'
        });
    }

    // Function untuk warning notification
    function showWarningAlert(title, message) {
        Swal.fire({
            title: title,
            text: message,
            icon: 'warning',
            confirmButtonText: 'OK',
            confirmButtonColor: 'var(--warning-orange)'
        });
    }

    // Function untuk info notification
    function showInfoAlert(title, message) {
        Swal.fire({
            title: title,
            text: message,
            icon: 'info',
            confirmButtonText: 'OK',
            confirmButtonColor: 'var(--accent-cyan)'
        });
    }
    </script>
    
    @stack('scripts')
</body>
</html>