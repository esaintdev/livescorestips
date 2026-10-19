@extends('layouts.app')

@section('title', $post->title . ' - ' . config('app.name'))
@section('description', $post->meta_description ?? Str::limit(strip_tags($post->content), 160))
@section('keywords', $post->meta_keywords ?? '')

@php
    $ogImage = $post->image_url ?? asset('images/icon.png');
    $ogDescription = $post->meta_description ?? Str::limit(strip_tags($post->content), 160);
@endphp

@push('head')
    <meta property="og:title" content="{{ $post->title }}" />
    <meta property="og:description" content="{{ $ogDescription }}" />
    <meta property="og:image" content="{{ $ogImage }}" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:type" content="article" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{{ $post->title }}" />
    <meta name="twitter:description" content="{{ $ogDescription }}" />
    <meta name="twitter:image" content="{{ $ogImage }}" />
@endpush

@section('content')
    <div class="bm-article-detail">
        <div class="bm-article-detail-inner">

            {{-- Breadcrumb --}}
            <div class="bm-breadcrumb">
                <a href="{{ route('home') }}">Home</a>
                <span class="bm-breadcrumb-sep">/</span>
                <a href="{{ route('blog.index') }}">Blog</a>
                <span class="bm-breadcrumb-sep">/</span>
                <span class="bm-breadcrumb-current">{{ $post->title }}</span>
            </div>

            {{-- Article Header --}}
            <header class="bm-article-detail-header">
                <h1 class="bm-article-detail-title">{{ $post->title }}</h1>
                <div class="bm-article-detail-meta">
                    <span class="bm-article-detail-author">By {{ $post->author ?? 'Admin' }}</span>
                    <span class="bm-article-detail-date">{{ \Carbon\Carbon::parse($post->published_at)->format('F j, Y') }}</span>
                </div>
            </header>

            {{-- Featured Image --}}
            @if($post->image_url)
                <div class="bm-article-detail-image">
                    <img src="{{ $post->image_url }}" alt="{{ $post->title }}">
                </div>
            @endif

            {{-- Article Content --}}
            <div class="bm-article-content">
                {!! $post->content !!}
            </div>

            {{-- Share Buttons --}}
            <div class="bm-article-share">
                <span class="bm-article-share-label">Share this article</span>
                <div class="bm-article-share-buttons">
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}" target="_blank" class="bm-share-btn bm-share-facebook" rel="nofollow noopener">
                        <i class="fa fa-facebook"></i> Share
                    </a>
                    <a href="https://twitter.com/intent/tweet?text={{ urlencode($post->title) }}&url={{ urlencode(request()->url()) }}" target="_blank" class="bm-share-btn bm-share-twitter" rel="nofollow noopener">
                        <i class="fa fa-twitter"></i> Tweet
                    </a>
                    <a href="https://api.whatsapp.com/send?text={{ urlencode($post->title . ' - ' . request()->url()) }}" target="_blank" class="bm-share-btn bm-share-whatsapp" rel="nofollow noopener">
                        <i class="fa fa-whatsapp"></i> Share
                    </a>
                </div>
            </div>

        </div>
    </div>

    {{-- Related Articles --}}
    @if(isset($relatedPosts) && $relatedPosts->count() > 0)
        <div class="bm-related-section">
            <div class="bm-related-inner">
                <h2 class="bm-section-title">Related Articles</h2>
                <div class="row">
                    @foreach($relatedPosts as $related)
                        <div class="col-md-4 mb-4">
                            <div class="bm-article-card h-100">
                                <div class="bm-article-img-wrapper position-relative">
                                    <div class="bm-article-img" style="background-image: url('{{ $related->image_url ?? asset('images/logo.png') }}')"></div>
                                </div>
                                <div class="bm-article-body">
                                    <span class="bm-article-date">{{ \Carbon\Carbon::parse($related->published_at)->format('M d, Y') }}</span>
                                    <h3 class="bm-article-title"><a href="{{ route('blog.show', $related->slug) }}">{{ Str::limit($related->title, 60) }}</a></h3>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
@endsection
