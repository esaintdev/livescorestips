class Winning {
  final int? id;
  final String? slug;
  final String? league;
  final String? homeTeam;
  final String? homeTeamLogo;
  final String? awayTeam;
  final String? awayTeamLogo;
  final int? homeScore;
  final int? awayScore;
  final String? matchDate;
  final String? tip;
  final String? category;
  final String? odds;
  final String? status;

  const Winning({
    this.id,
    this.slug,
    this.league,
    this.homeTeam,
    this.homeTeamLogo,
    this.awayTeam,
    this.awayTeamLogo,
    this.homeScore,
    this.awayScore,
    this.matchDate,
    this.tip,
    this.category,
    this.odds,
    this.status,
  });

  factory Winning.fromJson(Map<String, dynamic> json) {
    return Winning(
      id: json['id'] as int?,
      slug: json['slug']?.toString(),
      league: json['league']?.toString(),
      homeTeam: json['home_team']?.toString(),
      homeTeamLogo: json['home_team_logo']?.toString(),
      awayTeam: json['away_team']?.toString(),
      awayTeamLogo: json['away_team_logo']?.toString(),
      homeScore: _int(json['home_score']),
      awayScore: _int(json['away_score']),
      matchDate: json['match_date']?.toString(),
      tip: json['tip']?.toString(),
      category: json['category']?.toString(),
      odds: json['odds']?.toString(),
      status: json['status']?.toString(),
    );
  }
}

int? _int(dynamic v) {
  if (v == null) return null;
  if (v is int) return v;
  if (v is num) return v.toInt();
  return int.tryParse(v.toString());
}
