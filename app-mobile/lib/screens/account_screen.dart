import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../core/app_colors.dart';
import '../providers/auth_provider.dart';
import '../widgets/app_ui.dart';

class AccountScreen extends StatelessWidget {
  const AccountScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final user = auth.user;

    return Scaffold(
      backgroundColor: AppTokens.bg(context),
      appBar: AppBar(
        backgroundColor: AppTokens.bg(context),
        surfaceTintColor: Colors.transparent,
        elevation: 0,
        scrolledUnderElevation: 0,
        foregroundColor: AppTokens.textPrimary(context),
        title: const Text('My Account', style: TextStyle(fontSize: 16)),
      ),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
        children: [
          AppSurface(
            raised: true,
            padding: const EdgeInsets.all(20),
            child: Column(
              children: [
                const CircleAvatar(
                  radius: 30,
                  backgroundColor: AppColors.accent,
                  child: Icon(Icons.person,
                      size: 34, color: Color(0xFF160A00)),
                ),
                const SizedBox(height: 14),
                Text(
                  user?.name ?? '',
                  style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.w700,
                      color: AppTokens.textPrimary(context)),
                ),
                const SizedBox(height: 4),
                Text(user?.email ?? '',
                    style: TextStyle(
                        fontSize: 13, color: AppTokens.textMuted(context))),
              ],
            ),
          ),
          const SizedBox(height: 20),
          SizedBox(
            height: 52,
            child: ElevatedButton.icon(
              onPressed: () async {
                await context.read<AuthProvider>().logout();
                if (context.mounted) Navigator.of(context).pop();
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.danger.withValues(alpha: 0.12),
                foregroundColor: AppColors.danger,
                elevation: 0,
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12)),
              ),
              icon: const Icon(Icons.logout, size: 18),
              label: const Text('Sign Out',
                  style: TextStyle(fontWeight: FontWeight.w700)),
            ),
          ),
        ],
      ),
    );
  }
}
