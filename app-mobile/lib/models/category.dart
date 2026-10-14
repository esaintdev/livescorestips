class PredictionCategory {
  final String slug;
  final String name;
  final String? url;

  const PredictionCategory({
    required this.slug,
    required this.name,
    this.url,
  });

  factory PredictionCategory.fromJson(Map<String, dynamic> json) {
    return PredictionCategory(
      slug: json['slug']?.toString() ?? '',
      name: json['name']?.toString() ?? '',
      url: json['url']?.toString(),
    );
  }

  /// Human-friendly short label used in navigation (matches the web labels).
  String get shortLabel => _shortLabels[slug] ?? name;

  static const Map<String, String> _shortLabels = {
    'over-2-5-goals': 'Over 2.5 Goals',
    'over-1-5-goals': 'Over 1.5 Goals',
    'btts-gg': 'BTTS/GG',
    'double-chance': 'Double Chance',
    'draws': 'Draws',
    'home-wins': 'Home Wins',
    'away-wins': 'Away Wins',
    'correct-score': 'Correct Score',
  };
}
