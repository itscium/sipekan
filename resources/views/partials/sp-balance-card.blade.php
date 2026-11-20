{{--
    Kartu saldo akhir berwarna. Pemakaian:
    @include('partials.sp-balance-card', ['value' => $saldo_akhir, 'kind' => 'personal', 'title' => 'Saldo akhir'])
--}}
@php
    $raw = trim((string) ($value ?? ''));
    $isCredit = str_ends_with($raw, 'Cr.');
    $amount = trim(preg_replace('/\s*Cr\.$/', '', $raw));
    $digits = preg_replace('/[^\d]/', '', $amount);
    $isZero = $digits === '' || (int) $digits === 0;
    $travel = ($kind ?? 'personal') === 'travel';
    $state = $isZero ? 'zero' : ($isCredit ? 'pos' : 'neg');
@endphp
<div class="sp-bal-card {{ $state }}">
    <div class="sp-bal-card-title">{{ $title ?? 'Saldo akhir' }}</div>
    <div class="sp-bal-card-value num">Rp {{ $isZero ? '0' : $amount }}</div>
    @if($state === 'pos')
        <span class="sp-bal-chip pos">Lebih bayar · Cr.</span>
        <p class="sp-bal-card-note">Dana Anda yang masih tersimpan di organisasi.</p>
    @elseif($state === 'neg')
        <span class="sp-bal-chip neg">{{ $travel ? 'Belum dipertanggungjawabkan' : 'Kewajiban' }}</span>
        <p class="sp-bal-card-note">{{ $travel ? 'Uang muka perjalanan yang belum diselesaikan.' : 'Tagihan Anda yang belum lunas; akan dipotong berikutnya.' }}</p>
    @else
        <span class="sp-bal-chip">Lunas</span>
        <p class="sp-bal-card-note">Tidak ada saldo pada akhir periode ini.</p>
    @endif
</div>
