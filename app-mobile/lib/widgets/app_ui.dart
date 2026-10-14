import 'package:flutter/material.dart';

import '../core/app_colors.dart';

/// Central surface + token helpers so every screen shares one visual language.
/// Light, thin, moderate radii — hierarchy comes from type and spacing, not
/// from heavy filled panels.
abstract class AppTokens {
  static bool dark(BuildContext c) =>
      Theme.of(c).brightness == Brightness.dark;

  static Color bg(BuildContext c) =>
      dark(c) ? AppColors.darkBackground : AppColors.lightBackground;

  static Color surface(BuildContext c) =>
      dark(c) ? const Color(0xFF111111) : AppColors.lightSurface;

  static Color surfaceRaised(BuildContext c) =>
      dark(c) ? const Color(0xFF161616) : const Color(0xFFF7F7F9);

  static Color border(BuildContext c) =>
      dark(c) ? Colors.white.withValues(alpha: 0.06) : AppColors.lightBorder();

  static Color divider(BuildContext c) =>
      dark(c) ? Colors.white.withValues(alpha: 0.05) : AppColors.lightBorder(0.07);

  static Color textPrimary(BuildContext c) =>
      dark(c) ? const Color(0xFFF2F2F2) : AppColors.lightText900;

  static Color textSecondary(BuildContext c) =>
      dark(c) ? AppColors.darkTextSecondary : AppColors.lightText500;

  static Color textMuted(BuildContext c) =>
      dark(c) ? AppColors.darkTextMuted : AppColors.lightText400;

  static const double radius = 10;
  static const EdgeInsets cardPadding = EdgeInsets.all(16);
}

/// A light surface panel. Use sparingly — hero / summary blocks only.
class AppSurface extends StatelessWidget {
  const AppSurface({
    super.key,
    required this.child,
    this.padding = AppTokens.cardPadding,
    this.raised = false,
    this.accent,
  });

  final Widget child;
  final EdgeInsetsGeometry padding;
  final bool raised;

  /// Optional emphasis colour — tints the surface border.
  final Color? accent;

  @override
  Widget build(BuildContext context) {
    final Color color = raised
        ? AppTokens.surfaceRaised(context)
        : AppTokens.surface(context);
    // A uniform border is required whenever a borderRadius is present.
    final Color stroke = accent != null
        ? accent!.withValues(alpha: 0.4)
        : AppTokens.border(context);

    return Container(
      width: double.infinity,
      padding: padding,
      decoration: BoxDecoration(
        color: color,
        borderRadius: BorderRadius.circular(AppTokens.radius),
        border: Border.all(color: stroke),
      ),
      child: child,
    );
  }
}

/// Restrained section header: a short orange tick + title. No icon box.
class AppSectionHeader extends StatelessWidget {
  const AppSectionHeader({
    super.key,
    required this.title,
    this.trailing,
    this.accent = AppColors.accent,
    this.upperCase = false,
  });

  final String title;
  final Widget? trailing;
  final Color accent;
  final bool upperCase;

  @override
  Widget build(BuildContext context) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.center,
      children: [
        Container(
          width: 3,
          height: 14,
          decoration: BoxDecoration(
            color: accent,
            borderRadius: BorderRadius.circular(2),
          ),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: Text(
            upperCase ? title.toUpperCase() : title,
            style: TextStyle(
              fontSize: upperCase ? 12.5 : 15.5,
              fontWeight: upperCase ? FontWeight.w800 : FontWeight.w700,
              letterSpacing: upperCase ? 1.3 : -0.1,
              color: AppTokens.textPrimary(context),
            ),
          ),
        ),
        ?trailing,
      ],
    );
  }
}

/// A small uppercase label above a strong value.
class AppMetric extends StatelessWidget {
  const AppMetric({
    super.key,
    required this.label,
    required this.value,
    this.valueColor,
    this.valueSize = 18,
    this.align = CrossAxisAlignment.start,
    this.textAlign = TextAlign.left,
  });

  final String label;
  final String value;
  final Color? valueColor;
  final double valueSize;
  final CrossAxisAlignment align;
  final TextAlign textAlign;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: align,
      mainAxisSize: MainAxisSize.min,
      children: [
        Text(
          label.toUpperCase(),
          textAlign: textAlign,
          style: TextStyle(
            fontSize: 9.5,
            fontWeight: FontWeight.w700,
            letterSpacing: 0.8,
            color: AppTokens.textMuted(context),
          ),
        ),
        const SizedBox(height: 5),
        Text(
          value,
          textAlign: textAlign,
          maxLines: 2,
          overflow: TextOverflow.ellipsis,
          style: TextStyle(
            fontSize: valueSize,
            fontWeight: FontWeight.w800,
            height: 1.1,
            color: valueColor ?? AppTokens.textPrimary(context),
          ),
        ),
      ],
    );
  }
}

/// 1px hairline divider.
class AppDivider extends StatelessWidget {
  const AppDivider({super.key, this.indent = 0, this.endIndent = 0});

  final double indent;
  final double endIndent;

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 1,
      margin: EdgeInsets.only(left: indent, right: endIndent),
      color: AppTokens.divider(context),
    );
  }
}

/// A short vertical rule for separating metrics.
class AppVRule extends StatelessWidget {
  const AppVRule({super.key, this.height = 30});

  final double height;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 1,
      height: height,
      color: AppTokens.divider(context),
    );
  }
}

/// Compact status chip (result / live / status).
class AppStatusChip extends StatelessWidget {
  const AppStatusChip({
    super.key,
    required this.label,
    required this.color,
    this.filled = false,
  });

  final String label;
  final Color color;
  final bool filled;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: filled ? color : color.withValues(alpha: 0.14),
        borderRadius: BorderRadius.circular(6),
        border: Border.all(color: color.withValues(alpha: 0.35)),
      ),
      child: Text(
        label,
        style: TextStyle(
          fontSize: 10,
          fontWeight: FontWeight.w800,
          letterSpacing: 0.5,
          color: filled ? const Color(0xFF160A00) : color,
        ),
      ),
    );
  }
}

/// Refined bullet row (analysis factors).
class AppBullet extends StatelessWidget {
  const AppBullet({super.key, required this.text});

  final String text;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 7),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            margin: const EdgeInsets.only(top: 6),
            width: 5,
            height: 5,
            decoration: const BoxDecoration(
              color: AppColors.accent,
              shape: BoxShape.circle,
            ),
          ),
          const SizedBox(width: 11),
          Expanded(
            child: Text(
              text,
              style: TextStyle(
                fontSize: 13.5,
                height: 1.55,
                color: AppTokens.textSecondary(context),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

/// A compact, divider-based list row.
class AppListRow extends StatelessWidget {
  const AppListRow({
    super.key,
    required this.child,
    this.onTap,
    this.showDivider = true,
    this.padding = const EdgeInsets.symmetric(vertical: 13),
  });

  final Widget child;
  final VoidCallback? onTap;
  final bool showDivider;
  final EdgeInsetsGeometry padding;

  @override
  Widget build(BuildContext context) {
    final Widget content = InkWell(
      onTap: onTap,
      child: Padding(padding: padding, child: child),
    );

    if (!showDivider) return content;
    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [content, const AppDivider()],
    );
  }
}


