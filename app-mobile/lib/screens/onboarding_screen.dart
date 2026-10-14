import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../core/app_colors.dart';
import '../providers/auth_provider.dart';
import 'login_screen.dart';

/// Two-screen onboarding built around a football-broadcast visual language:
/// a full-bleed editorial hero, sharp typography and restrained accents — no
/// phone mockups, no cards, no floating stat boxes.
class OnboardingScreen extends StatefulWidget {
  const OnboardingScreen({super.key, required this.onDone});

  final VoidCallback onDone;

  @override
  State<OnboardingScreen> createState() => _OnboardingScreenState();
}

class _OnboardingScreenState extends State<OnboardingScreen>
    with TickerProviderStateMixin {
  final PageController _pages = PageController();
  late final AnimationController _entrance = AnimationController(
    vsync: this,
    duration: const Duration(milliseconds: 700),
  )..forward();

  int _currentPage = 0;

  @override
  void initState() {
    super.initState();
    _pages.addListener(_onScroll);
  }

  void _onScroll() {
    final int p = (_pages.page ?? 0).round();
    if (p != _currentPage) setState(() => _currentPage = p);
  }

  @override
  void dispose() {
    _pages.removeListener(_onScroll);
    _pages.dispose();
    _entrance.dispose();
    super.dispose();
  }

  bool get _isLast => _currentPage == 1;

  void _next() {
    _pages.animateToPage(1,
        duration: const Duration(milliseconds: 480), curve: Curves.easeOutCubic);
  }

  Future<void> _signIn() async {
    await Navigator.of(context).push(
      MaterialPageRoute(builder: (_) => const LoginScreen()),
    );
    if (mounted && context.read<AuthProvider>().isAuthenticated) {
      widget.onDone();
    }
  }

  @override
  Widget build(BuildContext context) {
    final bool dark = Theme.of(context).brightness == Brightness.dark;
    final Color bg = dark ? AppColors.darkBackground : AppColors.lightBackground;

    return Material(
      type: MaterialType.transparency,
      child: ColoredBox(
        color: bg,
        child: SafeArea(
          child: AnimatedBuilder(
            animation: _entrance,
            builder: (context, child) {
              final double e = Curves.easeOutCubic.transform(_entrance.value);
              return Opacity(
                opacity: e,
                child: Transform.translate(
                  offset: Offset(0, (1 - e) * 20),
                  child: child,
                ),
              );
            },
            child: Column(
              children: [
                Expanded(
                  child: PageView(
                    controller: _pages,
                    physics: const PageScrollPhysics(),
                    children: [
                      _page(
                        index: 0,
                        hero: const _ScoreboardHero(),
                        headline: 'Football, all in one place.',
                        body:
                            'Predictions, live scores, odds and match insights — without the clutter.',
                      ),
                      _page(
                        index: 1,
                        hero: const _InsightHero(),
                        headline: 'See the signal. Make your call.',
                        body:
                            'Open a match, see the prediction and follow the result in seconds.',
                      ),
                    ],
                  ),
                ),
                _bottom(context, dark),
              ],
            ),
          ),
        ),
      ),
    );
  }

  // -------------------------------------------------------------------------
  // Page composition
  // -------------------------------------------------------------------------

  Widget _page({
    required int index,
    required Widget hero,
    required String headline,
    required String body,
  }) {
    final bool dark = Theme.of(context).brightness == Brightness.dark;
    final Color titleColor = dark ? const Color(0xFFF6F6F6) : AppColors.lightText900;
    final Color bodyColor = dark ? AppColors.darkTextMuted : AppColors.lightText500;

    return LayoutBuilder(
      builder: (context, constraints) {
        return Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            SizedBox(
              height: constraints.maxHeight * 0.56,
              child: _parallax(
                index,
                panX: 48,
                scaleUp: 0.07,
                fade: 0.4,
                child: hero,
              ),
            ),
            const SizedBox(height: 26),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 24),
              child: _parallax(
                index,
                panX: 20,
                dy: -6,
                fade: 0.8,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Single restrained accent — a short bar, not an underline.
                    Container(
                      width: 34,
                      height: 3,
                      decoration: BoxDecoration(
                        color: AppColors.accent,
                        borderRadius: BorderRadius.circular(2),
                      ),
                    ),
                    const SizedBox(height: 16),
                    Text(
                      headline,
                      style: TextStyle(
                        fontSize: 29,
                        fontWeight: FontWeight.w800,
                        height: 1.15,
                        letterSpacing: -0.5,
                        color: titleColor,
                      ),
                    ),
                    const SizedBox(height: 12),
                    Text(
                      body,
                      style: TextStyle(
                        fontSize: 14.5,
                        height: 1.5,
                        color: bodyColor,
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const Spacer(),
          ],
        );
      },
    );
  }

  /// Directional parallax: outgoing content drifts + zooms gently (camera push),
  /// incoming content arrives from the right. Continuous, no rebuild/flash.
  Widget _parallax(
    int index, {
    required Widget child,
    double panX = 24,
    double dy = 0,
    double scaleUp = 0,
    double fade = 0.5,
  }) {
    return AnimatedBuilder(
      animation: _pages,
      child: child,
      builder: (context, c) {
        double delta = 0;
        if (_pages.hasClients) {
          delta = (_pages.page ?? index.toDouble()) - index;
        }
        final double t = delta.clamp(-1.0, 1.0);
        return Transform.translate(
          offset: Offset(t * panX, t.abs() * dy),
          child: Opacity(
            opacity: (1 - t.abs() * fade).clamp(0.0, 1.0),
            child: scaleUp == 0
                ? c
                : Transform.scale(scale: 1 + t.abs() * scaleUp, child: c),
          ),
        );
      },
    );
  }

  // -------------------------------------------------------------------------
  // Bottom area
  // -------------------------------------------------------------------------

  Widget _bottom(BuildContext context, bool dark) {
    final Color muted = dark ? AppColors.darkTextMuted : AppColors.lightText400;

    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 6, 20, 14),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Row(
            children: [
              SizedBox(
                width: 68,
                child: Align(
                  alignment: Alignment.centerLeft,
                  child: AnimatedOpacity(
                    duration: const Duration(milliseconds: 250),
                    opacity: _isLast ? 0 : 1,
                    child: IgnorePointer(
                      ignoring: _isLast,
                      child: TextButton(
                        onPressed: widget.onDone,
                        style: TextButton.styleFrom(
                          foregroundColor: muted,
                          padding: EdgeInsets.zero,
                          minimumSize: const Size(0, 40),
                        ),
                        child: const Text('Skip',
                            style: TextStyle(
                                fontSize: 15, fontWeight: FontWeight.w600)),
                      ),
                    ),
                  ),
                ),
              ),
              Expanded(child: Center(child: _dots(dark))),
              SizedBox(
                width: 68,
                child: Align(
                  alignment: Alignment.centerRight,
                  child: AnimatedOpacity(
                    duration: const Duration(milliseconds: 250),
                    opacity: _isLast ? 0 : 1,
                    child: IgnorePointer(
                      ignoring: _isLast,
                      child: _arrowButton(),
                    ),
                  ),
                ),
              ),
            ],
          ),
          AnimatedSize(
            duration: const Duration(milliseconds: 320),
            curve: Curves.easeOutCubic,
            child: _isLast
                ? Padding(
                    padding: const EdgeInsets.only(top: 14),
                    child: Column(
                      children: [
                        SizedBox(
                          width: double.infinity,
                          child: ElevatedButton(
                            onPressed: widget.onDone,
                            style: ElevatedButton.styleFrom(
                              backgroundColor: AppColors.accent,
                              foregroundColor: const Color(0xFF160A00),
                              padding: const EdgeInsets.symmetric(vertical: 16),
                              shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(14)),
                            ),
                            child: const Text('Get Started',
                                style: TextStyle(
                                    fontSize: 16,
                                    fontWeight: FontWeight.w800)),
                          ),
                        ),
                        const SizedBox(height: 12),
                        _signInRow(dark),
                      ],
                    ),
                  )
                : const SizedBox(width: double.infinity),
          ),
        ],
      ),
    );
  }

  Widget _arrowButton() {
    return Material(
      color: AppColors.accent,
      shape: const CircleBorder(),
      child: InkWell(
        onTap: _next,
        customBorder: const CircleBorder(),
        child: const SizedBox(
          width: 48,
          height: 48,
          child: Icon(Icons.arrow_forward,
              size: 22, color: Color(0xFF160A00)),
        ),
      ),
    );
  }

  Widget _signInRow(bool dark) {
    final Color muted = dark ? AppColors.darkTextMuted : AppColors.lightText400;
    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        Text('Already have an account? ',
            style: TextStyle(fontSize: 13, color: muted)),
        GestureDetector(
          onTap: _signIn,
          behavior: HitTestBehavior.opaque,
          child: const Text('Sign in',
              style: TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.w700,
                  color: AppColors.accent)),
        ),
      ],
    );
  }

  Widget _dots(bool dark) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        for (int i = 0; i < 2; i++)
          AnimatedContainer(
            duration: const Duration(milliseconds: 300),
            curve: Curves.easeOutCubic,
            margin: const EdgeInsets.symmetric(horizontal: 4),
            width: i == _currentPage ? 22 : 7,
            height: 7,
            decoration: BoxDecoration(
              color: i == _currentPage
                  ? AppColors.accent
                  : (dark
                      ? Colors.white.withValues(alpha: 0.22)
                      : Colors.black.withValues(alpha: 0.15)),
              borderRadius: BorderRadius.circular(4),
            ),
          ),
      ],
    );
  }
}

