{{--
    Slip gaji SIPekan (desain "Kartu SIPekan"), dipakai halaman cetak dan PDF.
    Butuh: $profile (baris stub 0 + relasi posted_payment), $earning, $deduction.
--}}
@php
    $pp = $profile->posted_payment;
    $paid = \Illuminate\Support\Carbon::parse($pp->date_payment)->locale('id');
    $name = ucwords(strtolower(trim($pp->name_regular)));
    $words = preg_split('/\s+/', $name);
    $initials = strtoupper(mb_substr($words[0] ?? '', 0, 1) . (count($words) > 1 ? mb_substr(end($words), 0, 1) : ''));

    // NIK disamarkan: hanya 4 digit awal dan akhir yang tampil
    $nik = preg_replace('/\D/', '', (string) $pp->employee_code_national);
    $nikMasked = strlen($nik) >= 8 ? substr($nik, 0, 4) . ' •••• •••• ' . substr($nik, -4) : ($nik ?: '–');

    $totalEarn = (float) $earning->sum('value');
    $totalDeduct = (float) $deduction->sum('value');
    $net = (float) $pp->value;
    $rp = fn ($n) => number_format((float) $n, 0, ',', '.');
    $pct = fn ($n) => $totalEarn > 0 ? max(0, min(100, $n / $totalEarn * 100)) : 0;

    // Warna bilah potongan, dari gelap ke terang
    $shades = ['#98a2b3', '#b9c1cf', '#d0d5dd', '#e4e7ec', '#c4cad5', '#a9b2c1'];

    // Terbilang (bahasa Indonesia)
    $terbilang = function ($n) use (&$terbilang) {
        $n = (int) floor(abs($n));
        $s = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];
        if ($n < 12) return $s[$n];
        if ($n < 20) return $terbilang($n - 10) . ' belas';
        if ($n < 100) return trim($terbilang(intdiv($n, 10)) . ' puluh ' . $terbilang($n % 10));
        if ($n < 200) return trim('seratus ' . $terbilang($n - 100));
        if ($n < 1000) return trim($terbilang(intdiv($n, 100)) . ' ratus ' . $terbilang($n % 100));
        if ($n < 2000) return trim('seribu ' . $terbilang($n - 1000));
        if ($n < 1000000) return trim($terbilang(intdiv($n, 1000)) . ' ribu ' . $terbilang($n % 1000));
        if ($n < 1000000000) return trim($terbilang(intdiv($n, 1000000)) . ' juta ' . $terbilang($n % 1000000));
        return trim($terbilang(intdiv($n, 1000000000)) . ' miliar ' . $terbilang($n % 1000000000));
    };
