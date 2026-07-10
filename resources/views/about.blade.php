@extends('layouts.app')

@section('title', $metadata['page_title'] ?? 'About Us - ' . config('app.name'))
@section('description', $metadata['page_description'] ?? 'Learn more about ' . config('app.name') . ' and our mission to provide the best football predictions.')
@section('keywords', $metadata['page_keywords'] ?? 'about, football predictions, betting tips')

@section('page-header')
    <div class="bm-about-page">
        <h2 class="bm-page-title">{{ $metadata['page_subheader'] ?? '' }}</h2>
        @if(isset($metadata['page_description']) && !empty($metadata['page_description']))
            <p class="bm-page-subheader">{{ $metadata['page_description'] }}</p>
        @endif
    </div>
@endsection

@section('content')
    <div class="bm-card mb-4">
        <div class="bm-article-content">
            @if(isset($metadata['footer_content']) && !empty($metadata['footer_content']))
                {!! $metadata['footer_content'] !!}
            @else
                <h2>Who We Are</h2>
                <p>Welcome to {{ config('app.name') }}, your number one source for all things football predictions. We're dedicated to giving you the very best of football tips, with a focus on accuracy, reliability, and detailed statistics.</p>
                
                <h2>Our Mission</h2>
                <p>Our goal is to provide football fans with the most accurate predictions using a sophisticated algorithm based on years of statistical data. We believe that informed betting is the best way to enjoy the game.</p>
            @endif
        </div>
    </div>

    <x-info-article :metadata="$metadata" />
@endsection
