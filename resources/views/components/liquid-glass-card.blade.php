@props([
    'class' => ''
])<div {{ $attributes->merge(['class' => 'liquid-glass-card overflow-hidden rounded-3xl ' . $class]) }}
     style="box-shadow: var(--glass-shadow);">
        <!-- LAYER 1: Adaptive Webkit Dynamic Refraction Matrix -->
    <div class="liquid-glass-backdrop absolute inset-0 pointer-events-none"
         style="
            background: var(--glass-bg);
            backdrop-filter: blur(var(--glass-blur)) saturate(var(--glass-saturation));
            -webkit-backdrop-filter: blur(var(--glass-blur)) saturate(var(--glass-saturation));
            border: 1px solid var(--glass-border);
            border-radius: inherit;
            filter: url(#liquid-lens-refraction);
            transform: translateZ(0);
            z-index: 1;
         ">
    </div>
    <!-- LAYER 2: Specular Glare Vector Mapping Layer -->
    <div class="liquid-glass-glare absolute inset-0 pointer-events-none opacity-0 transition-opacity duration-500"
         style="
            background: radial-gradient(circle 250px at var(--mouse-x, 0px) var(--mouse-y, 0px), var(--glass-glare-color) 0%, transparent 85%);
            mix-blend-mode: var(--glare-blend-mode);
            border-radius: inherit;
            z-index: 2;
         ">
    </div>
    <!-- LAYER 3: Isolated Content Shell (Unmodified vector layer for readability) -->
    <div class="relative w-full h-full" style="z-index: 3;">
        {{ $slot }}
    </div>
</div>
