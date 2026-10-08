<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="x-ua-compatible" content="ie=edge">

    <link rel="canonical" href="{{ $canonical ?? url()->current() }}">

    <title>{{ $title ?? config('app.name', 'LiveScores Tips') . ' | Football Predictions Today & Betting Tips' }}</title>

    <meta name="description"
        content="{{ meta_description($description ?? 'Get today\'s football predictions, betting tips, live scores, fixtures, results, match analysis, BTTS tips, Over 2.5 goals and correct score tips.') }}">

    <meta name="keywords"
        content="{{ $keywords ?? 'football predictions today, betting tips, football tips, ' . config('app.name', 'LiveScores Tips') . ', free football predictions, BTTS predictions, over 2.5 goals, correct score tips' }}">

    <meta name="author" content="{{ config('app.name', 'LiveScores Tips') }}">

    <meta property="og:site_name" content="{{ config('app.name', 'LiveScores Tips') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="{{ request()->path() === '/' ? 'website' : 'article' }}">
    <meta property="og:title" content="{{ $ogTitle ?? ($title ?? config('app.name', 'LiveScores Tips') . ' | Football Predictions Today') }}">
    <meta property="og:description"
        content="{{ $ogDescription ?? ($description ?? 'Get today\'s football predictions, betting tips, live scores, fixtures, results and match analysis from ' . config('app.name', 'LiveScores Tips') . '.') }}">
    <meta property="og:image" content="{{ $ogImage ?? asset('images/icon.png') }}">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => config('app.name', 'LiveScores Tips'),
        'alternateName' => [
            'LiveScoresTips',
            'Live Scores Tips',
        ],
        'url' => url('/'),
        'description' => 'Free football predictions, betting tips, live scores, fixtures, results and match analysis.'
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>

    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => config('app.name', 'LiveScores Tips'),
        'url' => url('/'),
        'logo' => asset('images/icon.png')
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>

    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/icon.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/icon.png') }}">
    <link rel="manifest" href="{{ asset('webmanifest.json') }}">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="google-site-verification" content="Kks1fd1gwqXihNHAUeXZWthvugwbVcQyU1EFp1KyAKA" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Sofia+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,400&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Quantico" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Manrope" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Itim" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/stylesheet.css') }}">
    <link rel="stylesheet" href="{{ asset('css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/jsRapCalendar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dark-theme.css') }}?v=1.0">
    <link rel="stylesheet" href="{{ asset_v('css/livescore-theme.css') }}">

    <script type="text/javascript">
        // Toggle text links visibility
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.getElementById('textLinksContainer');
            const button = document.getElementById('toggleTextLinks');

            if (container && button) {
                const lineHeight = parseFloat(window.getComputedStyle(container).lineHeight);
                const maxHeight = lineHeight * 2;

                if (container.scrollHeight > maxHeight) {
                    button.style.display = 'block';

                    button.addEventListener('click', function () {
                        container.classList.toggle('expanded');
                        button.classList.toggle('expanded');

                        if (container.classList.contains('expanded')) {
                            button.style.display = 'none';
                        } else {
                            button.style.display = 'block';
                        }
                    });
                } else {
                    button.style.display = 'none';
                }
            }
        });

        function isPlayingIO(url) {
            return url.includes("playing.io");
        }

        function preventRedirect() {
            if (isPlayingIO(window.location.href)) {
                window.stop();
                showAlert("Redirection to playing.io is blocked on this website.");
            }
        }

        window.onload = preventRedirect;
    </script>

    @if(isset($headScripts))
        @foreach($headScripts as $script)
            {!! $script->code !!}
        @endforeach
    @endif
</head>