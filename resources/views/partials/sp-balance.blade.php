{{--
    Saldo berwarna untuk akun pegawai (ER personal / ET travel).
    Saldo bertanda "Cr." = lebih bayar (hijau); tanpa "Cr." = kewajiban (merah).
    Pemakaian: @include('partials.sp-balance', ['value' => '2.114.554 Cr.', 'label' => true, 'kind' => 'personal'])
      label: true = selalu tampilkan label; false = label hanya untuk "Cr." (dipakai di baris tabel agar tidak ramai)
      kind:  'personal' atau 'travel' (teks label kewajiban berbeda)
--}}
@php
    $raw = trim((string) ($value ?? ''));
    $isCredit = str_ends_with($raw, 'Cr.');
    $amount = trim(preg_replace('/\s*Cr\.$/', '', $raw));
    $isZero = $amount === '' || preg_replace('/[^\d]/', '', $amount) === '' || (int) preg_replace('/[^\d]/', '', $amount) === 0;
    $negLabel = ($kind ?? 'personal') === 'travel' ? 'Belum dipertanggungjawabkan' : 'Kewajiban';
@endphp
@if($isZero)
    <span class="sp-bal num">{{ $amount !== '' ? $amount : '0' }}</span>
@elseif($isCredit)
    <span class="sp-bal pos num" title="Lebih bayar: dana Anda yang masih ada di organisasi">{{ $amount }} <span class="sp-bal-chip pos">Cr.</span></span>
@else
    <span class="sp-bal neg num" title="{{ $negLabel }}">{{ $amount }}@if($label ?? false) <span class="sp-bal-chip neg">{{ $negLabel }}</span>@endif</span>
@endif
