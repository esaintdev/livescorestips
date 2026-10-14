import 'package:flutter/material.dart';

import '../core/app_colors.dart';

class PageHeader extends StatelessWidget {
  const PageHeader({super.key, required this.title, this.subtitle});

  final String title;
  final String? subtitle;

  @override
  Widget build(BuildContext context) {
    final bool dark = Theme.of(context).brightness == Brightness.dark;
    final Color titleColor = dark ? AppColors.darkTextBody : AppColors.lightText900;
    final Color subColor =
        dark ? AppColors.darkTextSecondary : AppColors.lightText500;

    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 18, 16, 4),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            title,
            style: TextStyle(
              fontSize: 22,
              fontWeight: FontWeight.w800,
              color: titleColor,
              height: 1.2,
            ),
          ),
          if (subtitle != null && subtitle!.isNotEmpty) ...[
            const SizedBox(height: 6),
            Text(
              subtitle!,
              style: TextStyle(
                fontSize: 13.5,
                color: subColor,
                height: 1.45,
              ),
            ),
          ],
        ],
      ),
    );
  }
}
