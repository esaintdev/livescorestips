import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import '../core/app_colors.dart';
import '../models/winning.dart';
import 'app_network_image.dart';
import 'app_ui.dart';

class WinningCard extends StatelessWidget {
  const WinningCard({super.key, required this.winning});

  final Winning winning;

  @override
  Widget build(BuildContext context) {
    final Color text = AppTokens.textPrimary(context);

    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppTokens.surface(context),
        borderRadius: BorderRadius.circular(AppTokens.radius),
        border: Border.all(color: AppTokens.border(context)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Text(
                _dateLabel(),
                style: TextStyle(
                    fontSize: 10, color: AppTokens.textMuted(context)),
              ),
              const SizedBox(width: 8),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                decoration: BoxDecoration(
                  color: AppColors.accent.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(5),
                ),
                child: Text(
                  'Tip: ${winning.tip ?? '-'}',
                  style: const TextStyle(
                      fontSize: 10,
                      fontWeight: FontWeight.w700,
                      color: AppColors.accent),
                ),
              ),
              const Spacer(),
              const Icon(Icons.check_circle,
                  size: 13, color: AppColors.success),
            ],
          ),
          const SizedBox(height: 12),
          _teamRow(context, winning.homeTeam, winning.homeTeamLogo,
              winning.homeScore, text),
          const SizedBox(height: 7),
          _teamRow(context, winning.awayTeam, winning.awayTeamLogo,
              winning.awayScore, text),
        ],
      ),
    );
  }

  Widget _teamRow(BuildContext context, String? name, String? logo, int? score,
      Color text) {
    return Row(
      children: [
        AppNetworkImage(url: logo, width: 16, height: 16),
        const SizedBox(width: 8),
        Expanded(
          child: Text(
            name ?? '',
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style:
                TextStyle(fontSize: 13, fontWeight: FontWeight.w500, color: text),
          ),
        ),
        Text(
          score?.toString() ?? '-',
          style:
              TextStyle(fontSize: 13.5, fontWeight: FontWeight.w800, color: text),
        ),
      ],
    );
  }

  String _dateLabel() {
    final raw = winning.matchDate;
    if (raw == null || raw.isEmpty) return '';
    try {
      return DateFormat('MMM d').format(DateTime.parse(raw));
    } catch (_) {
      return raw;
    }
  }
}
