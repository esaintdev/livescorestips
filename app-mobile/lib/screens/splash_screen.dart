import 'dart:async';
import 'dart:math' as math;

import 'package:flutter/material.dart';

import '../core/app_colors.dart';

/// Animated launch splash.
///
/// * Favicon centred on the brand background.
/// * Thin, open circular loader ring rotating clockwise around it.
/// * Favicon rotates counter-clockwise (a little slower than the ring).
/// * Outro: the ring stops and fades, the favicon does a short final spin and
///   scales up explosively so it blows past the viewport, revealing the
///   onboarding screen sitting underneath.
class AnimatedSplash extends StatefulWidget {
  const AnimatedSplash({super.key, required this.onFinished, this.onReveal});

  /// Fired when the outro begins, so the target screen can be mounted and start
  /// settling in underneath the expanding favicon.
  final VoidCallback? onReveal;

  final VoidCallback onFinished;

  @override
  State<AnimatedSplash> createState() => _AnimatedSplashState();
}

class _AnimatedSplashState extends State<AnimatedSplash>
    with TickerProviderStateMixin {
  static const String _favicon = 'assets/images/icon.png';
  static const double _ringSize = 152;
  static const double _faviconSize = 58;

  /// Continuous clockwise loader rotation.
  late final AnimationController _ring = AnimationController(
    vsync: this,
    duration: const Duration(milliseconds: 1300),
  )..repeat();

  /// Continuous (slower) counter-clockwise favicon rotation.
  late final AnimationController _spin = AnimationController(
    vsync: this,
    duration: const Duration(milliseconds: 2400),
  )..repeat();

  /// One-shot outro: loader fade + favicon blow-up.
  late final AnimationController _outro = AnimationController(
    vsync: this,
    duration: const Duration(milliseconds: 950),
  );

  Timer? _introTimer;

  @override
  void initState() {
    super.initState();
    _introTimer = Timer(const Duration(milliseconds: 2200), _playOutro);
  }

  Future<void> _playOutro() async {
    if (!mounted) return;
    _ring.stop(); // loader decelerates to a halt
    widget.onReveal?.call(); // mount the target so it settles in underneath
    await _outro.forward();
    if (mounted) widget.onFinished();
  }

  @override
  void dispose() {
    _introTimer?.cancel();
    _ring.dispose();
    _spin.dispose();
    _outro.dispose();
    super.dispose();
  }

  double _window(double t, double a, double b) =>
      ((t - a) / (b - a)).clamp(0.0, 1.0);

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: Listenable.merge([_ring, _spin, _outro]),
      builder: (context, _) {
        final double t = _outro.value;

        // Background lifts away mid-outro to expose the onboarding behind.
        final double bgAlpha =
            1 - Curves.easeInOut.transform(_window(t, 0.14, 0.86));
        final double ringOpacity =
            1 - Curves.easeIn.transform(_window(t, 0.0, 0.42));
        // The favicon fills the viewport with brand colour, then dissolves as it
        // blows past the camera.
        final double favOpacity =
            1 - Curves.easeIn.transform(_window(t, 0.80, 1.0));

        // Brand colour briefly floods the viewport at the peak of the blow-up,
        // so the dark onboarding scene emerges out of the orange.
        final double orangeFill =
            Curves.easeInOut.transform(_window(t, 0.40, 0.66)) *
                (1 - Curves.easeInOut.transform(_window(t, 0.66, 0.95)));

        // Explosive, accelerating growth (slow start -> huge finish).
        final double scale = 1 + Curves.easeInCubic.transform(t) * 60;

        final double ringAngle = _ring.value * 2 * math.pi; // clockwise
        final double favAngle =
            _spin.value * -2 * math.pi - Curves.easeIn.transform(t) * 0.9;

        return Stack(
          children: [
            Positioned.fill(
              child: ColoredBox(
                color: AppColors.darkBackground.withValues(alpha: bgAlpha),
                child: Stack(
                  clipBehavior: Clip.none,
                  alignment: Alignment.center,
                  children: [
                    if (ringOpacity > 0.001)
                      Opacity(
                        opacity: ringOpacity,
                        child: Transform.rotate(
                          angle: ringAngle,
                          child: const SizedBox(
                            width: _ringSize,
                            height: _ringSize,
                            child: CustomPaint(painter: _LoaderRingPainter()),
                          ),
                        ),
                      ),
                    if (favOpacity > 0.001)
                      Opacity(
                        opacity: favOpacity,
                        child: Transform.rotate(
                          angle: favAngle,
                          child: Transform.scale(
                            scale: scale,
                            child: Image.asset(
                              _favicon,
                              width: _faviconSize,
                              height: _faviconSize,
                              filterQuality: FilterQuality.high,
                            ),
                          ),
                        ),
                      ),
                  ],
                ),
              ),
            ),
            if (orangeFill > 0.001)
              Positioned.fill(
                child: IgnorePointer(
                  child: ColoredBox(
                    color: AppColors.accent.withValues(alpha: orangeFill),
                  ),
                ),
              ),
          ],
        );
      },
    );
  }
}

/// Thin, open circular loader arc with rounded caps and a soft comet tail.
class _LoaderRingPainter extends CustomPainter {
  const _LoaderRingPainter();

  @override
  void paint(Canvas canvas, Size size) {
    const double stroke = 3.0;
    final Rect rect = (Offset.zero & size).deflate(stroke / 2 + 0.5);

    final Paint paint = Paint()
      ..style = PaintingStyle.stroke
      ..strokeWidth = stroke
      ..strokeCap = StrokeCap.round
      ..shader = SweepGradient(
        startAngle: 0,
        endAngle: math.pi * 2,
        colors: [
          AppColors.accent.withValues(alpha: 0.0),
          AppColors.accent.withValues(alpha: 0.15),
          AppColors.accent.withValues(alpha: 0.85),
          AppColors.accent,
        ],
        stops: const [0.0, 0.35, 0.78, 1.0],
        transform: const GradientRotation(-math.pi / 2),
      ).createShader(rect);

    // 265° of arc -> ~95° open gap.
    const double start = -math.pi / 2;
    const double sweep = math.pi * 1.47;
    canvas.drawArc(rect, start, sweep, false, paint);
  }

  @override
  bool shouldRepaint(covariant _LoaderRingPainter oldDelegate) => false;
}
