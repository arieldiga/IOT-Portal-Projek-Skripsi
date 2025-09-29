<form id="filterForm" class="row g-3 mb-3">
    <div class="col-md-3">
        <label class="form-label"><i class="fas fa-filter me-1"></i>Pilih Parameter</label>
        <select name="parameter" id="paramSelect" class="form-select">
            <option value="">Loading...</option>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">
            <i class="fas fa-calendar me-1"></i>Dari Tanggal
            <span class="text-danger">*</span>
        </label>
        <input type="date" name="from" id="fromDate" class="form-control" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">
            <i class="fas fa-calendar me-1"></i>Sampai Tanggal
            <span class="text-danger">*</span>
        </label>
        <input type="date" name="to" id="toDate" class="form-control" required>
    </div>
    <div class="col-md-3 d-flex align-items-end">
        <button type="submit" class="btn btn-primary w-100">
            <i class="fas fa-search me-1"></i> Tampilkan Data
        </button>
    </div>
</form>

<div class="alert alert-info border-0 shadow-sm">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Petunjuk:</strong> Silakan pilih rentang tanggal terlebih dahulu untuk menampilkan data sensor.
</div>