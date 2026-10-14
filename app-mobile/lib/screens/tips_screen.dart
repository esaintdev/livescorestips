import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../core/app_colors.dart';
import '../core/filters.dart';
import '../models/prediction.dart';
import '../services/api_service.dart';
import '../widgets/date_nav.dart';
import '../widgets/filter_bar.dart';
import '../widgets/match_list.dart';
import '../widgets/page_header.dart';
import '../widgets/search_bar.dart';
import 'category_screen.dart';

class TipsScreen extends StatefulWidget {
  const TipsScreen({super.key});

  @override
  State<TipsScreen> createState() => _TipsScreenState();
}

class _TipsScreenState extends State<TipsScreen>
    with AutomaticKeepAliveClientMixin {
  final TextEditingController _search = TextEditingController();

  DateTime _date = DateTime.now();
  bool _live = false;

  List<Prediction> _raw = const [];
  bool _loading = true;
  String? _error;
  int _liveCount = 0;

  SortOption _sort = SortOption.time;
  double _probability = 0;
  double _odds = 0;

  @override
  bool get wantKeepAlive => true;

  @override
  void initState() {
    super.initState();
    _date = DateTime.now();
    _load();
  }

  @override
  void dispose() {
    _search.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });

    final api = context.read<ApiService>();
    try {
      if (_live) {
        final result = await api.getLivePredictions();
        if (!mounted) return;
        setState(() {
          _raw = result.items;
          _liveCount = result.count;
          _loading = false;
        });
      } else {
        final result = await api.getPredictions(date: _dateKey());
        if (!mounted) return;
        setState(() {
          _raw = result.items;
          _loading = false;
        });
      }
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _error = e.toString();
        _loading = false;
      });
    }
  }

  String _dateKey() => DateFormat('yyyy-MM-dd').format(_date);

  void _changeDate(DateTime d) {
    setState(() => _date = d);
    _load();
  }

  Future<void> _pickDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _date,
      firstDate: DateTime(2020),
      lastDate: DateTime(2030),
    );
    if (picked != null) _changeDate(picked);
  }

  List<Prediction> _visible() {
    List<Prediction> list = _raw;

    final term = _search.text.trim().toLowerCase();
    if (term.isNotEmpty) {
      list = list.where((p) {
        final hay = [
          p.homeTeam ?? '',
          p.awayTeam ?? '',
          p.leagueName ?? '',
          p.leagueCountry ?? '',
        ].join(' ').toLowerCase();
        return hay.contains(term);
      }).toList();
    }

    return applyFilters(
      list,
      sort: _sort,
      probability: _probability,
      odds: _odds,
      dedupe: !_live,
    );
  }

  bool get _hasFilters =>
      _probability > 0 || _odds > 0 || _sort != SortOption.time;

  @override
  Widget build(BuildContext context) {
    super.build(context);
    return RefreshIndicator(
      onRefresh: _load,
      color: AppColors.accent,
      child: CustomScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        slivers: [
          const SliverToBoxAdapter(
            child: PageHeader(title: 'All Predictions'),
          ),
          SliverToBoxAdapter(
            child: FilterBar(
              marketLabel: 'Tips',
              onMarketSelected: (slug) {
                if (slug == null) return;
                Navigator.of(context).push(
                  MaterialPageRoute(builder: (_) => CategoryScreen(slug: slug)),
                );
              },
              sort: _sort,
              onSortChanged: (v) => setState(() => _sort = v),
              probability: _probability,
              onProbabilityChanged: (v) => setState(() => _probability = v),
              odds: _odds,
              onOddsChanged: (v) => setState(() => _odds = v),
              liveCount: _liveCount,
              liveActive: _live,
              onLiveTap: () {
                setState(() => _live = !_live);
                _load();
              },
              hasFilters: _hasFilters,
              onClear: () => setState(() {
                _sort = SortOption.time;
                _probability = 0;
                _odds = 0;
                _search.clear();
              }),
            ),
          ),
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.symmetric(vertical: 10),
              child: DateNav(
                date: _date,
                onPrev: () =>
                    _changeDate(_date.subtract(const Duration(days: 1))),
                onNext: () => _changeDate(_date.add(const Duration(days: 1))),
                onPickDate: _pickDate,
              ),
            ),
          ),
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(12, 4, 12, 12),
              child: TipsSearchBar(controller: _search),
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
          else
            SliverToBoxAdapter(
                child: MatchList(predictions: _visible(), isLive: _live)),
          const SliverToBoxAdapter(child: SizedBox(height: 24)),
        ],
      ),
    );
  }
}
