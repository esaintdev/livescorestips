import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../core/app_colors.dart';
import '../providers/auth_provider.dart';
import '../providers/theme_provider.dart';
import '../widgets/app_logo.dart';
import 'account_screen.dart';
import 'blog_screen.dart';
import 'category_screen.dart';
import 'home_screen.dart';
import 'login_screen.dart';
import 'tips_screen.dart';
import 'vip_screen.dart';
import 'winnings_screen.dart';

class MainShell extends StatefulWidget {
  const MainShell({super.key});

  @override
  State<MainShell> createState() => _MainShellState();
}

class _MainShellState extends State<MainShell> {
  int _index = 0;

  static const List<Widget> _tabs = [
    HomeScreen(),
    TipsScreen(),
    BlogScreen(),
    VipScreen(),
  ];

  @override
  Widget build(BuildContext context) {
    final bool dark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      backgroundColor: Theme.of(context).scaffoldBackgroundColor,
      appBar: AppBar(
        backgroundColor: dark ? AppColors.darkSurface : AppColors.lightSurface,
        leading: Builder(
          builder: (context) => IconButton(
            icon: Icon(Icons.menu,
                color: dark ? AppColors.darkText : AppColors.lightText900),
            tooltip: 'Open menu',
            onPressed: () => Scaffold.of(context).openDrawer(),
          ),
        ),
        title: AppLogo(isDark: dark, height: 30),
        centerTitle: false,
        actions: [
          _premiumButton(context),
          _themeToggle(context),
          _authButton(context),
          const SizedBox(width: 4),
        ],
      ),
      drawer: const _AppDrawer(),
      body: IndexedStack(index: _index, children: _tabs),
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _index,
        onTap: (i) => setState(() => _index = i),
        items: const [
          BottomNavigationBarItem(
            icon: Icon(Icons.home_outlined),
            activeIcon: Icon(Icons.home),
            label: 'Home',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.list_alt_outlined),
            activeIcon: Icon(Icons.list_alt),
            label: 'Tips',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.article_outlined),
            activeIcon: Icon(Icons.article),
            label: 'Blog',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.workspace_premium_outlined),
            activeIcon: Icon(Icons.workspace_premium),
            label: 'VIP',
          ),
        ],
      ),
    );
  }

  Widget _premiumButton(BuildContext context) {
    return InkWell(
      onTap: () => setState(() => _index = 3),
      borderRadius: BorderRadius.circular(8),
      child: Container(
        margin: const EdgeInsets.symmetric(vertical: 8),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
        decoration: BoxDecoration(
          gradient: const LinearGradient(
            colors: [AppColors.accent, AppColors.accentDeep],
          ),
          borderRadius: BorderRadius.circular(8),
        ),
        child: const Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.workspace_premium, size: 16, color: Colors.white),
            SizedBox(width: 5),
            Text(
              'Go Premium',
              style: TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w700,
                color: Colors.white,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _themeToggle(BuildContext context) {
    final theme = context.watch<ThemeProvider>();
    final bool dark = theme.isDark;
    return IconButton(
      icon: Icon(dark ? Icons.light_mode_outlined : Icons.dark_mode_outlined),
      color: dark ? AppColors.darkTextSecondary : AppColors.lightText500,
      tooltip: 'Toggle theme',
      onPressed: () => theme.toggle(),
    );
  }

  Widget _authButton(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final bool dark = Theme.of(context).brightness == Brightness.dark;

    if (auth.isAuthenticated) {
      return IconButton(
        icon: const Icon(Icons.account_circle, color: AppColors.accent),
        tooltip: 'My account',
        onPressed: () => Navigator.of(context).push(
          MaterialPageRoute(builder: (_) => const AccountScreen()),
        ),
      );
    }

    return IconButton(
      icon: Icon(Icons.person_outline,
          color: dark ? AppColors.darkTextSecondary : AppColors.lightText500),
      tooltip: 'Login',
      onPressed: () => Navigator.of(context).push(
        MaterialPageRoute(builder: (_) => const LoginScreen()),
      ),
    );
  }
}

class _AppDrawer extends StatefulWidget {
  const _AppDrawer();

  @override
  State<_AppDrawer> createState() => _AppDrawerState();
}

class _AppDrawerState extends State<_AppDrawer> {
  @override
  Widget build(BuildContext context) {
    final bool dark = Theme.of(context).brightness == Brightness.dark;
    final Color secondary =
        dark ? AppColors.darkTextSecondary : AppColors.lightText500;

    return Drawer(
      backgroundColor: dark ? AppColors.darkSurface : AppColors.lightSurface,
      child: SafeArea(
        child: ListView(
          padding: EdgeInsets.zero,
          children: [
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 20, 16, 16),
              child: AppLogo(isDark: dark, height: 32),
            ),
            const Divider(height: 1),
            const SizedBox(height: 8),
            _sectionLabel(context, 'Categories', secondary),
            ..._categories(context),
            const SizedBox(height: 8),
            const Divider(height: 1),
            const SizedBox(height: 8),
            _sectionLabel(context, 'Navigation', secondary),
            _drawerItem(context, Icons.home_outlined, 'Home', () {
              Navigator.of(context).pop();
            }),
            _drawerItem(context, Icons.list_alt_outlined, 'All Predictions', () {
              _switchTab(context, 1);
            }),
            _drawerItem(context, Icons.article_outlined, 'Blog', () {
              _switchTab(context, 2);
            }),
            _drawerItem(context, Icons.workspace_premium_outlined, 'VIP', () {
              _switchTab(context, 3);
            }),
            _drawerItem(context, Icons.emoji_events_outlined, 'Recent Winnings',
                () {
              Navigator.of(context).pop();
              Navigator.of(context).push(
                MaterialPageRoute(
                  builder: (_) => Scaffold(
                    appBar: AppBar(title: const Text('Recent Winnings')),
                    body: const WinningsScreen(),
                  ),
                ),
              );
            }),
          ],
        ),
      ),
    );
  }

  Widget _sectionLabel(BuildContext context, String label, Color color) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 4, 16, 6),
      child: Text(
        label.toUpperCase(),
        style: TextStyle(
          fontSize: 11,
          fontWeight: FontWeight.w700,
          letterSpacing: 0.8,
          color: color,
        ),
      ),
    );
  }

  List<Widget> _categories(BuildContext context) {
    const cats = [
      ('over-2-5-goals', 'Over 2.5 Goals'),
      ('over-1-5-goals', 'Over 1.5 Goals'),
      ('btts-gg', 'BTTS/GG'),
      ('double-chance', 'Double Chance'),
      ('draws', 'Draws'),
      ('home-wins', 'Home Wins'),
      ('away-wins', 'Away Wins'),
      ('correct-score', 'Correct Score'),
    ];

    return [
      for (final (slug, name) in cats)
        _drawerItem(context, Icons.chevron_right, name, () {
          Navigator.of(context).pop();
          Navigator.of(context).push(
            MaterialPageRoute(
              builder: (_) => CategoryScreen(slug: slug),
            ),
          );
        }),
    ];
  }

  Widget _drawerItem(
      BuildContext context, IconData icon, String label, VoidCallback onTap) {
    final bool dark = Theme.of(context).brightness == Brightness.dark;
    final Color text = dark ? AppColors.darkTextBody : AppColors.lightText900;

    return ListTile(
      dense: true,
      leading: Icon(icon, size: 20, color: AppColors.accent),
      title: Text(label,
          style:
              TextStyle(fontSize: 14, fontWeight: FontWeight.w500, color: text)),
      onTap: onTap,
    );
  }

  void _switchTab(BuildContext context, int index) {
    final shell = context.findAncestorStateOfType<_MainShellState>();
    if (shell != null) shell.setState(() => shell._index = index);
    Navigator.of(context).pop();
  }
}
