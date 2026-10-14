import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../core/app_colors.dart';
import '../models/vip.dart';
import '../services/api_service.dart';
import '../widgets/app_ui.dart';

class VipResultsScreen extends StatefulWidget {
  const VipResultsScreen({super.key});

  @override
  State<VipResultsScreen> createState() => _VipResultsScreenState();
}

class _VipResultsScreenState extends State<VipResultsScreen> {
  final ScrollController _scroll = ScrollController();

  final List<VipResult> _results = [];
  bool _loading = true;
  bool _loadingMore = false;
  String? _error;

  int _page = 1;
  int _lastPage = 1;
  double _winRate = 0;

  @override
  void initState() {
    super.initState();
    _scroll.addListener(_onScroll);
    _load();
  }

  @override
  void dispose() {
    _scroll.dispose();
    super.dispose();
  }

  void _onScroll() {
    if (_scroll.position.pixels >= _scroll.position.maxScrollExtent - 400) {
      _loadMore();
    }
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    final api = context.read<ApiService>();
    try {
      final result = await api.getVipResults(page: 1);
      if (!mounted) return;
      setState(() {
        _results
          ..clear()
          ..addAll(result.items);
        _page = result.page.currentPage;
        _lastPage = result.page.lastPage;
        _winRate =
            double.tryParse(result.meta['overall_win_rate'] ?? '') ?? 0;
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

  Future<void> _loadMore() async {
    if (_loadingMore || _loading || _page >= _lastPage) return;
    _loadingMore = true;
    final api = context.read<ApiService>();
    try {
      final result = await api.getVipResults(page: _page + 1);
      if (!mounted) return;
      setState(() {
        _results.addAll(result.items);
        _page = result.page.currentPage;
        _lastPage = result.page.lastPage;
        _loadingMore = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() => _loadingMore = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('VIP Results')),
      body: RefreshIndicator(
        onRefresh: _load,
        color: AppColors.accent,
        child: CustomScrollView(
          controller: _scroll,
          physics: const AlwaysScrollableScrollPhysics(),
          slivers: [
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: AppSurface(
                  accent: AppColors.accent,
                  padding: const EdgeInsets.symmetric(vertical: 18, horizontal: 16),
                  child: Column(
                    children: [
                      Text('${_winRate.toStringAsFixed(1)}%',
                          style: const TextStyle(
                              fontSize: 30,
                              fontWeight: FontWeight.w800,
                              color: AppColors.accent)),
                      const SizedBox(height: 4),
                      Text('Overall Win Rate',
                          style: TextStyle(
                              fontSize: 12,
                              color: AppTokens.textMuted(context))),
                    ],
                  ),
                ),
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
                      style: const TextStyle(
                          fontSize: 13, color: AppColors.danger)),
                ),
              )
            else if (_results.isEmpty)
              const SliverToBoxAdapter(
                child: Padding(
                  padding: EdgeInsets.all(24),
                  child: Center(child: Text('No VIP results yet.')),
                ),
              )
            else
              SliverPadding(
                padding: const EdgeInsets.fromLTRB(16, 0, 16, 0),
                sliver: SliverList(
                  delegate: SliverChildBuilderDelegate(
                    (context, index) => _resultCard(_results[index]),
                    childCount: _results.length,
                  ),
                ),
              ),
            if (_loadingMore)
              const SliverToBoxAdapter(
                child: Padding(
                  padding: EdgeInsets.symmetric(vertical: 20),
                  child: Center(child: CircularProgressIndicator()),
                ),
              ),
            const SliverToBoxAdapter(child: SizedBox(height: 24)),
          ],
        ),
      ),
    );
  }

  Widget _resultCard(VipResult result) {
    final bool dark = Theme.of(context).brightness == Brightness.dark;
    final Color statusColor =
        result.isWon ? AppColors.success : AppColors.danger;

    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppTokens.surface(context),
        borderRadius: BorderRadius.circular(AppTokens.radius),
        border: Border.all(color: AppTokens.border(context)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                _dateLabel(result.date),
                style: TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.w700,
                  color: dark ? AppColors.darkTextBody : AppColors.lightText900,
                ),
              ),
              Row(
                children: [
                  if (result.totalOdds != null && result.totalOdds!.isNotEmpty)
                    Text('Total Odds: ${result.totalOdds}',
                        style: TextStyle(
                            fontSize: 11,
                            color: dark
                                ? AppColors.darkTextMuted
                                : AppColors.lightText400)),
                  const SizedBox(width: 10),
                  Container(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                    decoration: BoxDecoration(
                      color: statusColor.withValues(alpha: 0.15),
                      borderRadius: BorderRadius.circular(5),
                    ),
                    child: Text(
                      result.isWon ? 'WON' : 'LOST',
                      style: TextStyle(
                          fontSize: 10,
                          fontWeight: FontWeight.w800,
                          color: statusColor,
                          letterSpacing: 0.5),
                    ),
                  ),
                ],
              ),
            ],
          ),
          const SizedBox(height: 12),
          for (final item in result.items) _itemRow(item),
        ],
      ),
    );
  }

  Widget _itemRow(VipResultItem item) {
    final bool dark = Theme.of(context).brightness == Brightness.dark;
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 5),
      child: Row(
        children: [
          Expanded(
            child: Text(
              '${item.homeTeam ?? ''} vs ${item.awayTeam ?? ''}',
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.w500,
                  color: dark ? AppColors.darkTextBody : AppColors.lightText900),
            ),
          ),
          const SizedBox(width: 8),
          Text(
            '${item.homeScore ?? '-'}-${item.awayScore ?? '-'}',
            style: TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w700,
                color: dark ? AppColors.darkTextSecondary : AppColors.lightText500),
          ),
          const SizedBox(width: 10),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
            decoration: BoxDecoration(
              color: AppColors.accent.withValues(alpha: 0.12),
              borderRadius: BorderRadius.circular(4),
            ),
            child: Text(item.tip ?? '-',
                style: const TextStyle(
                    fontSize: 10,
                    fontWeight: FontWeight.w700,
                    color: AppColors.accent)),
          ),
        ],
      ),
    );
  }

  String _dateLabel(String? raw) {
    if (raw == null || raw.isEmpty) return '';
    try {
      return DateFormat('EEE, MMM d, yyyy').format(DateTime.parse(raw));
    } catch (_) {
      return raw;
    }
  }
}
