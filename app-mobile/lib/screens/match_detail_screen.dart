import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../core/app_colors.dart';
import '../models/match_detail.dart';
import '../services/api_service.dart';
import '../widgets/app_network_image.dart';
import '../widgets/app_ui.dart';

/// Match details — an editorial/data page. Content lives directly on the page
/// background, organised with typography, spacing, alignment and thin rules.
/// No large rounded section containers, no outlined "cards".
class MatchDetailScreen extends StatefulWidget {
  const MatchDetailScreen({super.key, required this.slug});

  final String slug;

  @override
  State<MatchDetailScreen> createState() => _MatchDetailScreenState();
}

class _MatchDetailScreenState extends State<MatchDetailScreen> {
  MatchDetail? _match;
  bool _loading = true;
  String? _error;

  static const double _pad = 20;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    final api = context.read<ApiService>();
    try {
      final match = await api.getMatch(widget.slug);
      if (!mounted) return;
      setState(() {
        _match = match;
        _loading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _error = e.toString();
        _loading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final MatchDetail? match = _match;

    return Scaffold(
      backgroundColor: AppTokens.bg(context),
      body: SafeArea(
        top: true,
        bottom: false,
        child: _loading
            ? const Center(child: CircularProgressIndicator())
            : _error != null
                ? _errorView()
                : _body(match!),
      ),
    );
  }

  Widget _errorView() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.wifi_off, size: 34, color: AppTokens.textMuted(context)),
            const SizedBox(height: 12),
            Text(_error ?? 'Something went wrong.',
                textAlign: TextAlign.center,
                style: const TextStyle(fontSize: 13, color: AppColors.danger)),
            const SizedBox(height: 12),
            OutlinedButton(onPressed: _load, child: const Text('Retry')),
          ],
        ),
      ),
    );
  }

  Widget _body(MatchDetail m) {
    final List<Widget> blocks = [_header(m), _hero(m)];

    void section(String title, Widget child, {Widget? trailing}) {
      blocks.add(_rule());
      blocks.add(Padding(
        padding: const EdgeInsets.fromLTRB(_pad, 22, _pad, 22),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            AppSectionHeader(
                title: title, upperCase: true, trailing: trailing),
            const SizedBox(height: 16),
            child,
          ],
        ),
      ));
    }

    if (m.prediction != null) section('Expert Prediction', _prediction(m.prediction!));
    if (m.result != null && m.result != 'pending') {
      section('Prediction Result', _result(m),
          trailing: _resultChip(m.result!));
    }
    if (m.analysis != null && m.analysis!.isNotEmpty) {
      section('Prediction Analysis', _analysis(m.analysis!));
    }
    if (m.factors.isNotEmpty) section('Key Match Factors', _factors(m.factors));
    if (m.headToHead.isNotEmpty) section('Head to Head', _h2h(m.headToHead));
    if (m.teamStats != null) section('Team Stats', _teamStats(m));
    if (m.standings.isNotEmpty) section('League Standings', _standings(m));
    if (m.related.isNotEmpty) section('Related Predictions', _related(m.related));
    section('Discussion', _discussion(m));

    return ListView(
      padding: const EdgeInsets.only(bottom: 40),
      children: blocks,
    );
  }

  Widget _rule() => Container(height: 1, color: AppTokens.divider(context));

  Widget _resultChip(String result) {
    final Color c = result == 'won'
        ? AppColors.success
        : result == 'void'
            ? AppColors.warning
            : AppColors.danger;
    return AppStatusChip(label: result.toUpperCase(), color: c);
  }

  // ------------------------------------------------------------------ sections

  Widget _prediction(MatchPrediction p) {
    final Color secondary = AppTokens.textSecondary(context);
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(p.category ?? '',
            style: TextStyle(
                fontSize: 13.5,
                fontWeight: FontWeight.w500,
                color: secondary)),
        const SizedBox(height: 18),
        Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(
              child: _statTop(p.tip ?? '-', 'Our Tip',
                  color: AppColors.success, size: 28),
            ),
            Expanded(
              child: _statTop(
                  p.probability != null ? '${p.probability}%' : '-',
                  'Probability',
                  align: CrossAxisAlignment.center,
                  textAlign: TextAlign.center),
            ),
            Expanded(
              child: _statTop(p.odds ?? '-', 'Odds',
                  color: AppColors.warning,
                  align: CrossAxisAlignment.end,
                  textAlign: TextAlign.right),
            ),
          ],
        ),
      ],
    );
  }

  Widget _result(MatchDetail m) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(child: AppMetric(label: 'Final Score', value: m.scoreLabel)),
            Expanded(
              child: AppMetric(
                label: 'Prediction',
                value: m.prediction?.tip ?? '-',
                valueColor: AppColors.success,
                align: CrossAxisAlignment.end,
                textAlign: TextAlign.right,
              ),
            ),
          ],
        ),
        if (m.resultSummary != null && m.resultSummary!.isNotEmpty) ...[
          const SizedBox(height: 16),
          Text(m.resultSummary!,
              style: TextStyle(
                  fontSize: 13.5,
                  height: 1.65,
                  color: AppTokens.textSecondary(context))),
        ],
      ],
    );
  }

  Widget _analysis(String text) {
    return Text(
      text,
      style: TextStyle(
        fontSize: 14.5,
        height: 1.75,
        color: AppTokens.textSecondary(context),
      ),
    );
  }

  Widget _factors(List<String> factors) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        for (int i = 0; i < factors.length; i++) ...[
          AppBullet(text: factors[i]),
          if (i != factors.length - 1) const AppDivider(indent: 16, endIndent: 0),
        ],
      ],
    );
  }

  Widget _h2h(List<H2HMatch> items) {
    return Column(
      children: [
        for (int i = 0; i < items.length; i++) ...[
          Padding(
            padding: const EdgeInsets.symmetric(vertical: 11),
            child: _h2hRow(items[i]),
          ),
          if (i != items.length - 1) const AppDivider(),
        ],
      ],
    );
  }

  Widget _h2hRow(H2HMatch h) {
    return Row(
      children: [
        SizedBox(
          width: 66,
          child: Text(_shortDate(h.date),
              style: TextStyle(
                  fontSize: 11, color: AppTokens.textMuted(context))),
        ),
        Expanded(
          child: Text(h.homeTeam ?? '',
              textAlign: TextAlign.right,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: TextStyle(
                  fontSize: 13.5,
                  fontWeight: FontWeight.w600,
                  color: AppTokens.textPrimary(context))),
        ),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 12),
          child: Text(
            '${h.homeScore ?? '-'}–${h.awayScore ?? '-'}',
            style: const TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w800,
                color: AppColors.warning),
          ),
        ),
        Expanded(
          child: Text(h.awayTeam ?? '',
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: TextStyle(
                  fontSize: 13.5,
                  fontWeight: FontWeight.w600,
                  color: AppTokens.textPrimary(context))),
        ),
      ],
    );
  }

  Widget _teamStats(MatchDetail m) {
    final Color primary = AppTokens.textPrimary(context);
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Expanded(
              child: Text(m.homeTeam ?? '',
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                      fontSize: 12.5,
                      fontWeight: FontWeight.w700,
                      color: primary)),
            ),
            Expanded(
              child: Text(m.awayTeam ?? '',
                  textAlign: TextAlign.right,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                      fontSize: 12.5,
                      fontWeight: FontWeight.w700,
                      color: primary)),
            ),
          ],
        ),
        const SizedBox(height: 6),
        const AppDivider(),
        _statRow('Matches Played', m.teamStats!.home.played,
            m.teamStats!.away.played),
        const AppDivider(),
        _statRow('Wins', m.teamStats!.home.wins, m.teamStats!.away.wins),
        const AppDivider(),
        _statRow('Draws', m.teamStats!.home.draws, m.teamStats!.away.draws),
        const AppDivider(),
        _statRow('Losses', m.teamStats!.home.losses, m.teamStats!.away.losses),
        const AppDivider(),
        _statRow('Goals For', m.teamStats!.home.goalsFor,
            m.teamStats!.away.goalsFor),
        const AppDivider(),
        _statRow('Goals Against', m.teamStats!.home.goalsAgainst,
            m.teamStats!.away.goalsAgainst),
        const AppDivider(),
      ],
    );
  }

  Widget _statRow(String label, int home, int away) {
    final Color primary = AppTokens.textPrimary(context);
    final Color muted = AppTokens.textMuted(context);
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 11),
      child: Row(
        children: [
          SizedBox(
            width: 44,
            child: Text('$home',
                style: TextStyle(
                    fontSize: 15, fontWeight: FontWeight.w800, color: primary)),
          ),
          Expanded(
            child: Text(label.toUpperCase(),
                textAlign: TextAlign.center,
                style: TextStyle(
                    fontSize: 10,
                    letterSpacing: 0.6,
                    fontWeight: FontWeight.w600,
                    color: muted)),
          ),
          SizedBox(
            width: 44,
            child: Text('$away',
                textAlign: TextAlign.right,
                style: TextStyle(
                    fontSize: 15, fontWeight: FontWeight.w800, color: primary)),
          ),
        ],
      ),
    );
  }

  Widget _standings(MatchDetail m) {
    final Color muted = AppTokens.textMuted(context);
    final Color primary = AppTokens.textPrimary(context);
    final Color highlight = AppColors.accent
        .withValues(alpha: AppTokens.dark(context) ? 0.10 : 0.07);

    const double cRank = 22, cNum = 24, cG = 34, cPts = 30;

    Widget num(String v, double w, {Color? c, bool bold = false}) => SizedBox(
          width: w,
          child: Text(v,
              textAlign: TextAlign.center,
              style: TextStyle(
                  fontSize: 12,
                  fontWeight: bold ? FontWeight.w800 : FontWeight.w500,
                  color: c ?? primary)),
        );

    TextStyle th() => TextStyle(
        fontSize: 9.5, fontWeight: FontWeight.w700, letterSpacing: 0.8, color: muted);

    return Column(
      children: [
        Row(
          children: [
            SizedBox(width: cRank, child: Text('#', textAlign: TextAlign.center, style: th())),
            Expanded(child: Text('Team', style: th())),
            SizedBox(width: cNum, child: Text('MP', textAlign: TextAlign.center, style: th())),
            SizedBox(width: cNum, child: Text('W', textAlign: TextAlign.center, style: th())),
            SizedBox(width: cNum, child: Text('D', textAlign: TextAlign.center, style: th())),
            SizedBox(width: cNum, child: Text('L', textAlign: TextAlign.center, style: th())),
            SizedBox(width: cG, child: Text('G', textAlign: TextAlign.center, style: th())),
            SizedBox(width: cPts, child: Text('PTS', textAlign: TextAlign.center, style: th())),
          ],
        ),
        const SizedBox(height: 4),
        for (final row in m.standings)
          Container(
            color: (row.teamId == m.homeTeamId || row.teamId == m.awayTeamId)
                ? highlight
                : Colors.transparent,
            padding: const EdgeInsets.symmetric(vertical: 9),
            child: Row(
              children: [
                SizedBox(
                  width: cRank,
                  child: Text('${row.rank ?? 0}',
                      textAlign: TextAlign.center,
                      style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w700,
                          color: _rankColor(row.rank ?? 0, muted))),
                ),
                Expanded(
                  child: Row(
                    children: [
                      AppNetworkImage(url: row.teamLogo, width: 15, height: 15),
                      const SizedBox(width: 8),
                      Flexible(
                        child: Text(row.teamName ?? '',
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: TextStyle(
                                fontSize: 12.5,
                                fontWeight: FontWeight.w600,
                                color: primary)),
                      ),
                    ],
                  ),
                ),
                num('${row.played ?? 0}', cNum),
                num('${row.win ?? 0}', cNum),
                num('${row.draw ?? 0}', cNum),
                num('${row.lose ?? 0}', cNum),
                num('${row.goalsFor ?? 0}:${row.goalsAgainst ?? 0}', cG),
                num('${row.points ?? 0}', cPts,
                    c: AppColors.warning, bold: true),
              ],
            ),
          ),
      ],
    );
  }

  Color _rankColor(int rank, Color muted) {
    if (rank <= 4) return AppColors.success;
    if (rank >= 18) return AppColors.danger;
    return muted;
  }

  Widget _related(List<RelatedPrediction> items) {
    final Color primary = AppTokens.textPrimary(context);
    return Column(
      children: [
        for (int i = 0; i < items.length; i++) ...[
          InkWell(
            onTap: items[i].slug == null
                ? null
                : () => Navigator.of(context).pushReplacement(
                      MaterialPageRoute(
                          builder: (_) =>
                              MatchDetailScreen(slug: items[i].slug!)),
                    ),
            child: Padding(
              padding: const EdgeInsets.symmetric(vertical: 13),
              child: Row(
                children: [
                  Expanded(
                    child: Text(
                      '${items[i].homeTeam ?? ''} vs ${items[i].awayTeam ?? ''}',
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: TextStyle(
                          fontSize: 13.5,
                          fontWeight: FontWeight.w600,
                          color: primary),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Text(items[i].tip ?? '',
                      style: const TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w700,
                          color: AppColors.accent)),
                ],
              ),
            ),
          ),
          if (i != items.length - 1) const AppDivider(),
        ],
      ],
    );
  }

  Widget _discussion(MatchDetail m) {
    if (m.comments.isEmpty) {
      return Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('No comments yet.',
              style: TextStyle(
                  fontSize: 14.5,
                  fontWeight: FontWeight.w600,
                  color: AppTokens.textPrimary(context))),
          const SizedBox(height: 4),
          Text('Be the first to share your thoughts.',
              style: TextStyle(
                  fontSize: 13, color: AppTokens.textMuted(context))),
        ],
      );
    }

    return Column(
      children: [
        for (int i = 0; i < m.comments.length; i++) ...[
          Padding(
            padding: const EdgeInsets.symmetric(vertical: 12),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Expanded(
                      child: Text(m.comments[i].name ?? 'Anonymous',
                          style: const TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.w700,
                              color: AppColors.warning)),
                    ),
                    Text(_relativeTime(m.comments[i].createdAt),
                        style: TextStyle(
                            fontSize: 10.5,
                            color: AppTokens.textMuted(context))),
                  ],
                ),
                const SizedBox(height: 6),
                Text(m.comments[i].comment ?? '',
                    style: TextStyle(
                        fontSize: 13.5,
                        height: 1.55,
                        color: AppTokens.textSecondary(context))),
              ],
            ),
          ),
          if (i != m.comments.length - 1) const AppDivider(),
        ],
      ],
    );
  }

  // ------------------------------------------------------------------ header

  Widget _header(MatchDetail m) {
    final String title = '${m.homeTeam ?? ''} vs ${m.awayTeam ?? ''}';
    return Padding(
      padding: const EdgeInsets.fromLTRB(_pad, 2, _pad, 6),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          InkWell(
            onTap: () => Navigator.of(context).maybePop(),
            customBorder: const CircleBorder(),
            child: SizedBox(
              width: 40,
              height: 40,
              child: Align(
                alignment: Alignment.centerLeft,
                child: Icon(Icons.arrow_back,
                    size: 22, color: AppTokens.textPrimary(context)),
              ),
            ),
          ),
          const SizedBox(height: 2),
          Text(
            title,
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
            style: TextStyle(
              fontSize: 17,
              fontWeight: FontWeight.w700,
              height: 1.25,
              letterSpacing: -0.2,
              color: AppTokens.textPrimary(context),
            ),
          ),
        ],
      ),
    );
  }

  // ------------------------------------------------------------------ hero

  Widget _hero(MatchDetail m) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(_pad, 4, _pad, 26),
      child: Column(
        children: [
          Row(
            children: [
              AppNetworkImage(url: m.leagueLogo, width: 15, height: 15),
              const SizedBox(width: 7),
              Expanded(
                child: Text(m.leagueName ?? '',
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                        color: AppTokens.textSecondary(context))),
              ),
              Text(_dateLabel(m),
                  style: TextStyle(
                      fontSize: 11, color: AppTokens.textMuted(context))),
            ],
          ),
          const SizedBox(height: 24),
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(
                child: _team(
                    m.homeTeam, m.homeTeamLogo, m.homeForm),
              ),
              _score(m),
              Expanded(
                child: _team(
                    m.awayTeam, m.awayTeamLogo, m.awayForm),
              ),
            ],
          ),
          if ((m.venue != null && m.venue!.isNotEmpty) ||
              (m.referee != null && m.referee!.isNotEmpty)) ...[
            const SizedBox(height: 20),
            _rule(),
            const SizedBox(height: 12),
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                if (m.venue != null && m.venue!.isNotEmpty)
                  _meta(Icons.location_on_outlined, m.venue!),
                if (m.venue != null &&
                    m.venue!.isNotEmpty &&
                    m.referee != null &&
                    m.referee!.isNotEmpty)
                  const SizedBox(width: 18),
                if (m.referee != null && m.referee!.isNotEmpty)
                  _meta(Icons.sports_outlined, m.referee!),
              ],
            ),
          ],
        ],
      ),
    );
  }

  Widget _team(String? name, String? logo, List<String> form) {
    return Column(
      children: [
        AppNetworkImage(url: logo, width: 46, height: 46),
        const SizedBox(height: 11),
        Text(name ?? '',
            textAlign: TextAlign.center,
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
            style: TextStyle(
                fontSize: 14.5,
                fontWeight: FontWeight.w700,
                height: 1.2,
                color: AppTokens.textPrimary(context))),
        if (form.isNotEmpty) ...[
          const SizedBox(height: 9),
          Wrap(
            spacing: 3,
            alignment: WrapAlignment.center,
            children: [for (final r in form.take(5)) _formBadge(r)],
          ),
        ],
      ],
    );
  }

  Widget _score(MatchDetail m) {
    final Color primary = AppTokens.textPrimary(context);
    final Color color = m.isLive ? AppColors.accent : primary;
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 8),
      child: Column(
        children: [
          Text(m.hasScore ? m.scoreLabel : (m.matchTime ?? ''),
              style: TextStyle(
                  fontSize: m.hasScore ? 34 : 20,
                  fontWeight: FontWeight.w800,
                  height: 1,
                  color: color)),
          if (m.hasScore && (m.isLive || m.statusShort == 'FT')) ...[
            const SizedBox(height: 9),
            AppStatusChip(
                label: m.isLive ? 'LIVE' : 'FT',
                color: m.isLive ? AppColors.accent : AppTokens.textMuted(context)),
          ] else if (!m.hasScore) ...[
            const SizedBox(height: 5),
            Text('Not started',
                style: TextStyle(
                    fontSize: 10, color: AppTokens.textMuted(context))),
          ],
        ],
      ),
    );
  }

  Widget _formBadge(String r) {
    final Color c = r == 'W'
        ? AppColors.success
        : r == 'D'
            ? AppColors.warning
            : AppColors.danger;
    return Container(
      width: 17,
      height: 17,
      alignment: Alignment.center,
      decoration: BoxDecoration(
        color: c.withValues(alpha: 0.15),
        borderRadius: BorderRadius.circular(5),
      ),
      child: Text(r,
          style: TextStyle(fontSize: 9, fontWeight: FontWeight.w800, color: c)),
    );
  }

  Widget _meta(IconData icon, String text) {
    final Color color = AppTokens.textMuted(context);
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(icon, size: 13, color: color),
        const SizedBox(width: 5),
        Text(text, style: TextStyle(fontSize: 11, color: color)),
      ],
    );
  }

  /// Value on top, label beneath — used in the prediction block.
  Widget _statTop(
    String value,
    String label, {
    Color? color,
    double size = 20,
    CrossAxisAlignment align = CrossAxisAlignment.start,
    TextAlign textAlign = TextAlign.left,
  }) {
    return Column(
      crossAxisAlignment: align,
      mainAxisSize: MainAxisSize.min,
      children: [
        Text(value,
            textAlign: textAlign,
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
            style: TextStyle(
                fontSize: size,
                fontWeight: FontWeight.w800,
                height: 1.1,
                color: color ?? AppTokens.textPrimary(context))),
        const SizedBox(height: 6),
        Text(label.toUpperCase(),
            textAlign: textAlign,
            style: TextStyle(
                fontSize: 9.5,
                fontWeight: FontWeight.w700,
                letterSpacing: 0.8,
                color: AppTokens.textMuted(context))),
      ],
    );
  }

  String _dateLabel(MatchDetail m) {
    final raw = m.matchDate;
    if (raw == null || raw.isEmpty) return '';
    try {
      final d = DateTime.parse(raw);
      final t = m.matchTime ?? '';
      return '${DateFormat('MMM d').format(d)}${t.isNotEmpty ? ' · $t' : ''}';
    } catch (_) {
      return raw;
    }
  }

  String _shortDate(String? raw) {
    if (raw == null || raw.isEmpty) return '';
    try {
      return DateFormat('MMM d, yy').format(DateTime.parse(raw));
    } catch (_) {
      return raw;
    }
  }

  String _relativeTime(String? raw) {
    if (raw == null || raw.isEmpty) return '';
    try {
      final d = DateTime.parse(raw).toLocal();
      final diff = DateTime.now().difference(d);
      if (diff.inMinutes < 1) return 'just now';
      if (diff.inHours < 1) return '${diff.inMinutes}m ago';
      if (diff.inDays < 1) return '${diff.inHours}h ago';
      if (diff.inDays < 30) return '${diff.inDays}d ago';
      return DateFormat('MMM d, yyyy').format(d);
    } catch (_) {
      return '';
    }
  }
}
