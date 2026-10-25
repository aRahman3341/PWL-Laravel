@props(['href', 'icon', 'label', 'active' => false, 'badge' => 0])
<a href="{{ $href }}" title="{{ $label }}" class="nav-item {{ $active ? 'active' : '' }}">
    <x-admin.icon :name="$icon" class="w-5 h-5" />
    <span class="sb-label">{{ $label }}</span>
    @if ($badge > 0)<span class="nav-badge sb-label">{{ $badge }}</span>@endif
</a>
