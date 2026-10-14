import 'package:flutter/material.dart';

import '../core/app_colors.dart';

class TipsSearchBar extends StatelessWidget {
  const TipsSearchBar({
    super.key,
    this.controller,
    this.hintText = 'Team, league or country',
  });

  final TextEditingController? controller;
  final String hintText;

  @override
  Widget build(BuildContext context) {
    final bool dark = Theme.of(context).brightness == Brightness.dark;

    return Container(
      decoration: BoxDecoration(
        color: dark ? AppColors.darkSurface2 : AppColors.lightSurfaceMid,
        borderRadius: BorderRadius.circular(10),
      ),
      child: TextField(
        controller: controller,
        style: TextStyle(
          fontSize: 13,
          color: dark ? AppColors.darkText : AppColors.lightText900,
        ),
        decoration: InputDecoration(
          hintText: hintText,
          hintStyle: TextStyle(
            fontSize: 13,
            color: dark ? AppColors.darkTextMuted : AppColors.lightText400,
          ),
          prefixIcon: Icon(Icons.search,
              size: 18,
              color: dark ? AppColors.darkTextMuted : AppColors.lightText400),
          border: InputBorder.none,
          isDense: true,
          contentPadding:
              const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
          filled: false,
        ),
      ),
    );
  }
}
