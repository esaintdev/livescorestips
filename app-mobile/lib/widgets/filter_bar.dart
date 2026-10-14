import 'package:flutter/material.dart';

import '../core/app_colors.dart';
import '../core/filters.dart';

/// The horizontal filter toolbar, mirroring `bm-filter-bar` on the website.
class FilterBar extends StatelessWidget {
  const FilterBar({
    super.key,
    this.marketLabel = 'Tips',
    this.onMarketSelected,
    this.sort = SortOption.time,
    this.onSortChanged,
    this.probability = 0,
    this.onProbabilityChanged,
    this.odds = 0,
    this.onOddsChanged,
    this.liveCount = 0,
    this.liveActive = false,
    this.onLiveTap,
    this.hasFilters = false,
    this.onClear,
  });

  final String marketLabel;
  final ValueChanged<String?>? onMarketSelected;
  final SortOption sort;
  final ValueChanged<SortOption>? onSortChanged;
  final double probability;
  final ValueChanged<double>? onProbabilityChanged;
  final double odds;
  final ValueChanged<double>? onOddsChanged;
  final int liveCount;
  final bool liveActive;
  final VoidCallback? onLiveTap;
  final bool hasFilters;
  final VoidCallback? onClear;

  @override
  Widget build(BuildContext context) {
    final bool dark = Theme.of(context).brightness == Brightness.dark;

    return Container(
      color: dark ? AppColors.darkSurface : AppColors.lightSurface,
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      child: SingleChildScrollView(
        scrollDirection: Axis.horizontal,
        child: Row(
          children: [
            _liveButton(context, dark),
            const SizedBox(width: 6),
            _marketDropdown(context, dark),
            const SizedBox(width: 6),
            _sortDropdown(context, dark),
            const SizedBox(width: 6),
            _probabilityDropdown(context, dark),
            const SizedBox(width: 6),
            _oddsDropdown(context, dark),
            if (hasFilters) ...[
              const SizedBox(width: 6),
              _clearButton(context, dark),
            ],
          ],
        ),
      ),
    );
  }

  Widget _liveButton(BuildContext context, bool dark) {
    final Color bg = liveActive
        ? AppColors.liveRed
        : AppColors.liveRed.withValues(alpha: dark ? 0.08 : 0.08);
    final Color fg = liveActive ? Colors.white : AppColors.liveRed;
    final Color border =
        liveActive ? AppColors.liveRed : AppColors.liveRed.withValues(alpha: 0.5);

    return _pill(
      context,
      dark: dark,
      bg: bg,
      border: border,
      onTap: onLiveTap,
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          _LiveDot(color: liveActive ? Colors.white : AppColors.liveRed),
          const SizedBox(width: 5),
          Text('Live', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: fg)),
          if (liveCount > 0) ...[
            const SizedBox(width: 5),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1),
              decoration: BoxDecoration(
                color: liveActive ? Colors.white : AppColors.liveRed,
                borderRadius: BorderRadius.circular(9),
              ),
              child: Text(
                '$liveCount',
                style: TextStyle(
                  fontSize: 9,
                  fontWeight: FontWeight.w800,
                  color: liveActive ? AppColors.liveRed : Colors.white,
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _marketDropdown(BuildContext context, bool dark) => _dropdown(
        context,
        dark,
        value: marketLabel,
        items: marketOptions.map((o) => o.label).toList(),
        onSelected: (label) {
          final match = marketOptions.firstWhere(
            (o) => o.label == label,
            orElse: () => marketOptions.first,
          );
          onMarketSelected?.call(match.slug);
        },
      );

  Widget _sortDropdown(BuildContext context, bool dark) => _dropdown(
        context,
        dark,
        value: sort.label,
        items: SortOption.values.map((s) => s.label).toList(),
        onSelected: (label) {
          final match = SortOption.values.firstWhere((s) => s.label == label);
          onSortChanged?.call(match);
        },
      );

  Widget _probabilityDropdown(BuildContext context, bool dark) => _dropdown(
        context,
        dark,
        value: probabilityLabel(probability),
        items: probabilityOptions.map(probabilityLabel).toList(),
        onSelected: (label) {
          final idx = probabilityOptions.indexWhere(
              (v) => probabilityLabel(v) == label);
          if (idx >= 0) onProbabilityChanged?.call(probabilityOptions[idx]);
        },
      );

  Widget _oddsDropdown(BuildContext context, bool dark) => _dropdown(
        context,
        dark,
        value: oddsLabel(odds),
        items: oddsOptions.map(oddsLabel).toList(),
        onSelected: (label) {
          final idx = oddsOptions.indexWhere((v) => oddsLabel(v) == label);
          if (idx >= 0) onOddsChanged?.call(oddsOptions[idx]);
        },
      );

  Widget _dropdown(
    BuildContext context,
    bool dark, {
    required String value,
    required List<String> items,
    required ValueChanged<String> onSelected,
  }) {
    final Color bg = dark ? AppColors.darkSurface2 : AppColors.lightSurfaceMid;
    final Color border = dark
        ? AppColors.darkBorder(0.2)
        : AppColors.lightBorder();

    return PopupMenuButton<String>(
      tooltip: value,
      onSelected: onSelected,
      color: dark ? AppColors.darkSurface3 : AppColors.lightSurface,
      itemBuilder: (context) => items
          .map((e) => PopupMenuItem<String>(
                value: e,
                child: Text(
                  e,
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: e == value ? FontWeight.w700 : FontWeight.w400,
                    color: e == value ? AppColors.accent : null,
                  ),
                ),
              ))
          .toList(),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
        decoration: BoxDecoration(
          color: bg,
          border: Border.all(color: border),
          borderRadius: BorderRadius.circular(8),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(
              value,
              style: TextStyle(
                fontSize: 11,
                fontWeight: FontWeight.w600,
                color: dark ? Colors.white : AppColors.lightText900,
              ),
            ),
            const SizedBox(width: 4),
            Icon(Icons.keyboard_arrow_down,
                size: 13,
                color: dark ? AppColors.darkTextMuted : AppColors.lightText400),
          ],
        ),
      ),
    );
  }

  Widget _clearButton(BuildContext context, bool dark) {
    return _pill(
      context,
      dark: dark,
      bg: AppColors.danger,
      border: AppColors.danger,
      onTap: onClear,
      child: const Icon(Icons.close, size: 14, color: Colors.white),
    );
  }

  Widget _pill(
    BuildContext context, {
    required bool dark,
    required Color bg,
    required Color border,
    required VoidCallback? onTap,
    required Widget child,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(8),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
        decoration: BoxDecoration(
          color: bg,
          border: Border.all(color: border),
          borderRadius: BorderRadius.circular(8),
        ),
        child: child,
      ),
    );
  }
}

class _LiveDot extends StatelessWidget {
  const _LiveDot({required this.color});

  final Color color;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 8,
      height: 8,
      decoration: BoxDecoration(color: color, shape: BoxShape.circle),
    );
  }
}
