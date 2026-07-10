<!DOCTYPE html>
<html lang="en">

<x-layout.head title="Recent Winnings - LiveScores Tips"
    description="View our recent winning predictions. Proof of our accuracy and expertise in football betting tips."
    keywords="winning predictions, past results, betting success" />

<body class="antialiased">
    <x-preloader />

    <x-layout.navbar />

    {{-- Hero Section --}}
    <section style="background: linear-gradient(135deg, #04145d 0%, #0c7ad5 100%); padding: 40px 0;">
        <div class="container">
            <div class="text-center py-4">
                <h1 style="color: #ffffff; font-size: 2.5rem; font-weight: 700; margin-bottom: 15px;">
                    <i class="fa fa-trophy mr-2"></i>Recent Winnings
                </h1>
                <p style="color: rgba(255, 255, 255, 0.9); font-size: 1.1rem;">
                    Our track record speaks for itself. Here are our recent winning predictions.
                </p>
            </div>
        </div>
    </section>

    {{-- Stats Bar --}}
    <section style="background: #04145d; padding: 20px 0;">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-4 mb-3 mb-md-0">
                    <h3 style="color: #fff; margin: 0; font-size: 2rem; font-weight: 700;">{{ $winnings->total() }}+
                    </h3>
                    <p style="color: rgba(255,255,255,0.8); margin: 0;">Total Winning Tips</p>
                </div>
                <div class="col-md-4 mb-3 mb-md-0">
                    <h3 style="color: #0c7ad5; margin: 0; font-size: 2rem; font-weight: 700;">95%+</h3>
                    <p style="color: rgba(255,255,255,0.8); margin: 0;">Accuracy Rate</p>
                </div>
                <div class="col-md-4">
                    <h3 style="color: #d35244; margin: 0; font-size: 2rem; font-weight: 700;">Daily</h3>
                    <p style="color: rgba(255,255,255,0.8); margin: 0;">Updated Results</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Winnings List --}}
    <section class="py-5">
        <div class="container">
            @if($winnings->count() > 0)
                <div class="row">
                    @foreach($winnings as $winning)
                        <div class="col-12 mb-2">
                            <x-winning-card :winning="$winning" />
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="d-flex justify-content-center mt-4">
                    {{ $winnings->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <h3 class="text-muted">No winning predictions yet.</h3>
                    <p class="text-muted">Check back soon for our winning results!</p>
                    <a href="{{ route('home') }}" class="btn btn-primary mt-3">View Today's Tips</a>
                </div>
            @endif
        </div>
    </section>

    {{-- CTA Section --}}
    <section style="background: linear-gradient(135deg, #04145d 0%, #0c7ad5 100%); padding: 50px 0;">
        <div class="container text-center">
            <h2 style="color: white; font-weight: 700; margin-bottom: 20px;">Want Access to Premium Winning Tips?</h2>
            <p style="color: rgba(255,255,255,0.9); margin-bottom: 30px;">Join our VIP subscription for exclusive
                high-accuracy predictions!</p>
            <a href="{{ route('vip.index') }}" class="btn btn-lg"
                style="background: linear-gradient(135deg, #d35244 0%, #b8322a 100%); color: white; font-weight: 700; padding: 15px 40px; border-radius: 30px;">
                View VIP Plans <i class="fa fa-arrow-right ml-2"></i>
            </a>
        </div>
    </section>

    {{-- Scripts --}}
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/popper.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>

    <x-layout.footer />

    <style>
        .pagination .page-link {
            color: #04145d;
        }

        .pagination .page-item.active .page-link {
            background-color: #04145d;
            border-color: #04145d;
        }
    </style>
</body>

</html>