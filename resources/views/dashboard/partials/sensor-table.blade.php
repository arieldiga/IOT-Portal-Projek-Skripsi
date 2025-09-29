<div id="sensorTable" class="table-responsive mt-4" style="display: none;">
    <!-- Export Button Inside Table Area -->
    <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded">
        <h5 class="mb-0"><i class="fas fa-table me-2"></i>Data Sensor Detail</h5>
        <div class="btn-group">
            <button class="btn btn-success btn-lg" onclick="exportToExcel()">
                <i class="fas fa-file-excel me-2"></i> Export ke Excel
            </button>
        </div>
    </div>
    
    <div id="tableContent">
        <!-- Table content will be loaded via AJAX -->
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading table data...</p>
        </div>
    </div>
</div>

<script>
function exportToExcel() {
    const params = new URLSearchParams({
        from: currentFilters.from || '',
        to: currentFilters.to || '',
        parameter: currentFilters.parameter || ''
    });

    if (confirm('Export data tabel saat ini ke Excel?')) {
        showToast('Generating Excel file...', 'info');
        window.location.href = `/sensor/export-excel?${params}`;
    }
}
</script>
