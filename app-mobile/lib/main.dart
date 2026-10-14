import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'core/api_client.dart';
import 'providers/auth_provider.dart';
import 'providers/theme_provider.dart';
import 'screens/root_gate.dart';
import 'services/api_service.dart';
import 'services/notification_service.dart';
import 'theme/app_theme.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Firebase Cloud Messaging (Android: live; iOS: after the APNs key is added).
  await NotificationService.init();

  final apiClient = ApiClient();
  final apiService = ApiService(apiClient);
  final authProvider = AuthProvider(apiService);
  final themeProvider = ThemeProvider();

  await authProvider.init();
  await themeProvider.init();

  runApp(
    MultiProvider(
      providers: [
        ChangeNotifierProvider<AuthProvider>.value(value: authProvider),
        ChangeNotifierProvider<ThemeProvider>.value(value: themeProvider),
        Provider<ApiService>.value(value: apiService),
      ],
      child: const LivescoresTipsApp(),
    ),
  );
}

class LivescoresTipsApp extends StatelessWidget {
  const LivescoresTipsApp({super.key});

  @override
  Widget build(BuildContext context) {
    final theme = context.watch<ThemeProvider>();

    return MaterialApp(
      title: 'LiveScores Tips',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.light(),
      darkTheme: AppTheme.dark(),
      themeMode: theme.isDark ? ThemeMode.dark : ThemeMode.light,
      home: const RootGate(),
    );
  }
}
