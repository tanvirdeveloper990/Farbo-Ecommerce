<ul class="space-y-3 text-sm text-white/85">
@foreach(\App\Models\Category::where('is_active', true)->orderBy('serial')->orderBy('id')->take(5)->get() as $footerCategory)
    <li><a href="{{ url('/categories/'.$footerCategory->slug) }}" class="inline-flex items-center gap-2.5 hover:text-white hover:translate-x-1 transition"><i class="fa-solid fa-chevron-right text-[9px] text-white/70"></i>{{ $footerCategory->name }}</a></li>
@endforeach
</ul>
