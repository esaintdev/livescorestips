@extends('admin.layout')

@section('page-title', 'Update Prediction Status')

@section('content')
    <div class="as-page">
        <section class="as-section">
            <div class="as-section-header">
                <div class="as-section-icon">
                    <i class="fa fa-refresh"></i>
                </div>
                <div>
                    <h2 class="as-section-title">Manual Status Update</h2>
                    <p class="as-section-subtitle">Check and update pending predictions</p>
                </div>
            </div>
            <div class="as-card">
                <div class="as-card-body">
                    <p class="as-hint" style="font-size:14px;margin-bottom:20px;">
                        Click the button below to manually check and update the status of all pending predictions.
                        This process connects to the football API to verify match results.
                    </p>

                    <form action="{{ route('admin.predictions.update-status.run') }}" method="POST">
                        @csrf
                        <button type="submit" class="as-btn">
                            <i class="fa fa-refresh"></i> Run Update Now
                        </button>
                    </form>

                    @if(session('command_output'))
                        <div class="mt-4">
                            <div style="font-size:13px;font-weight:700;color:rgba(255,255,255,0.5);text-transform:uppercase;letter-spacing:0.04em;margin-bottom:8px;">Output:</div>
                            <pre style="background:rgba(0,0,0,0.3);border:1px solid rgba(255,255,255,0.06);border-radius:12px;padding:16px;max-height:300px;overflow-y:auto;color:#10b981;font-size:13px;font-family:'SF Mono','Menlo','Monaco','Courier New',monospace;">{{ session('command_output') }}</pre>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>
@endsection
