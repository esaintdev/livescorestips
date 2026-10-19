@extends('layouts.app')

@section('title', $metadata['page_title'] ?? 'Blog - ' . config('app.name'))
@section('description', $metadata['page_description'] ?? 'Stay updated with the latest sports news, expert analysis, and betting tips from ' . config('app.name') . '.')
@section('keywords', $metadata['page_keywords'] ?? 'sports news, betting tips, football analysis')

@section('content')
    <x-category-hero 
        :header="$metadata['page_subheader'] ?? 'Blog'" 
        :subheader="$metadata['page_description'] ?? null" 
        :date="now()->toDateString()" 
        tag="h1"
    />

    <div class="blog-container">
        {{-- Category Navigation --}}
        @if(isset($categories) && $categories->count() > 0)
            <nav class="bm-category-nav mb-5 d-flex flex-wrap justify-content-center">
                <a href="{{ route('blog.index') }}" class="bm-cat-link {{ !isset($category) ? 'active' : '' }}">All Topics</a>
                @foreach($categories as $cat)
                    <a href="{{ route('blog.show', $cat->slug) }}" class="bm-cat-link {{ (isset($category) && $category->id == $cat->id) ? 'active' : '' }}">
                        {{ $cat->name }}
                    </a>
                @endforeach
            </nav>
        @endif

        <div class="row">
            @forelse($posts as $post)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="bm-article-card h-100">
                        <div class="bm-article-img-wrapper position-relative">
                            <div class="bm-article-img" style="background-image: url('{{ $post->image_url ?? asset('images/logo.png') }}')"></div>
                            @if($post->category)
                                <a href="{{ route('blog.show', $post->category->slug) }}" class="bm-article-cat-badge">
                                    {{ $post->category->name }}
                                </a>
                            @endif
                        </div>
                        <div class="bm-article-body">
                            <span class="bm-article-date"><i class="fa fa-calendar-o mr-1"></i> {{ \Carbon\Carbon::parse($post->published_at)->format('M d, Y') }}</span>
                            <h3 class="bm-article-title"><a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a></h3>
                            <p class="bm-article-excerpt">{{ Str::limit($post->excerpt, 120) }}</p>
                            <a href="{{ route('blog.show', $post->slug) }}" class="bm-btn-readmore">READ MORE <i class="fa fa-arrow-right ml-1"></i></a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <p class="text-muted">No articles found.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $posts->links() }}
        </div>
    </div>

    <x-info-article :metadata="$metadata" />
@endsection