import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../core/app_colors.dart';
import '../models/winning.dart';
import '../services/api_service.dart';
import '../widgets/page_header.dart';
import '../widgets/winning_card.dart';

class WinningsScreen extends StatefulWidget {
  const WinningsScreen({super.key});

  @override
  State<WinningsScreen> createState() => _WinningsScreenState();
}

class _WinningsScreenState extends State<WinningsScreen>
    with AutomaticKeepAliveClientMixin {
  final ScrollController _scroll = ScrollController();

  final List<Winning> _winnings = [];
  bool _loading = true;
  bool _loadingMore = false;
  String? _error;

  int _page = 1;
  int _lastPage = 1;
  int _total = 0;
  String? _title;
  String? _subtitle;

  @override
  bool get wantKeepAlive => true;

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
      final result = await api.getRecentWinnings(page: 1);
      if (!mounted) return;
      setState(() {
        _winnings
          ..clear()
          ..addAll(result.items);
        _page = result.page.currentPage;
        _lastPage = result.page.lastPage;
        _total = result.page.total;
        _title = result.meta['page_subheader'];
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

  Future<void> _loadMore() async {
    if (_loadingMore || _loading || _page >= _lastPage) return;
    _loadingMore = true;
    final api = context.read<ApiService>();
    try {
      final result = await api.getRecentWinnings(page: _page + 1);
      if (!mounted) return;
      setState(() {
        _winnings.addAll(result.items);
        _page = result.page.currentPage;
        _lastPage = result.page.lastPage;
        _total = result.page.total;
        _loadingMore = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() => _loadingMore = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    super.build(context);
    return RefreshIndicator(
      onRefresh: _load,
      color: AppColors.accent,
      child: CustomScrollView(
        controller: _scroll,
        physics: const AlwaysScrollableScrollPhysics(),
        slivers: [
          SliverToBoxAdapter(
            child: PageHeader(
              title: _title ?? 'Recent Winning Tips',
              subtitle: _subtitle,
            ),
          ),
          SliverToBoxAdapter(child: _statsBar(context)),
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
          else if (_winnings.isEmpty)
            const SliverToBoxAdapter(
              child: Padding(
                padding: EdgeInsets.all(24),
                child: Center(child: Text('No recent winnings found.')),
              ),
            )
          else
            SliverPadding(
              padding: const EdgeInsets.fromLTRB(16, 4, 16, 8),
              sliver: SliverGrid(
                gridDelegate: const SliverGridDelegateWithMaxCrossAxisExtent(
                  maxCrossAxisExtent: 260,
                  mainAxisSpacing: 12,
                  crossAxisSpacing: 12,
                  childAspectRatio: 1.7,
                ),
                delegate: SliverChildBuilderDelegate(
                  (context, index) => WinningCard(winning: _winnings[index]),
                  childCount: _winnings.length,
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
    );
  }

  Widget _statsBar(BuildContext context) {
    final bool dark = Theme.of(context).brightness == Brightness.dark;
    final Color surface =
        dark ? AppColors.darkSurface : AppColors.lightSurface;
    final Color text = dark ? AppColors.darkTextBody : AppColors.lightText900;
    final Color muted = dark ? AppColors.darkTextMuted : AppColors.lightText400;

    return Container(
      margin: const EdgeInsets.fromLTRB(16, 8, 16, 16),
      padding: const EdgeInsets.symmetric(vertical: 20),
      decoration: BoxDecoration(
        color: surface,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: dark ? AppColors.darkBorder() : AppColors.lightBorder(),
        ),
      ),
      child: Row(
        children: [
          _stat(context, '$_total+', 'Total Winning Tips', AppColors.accent, text, muted),
          _stat(context, '95%+', 'Accuracy Rate', AppColors.success, text, muted),
          _stat(context, 'Daily', 'Updated Results', AppColors.warning, text, muted),
        ],
      ),
    );
  }

  Widget _stat(BuildContext context, String value, String label, Color color,
      Color text, Color muted) {
    return Expanded(
      child: Column(
        children: [
          Text(value,
              style: TextStyle(
                  fontSize: 22, fontWeight: FontWeight.w800, color: color)),
          const SizedBox(height: 4),
          Text(label,
              textAlign: TextAlign.center,
              style: TextStyle(fontSize: 11, color: muted)),
        ],
      ),
    );
  }
}
