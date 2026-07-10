@extends('emails.layout')

@section('content')
    <!-- Welcome Section -->
    <div style="text-align: center;">
        <h1 style="margin: 0 0 20px; color: #092337; font-size: 28px; font-weight: 700;">
            Welcome to the Team! 🎯
        </h1>
        <p style="margin: 0 0 15px; color: #333333; font-size: 16px; line-height: 1.6;">
            Thank you for subscribing to <strong>LiveScores Tips</strong>!
        </p>
        <p style="margin: 0; color: #666666; font-size: 15px; line-height: 1.6;">
            You've just joined thousands of smart bettors who receive daily free football prediction tips directly to their
            inbox.
        </p>
    </div>

    <!-- Benefits Section -->
    <div style="margin-top: 30px;">
        <table role="presentation" style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 15px; background-color: #f8f9fa; border-radius: 8px; margin-bottom: 10px;">
                    <table role="presentation" style="width: 100%;">
                        <tr>
                            <td style="width: 50px; vertical-align: top;">
                                <div
                                    style="width: 40px; height: 40px; background-color: #14588B; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                    ⚽
                                </div>
                            </td>
                            <td style="vertical-align: top; padding-left: 15px;">
                                <h3 style="margin: 0 0 8px; color: #092337; font-size: 16px; font-weight: 600;">
                                    Expert Analysis
                                </h3>
                                <p style="margin: 0; color: #666666; font-size: 14px; line-height: 1.5;">
                                    Get daily predictions backed by in-depth match analysis and team statistics
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td style="padding: 15px; background-color: #f8f9fa; border-radius: 8px; margin-top: 10px;">
                    <table role="presentation" style="width: 100%;">
                        <tr>
                            <td style="width: 50px; vertical-align: top;">
                                <div
                                    style="width: 40px; height: 40px; background-color: #092337; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                    📊
                                </div>
                            </td>
                            <td style="vertical-align: top; padding-left: 15px;">
                                <h3 style="margin: 0 0 8px; color: #092337; font-size: 16px; font-weight: 600;">
                                    Winning Predictions
                                </h3>
                                <p style="margin: 0; color: #666666; font-size: 14px; line-height: 1.5;">
                                    Access high-probability tips with detailed odds and betting insights
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td style="padding: 15px; background-color: #f8f9fa; border-radius: 8px; margin-top: 10px;">
                    <table role="presentation" style="width: 100%;">
                        <tr>
                            <td style="width: 50px; vertical-align: top;">
                                <div
                                    style="width: 40px; height: 40px; background-color: #092337; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                    🎁
                                </div>
                            </td>
                            <td style="vertical-align: top; padding-left: 15px;">
                                <h3 style="margin: 0 0 8px; color: #092337; font-size: 16px; font-weight: 600;">
                                    100% Free
                                </h3>
                                <p style="margin: 0; color: #666666; font-size: 14px; line-height: 1.5;">
                                    All our tips are completely free - no hidden fees, no premium tiers
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <!-- CTA Button -->
    <div style="text-align: center; margin-top: 30px;">
        <a href="{{ url('/') }}"
            style="display: inline-block; padding: 16px 40px; background: linear-gradient(135deg, #092337 0%, #14588B 100%); color: #ffffff; text-decoration: none; border-radius: 50px; font-size: 16px; font-weight: 600; box-shadow: 0 4px 12px rgba(9, 35, 55, 0.3);">
            View Latest Predictions →
        </a>
        <p style="margin: 20px 0 0; color: #999999; font-size: 13px;">
            Start winning today with our expert football tips!
        </p>
    </div>
@endsection