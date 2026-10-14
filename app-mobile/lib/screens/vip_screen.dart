import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';

import '../core/app_colors.dart';
import '../core/config.dart';
import '../models/vip.dart';
import '../services/api_service.dart';
import '../widgets/app_ui.dart';
import '../widgets/page_header.dart';
import 'vip_results_screen.dart';

class VipScreen extends StatefulWidget {
  const VipScreen({super.key});

  @override
  State<VipScreen> createState() => _VipScreenState();
}

class _VipScreenState extends State<VipScreen>
    with AutomaticKeepAliveClientMixin {
  Map<String, List<VipPlan>> _plans = const {};
  Map<String, dynamic> _countries = const {};
  String? _country;
  String? _title;
  String? _subtitle;
  bool _loading = true;
  String? _error;

  @override
  bool get wantKeepAlive => true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    final api = context.read<ApiService>();
    try {
      final result = await api.getVipPlans();
      if (!mounted) return;
      setState(() {
        _plans = result.plans;
        _countries = result.countries;
        _title = result.meta['page_subheader'] ?? result.meta['page_title'];
        _subtitle = result.meta['page_description'];
        _loading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _error = e.toString();
        _loading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    super.build(context);
    return RefreshIndicator(
      onRefresh: _load,
      color: AppColors.accent,
      child: CustomScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        slivers: [
          SliverToBoxAdapter(
            child: PageHeader(
              title: _title ?? 'Choose Your Perfect Plan',
              subtitle: _subtitle,
            ),
          ),
          if (_loading)
            const SliverToBoxAdapter(
              child: Padding(
                padding: EdgeInsets.symmetric(vertical: 60),
                child: Center(child: CircularProgressIndicator()),
              ),
            )
          else if (_error != null)
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.all(24),
                child: Text(_error ?? 'Something went wrong.',
                    textAlign: TextAlign.center,
                    style:
                        const TextStyle(fontSize: 13, color: AppColors.danger)),
              ),
            )
          else ...[
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(16, 4, 16, 8),
                child: _countrySelector(context),
              ),
            ),
            if (_plans.isEmpty)
              const SliverToBoxAdapter(
                child: Padding(
                  padding: EdgeInsets.all(24),
                  child: Center(child: Text('No VIP plans available.')),
                ),
              )
            else
              SliverPadding(
                padding: const EdgeInsets.fromLTRB(16, 4, 16, 0),
                sliver: SliverList(
                  delegate: SliverChildBuilderDelegate(
                    (context, index) {
                      final category = _plans.keys.elementAt(index);
                      final plans = _plans[category]!;
                      return _planGroup(context, category, plans);
                    },
                    childCount: _plans.length,
                  ),
                ),
              ),
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(16, 8, 16, 0),
                child: _resultsLink(context),
              ),
            ),
            const SliverToBoxAdapter(child: SizedBox(height: 24)),
          ],
        ],
      ),
    );
  }

  Widget _countrySelector(BuildContext context) {
    // Countries come from the CurrencyService on the backend. If empty, hide.
    if (_countries.isEmpty) return const SizedBox.shrink();

    final sorted = _countries.keys.toList()..sort();

    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppTokens.surface(context),
        borderRadius: BorderRadius.circular(AppTokens.radius),
        border: Border.all(color: AppTokens.border(context)),
      ),
      child: Row(
        children: [
          const Icon(Icons.public, color: AppColors.accent),
          const SizedBox(width: 12),
          Expanded(
            child: DropdownButtonHideUnderline(
              child: DropdownButton<String>(
                isExpanded: true,
                value: _country,
                hint: const Text('Select Your Country',
                    style: TextStyle(fontSize: 14)),
                items: [
                  for (final c in sorted)
                    DropdownMenuItem(value: c, child: Text(c)),
                ],
                onChanged: (v) => setState(() => _country = v),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _planGroup(BuildContext context, String category, List<VipPlan> plans) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const SizedBox(height: 6),
        AppSectionHeader(title: category, upperCase: true),
        const SizedBox(height: 14),
        for (final plan in plans) _planCard(context, plan),
      ],
    );
  }

  Widget _planCard(BuildContext context, VipPlan plan) {
    final String symbol = _symbol();
    final double converted = plan.price * _rate();
    final Color titleColor = AppTokens.textPrimary(context);
    final Color bodyColor = AppTokens.textSecondary(context);

    return Container(
      width: double.infinity,
      margin: const EdgeInsets.only(bottom: 16),
      decoration: BoxDecoration(
        color: AppTokens.surface(context),
        borderRadius: BorderRadius.circular(AppTokens.radius),
        border: Border.all(color: AppTokens.border(context)),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Container(
            padding: const EdgeInsets.symmetric(vertical: 13, horizontal: 20),
            decoration: const BoxDecoration(
              color: AppColors.accent,
              borderRadius: BorderRadius.vertical(
                  top: Radius.circular(AppTokens.radius - 0.5)),
            ),
            child: Text(
              plan.name,
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.w800,
                color: Colors.white,
              ),
            ),
          ),
          Padding(
            padding: const EdgeInsets.fromLTRB(20, 18, 20, 20),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                if (plan.duration != null && plan.duration!.isNotEmpty) ...[
                  Text(
                    plan.duration!.toUpperCase(),
                    textAlign: TextAlign.center,
                    style: const TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w700,
                      letterSpacing: 1,
                      color: AppColors.accent,
                    ),
                  ),
                  const SizedBox(height: 10),
                ],
                if (plan.badgeText != null && plan.badgeText!.isNotEmpty) ...[
                  Center(
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 12, vertical: 4),
                      decoration: BoxDecoration(
                        color: AppColors.accent.withValues(alpha: 0.15),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Text(plan.badgeText!,
                          style: const TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.w700,
                              color: AppColors.accent)),
                    ),
                  ),
                  const SizedBox(height: 10),
                ],
                Text(
                  '$symbol${converted.toStringAsFixed(0)}',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    fontSize: 42,
                    fontWeight: FontWeight.w800,
                    color: titleColor,
                  ),
                ),
                if (_country != null && _currencyCode() != 'USD') ...[
                  const SizedBox(height: 2),
                  Text('(${_currencyCode()})',
                      textAlign: TextAlign.center,
                      style: TextStyle(fontSize: 11, color: bodyColor)),
                ],
                if (plan.features.isNotEmpty) ...[
                  const SizedBox(height: 16),
                  for (final f in _cleanFeatures(plan.features))
                    Padding(
                      padding: const EdgeInsets.only(bottom: 8),
                      child: Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Padding(
                            padding: EdgeInsets.only(top: 1),
                            child: Icon(Icons.check_circle,
                                size: 15, color: AppColors.success),
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(f,
                                style: TextStyle(
                                    fontSize: 13,
                                    height: 1.4,
                                    color: bodyColor)),
                          ),
                        ],
                      ),
                    ),
                ],
                const SizedBox(height: 18),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: () => _subscribe(plan),
                    style: ElevatedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12)),
                    ),
                    child: const Text(
                      'Subscribe Now',
                      style:
                          TextStyle(fontSize: 15, fontWeight: FontWeight.w800),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _resultsLink(BuildContext context) {
    return AppSurface(
      accent: AppColors.accent,
      padding: const EdgeInsets.all(16),
      child: Row(
        children: [
          const Icon(Icons.emoji_events, color: AppColors.accent, size: 20),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              'See our recent VIP results',
              style: TextStyle(
                fontSize: 15,
                fontWeight: FontWeight.w700,
                color: AppTokens.textPrimary(context),
              ),
            ),
          ),
          TextButton(
            onPressed: () => Navigator.of(context).push(
              MaterialPageRoute(builder: (_) => const VipResultsScreen()),
            ),
            child: const Text('View',
                style: TextStyle(fontWeight: FontWeight.w700)),
          ),
        ],
      ),
    );
  }

  Future<void> _subscribe(VipPlan plan) async {
    final uri = Uri.parse('${AppConfig.websiteUrl}/vip/${plan.id}/checkout');
    if (!await launchUrl(uri, mode: LaunchMode.externalApplication)) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Could not open the checkout page.')),
        );
      }
    }
  }

  List<String> _cleanFeatures(List<String> features) {
    final out = <String>[];
    for (final f in features) {
      final cleaned = f
          .trim()
          .replaceAll('Access Access', 'Access')
          .replaceAll('Access Service Access', 'Access');
      if (cleaned.isEmpty || cleaned == 'Access') continue;
      out.add(cleaned);
    }
    return out;
  }

  String _symbol() => (_countries[_country] is Map)
      ? ((_countries[_country] as Map)['symbol']?.toString() ?? '\$')
      : '\$';

  String _currencyCode() => (_countries[_country] is Map)
      ? ((_countries[_country] as Map)['code']?.toString() ?? 'USD')
      : 'USD';

  double _rate() {
    final c = _countries[_country];
    if (c is Map) {
      final r = c['rate'];
      if (r is num) return r.toDouble();
      return double.tryParse(r?.toString() ?? '') ?? 1.0;
    }
    return 1.0;
  }
}
