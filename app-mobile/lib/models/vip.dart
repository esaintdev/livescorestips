class VipPlan {
  final int id;
  final String category;
  final String name;
  final String? duration;
  final int? durationDays;
  final double price;
  final String? currency;
  final String? badgeText;
  final List<String> features;

  const VipPlan({
    required this.id,
    required this.category,
    required this.name,
    this.duration,
    this.durationDays,
    required this.price,
    this.currency,
    this.badgeText,
    this.features = const [],
  });

  factory VipPlan.fromJson(Map<String, dynamic> json) {
    return VipPlan(
      id: json['id'] as int,
      category: json['category']?.toString() ?? '',
      name: json['name']?.toString() ?? '',
      duration: json['duration']?.toString(),
      durationDays: json['duration_days'] as int?,
      price: (json['price'] is num)
          ? (json['price'] as num).toDouble()
          : double.tryParse(json['price']?.toString() ?? '') ?? 0,
      currency: json['currency']?.toString(),
      badgeText: json['badge_text']?.toString(),
      features: (json['features'] is List)
          ? (json['features'] as List).map((e) => e.toString()).toList()
          : const [],
    );
  }
}

class VipResult {
  final int id;
  final String? date;
  final String? totalOdds;
  final String? status;
  final String? winRateDisplay;
  final List<VipResultItem> items;

  const VipResult({
    required this.id,
    this.date,
    this.totalOdds,
    this.status,
    this.winRateDisplay,
    this.items = const [],
  });

  factory VipResult.fromJson(Map<String, dynamic> json) {
    return VipResult(
      id: json['id'] as int,
      date: json['date']?.toString(),
      totalOdds: json['total_odds']?.toString(),
      status: json['status']?.toString(),
      winRateDisplay: json['win_rate_display']?.toString(),
      items: (json['items'] is List)
          ? (json['items'] as List)
              .whereType<Map>()
              .map((e) => VipResultItem.fromJson(Map<String, dynamic>.from(e)))
              .toList()
          : const [],
    );
  }

  bool get isWon => status == 'won';
}

class VipResultItem {
  final String? leagueName;
  final String? matchTime;
  final String? homeTeam;
  final String? awayTeam;
  final int? homeScore;
  final int? awayScore;
  final String? tip;
  final String? odds;
  final bool? isWon;

  const VipResultItem({
    this.leagueName,
    this.matchTime,
    this.homeTeam,
    this.awayTeam,
    this.homeScore,
    this.awayScore,
    this.tip,
    this.odds,
    this.isWon,
  });

  factory VipResultItem.fromJson(Map<String, dynamic> json) {
    return VipResultItem(
      leagueName: json['league_name']?.toString(),
      matchTime: json['match_time']?.toString(),
      homeTeam: json['home_team']?.toString(),
      awayTeam: json['away_team']?.toString(),
      homeScore: _int(json['home_score']),
      awayScore: _int(json['away_score']),
      tip: json['tip']?.toString(),
      odds: json['odds']?.toString(),
      isWon: json['is_won'] as bool?,
    );
  }
}

int? _int(dynamic v) {
  if (v == null) return null;
  if (v is int) return v;
  if (v is num) return v.toInt();
  return int.tryParse(v.toString());
}
