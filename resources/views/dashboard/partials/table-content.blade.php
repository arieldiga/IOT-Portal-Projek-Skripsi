{{-- This will be returned via AJAX --}}
@if(!empty($availableColumns) && $sensorData->count() > 0)
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th><i class="fas fa-clock me-1"></i>Tanggal</th>
                @foreach($availableColumns as $column => $label)
                    <th>
                        @php
                            $icon = match($column) {
                                'suhu' => 'fas fa-thermometer-half',
                                'ph' => 'fas fa-flask',
                                'salinitas' => 'fas fa-water',
                                'tds' => 'fas fa-atom',
                                'debit' => 'fas fa-tint',
                                'conductivity' => 'fas fa-bolt',
                                default => 'fas fa-chart-line'
                            };
                        @endphp
                        <i class="{{ $icon }} me-1"></i>{{ $label }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($sensorData as $row)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($row->datetime ?? $row->created_at)->format('d-m-Y H:i') }}</td>
                    @foreach($availableColumns as $column => $label)
                        <td>
                            @php
                                $value = $row->$column ?? '-';
                                $badgeClass = match($column) {
                                    'suhu' => 'bg-danger',
                                    'ph' => 'bg-info',
                                    'salinitas' => 'bg-primary',
                                    'tds' => 'bg-warning',
                                    'debit' => 'bg-success',
                                    'conductivity' => 'bg-dark',
                                    default => 'bg-secondary'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">
                                {{ is_numeric($value) ? number_format($value, 2) : $value }}
                            </span>
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Enhanced Pagination --}}
    @if($sensorData->hasPages())
        <div class="pagination-wrapper">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div class="pagination-info mb-2 mb-md-0">
                    <i class="fas fa-info-circle"></i>
                    <span>
                        Menampilkan {{ $sensorData->firstItem() }}-{{ $sensorData->lastItem() }} 
                        dari {{ number_format($sensorData->total()) }} data
                    </span>
                </div>
                <div class="pagination-controls">
                    {{ $sensorData->links('dashboard.partials.custom-pagination') }}
                </div>
            </div>
        </div>
    @endif
@else
    <div class="text-center py-5">
        <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
        <h5 class="text-muted">Tidak ada data sensor tersedia</h5>
        <p class="text-muted">Silakan hubungi admin untuk konfigurasi sensor atau coba filter lain</p>
    </div>
@endif