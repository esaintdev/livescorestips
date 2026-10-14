class AppConfig {
  AppConfig._();

  /// Base URL of the LiveScores Tips website / API.
  ///
  /// Override at build time with:
  ///   flutter run --dart-define=API_BASE_URL=https://www.livescorestips.com
  static const String apiBaseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'https://www.livescorestips.com',
  );

  static const String websiteUrl = 'https://www.livescorestips.com';

  /// Whether the API is served from the same origin as the website.
  static String get apiUrl => '$apiBaseUrl/api';
}
