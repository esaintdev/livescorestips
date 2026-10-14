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

class CategoryScreen extends StatefulWidget {
  const CategoryScreen({super.key, required this.slug});

  final String slug;

  @override
  State<CategoryScreen> createState() => _CategoryScreenState();
}

class _CategoryScreenState extends State<CategoryScreen> {
  final TextEditingController _search = TextEditingController();

  DateTime _date = DateTime.now();
  bool _live = false;

  List<Prediction> _raw = const [];
  bool _loading = true;
  String? _error;

  String _name = '';
  String? _title;
  String? _subtitle;
  int _liveCount = 0;

  SortOption _sort = SortOption.time;
  double _probability = 0;
  double _odds = 0;

  @override
  void initState() {
    super.initState();
    _date = DateTime.now();
    _name = _nameFor(widget.slug);
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
          _raw = result.items
              .where((p) => p.category == _name)
              .toList();
          _liveCount = result.count;
          _loading = false;
        });
      } else {
        final result =
            await api.getCategory(widget.slug, date: _dateKey());
        if (!mounted) return;
        setState(() {
          _raw = result.items;
          _name = result.name;
          _title = result.meta['page_subheader'] ?? result.name;
          _subtitle = result.meta['page_description'];
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

  void _onMarketSelected(String? slug) {
    if (slug == null) {
      Navigator.of(context).pop();
      return;
    }
    if (slug == widget.slug) return;
    Navigator.of(context).pushReplacement(
      MaterialPageRoute(builder: (_) => CategoryScreen(slug: slug)),
    );
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
      dedupe: false,
    );
  }

  bool get _hasFilters =>
      _probability > 0 || _odds > 0 || _sort != SortOption.time;

  @override
  Widget build(BuildContext context) {
    final marketLabel = marketOptions
        .firstWhere((o) => o.slug == widget.slug,
            orElse: () => MarketOption(null, _name))
        .label;

    return Scaffold(
      appBar: AppBar(title: Text(_title ?? _name)),
      body: RefreshIndicator(
        onRefresh: _load,
        color: AppColors.accent,
        child: CustomScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          slivers: [
            SliverToBoxAdapter(
              child: PageHeader(
                title: _title ?? _name,
                subtitle: _subtitle,
              ),
            ),
            SliverToBoxAdapter(
              child: FilterBar(
                marketLabel: marketLabel,
                onMarketSelected: _onMarketSelected,
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
                      style: const TextStyle(
                          fontSize: 13, color: AppColors.danger)),
                ),
              )
            else
              SliverToBoxAdapter(
                  child: MatchList(predictions: _visible(), isLive: _live)),
            const SliverToBoxAdapter(child: SizedBox(height: 24)),
          ],
        ),
      ),
    );
  }

  String _nameFor(String slug) => const {
        'double-chance': 'Double Chance',
        'over-1-5-goals': 'Over 1.5 Goals',
        'over-2-5-goals': 'Over 2.5 Goals',
        'btts-gg': 'Both Teams To Score',
        'draws': 'Draw',
        'home-wins': 'Home Win',
        'away-wins': 'Away Win',
        'correct-score': 'Correct Score',
      }[slug] ??
      slug;
}
