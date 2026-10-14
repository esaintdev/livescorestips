import 'package:flutter/material.dart';

/// The LiveScores Tips logo, switching between the dark/light variants.
class AppLogo extends StatelessWidget {
  const AppLogo({super.key, this.isDark = true, this.height = 32});

  final bool isDark;
  final double height;

  @override
  Widget build(BuildContext context) {
    return Image.asset(
      isDark ? 'assets/images/logo.png' : 'assets/images/logo_light.png',
      height: height,
      fit: BoxFit.contain,
      errorBuilder: (context, error, stack) => _fallback(context),
    );
  }

  Widget _fallback(BuildContext context) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Image.asset('assets/images/icon.png', height: height * 0.9),
        const SizedBox(width: 8),
        Text(
          'LiveScores Tips',
          style: TextStyle(
            fontWeight: FontWeight.w800,
            fontSize: height * 0.55,
            color: Theme.of(context).colorScheme.onSurface,
          ),
        ),
      ],
    );
  }
}
