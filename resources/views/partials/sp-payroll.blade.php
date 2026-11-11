{{-- Riwayat gaji dengan filter bulan. Variabel: $pay (DashboardService::payroll), $payRoute (nama route halaman), $payAnchor (id tujuan setelah pindah bulan) --}}
@php($payRoute = $payRoute ?? 'dashboard')
@php($payAnchor = $payAnchor ?? 'riwayat-gaji')
@php($rp = $rp ?? fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.'))
@if($pay['error'])
    <div class="sp-alert mb-3"><i class="fas fa-exclamation-triangle mt-1"></i><div>Data gaji belum bisa dimuat dari server payroll. Coba muat ulang dalam beberapa menit.</div></div>
@elseif($pay['available'])
    @php($bulanNama = collect(range(1, 12))->mapWithKeys(fn ($m) => [$m => \Illuminate\Support\Carbon::create(2000, $m, 1)->locale('id')->translatedFormat('F')]))
    @php($selYear = (int) substr($pay['selected'], 0, 4))
    @php($selMonth = (int) substr($pay['selected'], 5, 2))
    <div class="sp-card mb-3" id="riwayat-gaji">
        <div class="sp-card-head flex-wrap">
            <div>
                <p class="sp-card-title">Riwayat gaji</p>
                <p class="sp-card-sub">Pilih bulan untuk melihat gaji bulan-bulan sebelumnya</p>
            </div>

            <form method="get" action="{{ route($payRoute) }}#{{ $payAnchor }}" class="sp-pay-filter" id="spPayFilter">
                <a href="{{ $pay['prev'] ? route($payRoute, ['gaji' => $pay['prev']]) . '#' . $payAnchor : '#' }}"
                   class="sp-icon-btn {{ $pay['prev'] ? '' : 'sp-disabled' }}" aria-label="Bulan sebelumnya" title="Bulan sebelumnya">
                    <i class="fas fa-chevron-left"></i>
                </a>
                <select id="spPayMonth" class="sp-select" aria-label="Bulan">
                    @foreach($bulanNama as $num => $nama)
                        <option value="{{ $num }}" {{ $num === $selMonth ? 'selected' : '' }}>{{ $nama }}</option>
                    @endforeach
                </select>
                <select id="spPayYear" class="sp-select" aria-label="Tahun">
                    @foreach($pay['years'] as $y)
                        <option value="{{ $y }}" {{ $y === $selYear ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
                <input type="hidden" name="gaji" id="spPayValue" value="{{ $pay['selected'] }}">
                <a href="{{ $pay['next'] ? route($payRoute, ['gaji' => $pay['next']]) . '#' . $payAnchor : '#' }}"
                   class="sp-icon-btn {{ $pay['next'] ? '' : 'sp-disabled' }}" aria-label="Bulan berikutnya" title="Bulan berikutnya">
                    <i class="fas fa-chevron-right"></i>
                </a>
                @if(request()->filled('gaji'))
                    <a href="{{ route($payRoute) }}#{{ $payAnchor }}" class="small ml-1">Terbaru</a>
                @endif
            </form>
        </div>

        @if($pay['not_found'])
            <div class="sp-alert mb-3">
                <i class="fas fa-info-circle mt-1"></i>
                <div>Belum ada gaji yang diposting untuk {{ \Illuminate\Support\Carbon::createFromFormat('Y-m', $pay['requested'])->locale('id')->translatedFormat('F Y') }}. Ditampilkan gaji terakhir.</div>
            </div>
        @endif

        <div class="sp-pay">
            <div>
                <div class="sp-pay-summary">
                    <div class="sp-pay-net">
                        <div class="sp-muted" style="font-size:13px">Gaji bersih {{ $pay['month']->locale('id')->translatedFormat('F Y') }}</div>
                        <div class="sp-stat-value sp-num" style="font-size:28px;margin:6px 0">{{ $rp($pay['net']) }}</div>
                        <div class="sp-stat-foot">
                            @if($pay['paid_at'])Dibayar {{ $pay['paid_at']->locale('id')->translatedFormat('d M Y') }} · @endif
                            <a href="{{ route('personal.keuangan.payrol', ['periode' => $pay['selected']]) }}">Lihat slip gaji</a>
                        </div>
                    </div>
                    <div class="sp-pay-totals">
                        <div><span class="sp-muted">Pendapatan</span><strong class="sp-num" style="color:var(--sp-success)">{{ number_format($pay['total_earnings'], 0, ',', '.') }}</strong></div>
                        <div><span class="sp-muted">Potongan</span><strong class="sp-num" style="color:var(--sp-danger)">{{ number_format($pay['total_deductions'], 0, ',', '.') }}</strong></div>
                    </div>
                </div>

                <div class="sp-pay-items">
                    <div>
                        <p class="sp-pay-items-title">Pendapatan</p>
                        @forelse($pay['earnings'] as $it)
                            <div class="sp-pay-row"><span>{{ $it['name'] }}</span><span class="sp-num">{{ number_format($it['value'], 0, ',', '.') }}</span></div>
                        @empty
                            <div class="sp-faint" style="font-size:13px">Tidak ada rincian.</div>
                        @endforelse
                    </div>
                    <div>
                        <p class="sp-pay-items-title">Potongan</p>
                        @forelse($pay['deductions'] as $it)
                            <div class="sp-pay-row"><span>{{ $it['name'] }}</span><span class="sp-num">{{ number_format($it['value'], 0, ',', '.') }}</span></div>
                        @empty
                            <div class="sp-faint" style="font-size:13px">Tidak ada potongan.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="sp-pay-chart-wrap">
                <p class="sp-pay-items-title">Gaji bersih 12 bulan</p>
                @php($maxNet = max(1, collect($pay['trend'])->max('net')))
                <div class="sp-pay-chart" role="img" aria-label="Grafik gaji bersih 12 bulan terakhir sampai bulan terpilih">
                    @foreach($pay['trend'] as $tr)
                        <a href="{{ route($payRoute, ['gaji' => $tr['key']]) }}#{{ $payAnchor }}"
                           class="sp-pay-bar {{ $tr['key'] === $pay['selected'] ? 'active' : '' }}"
                           title="{{ $tr['label'] }}: {{ $rp($tr['net']) }}">
                            <span style="height: {{ max(3, round($tr['net'] / $maxNet * 100)) }}%"></span>
                            <small>{{ $tr['label'] }}</small>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endif

@if($pay['available'] ?? false)
    @push('js')
        <script>
            (function () {
                var form = document.getElementById('spPayFilter');
                if (!form) return;
                var monthSel = document.getElementById('spPayMonth');
                var yearSel = document.getElementById('spPayYear');
                var value = document.getElementById('spPayValue');
                var byYear = @json($pay['months_by_year']);

                // Hanya bulan yang sudah diposting yang bisa dipilih untuk tahun terpilih.
                function syncMonths() {
                    var avail = (byYear[yearSel.value] || []).map(Number);
                    Array.prototype.forEach.call(monthSel.options, function (o) {
                        o.disabled = avail.indexOf(Number(o.value)) === -1;
                    });
                    if (monthSel.selectedOptions[0].disabled) {
                        monthSel.value = String(Math.max.apply(null, avail));
                    }
                }

                function go() {
                    syncMonths();
                    value.value = yearSel.value + '-' + ('0' + monthSel.value).slice(-2);
                    form.submit();
                }

                syncMonths();
                monthSel.addEventListener('change', go);
                yearSel.addEventListener('change', go);
                form.querySelectorAll('.sp-disabled').forEach(function (a) {
                    a.addEventListener('click', function (e) { e.preventDefault(); });
                });
            })();
        </script>
    @endpush
@endif
