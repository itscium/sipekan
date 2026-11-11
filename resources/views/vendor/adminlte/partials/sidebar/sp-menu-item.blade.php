@if(isset($item['header']))
    <li class="sp-nav-header">{{ is_string($item['header']) ? $item['header'] : ($item['text'] ?? '') }}</li>
@elseif(isset($item['submenu']))
    <li class="sp-nav-group {{ !empty($item['active']) ? 'open' : '' }}">
        <button type="button" class="sp-nav-link {{ !empty($item['active']) ? 'active' : '' }}" data-sp-group
                aria-expanded="{{ !empty($item['active']) ? 'true' : 'false' }}">
            <i class="sp-nav-icon {{ $item['icon'] ?? 'far fa-fw fa-circle' }}"></i>
            <span>{{ $item['text'] }}</span>
            <i class="fas fa-chevron-right sp-nav-caret"></i>
        </button>
        <ul class="sp-nav-sub">
            @foreach($item['submenu'] as $sub)
                <li>
                    <a href="{{ $sub['href'] ?? '#' }}" class="sp-nav-link {{ !empty($sub['active']) ? 'active' : '' }}"
                       @if(!empty($sub['active'])) aria-current="page" @endif>
                        <span>{{ $sub['text'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </li>
@elseif(isset($item['text']))
    <li>
        <a href="{{ $item['href'] ?? '#' }}" class="sp-nav-link {{ !empty($item['active']) ? 'active' : '' }}"
           @if(!empty($item['active'])) aria-current="page" @endif
           @isset($item['target']) target="{{ $item['target'] }}" @endisset>
            <i class="sp-nav-icon {{ $item['icon'] ?? 'far fa-fw fa-circle' }}"></i>
            <span>{{ $item['text'] }}</span>
        </a>
    </li>
@endif
