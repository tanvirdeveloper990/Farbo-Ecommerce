<div class="mt-6 flex items-center gap-3">
@foreach(['facebook' => ['Facebook', 'fa-facebook-f'], 'linkedin' => ['LinkedIn', 'fa-linkedin-in'], 'youtube' => ['YouTube', 'fa-youtube'], 'whatsapp' => ['WhatsApp', 'fa-whatsapp']] as $key => [$label, $icon])
    @if(!empty($farboSettings[$key]))
    <a href="{{ $farboSettings[$key] }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $label }}" class="w-10 h-10 grid place-items-center rounded-full bg-white/10 border border-white/20 hover:bg-brand-red transition"><i class="fa-brands {{ $icon }}"></i></a>
    @endif
@endforeach
</div>
