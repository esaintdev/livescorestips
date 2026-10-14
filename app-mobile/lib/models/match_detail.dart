class MatchDetail {
  final int? id;
  final int? fixtureId;
  final String? slug;

  final int? leagueId;
  final String? leagueName;
  final String? leagueCountry;
  final String? leagueLogo;
  final String? leagueSeason;

  final int? homeTeamId;
  final String? homeTeam;
  final String? homeTeamLogo;
  final int? awayTeamId;
  final String? awayTeam;
  final String? awayTeamLogo;

  final int? homeScore;
  final int? awayScore;

  final String? matchDate;
  final String? matchTime;
  final String? statusShort;
  final int? statusElapsed;
  final bool isLive;
  final String? venue;
  final String? referee;

  final MatchPrediction? prediction;
  final String? result;
  final String? resultSummary;

  final List<String> homeForm;
  final List<String> awayForm;
  final List<H2HMatch> headToHead;
  final List<StandingRow> standings;
  final TeamStatsPair? teamStats;

  final String? analysis;
  final List<String> factors;
  final List<RelatedPrediction> related;
  final String? categorySlug;
  final List<MatchComment> comments;

  const MatchDetail({
    this.id,
    this.fixtureId,
    this.slug,
    this.leagueId,
    this.leagueName,
    this.leagueCountry,
    this.leagueLogo,
    this.leagueSeason,
    this.homeTeamId,
    this.homeTeam,
    this.homeTeamLogo,
    this.awayTeamId,
    this.awayTeam,
    this.awayTeamLogo,
    this.homeScore,
    this.awayScore,
    this.matchDate,
    this.matchTime,
    this.statusShort,
    this.statusElapsed,
    this.isLive = false,
    this.venue,
    this.referee,
    this.prediction,
    this.result,
    this.resultSummary,
    this.homeForm = const [],
    this.awayForm = const [],
    this.headToHead = const [],
    this.standings = const [],
    this.teamStats,
    this.analysis,
    this.factors = const [],
    this.related = const [],
    this.categorySlug,
    this.comments = const [],
  });

  factory MatchDetail.fromJson(Map<String, dynamic> json) {
    final league = _map(json['league']);
    final home = _map(json['home_team']);
    final away = _map(json['away_team']);
    final score = _map(json['score']);
    final pred = _map(json['prediction']);
    final stats = _map(json['team_stats']);

    return MatchDetail(
      id: _int(json['id']),
      fixtureId: _int(json['fixture_id']),
      slug: _str(json['slug']),
      leagueId: _int(league['id']),
      leagueName: _str(league['name']),
      leagueCountry: _str(league['country']),
      leagueLogo: _str(league['logo']),
      leagueSeason: _str(league['season']),
      homeTeamId: _int(home['id']),
      homeTeam: _str(home['name']),
      homeTeamLogo: _str(home['logo']),
      awayTeamId: _int(away['id']),
      awayTeam: _str(away['name']),
      awayTeamLogo: _str(away['logo']),
      homeScore: _int(score['home']),
      awayScore: _int(score['away']),
      matchDate: _str(json['match_date']),
      matchTime: _str(json['match_time']),
      statusShort: _str(json['status_short']),
      statusElapsed: _int(json['status_elapsed']),
      isLive: json['is_live'] == true,
      venue: _str(json['venue']),
      referee: _str(json['referee']),
      prediction: pred.isEmpty ? null : MatchPrediction.fromJson(pred),
      result: _str(json['result']),
      resultSummary: _str(json['result_summary']),
      homeForm: _strList(json['home_form']),
      awayForm: _strList(json['away_form']),
      headToHead: _list(json['head_to_head'], H2HMatch.fromJson),
      standings: _list(json['standings'], StandingRow.fromJson),
      teamStats: stats.isEmpty ? null : TeamStatsPair.fromJson(stats),
      analysis: _str(json['analysis']),
      factors: _strList(json['factors']),
      related: _list(json['related'], RelatedPrediction.fromJson),
      categorySlug: _str(json['category_slug']),
      comments: _list(json['comments'], MatchComment.fromJson),
    );
  }

  bool get hasScore => homeScore != null && awayScore != null;
  String get scoreLabel => hasScore ? '$homeScore-$awayScore' : '';
  String get statusLabel => isLive
      ? (statusElapsed != null ? '$statusElapsed\'' : 'LIVE')
      : (statusShort == 'FT' ? 'FT' : '');
}

class MatchPrediction {
  final String? tip;
  final String? category;
  final String? odds;
  final String? probability;
  final String? status;

  const MatchPrediction({this.tip, this.category, this.odds, this.probability, this.status});