@endphp
<style>
    @page { size: A4; margin: 0; }
    html, body { margin: 0; padding: 0; background: #ffffff; }
    .slip, .slip * { box-sizing: border-box; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .slip { width: 210mm; min-height: 296mm; margin: 0 auto; background: #f4f6fa; color: #101828;
        font-family: Inter, -apple-system, 'Segoe UI', Roboto, sans-serif; font-size: 14px; line-height: 1.45;
        display: flex; flex-direction: column; }
    .slip .num { font-variant-numeric: tabular-nums; }
    .slip-head { background: #133c7a; color: #fff; padding: 40px 52px 88px; display: flex; align-items: center; gap: 18px; }
    .slip-logo { width: 68px; height: 68px; border-radius: 50%; background: #fff; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .slip-logo img { width: 46px; height: 42px; object-fit: contain; }
    .slip-org { flex-grow: 1; }
    .slip-org b { display: block; font-family: 'Space Grotesk', Inter, sans-serif; font-size: 20px; font-weight: 600; }
    .slip-org span { display: block; font-size: 13px; color: #c9d6ee; margin-top: 3px; }
    .slip-month { text-align: right; font-size: 13px; color: #c9d6ee; }
    .slip-month b { display: block; font-family: 'Space Grotesk', Inter, sans-serif; font-size: 20px; color: #fff; }
    .slip-person { margin: -62px 52px 0; padding: 20px 24px; background: #fff; border: 1px solid #dfe5ef; border-radius: 14px; display: flex; align-items: center; gap: 18px; }
    .slip-avatar { width: 52px; height: 52px; border-radius: 50%; background: #e8f0fc; color: #164a96; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 17px; flex-shrink: 0; }
    .slip-person-main { flex-grow: 1; }
    .slip-person-main b { display: block; font-size: 17px; }
    .slip-person-main span, .slip-net span { display: block; font-size: 13px; color: #475467; margin-top: 2px; }
    .slip-net { text-align: right; }
    .slip-net b { display: block; font-family: 'Space Grotesk', Inter, sans-serif; font-size: 28px; color: #1f5fbf; }
    .slip-meta { padding: 18px 52px 0; display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; font-size: 13px; }
    .slip-meta div { padding: 12px 16px; background: #fff; border: 1px solid #dfe5ef; border-radius: 10px; color: #475467; }
    .slip-meta b { display: block; color: #101828; margin-top: 3px; }
    .slip-cols { padding: 16px 52px 0; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
    .slip-box { background: #fff; border: 1px solid #dfe5ef; border-radius: 14px; overflow: hidden; display: flex; flex-direction: column; }
    .slip-box h2 { margin: 0; padding: 12px 18px; font-size: 14px; font-weight: 600; }
    .slip-box.in h2 { background: #e7f6ee; color: #05603a; }
    .slip-box.out h2 { background: #fdecea; color: #912018; }
    .slip-rows { padding: 4px 18px; flex-grow: 1; }
    .slip-row { display: flex; justify-content: space-between; gap: 12px; padding: 9px 0; border-bottom: 1px solid #eef2f8; }
    .slip-row:last-child { border-bottom: 0; }
    .slip-total { display: flex; justify-content: space-between; padding: 12px 18px; background: #f4f6fa; font-weight: 600; }
    .slip-comp { margin: 16px 52px 0; padding: 18px 22px; background: #fff; border: 1px solid #dfe5ef; border-radius: 14px; }
    .slip-comp-head { display: flex; justify-content: space-between; font-size: 13px; color: #475467; margin-bottom: 10px; }
    .slip-bar { display: flex; height: 14px; border-radius: 99px; overflow: hidden; background: #eef2f8; }
    .slip-legend { display: flex; gap: 8px 16px; flex-wrap: wrap; font-size: 12px; color: #475467; margin-top: 10px; }
    .slip-legend i { display: inline-block; width: 10px; height: 10px; border-radius: 3px; margin-right: 6px; vertical-align: -1px; }
    .slip-words { margin: 12px 52px 0; font-size: 13px; color: #475467; font-style: italic; }
    .slip-foot { margin-top: auto; padding: 20px 52px 36px; display: flex; justify-content: space-between; gap: 24px; font-size: 12px; color: #667085; }
</style>

<div class="slip" id="slip">
    <header class="slip-head">
        <div class="slip-logo"><img src="{{ asset('img/logo.png') }}" alt="Logo Gereja Masehi Advent Hari Ketujuh"></div>
        <div class="slip-org">
            <b>{{ trim($profile->entity_name ?? '') ?: 'Central Indonesia Union Mission' }}</b>
            <span>JL. MT Haryono Blok A, Kav 4 - 5, Jakarta Selatan, 12810</span>
        </div>
        <div class="slip-month">Slip gaji<b>{{ $paid->translatedFormat('M Y') }}</b></div>
    </header>

    <div class="slip-person">
        <div class="slip-avatar">{{ $initials }}</div>
        <div class="slip-person-main">
            <b>{{ $name }}</b>
            <span>{{ $profile->enrollment_code }} · KTP {{ $nikMasked }}</span>
        </div>
        <div class="slip-net">
            <span>Gaji bersih diterima</span>
            <b class="num">Rp {{ $rp($net) }}</b>
        </div>
    </div>

    <div class="slip-meta">
        <div>Periode<b>1–{{ $paid->format('t') }} {{ $paid->translatedFormat('M Y') }}</b></div>
        <div>Tanggal bayar<b>{{ $paid->translatedFormat('d M Y') }}</b></div>
        <div>No. EFT<b>0{{ $pp->eft_number }}</b></div>
    </div>

    <div class="slip-cols">
        <section class="slip-box in">
            <h2>Pendapatan</h2>
            <div class="slip-rows">
                @foreach($earning as $e)
                    <div class="slip-row"><span>{{ $e->allowance_name }}</span><span class="num">{{ $rp($e->value) }}</span></div>
                @endforeach
            </div>
            <div class="slip-total"><span>Total</span><span class="num">{{ $rp($totalEarn) }}</span></div>
        </section>
        <section class="slip-box out">
            <h2>Potongan</h2>
            <div class="slip-rows">
                @forelse($deduction as $d)
                    <div class="slip-row"><span>{{ $d->allowance_name }}</span><span class="num">{{ $rp($d->value) }}</span></div>
                @empty
                    <div class="slip-row"><span>Tidak ada potongan</span><span></span></div>
                @endforelse
            </div>
            <div class="slip-total"><span>Total</span><span class="num">{{ $rp($totalDeduct) }}</span></div>
        </section>
    </div>

    @if($totalEarn > 0)
        <div class="slip-comp">
            <div class="slip-comp-head">
                <span>Dari Rp {{ $rp($totalEarn) }} pendapatan</span>
                <span>Diterima {{ round($pct($net)) }}%</span>
            </div>
            <div class="slip-bar">
                @foreach($deduction as $i => $d)
                    <div style="width: {{ $pct($d->value) }}%; background: {{ $shades[$i % count($shades)] }}"></div>
                @endforeach
                <div style="width: {{ $pct($net) }}%; background: #1f5fbf"></div>
            </div>
            <div class="slip-legend">
                @foreach($deduction as $i => $d)
                    <span><i style="background: {{ $shades[$i % count($shades)] }}"></i>{{ $d->allowance_name }} {{ round($pct($d->value)) }}%</span>
                @endforeach
                <span><i style="background: #1f5fbf"></i>Diterima {{ round($pct($net)) }}%</span>
            </div>
        </div>
    @endif

    <div class="slip-words">Terbilang: {{ ucfirst($terbilang($net)) }} rupiah</div>

    <footer class="slip-foot">
        <span>Dibuat otomatis oleh <strong style="color: #101828">SIPekan</strong>. Sah tanpa tanda tangan.</span>
        <span>Dicetak {{ now()->locale('id')->translatedFormat('d M Y, H.i') }}</span>
    </footer>
</div>
