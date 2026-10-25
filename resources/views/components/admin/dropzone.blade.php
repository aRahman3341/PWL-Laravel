@props(['name' => 'photo', 'current' => null, 'accept' => 'image/jpeg,image/png,image/webp', 'image' => true, 'text' => 'Klik atau seret file ke sini'])
<div data-dropzone class="dz p-3 text-center">
    <input type="file" name="{{ $name }}" accept="{{ $accept }}" class="hidden">
    @if ($image)
        <img data-preview src="{{ $current }}" alt="" class="{{ $current ? '' : 'hidden' }} mx-auto max-h-40 max-w-full rounded-lg object-contain">
    @endif
    <div data-placeholder class="{{ $current ? 'hidden' : '' }} py-5 text-slate-400">
        <x-admin.icon name="upload" class="w-8 h-8 mx-auto mb-1" />
        <div class="text-sm">{{ $text }}</div>
    </div>
    <div data-fname class="text-xs text-slate-500 mt-2">{{ $current ? 'Klik atau seret foto baru untuk menggantinya' : '' }}</div>
</div>
