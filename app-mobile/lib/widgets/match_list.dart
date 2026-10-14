import 'package:flutter/material.dart';

import '../core/app_colors.dart';
import '../models/prediction.dart';
import '../screens/match_detail_screen.dart';
import 'league_header.dart';
import 'match_card.dart';

class MatchList extends StatelessWidget {
  const MatchList({
    super.key,
    required this.predictions,
    this.isLive = false,
    this.onEmptyText,
  });

  final List<Prediction> predictions;
  final bool isLive;
  final String? onEmptyText;

  @override
  Widget build(BuildContext context) {
    if (predictions.isEmpty) {
      return _empty(context);
    }

    final groups = <Object, List<Prediction>>{};
    for (final p in predictions) {
      final key = p.leagueId ?? p.leagueName ?? 'other';
      groups.putIfAbsent(key, () => []).add(p);
    }

    final bool dark = Theme.of(context).brightness == Brightness.dark;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        for (final group in groups.values) ...[
          LeagueHeader(
            leagueName: group.first.leagueName,
            leagueLogo: group.first.leagueLogo,
            leagueCountry: group.first.leagueCountry,
          ),
          for (final p in group)
            Container(
              decoration: BoxDecoration(
                border: Border(
                  bottom: BorderSide(
                    color: dark
                        ? AppColors.darkBorder(0.05)
                        : AppColors.lightBorder(0.06),
                  ),
                ),
              ),
              child: MatchCard(
                prediction: p,
                isLive: isLive,
                onTap: () => _openMatch(context, p),
              ),
            ),
        ],
      ],
    );
  }

  void _openMatch(BuildContext context, Prediction p) {
    final slug = (p.slug != null && p.slug!.isNotEmpty)
        ? p.slug
        : (p.fixtureId != null ? '${p.fixtureId}' : null);
    if (slug == null) return;

    Navigator.of(context).push(
      MaterialPageRoute(builder: (_) => MatchDetailScreen(slug: slug)),
    );
  }

  Widget _empty(BuildContext context) {
    final bool dark = Theme.of(context).brightness == Brightness.dark;
    return Container(
      width: double.infinity,
      margin: const EdgeInsets.all(16),
      padding: const EdgeInsets.symmetric(vertical: 40, horizontal: 20),
      decoration: BoxDecoration(
        color: dark ? AppColors.darkSurface3 : AppColors.lightSurface,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Column(
        children: [
          Icon(Icons.calendar_today_outlined,
              size: 42,
              color: dark ? AppColors.darkTextMuted : AppColors.lightText200),
          const SizedBox(height: 12),
          Text(
            onEmptyText ??
                'No predictions available for this date. Check back later!',
            textAlign: TextAlign.center,
            style: TextStyle(
              fontSize: 13,
              color: dark ? AppColors.darkTextMuted : AppColors.lightText400,
            ),
          ),
        ],
      ),
    );
  }
}
