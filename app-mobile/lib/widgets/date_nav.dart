import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import '../core/app_colors.dart';

class DateNav extends StatelessWidget {
  const DateNav({
    super.key,
    required this.date,
    this.onPrev,
    this.onNext,
    this.onPickDate,
  });

  final DateTime date;
  final VoidCallback? onPrev;
  final VoidCallback? onNext;
  final VoidCallback? onPickDate;

  @override
  Widget build(BuildContext context) {
    final bool dark = Theme.of(context).brightness == Brightness.dark;
    final Color arrowColor =
        dark ? AppColors.darkTextSecondary : AppColors.lightText500;

    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        _arrow(context, Icons.chevron_left, arrowColor, onPrev),
        const SizedBox(width: 12),
        InkWell(
          onTap: onPickDate,
          borderRadius: BorderRadius.circular(8),
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
            decoration: BoxDecoration(
              color: dark
                  ? Colors.white.withValues(alpha: 0.06)
                  : Colors.black.withValues(alpha: 0.03),
              border: Border.all(
                color: dark
                    ? Colors.white.withValues(alpha: 0.1)
                    : Colors.black.withValues(alpha: 0.12),
              ),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Icon(Icons.calendar_today_outlined,
                    size: 13, color: AppColors.accent),
                const SizedBox(width: 7),
                Text(
                  DateFormat('dd-MM-yyyy').format(date),
                  style: TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                    color: dark ? AppColors.darkText : AppColors.lightText900,
                  ),
                ),
              ],
            ),
          ),
        ),
        const SizedBox(width: 12),
        _arrow(context, Icons.chevron_right, arrowColor, onNext),
      ],
    );
  }

  Widget _arrow(
      BuildContext context, IconData icon, Color color, VoidCallback? onTap) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(8),
      child: Container(
        padding: const EdgeInsets.all(8),
        decoration: BoxDecoration(
          border: Border.all(color: Colors.transparent),
          borderRadius: BorderRadius.circular(8),
        ),
        child: Icon(icon, size: 18, color: color),
      ),
    );
  }
}
