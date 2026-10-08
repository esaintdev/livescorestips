<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? config('app.name', 'LiveScores Tips') }}</title>
</head>

<body
    style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f4;">
    <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f4f4f4;">
        <tr>
            <td align="center" style="padding: 20px 0;">
                <!-- Main Container -->
                <table role="presentation"
                    style="width: 600px; max-width: 100%; border-collapse: collapse; background-color: #ffffff; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); border-radius: 8px; overflow: hidden;">

                    <!-- Header with Logo -->
                    <tr>
                        <td
                            style="background: linear-gradient(135deg, #092337 0%, #14588B 100%); padding: 40px 20px; text-align: center;">
                            <img src="{{ url('images/logo.png') }}" alt="{{ config('app.name', 'LiveScores Tips') }}"
                                style="max-width: 200px; height: auto; display: block; margin: 0 auto;">
                        </td>
                    </tr>

                    <!-- Main Content Area -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            @yield('content')
                        </td>
                    </tr>

                    <!-- Divider -->
                    <tr>
                        <td style="padding: 0 30px;">
                            <div style="height: 1px; background-color: #e0e0e0;"></div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 30px; text-align: center; background-color: #f8f9fa;">
                            <p style="margin: 0 0 15px; color: #092337; font-size: 16px; font-weight: 600;">
                                {{ config('app.name', 'LiveScores Tips') }}
                            </p>
                            <p style="margin: 0 0 10px; color: #666666; font-size: 14px;">
                                Daily Free Football Prediction Tips
                            </p>
                            <p style="margin: 0 0 15px; color: #666666; font-size: 13px;">
                                <a href="mailto:livescorestips@gmail.com"
                                    style="color: #14588B; text-decoration: none;">livescorestips@gmail.com</a>
                            </p>
                            <p style="margin: 0 0 20px; color: #666666; font-size: 13px;">
                                <a href="{{ url('/') }}"
                                    style="color: #092337; text-decoration: none;">livescorestips.com</a>
                            </p>
                        </td>
                    </tr>

                    <!-- Bottom Brand Strip -->
                    <tr>
                        <td
                            style="background: linear-gradient(135deg, #092337 0%, #14588B 100%); padding: 15px; text-align: center;">
                            <p style="margin: 0; color: #ffffff; font-size: 12px; font-weight: 600;">
                                YOUR WINNING STARTS HERE
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>

</html>