  factory MatchPrediction.fromJson(Map<String, dynamic> json) => MatchPrediction(
        tip: _str(json['tip']),
        category: _str(json['category']),
        odds: _str(json['odds']),
        probability: _str(json['probability']),
        status: _str(json['status']),
      );
}

class H2HMatch {
  final String? date;
  final String? homeTeam;
  final String? awayTeam;
  final int? homeScore;
  final int? awayScore;

  const H2HMatch({this.date, this.homeTeam, this.awayTeam, this.homeScore, this.awayScore});

  factory H2HMatch.fromJson(Map<String, dynamic> json) => H2HMatch(
        date: _str(json['date']),
        homeTeam: _str(json['home_team']),
        awayTeam: _str(json['away_team']),
        homeScore: _int(json['home_score']),
        awayScore: _int(json['away_score']),
      );
}

class StandingRow {
  final int? rank;
  final int? teamId;
  final String? teamName;
  final String? teamLogo;
  final int? played;
  final int? win;
  final int? draw;
  final int? lose;
  final int? goalsFor;
  final int? goalsAgainst;
  final int? points;

  const StandingRow({
    this.rank,
    this.teamId,
    this.teamName,
    this.teamLogo,
    this.played,
    this.win,
    this.draw,
    this.lose,
    this.goalsFor,
    this.goalsAgainst,
    this.points,
  });

  factory StandingRow.fromJson(Map<String, dynamic> json) => StandingRow(
        rank: _int(json['rank']),
        teamId: _int(json['team_id']),
        teamName: _str(json['team_name']),
        teamLogo: _str(json['team_logo']),
        played: _int(json['played']),
        win: _int(json['win']),
        draw: _int(json['draw']),
        lose: _int(json['lose']),
        goalsFor: _int(json['goals_for']),
        goalsAgainst: _int(json['goals_against']),
        points: _int(json['points']),
      );
}

class TeamStatsPair {
  final TeamStats home;
  final TeamStats away;

  const TeamStatsPair({required this.home, required this.away});

  factory TeamStatsPair.fromJson(Map<String, dynamic> json) => TeamStatsPair(
        home: TeamStats.fromJson(_map(json['home'])),
        away: TeamStats.fromJson(_map(json['away'])),
      );
}

class TeamStats {
  final int played;
  final int wins;
  final int draws;
  final int losses;
  final int goalsFor;
  final int goalsAgainst;

  const TeamStats({
    this.played = 0,
    this.wins = 0,
    this.draws = 0,
    this.losses = 0,
    this.goalsFor = 0,
    this.goalsAgainst = 0,
  });

  factory TeamStats.fromJson(Map<String, dynamic> json) => TeamStats(
        played: _int(json['played']) ?? 0,
        wins: _int(json['wins']) ?? 0,
        draws: _int(json['draws']) ?? 0,
        losses: _int(json['losses']) ?? 0,
        goalsFor: _int(json['goals_for']) ?? 0,
        goalsAgainst: _int(json['goals_against']) ?? 0,
      );
}

class RelatedPrediction {
  final String? slug;
  final String? homeTeam;
  final String? awayTeam;
  final String? category;
  final String? tip;
  final String? matchDate;

  const RelatedPrediction({
    this.slug,
    this.homeTeam,
    this.awayTeam,
    this.category,
    this.tip,
    this.matchDate,
  });

  factory RelatedPrediction.fromJson(Map<String, dynamic> json) => RelatedPrediction(
        slug: _str(json['slug']),
        homeTeam: _str(json['home_team']),
        awayTeam: _str(json['away_team']),
        category: _str(json['category']),
        tip: _str(json['tip']),
        matchDate: _str(json['match_date']),
      );
}

class MatchComment {
  final String? name;
  final String? comment;
  final String? createdAt;

  const MatchComment({this.name, this.comment, this.createdAt});

  factory MatchComment.fromJson(Map<String, dynamic> json) => MatchComment(
        name: _str(json['name']),
        comment: _str(json['comment']),
        createdAt: _str(json['created_at']),
      );
}

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

String? _str(dynamic v) => v?.toString();

int? _int(dynamic v) {
  if (v == null) return null;
  if (v is int) return v;
  if (v is num) return v.toInt();
  return int.tryParse(v.toString());
}

Map<String, dynamic> _map(dynamic v) {
  if (v is Map<String, dynamic>) return v;
  if (v is Map) return Map<String, dynamic>.from(v);
  return const {};
}

List<String> _strList(dynamic v) {
  if (v is! List) return const [];
  return v.map((e) => e?.toString() ?? '').where((e) => e.isNotEmpty).toList();
}

List<T> _list<T>(dynamic v, T Function(Map<String, dynamic>) parse) {
  if (v is! List) return const [];
  return v
      .whereType<Map>()
      .map((e) => parse(Map<String, dynamic>.from(e)))
      .toList();
}
