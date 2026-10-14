import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'main_shell.dart';
import 'onboarding_screen.dart';
import 'splash_screen.dart';

/// Shows the animated launch splash over the app, then reveals the onboarding
/// flow (first launch) or the main shell.
class RootGate extends StatefulWidget {
  const RootGate({super.key});

  @override
  State<RootGate> createState() => _RootGateState();
}

class _RootGateState extends State<RootGate> {
  static const _kOnboardingSeen = 'onboarding_seen';

  bool? _seen;
  bool _revealTarget = false;
  bool _splashDone = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    final prefs = await SharedPreferences.getInstance();
    if (!mounted) return;
    setState(() => _seen = prefs.getBool(_kOnboardingSeen) ?? false);
  }

  Future<void> _finishOnboarding() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool(_kOnboardingSeen, true);
    if (!mounted) return;
    setState(() => _seen = true);
  }

  @override
  Widget build(BuildContext context) {
    // The target is mounted only once the splash outro begins, so its entrance
    // animation plays as the favicon expands and reveals it underneath.
    final Widget target = !_revealTarget || _seen == null
        ? const ColoredBox(color: Color(0xFF0A0A0A))
        : (_seen!
            ? const MainShell()
            : OnboardingScreen(onDone: _finishOnboarding));

    return Stack(
      children: [
        target,
        if (!_splashDone)
          Positioned.fill(
            child: AnimatedSplash(
              onReveal: () => setState(() => _revealTarget = true),
              onFinished: () => setState(() => _splashDone = true),
            ),
          ),
      ],
    );
  }
}
