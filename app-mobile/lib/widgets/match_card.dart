import 'package:flutter/material.dart';

import '../core/app_colors.dart';
import '../models/prediction.dart';
import 'app_network_image.dart';

class MatchCard extends StatelessWidget {
  const MatchCard({
    super.key,
    required this.prediction,
    this.onTap,
    this.isLive = false,
  });

  final Prediction prediction;
  final VoidCallback? onTap;
  final bool isLive;

  @override
  Widget build(BuildContext context) {
    final bool dark = Theme.of(context).brightness == Brightness.dark;
    final Color timeColor = isLive
        ? AppColors.liveRed
        : (dark ? AppColors.darkTextMuted : AppColors.lightText200);

    return InkWell(
      onTap: onTap,
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 10),
        child: Row(
          children: [
            SizedBox(
              width: 40,
              child: Text(
                isLive ? 'LIVE' : (prediction.matchTime ?? ''),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(
                  fontSize: 11,
                  fontWeight: isLive ? FontWeight.w800 : FontWeight.w500,
                  color: timeColor,
                  letterSpacing: isLive ? 0.5 : 0,
                ),
              ),
            ),
            Expanded(child: _teams(context)),
            const SizedBox(width: 8),
            _stats(context),
          ],
        ),
      ),
    );
  }

  Widget _teams(BuildContext context) {
    final Color nameColor = Theme.of(context).colorScheme.onSurface;
    final Color scoreColor = Theme.of(context).colorScheme.onSurface;

    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        _teamRow(
          context,
          name: prediction.homeTeam,
          logo: prediction.homeTeamLogo,
          score: prediction.homeScore,
          nameColor: nameColor,
          scoreColor: scoreColor,
        ),
        const SizedBox(height: 4),
        _teamRow(
          context,
          name: prediction.awayTeam,
          logo: prediction.awayTeamLogo,
          score: prediction.awayScore,
          nameColor: nameColor,
          scoreColor: scoreColor,
        ),
      ],
    );
  }

  Widget _teamRow(
    BuildContext context, {
    required String? name,
    required String? logo,
    required int? score,
    required Color nameColor,
    required Color scoreColor,
  }) {
    return Row(
      children: [
        AppNetworkImage(
          url: logo,
          width: 14,
          height: 14,
          fallbackIcon: Icons.shield_outlined,
        ),
        const SizedBox(width: 6),
        Expanded(
          child: Text(
            name ?? '',
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.w500,
              color: nameColor,
            ),
          ),
        ),
        if (score != null)
          Padding(
            padding: const EdgeInsets.only(left: 6),
            child: Text(
              '$score',
              style: TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w700,
                color: scoreColor,
              ),
            ),
          ),
      ],
    );
  }

  Widget _stats(BuildContext context) {
    final bool dark = Theme.of(context).brightness == Brightness.dark;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.end,
      mainAxisSize: MainAxisSize.min,
      children: [
        _probBadge(),
        const SizedBox(height: 4),
        _tipBadge(dark),
        const SizedBox(height: 2),
        Text(
          prediction.odds ?? '-',
          style: const TextStyle(
            fontSize: 13,
            fontWeight: FontWeight.w700,
            color: AppColors.oddsYellow,
          ),
        ),
      ],
    );
  }

  Widget _probBadge() {
    final tier = prediction.probTier;
    final (bg, fg, border) = switch (tier) {
      'high' => (
          AppColors.success.withValues(alpha: 0.2),
          Colors.white,
          AppColors.success.withValues(alpha: 0.3),
        ),
      'med' => (
          AppColors.warning.withValues(alpha: 0.2),
          Colors.white,
          AppColors.warning.withValues(alpha: 0.3),
        ),
      _ => (
          AppColors.danger.withValues(alpha: 0.2),
          Colors.white,
          AppColors.danger.withValues(alpha: 0.3),
        ),
    };

    if (prediction.probabilityValue == null) {
      return const Text('-', style: TextStyle(fontSize: 11, color: Colors.grey));
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(4),
        border: Border.all(color: border),
      ),
      child: Text(
        prediction.probabilityLabel,
        style: TextStyle(
          fontSize: 10,
          fontWeight: FontWeight.w700,
          color: fg,
        ),
      ),
    );
  }

  Widget _tipBadge(bool dark) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
      decoration: BoxDecoration(
        color: dark
            ? Colors.white.withValues(alpha: 0.1)
            : Colors.black.withValues(alpha: 0.06),
        borderRadius: BorderRadius.circular(4),
      ),
      child: Text(
        prediction.tipLabel,
        style: TextStyle(
          fontSize: 10,
          fontWeight: FontWeight.w600,
          color: dark ? AppColors.darkTextBody : AppColors.lightText900,
        ),
      ),
    );
  }
}