// ---------------------------------------------------------------------------
// Heroes
// ---------------------------------------------------------------------------

/// Screen 1 — a full-bleed live scoreboard over the near-black background.
class _ScoreboardHero extends StatelessWidget {
  const _ScoreboardHero();

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(24, 6, 24, 0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const _LiveTag(),
              const Spacer(),
              Text('LA LIGA',
                  style: TextStyle(
                    fontSize: 10,
                    fontWeight: FontWeight.w700,
                    letterSpacing: 2.2,
                    color: Colors.white.withValues(alpha: 0.4),
                  )),
            ],
          ),
          const SizedBox(height: 28),
          const _ScoreRow('assets/images/teams/barcelona.png', 'BARCELONA', '2'),
          const SizedBox(height: 16),
          const _ScoreRow(
              'assets/images/teams/real-madrid.png', 'REAL MADRID', '1'),
        ],
      ),
    );
  }
}

class _LiveTag extends StatelessWidget {
  const _LiveTag();

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(
          width: 7,
          height: 7,
          decoration: const BoxDecoration(
              color: AppColors.accent, shape: BoxShape.circle),
        ),
        const SizedBox(width: 7),
        const Text('LIVE',
            style: TextStyle(
                fontSize: 11,
                fontWeight: FontWeight.w800,
                letterSpacing: 1.4,
                color: AppColors.accent)),
        const SizedBox(width: 9),
        Text("68'",
            style: TextStyle(
                fontSize: 11,
                fontWeight: FontWeight.w700,
                color: Colors.white.withValues(alpha: 0.4))),
      ],
    );
  }
}

