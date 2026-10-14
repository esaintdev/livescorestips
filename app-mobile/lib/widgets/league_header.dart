import 'package:flutter/material.dart';

import '../core/app_colors.dart';
import 'app_network_image.dart';

class LeagueHeader extends StatelessWidget {
  const LeagueHeader({
    super.key,
    required this.leagueName,
    required this.leagueLogo,
    this.leagueCountry,
  });

  final String? leagueName;
  final String? leagueLogo;
  final String? leagueCountry;

  @override
  Widget build(BuildContext context) {
    final bool dark = Theme.of(context).brightness == Brightness.dark;
    final Color bg =
        dark ? AppColors.darkSurface2.withValues(alpha: 0.55) : AppColors.lightSurfaceMid;
    final Color fg =
        dark ? AppColors.darkTextSecondary : AppColors.lightText500;

    final String label = [
      if (leagueCountry != null && leagueCountry!.isNotEmpty) leagueCountry,
      if (leagueName != null && leagueName!.isNotEmpty) leagueName,
    ].join(' - ');

    return Container(
      width: double.infinity,
      color: bg,
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 7),
      child: Row(
        children: [
          AppNetworkImage(
            url: leagueLogo,
            width: 16,
            height: 16,
            fallbackIcon: Icons.emoji_events_outlined,
          ),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              label.isEmpty ? 'League' : label,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w600,
                color: fg,
              ),
            ),
          ),
        ],
      ),
    );
  }
}
