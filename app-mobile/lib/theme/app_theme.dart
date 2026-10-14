import 'package:flutter/material.dart';

import '../core/app_colors.dart';

const String _fontFamily = 'SofiaSans';

abstract class AppTheme {
  AppTheme._();

  static ThemeData dark() => _build(brightness: Brightness.dark);

  static ThemeData light() => _build(brightness: Brightness.light);

  static ThemeData _build({required Brightness brightness}) {
    final bool isDark = brightness == Brightness.dark;

    final Color background =
        isDark ? AppColors.darkBackground : AppColors.lightBackground;
    final Color surface =
        isDark ? AppColors.darkSurface : AppColors.lightSurface;
    final Color text =
        isDark ? AppColors.darkText : AppColors.lightText900;
    final Color textSecondary =
        isDark ? AppColors.darkTextSecondary : AppColors.lightText500;
    final Color border =
        isDark ? AppColors.darkBorder() : AppColors.lightBorder();

    final base = isDark ? ThemeData.dark() : ThemeData.light();

    return base.copyWith(
      scaffoldBackgroundColor: background,
      colorScheme: base.colorScheme.copyWith(
        brightness: brightness,
        primary: AppColors.accent,
        onPrimary: isDark ? Colors.black : Colors.white,
        secondary: AppColors.accent,
        surface: surface,
        onSurface: text,
        error: AppColors.danger,
      ),
      dividerColor: border,
      textTheme: base.textTheme.apply(
        fontFamily: _fontFamily,
        bodyColor: text,
        displayColor: text,
      ).copyWith(
        bodySmall: TextStyle(
          fontFamily: _fontFamily,
          color: textSecondary,
          fontSize: 12,
        ),
      ),
      appBarTheme: AppBarTheme(
        backgroundColor: surface,
        foregroundColor: text,
        elevation: 0,
        centerTitle: false,
        titleTextStyle: TextStyle(
          fontFamily: _fontFamily,
          color: text,
          fontSize: 18,
          fontWeight: FontWeight.w700,
        ),
      ),
      bottomNavigationBarTheme: BottomNavigationBarThemeData(
        backgroundColor: surface,
        selectedItemColor: AppColors.accent,
        unselectedItemColor:
            isDark ? Colors.white38 : Colors.black38,
        type: BottomNavigationBarType.fixed,
        showUnselectedLabels: true,
        selectedLabelStyle: const TextStyle(
          fontFamily: _fontFamily,
          fontSize: 10,
          fontWeight: FontWeight.w600,
        ),
        unselectedLabelStyle: const TextStyle(
          fontFamily: _fontFamily,
          fontSize: 10,
          fontWeight: FontWeight.w500,
        ),
      ),
      dividerTheme: DividerThemeData(color: border, thickness: 1),
      snackBarTheme: SnackBarThemeData(
        backgroundColor: isDark ? AppColors.darkSurface3 : const Color(0xFF323232),
        contentTextStyle: const TextStyle(fontFamily: _fontFamily, color: Colors.white),
      ),
      progressIndicatorTheme: const ProgressIndicatorThemeData(
        color: AppColors.accent,
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: isDark ? AppColors.darkSurface2 : AppColors.lightSurfaceMid,
        hintStyle: TextStyle(color: textSecondary, fontFamily: _fontFamily),
        labelStyle: TextStyle(color: textSecondary, fontFamily: _fontFamily),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: border),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: border),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: AppColors.accent, width: 1.4),
        ),
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: AppColors.accent,
          foregroundColor: isDark ? Colors.black : Colors.white,
          textStyle: const TextStyle(
            fontFamily: _fontFamily,
            fontWeight: FontWeight.w700,
          ),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
        ),
      ),
    );
  }
}
