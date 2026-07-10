@extends('emails.layout')

@section('content')
    <!-- New Prediction Announcement -->
    <div style="text-align: center;">
        <h1 style="margin: 0 0 10px; color: #092337; font-size: 28px; font-weight: 700;">
            🎯 New Prediction Alert!
        </h1>
        <p style="margin: 0 0 20px; color: #666666; font-size: 14px;">
            We've just posted a new tip for you
        </p>
    </div>

    <!-- Match Details Card -->
    <div
        style="background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); border-radius: 12px; padding: 25px; margin: 20px 0; border-left: 4px solid #14588B;">
        <!-- League Info -->
        <div style="text-align: center; margin-bottom: 20px;">
            <p style="margin: 0; color: #666666; font-size: 13px; text-transform: uppercase; letter-spacing: 1px;">
                {{ $prediction->league_name }}
            </p>
        </div>

        <!-- Teams -->
        <div style="text-align: center; margin-bottom: 20px;">
            <h2 style="margin: 0; color: #092337; font-size: 22px; font-weight: 700; line-height: 1.4;">
                {{ $prediction->home_team }}
                <span style="color: #C1392A; font-size: 18px; margin: 0 10px;">VS</span>
                {{ $prediction->away_team }}
            </h2>
        </div>

        <!-- Match Date & Time -->
        <div style="text-align: center; margin-bottom: 20px;">
            <p style="margin: 0; color: #666666; font-size: 14px;">
                📅 {{ \Carbon\Carbon::parse($prediction->match_date)->format('l, F j, Y') }}
                <br>
                ⏰ {{ $prediction->match_time }}
            </p>
        </div>

        <!-- Divider -->
        <div style="height: 1px; background: linear-gradient(90deg, transparent, #e0e0e0, transparent); margin: 20px 0;">
        </div>

        <!-- Prediction Details -->
        <div style="background: #ffffff; border-radius: 8px; padding: 20px; margin-top: 20px;">
            <table role="presentation" style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 10px; text-align: center; border-right: 1px solid #e0e0e0;">
                        <p style="margin: 0 0 5px; color: #999999; font-size: 12px; text-transform: uppercase;">Our Tip</p>
                        <p style="margin: 0; color: #092337; font-size: 18px; font-weight: 700;">{{ $prediction->tip }}</p>
                    </td>
                    <td style="padding: 10px; text-align: center; border-right: 1px solid #e0e0e0;">
                        <p style="margin: 0 0 5px; color: #999999; font-size: 12px; text-transform: uppercase;">Odds</p>
                        <p style="margin: 0; color: #C1392A; font-size: 18px; font-weight: 700;">{{ $prediction->odds }}</p>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <p style="margin: 0 0 5px; color: #999999; font-size: 12px; text-transform: uppercase;">Win
                            Probability</p>
                        <p style="margin: 0; color: #14588B; font-size: 18px; font-weight: 700;">
                            {{ $prediction->probability }}%</p>
                    </td>
                </tr>
            </table>
        </div>

        @if($prediction->category)
            <div style="text-align: center; margin-top: 15px;">
                <span
                    style="display: inline-block; background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 6px 16px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                    {{ $prediction->category }}
                </span>
            </div>
        @endif
    </div>

    <!-- Team Forms (if available) -->
    @if($prediction->home_form || $prediction->away_form)
        <div style="margin: 20px 0;">
            <h3 style="margin: 0 0 15px; color: #092337; font-size: 16px; font-weight: 600; text-align: center;">
                Recent Form
            </h3>
            <table role="presentation" style="width: 100%; border-collapse: collapse;">
                <tr>
                    @if($prediction->home_form)
                        <td style="width: 50%; padding: 10px; text-align: center;">
                            <p style="margin: 0 0 8px; color: #666666; font-size: 13px; font-weight: 600;">
                                {{ $prediction->home_team }}</p>
                            <p style="margin: 0; font-size: 14px; letter-spacing: 3px;">{{ $prediction->home_form }}</p>
                        </td>
                    @endif
                    @if($prediction->away_form)
                        <td
                            style="width: 50%; padding: 10px; text-align: center; {{ $prediction->home_form ? 'border-left: 1px solid #e0e0e0;' : '' }}">
                            <p style="margin: 0 0 8px; color: #666666; font-size: 13px; font-weight: 600;">
                                {{ $prediction->away_team }}</p>
                            <p style="margin: 0; font-size: 14px; letter-spacing: 3px;">{{ $prediction->away_form }}</p>
                        </td>
                    @endif
                </tr>
            </table>
        </div>
    @endif

    <!-- CTA Button -->
    <div style="text-align: center; margin-top: 30px;">
        <a href="{{ url('/') }}"
            style="display: inline-block; padding: 16px 40px; background: linear-gradient(135deg, #092337 0%, #14588B 100%); color: #ffffff; text-decoration: none; border-radius: 50px; font-size: 16px; font-weight: 600; box-shadow: 0 4px 12px rgba(9, 35, 55, 0.3);">
            View All Predictions →
        </a>
        <p style="margin: 15px 0 0; color: #999999; font-size: 13px;">
            Place your bet and good luck! 🍀
        </p>
    </div>

    <!-- Disclaimer -->
    <div
        style="margin-top: 30px; padding: 15px; background: rgba(251, 191, 36, 0.1); border-left: 3px solid #fbbf24; border-radius: 4px;">
        <p style="margin: 0; color: #666666; font-size: 12px; line-height: 1.6;">
            <strong style="color: #fbbf24;">⚠️ Disclaimer:</strong> Please gamble responsibly. This is a prediction based on
            analysis and statistics. No guarantee of winning. Only bet what you can afford to lose.
        </p>
    </div>
@endsection