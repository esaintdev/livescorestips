@props(['category', 'date', 'subheader' => null, 'header' => null, 'tag' => 'h1'])

<div class="category-hero">
    <div class="w-100 px-3 px-md-4 mx-auto">
        <{{ $tag }} class="bm-page-title">
            {!! $header ?? '' !!}
        </{{ $tag }}>

        @if($subheader)
            <p class="hero-description mx-auto" style="max-width: 700px; line-height: 1.6;">
                {{ $subheader }}
            </p>
        @endif
    </div>
</div>

<style>
    .category-hero {
        padding: 1.5rem 0 0.5rem;
    }

    .hero-description {
        font-size: 1.1rem;
        color: rgba(255, 255, 255, 0.6);
        font-weight: 300;
        text-align: center;
    }
</style>