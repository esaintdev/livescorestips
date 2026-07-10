@extends('emails.layout')

@section('content')
    <!-- Batched Predictions Announcement -->
    <div style="text-align: center;">
        <h1 style="margin: 0 0 10px; color: #092337; font-size: 28px; font-weight: 700;">
            🎯 {{ $predictions->count() }} New Prediction{{ $predictions->count() > 1 ? 's' : '' }} Alert!
        </h1>
        <p style="margin: 0 0 20px; color: #666666; font-size: 14px;">
            We've just posted {{ $predictions->count() }} new tip{{ $predictions->count() > 1 ? 's' : '' }} for you
        </p>
    </div>

    @foreach($predictions as $prediction)
        <!-- Match Details Card -->
        <div
            style="background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); border-radius: 12px; padding: 20px; margin: {{ $loop->first ? '20px' : '15px' }} 0; border-left: 4px solid #14588B;">
            <!-- League Info -->
            <div style="text-align: center; margin-bottom: 15px;">
                <p style="margin: 0; color: #666666; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">
                    {{ $prediction->league_name }}
                </p>
            </div>

            <!-- Teams -->
            <div style="text-align: center; margin-bottom: 15px;">
                <h2 style="margin: 0; color: #092337; font-size: 20px; font-weight: 700; line-height: 1.4;">
                    {{ $prediction->home_team }}
                    <span style="color: #C1392A; font-size: 16px; margin: 0 8px;">VS</span>
                    {{ $prediction->away_team }}
                </h2>
            </div>

            <!-- Match Date & Time -->
            <div style="text-align: center; margin-bottom: 15px;">
                <p style="margin: 0; color: #666666; font-size: 13px;">
                    📅 {{ \Carbon\Carbon::parse($prediction->match_date)->format('D, M j, Y') }}
                    &nbsp;&nbsp;
                    ⏰ {{ $prediction->match_time }}
                </p>
            </div>

            <!-- Divider -->
            <div style="height: 1px; background: linear-gradient(90deg, transparent, #e0e0e0, transparent); margin: 15px 0;">
            </div>

            <!-- Prediction Details -->
            <div style="background: #ffffff; border-radius: 8px; padding: 15px; margin-top: 15px;">
                <table role="presentation" style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px; text-align: center; border-right: 1px solid #e0e0e0;">
                            <p style="margin: 0 0 5px; color: #999999; font-size: 11px; text-transform: uppercase;">Our Tip
                            </p>
                            <p style="margin: 0; color: #092337; font-size: 16px; font-weight: 700;">{{ $prediction->tip }}
                            </p>
                        </td>
                        <td style="padding: 8px; text-align: center; border-right: 1px solid #e0e0e0;">
                            <p style="margin: 0 0 5px; color: #999999; font-size: 11px; text-transform: uppercase;">Odds</p>
                            <p style="margin: 0; color: #C1392A; font-size: 16px; font-weight: 700;">{{ $prediction->odds }}
                            </p>
                        </td>
                        <td style="padding: 8px; text-align: center;">
                            <p style="margin: 0 0 5px; color: #999999; font-size: 11px; text-transform: uppercase;">Win Prob.
                            </p>
                            <p style="margin: 0; color: #14588B; font-size: 16px; font-weight: 700;">
                                {{ $prediction->probability }}%
                            </p>
                        </td>
                    </tr>
                </table>
            </div>

            @if($prediction->category)
                <div style="text-align: center; margin-top: 12px;">
                    <span
                        style="display: inline-block; background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 5px 14px; border-radius: 20px; font-size: 11px; font-weight: 600;">
                        {{ $prediction->category }}
                    </span>
                </div>
            @endif
        </div>

        @if(!$loop->last)
            <!-- Separator between predictions -->
            <div style="height: 1px; background: #e0e0e0; margin: 15px 0;"></div>
        @endif
    @endforeach

    <!-- CTA Button -->
    <div style="text-align: center; margin-top: 30px;">
        <a href="{{ url('/') }}"
            style="display: inline-block; padding: 16px 40px; background: linear-gradient(135deg, #092337 0%, #14588B 100%); color: #ffffff; text-decoration: none; border-radius: 50px; font-size: 16px; font-weight: 600; box-shadow: 0 4px 12px rgba(9, 35, 55, 0.3);">
            View All Predictions →
        </a>
        <p style="margin: 15px 0 0; color: #999999; font-size: 13px;">
            Place your bets and good luck! 🍀
        </p>
    </div>

    <!-- Disclaimer -->
    <div
        style="margin-top: 30px; padding: 15px; background: rgba(251, 191, 36, 0.1); border-left: 3px solid #fbbf24; border-radius: 4px;">
        <p style="margin: 0; color: #666666; font-size: 12px; line-height: 1.6;">
            <strong style="color: #fbbf24;">⚠️ Disclaimer:</strong> Please gamble responsibly. These are predictions based
            on
            analysis and statistics. No guarantee of winning. Only bet what you can afford to lose.
        </p>
    </div>
@endsection