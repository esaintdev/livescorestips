/// A single football prediction, normalised from the various API shapes.
///
/// The website exposes predictions in two JSON shapes:
///  * `GET /api/predictions`  -> nested `league`/`home_team`/`away_team`/`score`/`prediction`
///  * `GET /api/categories/{slug}` -> flat fields
///
/// This model parses both.
class Prediction {
  final int? id;
  final int? fixtureId;
  final String? slug;

  final int? leagueId;
  final String? leagueName;
  final String? leagueCountry;
  final String? leagueLogo;

  final String? homeTeam;
  final String? homeTeamLogo;
  final String? awayTeam;
  final String? awayTeamLogo;

  final int? homeScore;
  final int? awayScore;

  final String? matchDate;
  final String? matchTime;

  final String? tip;
  final String? category;
  final String? odds;
  final String? probability;

  final String? status;
  final String? url;

  const Prediction({
    this.id,
    this.fixtureId,
    this.slug,
    this.leagueId,
    this.leagueName,
    this.leagueCountry,
    this.leagueLogo,
    this.homeTeam,
    this.homeTeamLogo,
    this.awayTeam,
    this.awayTeamLogo,
    this.homeScore,
    this.awayScore,
    this.matchDate,
    this.matchTime,
    this.tip,
    this.category,
    this.odds,
    this.probability,
    this.status,
    this.url,
  });

  factory Prediction.fromAny(Map<String, dynamic> json) {
    final bool nested = json['home_team'] is Map;

    final Map<String, dynamic> league = _asMap(json['league']);
    final Map<String, dynamic> home =
        nested ? _asMap(json['home_team']) : const {};
    final Map<String, dynamic> away =
        nested ? _asMap(json['away_team']) : const {};
    final Map<String, dynamic> score = _asMap(json['score']);
    final Map<String, dynamic> pred = _asMap(json['prediction']);

    return Prediction(
      id: _asInt(json['id']),
      fixtureId: _asInt(json['fixture_id']),
      slug: _asString(json['slug']),
      leagueId: _asInt(league['id']),
      leagueName: _asString(league['name']),
      leagueCountry: _asString(league['country']),
      leagueLogo: _asString(league['logo']),
      homeTeam: nested
          ? _asString(home['name'])
          : _asString(json['home_team']),
      homeTeamLogo: nested
          ? _asString(home['logo'])
          : _asString(json['home_team_logo']),
      awayTeam: nested
          ? _asString(away['name'])
          : _asString(json['away_team']),
      awayTeamLogo: nested
          ? _asString(away['logo'])
          : _asString(json['away_team_logo']),
      homeScore: nested ? _asInt(score['home']) : _asInt(json['home_score']),
      awayScore: nested ? _asInt(score['away']) : _asInt(json['away_score']),
      matchDate: _asString(json['match_date']),
      matchTime: _normaliseTime(_asString(json['match_time'])),
      tip: nested ? _asString(pred['tip']) : _asString(json['tip']),
      category: nested
          ? _asString(pred['category'])
          : _asString(json['category']),
      odds: nested ? _asString(pred['odds']) : _asString(json['odds']),
      probability: nested
          ? _asString(pred['probability'])
          : _asString(json['probability']),
      status: _asString(json['status']),
      url: _asString(json['url']),
    );
  }

  /// Display label for the tip (used inside the tip badge).
  String get tipLabel => (tip == null || tip!.isEmpty) ? '-' : tip!;

  /// Formatted probability, e.g. "61.73%".
  String get probabilityLabel {
    final p = probability;
    if (p == null || p.isEmpty) return '-';
    return '$p%';
  }

  double? get probabilityValue => double.tryParse(probability ?? '');

  bool get hasScore =>
      homeScore != null && awayScore != null;

  String get scoreLabel => hasScore ? '$homeScore-$awayScore' : '';

  bool get isTerminal =>
      status == 'won' || status == 'lost' || status == 'void';

  /// Probability badge colour tier (matches the website thresholds).
  String get probTier {
    final v = probabilityValue;
    if (v == null) return 'low';
    if (v >= 80) return 'high';
    if (v >= 50) return 'med';
    return 'low';
  }
}

String? _asString(dynamic v) {
  if (v == null) return null;
  if (v is String) return v;
  return v.toString();
}

int? _asInt(dynamic v) {
  if (v == null) return null;
  if (v is int) return v;
  if (v is num) return v.toInt();
  return int.tryParse(v.toString());
}

Map<String, dynamic> _asMap(dynamic v) {
  if (v is Map<String, dynamic>) return v;
  if (v is Map) return Map<String, dynamic>.from(v);
  return const {};
}

/// The DB stores match times like "02:00" or "02:00:00". Normalise to "HH:mm".
String _normaliseTime(String? raw) {
  if (raw == null || raw.isEmpty) return '';
  final parts = raw.split(':');
  if (parts.length >= 2) return '${parts[0]}:${parts[1]}';
  return raw;
}