class _ScoreRow extends StatelessWidget {
  const _ScoreRow(this.crest, this.name, this.score);

  final String crest;
  final String name;
  final String score;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        _Crest(crest, size: 22),
        const SizedBox(width: 11),
        Expanded(
          child: Text(
            name,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: const TextStyle(
              fontSize: 24,
              fontWeight: FontWeight.w900,
              height: 1,
              letterSpacing: 0.4,
              color: Color(0xFFF6F6F6),
            ),
          ),
        ),
        Text(
          score,
          style: const TextStyle(
            fontSize: 26,
            fontWeight: FontWeight.w900,
            height: 1,
            color: Color(0xFFF6F6F6),
          ),
        ),
      ],
    );
  }
}

/// Screen 2 — the camera moves in: one fixture, the prediction laid out
/// typographically with a single thin rule.
class _InsightHero extends StatelessWidget {
  const _InsightHero();

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(24, 6, 24, 0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const _FixtureLine('assets/images/teams/barcelona.png', 'BARCELONA'),
          Padding(
            padding: const EdgeInsets.only(left: 4, top: 4, bottom: 4),
            child: Text('vs',
                style: TextStyle(
                    fontSize: 13,
                    fontStyle: FontStyle.italic,
                    color: Colors.white.withValues(alpha: 0.35))),
          ),
          const _FixtureLine('assets/images/teams/real-madrid.png', 'REAL MADRID'),
          const SizedBox(height: 26),
          Container(
            height: 1,
            color: AppColors.accent.withValues(alpha: 0.5),
          ),
          const SizedBox(height: 20),
          Row(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: const [
                  _MetaLabel('PREDICTION'),
                  SizedBox(height: 4),
                  Text('1X',
                      style: TextStyle(
                        fontSize: 46,
                        fontWeight: FontWeight.w900,
                        height: 1,
                        color: AppColors.accent,
                      )),
                ],
              ),
              const Spacer(),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: const [
                  _MetaLabel('CONFIDENCE'),
                  SizedBox(height: 4),
                  Text('81%',
                      style: TextStyle(
                          fontSize: 22,
                          fontWeight: FontWeight.w800,
                          height: 1.1,
                          color: Color(0xFFF6F6F6))),
                ],
              ),
              const SizedBox(width: 26),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: const [
                  _MetaLabel('ODDS'),
                  SizedBox(height: 4),
                  Text('1.42',
                      style: TextStyle(
                          fontSize: 22,
                          fontWeight: FontWeight.w800,
                          height: 1.1,
                          color: AppColors.oddsYellow)),
                ],
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _FixtureLine extends StatelessWidget {
  const _FixtureLine(this.crest, this.name);

  final String crest;
  final String name;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        _Crest(crest, size: 26),
        const SizedBox(width: 12),
        Expanded(
          child: Text(
            name,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: const TextStyle(
              fontSize: 24,
              fontWeight: FontWeight.w900,
              height: 1,
              letterSpacing: 0.4,
              color: Color(0xFFF6F6F6),
            ),
          ),
        ),
      ],
    );
  }
}

class _MetaLabel extends StatelessWidget {
  const _MetaLabel(this.text);

  final String text;

  @override
  Widget build(BuildContext context) {
    return Text(text,
        style: TextStyle(
          fontSize: 9,
          fontWeight: FontWeight.w700,
          letterSpacing: 1.8,
          color: Colors.white.withValues(alpha: 0.4),
        ));
  }
}

class _Crest extends StatelessWidget {
  const _Crest(this.asset, {this.size = 28});

  final String asset;
  final double size;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: size,
      height: size,
      child: Image.asset(
        asset,
        fit: BoxFit.contain,
        errorBuilder: (context, error, stack) => Center(
          child: Container(
            width: size * 0.6,
            height: size * 0.6,
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.08),
              shape: BoxShape.circle,
            ),
          ),
        ),
      ),
    );
  }
}
