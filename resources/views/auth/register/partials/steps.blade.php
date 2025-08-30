@php($active = $active ?? 1)
<div class="flex items-center justify-center gap-3 mb-6 text-sm">
    @php($labels = ['Data Diri','Domisili & Kontak','Akun','Persetujuan'])
    @foreach($labels as $i => $label)
        @php($step = $i + 1)
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-full flex items-center justify-center border {{ $active >= $step ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-500 border-gray-300' }}">
                {{ $step }}
            </div>
            <span class="hidden sm:inline {{ $active >= $step ? 'text-blue-700' : 'text-gray-500' }}">{{ $label }}</span>
        </div>
        @if(!$loop->last)
            <div class="w-8 sm:w-16 h-px bg-gray-300"></div>
        @endif
    @endforeach
</div>
