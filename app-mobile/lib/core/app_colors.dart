import 'package:flutter/material.dart';

/// Brand and UI colors, mirrored 1:1 from the website's CSS
/// (`livescore-theme.css` and `light-theme.css`).
class AppColors {
  AppColors._();

  // Brand
  static const Color accent = Color(0xFFFF6B00); // orange
  static const Color accentGradientEnd = Color(0xFFE65E00);
  static const Color accentDeep = Color(0xFFD25800);

  // Status / semantic
  static const Color success = Color(0xFF28A745);
  static const Color danger = Color(0xFFDC3545);
  static const Color liveRed = Color(0xFFFF4444);
  static const Color warning = Color(0xFFFFC107);
  static const Color oddsYellow = Color(0xFFFFDF00);

  // Dark theme surfaces
  static const Color darkBackground = Color(0xFF0A0A0A);
  static const Color darkSurface = Color(0xFF1A1A1A);
  static const Color darkSurface2 = Color(0xFF2A2A2A);
  static const Color darkSurface3 = Color(0xFF1E1E1E);

  static const Color darkText = Color(0xFFE0E0E0);
  static const Color darkTextBody = Color(0xFFEEEEEE);
  static const Color darkTextSecondary = Color(0xFFAAAAAA);
  static const Color darkTextMuted = Color(0xFF888888);

  static Color darkBorder([double alpha = 0.08]) =>
      Colors.white.withValues(alpha: alpha);

  // Light theme surfaces (Apple-inspired, from light-theme.css)
  static const Color lightBackground = Color(0xFFF5F5F7);
  static const Color lightSurface = Color(0xFFFFFFFF);
  static const Color lightSurfaceMid = Color(0xFFF2F2F7);
  static const Color lightSurfaceHover = Color(0xFFE8E8ED);

  static const Color lightText900 = Color(0xFF1D1D1F);
  static const Color lightText500 = Color(0xFF48484A);
  static const Color lightText400 = Color(0xFF6E6E73);
  static const Color lightText200 = Color(0xFFAEAEB2);

  static Color lightBorder([double alpha = 0.09]) =>
      Colors.black.withValues(alpha: alpha);
}
