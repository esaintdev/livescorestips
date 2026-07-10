@extends('layouts.app')

@section('title', ($seoPage->meta_title ?: $seoPage->title) . ' - ' . config('app.name', 'LiveScores Tips'))
@section('description', $seoPage->meta_description)
@section('keywords', $seoPage->meta_keywords)

@section('page-header')
    <h2 class="bm-page-title">
        {{ $seoPage->title }}
    </h2>
    @if($seoPage->sub_header)
        <p class="bm-page-subheader">
            {{ $seoPage->sub_header }}
        </p>
    @endif
@endsection

@section('content')
    {{-- Filter Toolbar --}}
    <x-filter-toolbar :date="$date" :dateRange="$dateRange" :isLive="$isLive" :liveCount="$liveCount" 
        route="seo.show"
        :routeParams="['slug' => $seoPage->slug]"
        :marketFilters="[]"
    />

    <!-- Predictions List -->
    <div class="bm-match-list mt-4">
        @forelse($predictions as $tip)
            <x-betting-card :matchUrl="route('fixture.show', [$tip->slug ?? $tip->fixture_id, $tip->match_date->format('Y-m-d')])" :time="\Carbon\Carbon::parse($tip->match_time)->format('H:i')"
                :isLive="isset($tip->is_live) && $tip->is_live" :homeTeam="$tip->home_team" :homeTeamLogo="$tip->home_team_logo"
                :awayTeam="$tip->away_team" :awayTeamLogo="$tip->away_team_logo" :homeScore="$tip->home_score"
                :awayScore="$tip->away_score" :probability="$tip->probability" :market="$tip->category" :tip="$tip->tip"
                :odds="$tip->odds" :status="$tip->status" :homeForm="$tip->home_form" :awayForm="$tip->away_form" />
        @empty
            <div class="p-4 text-center text-muted card mb-4" style="background: #1e1e1e; border: none; border-radius: 12px;">
                <p>No predictions available for this date.</p>
            </div>
        @endforelse
    </div>

    <!-- SEO Content - Moved to "Middle/Bottom" and styled like info-article -->
    <div class="bm-card bm-info-article-dynamic mt-5 mb-5">
        <div class="bm-article-content" style="color: #ccc; line-height: 1.8;">
            {!! $seoPage->content !!}
        </div>
    </div>

    {{-- Sports Articles --}}
    @if(isset($latestArticles) && $latestArticles->count() > 0)
        <section class="bm-card mt-5">
            <div class="d-flex align-items-center mb-4">
                <h2 class="bm-section-title mb-0"><i class="fa fa-newspaper-o mr-2 text-warning"></i> Latest Sports News</h2>
                <a href="{{ route('blog.index') }}" class="ml-auto text-warning" style="font-size: 13px; font-weight: 500;">View
                    all <i class="fa fa-chevron-right ml-1"></i></a>
            </div>
            <div class="row">
                @foreach($latestArticles as $article)
                    <div class="col-md-4 mb-3">
                        <div class="bm-article-card h-100">
                            <div class="bm-article-img"
                                style="background-image: url('{{ $article->image ? asset($article->image) : asset('images/logo.png') }}')">
                            </div>
                            <div class="bm-article-body">
                                <span
                                    class="bm-article-date">{{ \Carbon\Carbon::parse($article->published_at)->format('M d, Y') }}</span>
                                <h3 class="bm-article-title"><a
                                        href="{{ route('blog.show', $article->slug) }}">{{ Str::limit($article->title, 50) }}</a>
                                </h3>
                                <p class="bm-article-excerpt">{{ Str::limit($article->excerpt, 80) }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Recent Winnings --}}
    @if(isset($recentWinnings) && $recentWinnings->count() > 0)
        <section class="bm-card mt-4">
            <div class="d-flex align-items-center mb-4">
                <h2 class="bm-section-title mb-0"><i class="fa fa-trophy mr-2 text-warning"></i> Recent Winnings</h2>
                <a href="{{ route('recent-winnings.index') }}" class="ml-auto text-success"
                    style="font-size: 13px; font-weight: 500;">View all <i class="fa fa-chevron-right ml-1"></i></a>
            </div>
            <div class="row">
                @foreach($recentWinnings->take(3) as $winning)
                    <div class="col-md-4 mb-3">
                        <x-winning-card :winning="$winning" />
                    </div>
                @endforeach
            </div>
        </section>
    @endif
@endsection

@push('styles')
    <style>
        .bm-article-content h1,
        .bm-article-content h2,
        .bm-article-content h3 {
            color: #fff;
            margin-top: 1.5rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .bm-article-content h1 {
            font-size: 1.8rem;
        }

        .bm-article-content h2 {
            font-size: 1.5rem;
        }

        .bm-article-content h3 {
            font-size: 1.3rem;
        }

        .bm-article-content p {
            margin-bottom: 1.2rem;
            white-space: pre-wrap;
        }

        .bm-article-content {
            white-space: pre-wrap;
            overflow-wrap: break-word;
        }

        .bm-article-content ul,
        .bm-article-content ol {
            margin-bottom: 1.5rem;
            padding-left: 1.5rem;
        }

        .bm-article-content li {
            margin-bottom: 0.5rem;
        }

        .bm-article-content strong {
            color: #fff;
        }
    </style>
@endpush