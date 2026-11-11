{{-- Foto profil bulat; jika belum ada foto, tampilkan inisial. Pemakaian: @include('partials.sp-avatar', ['u' => $user, 'size' => 34]) --}}
@php($size = $size ?? 34)
@if($u->foto_url)
    <img src="{{ $u->foto_url }}" alt="Foto {{ $u->name }}" class="sp-avatar sp-avatar-img {{ $class ?? '' }}"
         style="width:{{ $size }}px;height:{{ $size }}px;flex-basis:{{ $size }}px">
@else
    <span class="sp-avatar {{ $class ?? '' }}"
          style="width:{{ $size }}px;height:{{ $size }}px;flex-basis:{{ $size }}px;font-size:{{ max(12, round($size * 0.36)) }}px">{{ $u->initials }}</span>
@endif
