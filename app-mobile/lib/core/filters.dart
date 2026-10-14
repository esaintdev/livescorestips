import '../models/prediction.dart';

/// Market filter option (mirrors the website's "Matches" dropdown).
class MarketOption {
  final String? slug; // null => "Tips" (all)
  final String label;

  const MarketOption(this.slug, this.label);
}

const List<MarketOption> marketOptions = [
  MarketOption(null, 'Tips'),
  MarketOption('home-wins', 'Home Win'),
  MarketOption('draws', 'Draw'),
  MarketOption('away-wins', 'Away Win'),
  MarketOption('correct-score', 'Correct Score'),
  MarketOption('btts-gg', 'Both teams to score'),
  MarketOption('over-2-5-goals', 'Under/Over: 2.5'),
  MarketOption('double-chance', 'Double chance'),
  MarketOption('over-1-5-goals', 'Over 1.5 Goals'),
];

enum SortOption { time, league, probability, odds }

extension SortOptionX on SortOption {
  String get label => switch (this) {
        SortOption.time => 'Time',
        SortOption.league => 'League',
        SortOption.probability => 'Probability',
        SortOption.odds => 'Odds',
      };
}

const List<double> probabilityOptions = [0, 50, 55, 60, 65, 70, 75, 80];

String probabilityLabel(double v) =>
    v == 0 ? 'All' : '${v.toStringAsFixed(0)}% – 100%';

const List<double> oddsOptions = [
  0, 1.20, 1.30, 1.40, 1.50, 1.60, 1.70, 1.80, 1.90, 2.00,
];

String oddsLabel(double v) => v == 0 ? 'Any' : '> ${v.toStringAsFixed(2)}';

/// Applies sort + probability + odds filters to an already-fetched list.
/// `dedupe` collapses multiple rows for the same fixture (first wins), matching
/// the website's home page behaviour.
List<Prediction> applyFilters(
  List<Prediction> items, {
  required SortOption sort,
  required double probability,
  required double odds,
  bool dedupe = true,
}) {
  var result = items;

  if (dedupe) {
    final seen = <int?>{};
    result = result.where((p) => seen.add(p.fixtureId)).toList();
  }

  if (probability > 0) {
    result = result
        .where((p) => (p.probabilityValue ?? 0) >= probability)
        .toList();
  }

  if (odds > 0) {
    result = result.where((p) {
      final o = double.tryParse(p.odds ?? '');
      return o == null || o >= odds;
    }).toList();
  }

  switch (sort) {
    case SortOption.time:
      result = List.of(result)
        ..sort((a, b) => (a.matchTime ?? '').compareTo(b.matchTime ?? ''));
      break;
    case SortOption.league:
      result = List.of(result)
        ..sort((a, b) => (a.leagueName ?? '').compareTo(b.leagueName ?? ''));
      break;
    case SortOption.probability:
      result = List.of(result)
        ..sort((a, b) => (b.probabilityValue ?? 0).compareTo(a.probabilityValue ?? 0));
      break;
    case SortOption.odds:
      result = List.of(result)
        ..sort((a, b) => (double.tryParse(b.odds ?? '') ?? 0)
            .compareTo(double.tryParse(a.odds ?? '') ?? 0));
      break;
  }

  return result;
}
