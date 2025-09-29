<div class="chart-container" id="chartContainer">
    <!-- Placeholder awal sebelum filter -->
    <div id="chartPlaceholder" class="text-center py-5">
        <i class="fas fa-calendar-alt fa-4x text-muted mb-3"></i>
        <h5 class="text-muted">Belum Ada Data yang Ditampilkan</h5>
        <p class="text-muted mb-0">Silakan pilih rentang tanggal di atas untuk menampilkan grafik sensor</p>
    </div>
    
    <!-- Canvas chart (hidden by default) -->
    <canvas id="sensorChart" height="100" style="display: none;"></canvas>
    
    <!-- Loading spinner -->
    <div class="ajax-loading" style="display: none;">
        <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2">Loading chart data...</p>
    </div>
</div>

<div class="text-center mt-4">
    <button id="toggleTableBtn" class="btn btn-outline-primary" style="display: none;">
        <i class="fas fa-eye me-2"></i> Tampilkan Data Tabel
    </button>
</div>