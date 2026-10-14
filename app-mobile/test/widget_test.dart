import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

import 'package:livescores_tips/models/prediction.dart';
import 'package:livescores_tips/screens/login_screen.dart';
import 'package:livescores_tips/screens/onboarding_screen.dart';
import 'package:livescores_tips/screens/register_screen.dart';
import 'package:livescores_tips/widgets/app_ui.dart';

Future<void> _pumpAt(WidgetTester tester, Widget child, Size size) async {
  tester.view.physicalSize = size;
  tester.view.devicePixelRatio = 1.0;
  addTearDown(tester.view.resetPhysicalSize);
  addTearDown(tester.view.resetDevicePixelRatio);
  await tester.pumpWidget(MaterialApp(home: child));
  await tester.pumpAndSettle();
}

void main() {
  test('parses nested /api/predictions shape', () {
    final p = Prediction.fromAny({
      'id': 1,
      'fixture_id': 100,
      'slug': 'a-vs-b',
      'league': {'id': 39, 'name': 'Premier League', 'country': 'England', 'logo': 'l.png'},
      'home_team': {'name': 'Arsenal', 'logo': 'h.png'},
      'away_team': {'name': 'Chelsea', 'logo': 'a.png'},
      'score': {'home': 2, 'away': 1},
      'match_date': '2026-10-09',
      'match_time': '17:30:00',
      'prediction': {'tip': '1', 'category': 'Home Win', 'odds': '1.85', 'probability': '54.05'},
      'status': 'pending',
    });

    expect(p.homeTeam, 'Arsenal');
    expect(p.awayTeam, 'Chelsea');
    expect(p.leagueName, 'Premier League');
    expect(p.matchTime, '17:30');
    expect(p.odds, '1.85');
    expect(p.probabilityLabel, '54.05%');
    expect(p.probTier, 'med');
    expect(p.hasScore, true);
    expect(p.scoreLabel, '2-1');
  });

  test('parses flat /api/categories/{slug} shape', () {
    final p = Prediction.fromAny({
      'id': 2,
      'fixture_id': 200,
      'slug': 'c-vs-d',
      'league': {'id': 140, 'name': 'La Liga', 'country': 'Spain', 'logo': 'l.png'},
      'home_team': 'Barcelona',
      'away_team': 'Sevilla',
      'home_team_logo': 'h.png',
      'away_team_logo': 'a.png',
      'home_score': null,
      'away_score': null,
      'match_date': '2026-10-09',
      'match_time': '20:00',
      'tip': 'Over 2.5',
      'category': 'Over 2.5 Goals',
      'odds': '1.95',
      'probability': '80.00',
      'status': 'pending',
    });

    expect(p.homeTeam, 'Barcelona');
    expect(p.awayTeam, 'Sevilla');
    expect(p.leagueName, 'La Liga');
    expect(p.probTier, 'high');
    expect(p.hasScore, false);
  });

  testWidgets('onboarding: Skip completes the flow', (tester) async {
    var done = false;
    await tester.pumpWidget(
      MaterialApp(home: OnboardingScreen(onDone: () => done = true)),
    );

    expect(find.textContaining('Football, all in one place'), findsOneWidget);
    await tester.tap(find.text('Skip'));
    await tester.pumpAndSettle();
    expect(done, true);
  });

  testWidgets('onboarding: arrow advances then Get Started completes',
      (tester) async {
    var done = false;
    await tester.pumpWidget(
      MaterialApp(home: OnboardingScreen(onDone: () => done = true)),
    );

    // Screen 1: circular arrow CTA, no Get Started yet.
    expect(find.byIcon(Icons.arrow_forward), findsOneWidget);
    expect(find.text('Get Started'), findsNothing);

    await tester.tap(find.byIcon(Icons.arrow_forward));
    await tester.pumpAndSettle();

    // Screen 2: final CTA + sign-in affordance.
    expect(find.text('Get Started'), findsOneWidget);
    expect(find.text('Sign in'), findsOneWidget);

    await tester.tap(find.text('Get Started'));
    await tester.pumpAndSettle();
    expect(done, true);
  });

  testWidgets('sign in renders without overflow at phone sizes',
      (tester) async {
    for (final size in const [Size(390, 844), Size(320, 568)]) {
      await _pumpAt(tester, const LoginScreen(), size);
      expect(find.text('Sign In'), findsWidgets);
      expect(find.text('Welcome back'), findsOneWidget);
      expect(find.text('Email'), findsOneWidget);
      expect(find.text('Remember me'), findsOneWidget);
      expect(find.text('Create one'), findsOneWidget);
      expect(tester.takeException(), isNull);
    }
  });

  testWidgets('create account renders without overflow at phone sizes',
      (tester) async {
    for (final size in const [Size(390, 844), Size(320, 568)]) {
      await _pumpAt(tester, const RegisterScreen(), size);
      expect(find.text('Create your account'), findsOneWidget);
      expect(find.text('Full Name'), findsOneWidget);
      expect(find.text('Phone (WhatsApp)'), findsOneWidget);
      expect(find.text('Confirm Password'), findsOneWidget);
      expect(find.text('Sign in'), findsOneWidget);
      expect(tester.takeException(), isNull);
    }
  });

  testWidgets('password visibility toggle works', (tester) async {
    await _pumpAt(tester, const LoginScreen(), const Size(390, 844));
    expect(find.byIcon(Icons.visibility_off_outlined), findsOneWidget);
    await tester.tap(find.byIcon(Icons.visibility_off_outlined));
    await tester.pump();
    expect(find.byIcon(Icons.visibility_outlined), findsOneWidget);
  });

  testWidgets('sign in -> create one navigates to register', (tester) async {
    await _pumpAt(tester, const LoginScreen(), const Size(390, 844));
    await tester.tap(find.text('Create one'));
    await tester.pumpAndSettle();
    expect(find.text('Create your account'), findsOneWidget);
  });

  testWidgets('app_ui components render', (tester) async {
    await tester.pumpWidget(MaterialApp(
      home: Scaffold(
        body: ListView(children: [
          AppSurface(child: Text('SURFACE_OK')),
          AppSurface(accent: Colors.orange, child: Text('ACCENT_OK')),
          AppSectionHeader(title: 'HEADER_OK'),
          AppMetric(label: 'L', value: 'METRIC_OK'),
          AppBullet(text: 'BULLET_OK'),
        ]),
      ),
    ));
    expect(find.text('SURFACE_OK'), findsOneWidget);
    expect(find.text('ACCENT_OK'), findsOneWidget);
    expect(find.text('HEADER_OK'), findsOneWidget);
    expect(find.text('METRIC_OK'), findsOneWidget);
    expect(find.text('BULLET_OK'), findsOneWidget);
  });
}
