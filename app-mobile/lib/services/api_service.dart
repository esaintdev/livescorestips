import '../core/api_client.dart';
import '../models/article.dart';
import '../models/category.dart';
import '../models/match_detail.dart';
import '../models/prediction.dart';
import '../models/user.dart';
import '../models/vip.dart';
import '../models/winning.dart';

typedef PageMeta = ({int currentPage, int lastPage, int total});

class ApiService {
  ApiService(this._client);

  final ApiClient _client;

  /// Current bearer token (set after login/register, cleared on logout).
  String? get token => _client.token;
  set token(String? value) => _client.token = value;

  // ---------------------------------------------------------------------------
  // Predictions
  // ---------------------------------------------------------------------------

  Future<({List<Prediction> items, String date, Map<String, String?> meta})>
      getPredictions({String? date, String? category}) async {
    final query = <String, String>{
      'date': ?date,
      'category': ?category,
    };

    final json = await _client.get('/predictions', query: query);
    final items = _parseList(json['data'], Prediction.fromAny);

    return (
      items: items,
      date: json['date']?.toString() ?? _today(),
      meta: _stringMap(json['meta']),
    );
  }

  Future<({List<Prediction> items, int count})> getLivePredictions() async {
    final json = await _client.get('/predictions/live');
    final items = _parseList(json['data'], Prediction.fromAny);
    final count = json['count'];
    return (
      items: items,
      count: count is int ? count : items.length,
    );
  }

  // ---------------------------------------------------------------------------
  // Categories
  // ---------------------------------------------------------------------------

  Future<List<PredictionCategory>> getCategories() async {
    final json = await _client.get('/categories');
    return _parseList(json['data'], PredictionCategory.fromJson);
  }

  Future<
      ({
        List<Prediction> items,
        String slug,
        String name,
        String date,
        Map<String, String?> meta,
      })> getCategory(String slug, {String? date}) async {
    final json = await _client.get('/categories/$slug',
        query: date != null ? {'date': date} : null);
    final items = _parseList(json['data'], Prediction.fromAny);
    final cat = json['category'];

    return (
      items: items,
      slug: slug,
      name: (cat is Map && cat['name'] != null)
          ? cat['name'].toString()
          : _categoryName(slug),
      date: json['date']?.toString() ?? _today(),
      meta: _stringMap(json['meta']),
    );
  }

  // ---------------------------------------------------------------------------
  // Articles
  // ---------------------------------------------------------------------------

  Future<({List<Article> items, PageMeta page})> getArticles(
      {int page = 1}) async {
    final json = await _client.get('/articles', query: {'page': '$page'});
    final data = json['data'];
    final list = (data is Map && data['data'] is List)
        ? _parseList(data['data'], Article.fromSummaryJson)
        : _parseList(data, Article.fromSummaryJson);

    final meta = json['meta'];
    return (
      items: list,
      page: _pageMeta(meta),
    );
  }

  Future<Article> getArticle(String slug) async {
    final json = await _client.get('/articles/$slug');
    return Article.fromDetailJson(Map<String, dynamic>.from(json['data'] as Map));
  }

  // ---------------------------------------------------------------------------
  // Recent winnings
  // ---------------------------------------------------------------------------

  Future<
      ({
        List<Winning> items,
        PageMeta page,
        Map<String, String?> meta,
      })> getRecentWinnings({int page = 1}) async {
    final json = await _client.get('/recent-winnings', query: {'page': '$page'});
    final data = json['data'];
    final list = (data is Map && data['data'] is List)
        ? _parseList(data['data'], Winning.fromJson)
        : _parseList(data, Winning.fromJson);

    final meta = json['meta'];
    return (
      items: list,
      page: _pageMeta(meta),
      meta: _stringMap(meta),
    );
  }

  // ---------------------------------------------------------------------------
  // VIP
  // ---------------------------------------------------------------------------

  Future<
      ({
        Map<String, List<VipPlan>> plans,
        Map<String, dynamic> countries,
        Map<String, String?> meta,
      })> getVipPlans() async {
    final json = await _client.get('/vip/plans');
    final data = json['data'];
    final plans = <String, List<VipPlan>>{};

    if (data is Map) {
      data.forEach((category, rawPlans) {
        if (rawPlans is List) {
          plans[category.toString()] = rawPlans
              .whereType<Map>()
              .map((e) => VipPlan.fromJson(Map<String, dynamic>.from(e)))
              .toList();
        }
      });
    }

    final countries = (json['countries'] is Map)
        ? Map<String, dynamic>.from(json['countries'] as Map)
        : <String, dynamic>{};

    return (plans: plans, countries: countries, meta: _stringMap(json['meta']));
  }

  Future<({List<VipResult> items, PageMeta page, Map<String, String?> meta})>
      getVipResults({int page = 1}) async {
    final json = await _client.get('/vip-results', query: {'page': '$page'});
    final data = json['data'];
    final list = (data is Map && data['data'] is List)
        ? _parseList(data['data'], VipResult.fromJson)
        : _parseList(data, VipResult.fromJson);

    return (
      items: list,
      page: _pageMeta(json['meta']),
      meta: _stringMap(json['meta']),
    );
  }

  // ---------------------------------------------------------------------------
  // Match detail
  // ---------------------------------------------------------------------------

  Future<MatchDetail> getMatch(String slug) async {
    final json = await _client.get('/matches/$slug');
    return MatchDetail.fromJson(Map<String, dynamic>.from(json['data'] as Map));
  }

  // ---------------------------------------------------------------------------
  // Auth
  // ---------------------------------------------------------------------------

  Future<({String token, AppUser user})> login(
      String email, String password) async {
    final json = await _client.post('/auth/login', body: {
      'email': email,
      'password': password,
    });
    return (
      token: json['token'] as String,
      user: AppUser.fromJson(Map<String, dynamic>.from(json['user'] as Map)),
    );
  }

  Future<({String token, AppUser user})> register({
    required String name,
    required String email,
    required String password,
    String? phone,
  }) async {
    final json = await _client.post('/auth/register', body: {
      'name': name,
      'email': email,
      'password': password,
      if (phone != null && phone.isNotEmpty) 'phone': phone,
    });
    return (
      token: json['token'] as String,
      user: AppUser.fromJson(Map<String, dynamic>.from(json['user'] as Map)),
    );
  }

  Future<AppUser> getUser() async {
    final json = await _client.get('/user');
    return AppUser.fromJson(Map<String, dynamic>.from(json));
  }

  Future<void> logout() async {
    await _client.post('/auth/logout');
  }

  // ---------------------------------------------------------------------------
  // Helpers
  // ---------------------------------------------------------------------------

  String _today() => DateTime.now().toIso8601String().substring(0, 10);

  List<T> _parseList<T>(dynamic data, T Function(Map<String, dynamic>) parse) {
    if (data is! List) return const [];
    return data
        .whereType<Map>()
        .map((e) => parse(Map<String, dynamic>.from(e)))
        .toList();
  }

  Map<String, String?> _stringMap(dynamic v) {
    if (v is! Map) return const {};
    final out = <String, String?>{};
    v.forEach((k, val) => out[k.toString()] = val?.toString());
    return out;
  }

  PageMeta _pageMeta(dynamic v) {
    int i(String key) {
      final val = (v is Map) ? v[key] : null;
      if (val is int) return val;
      return int.tryParse(val?.toString() ?? '') ?? 1;
    }

    return (currentPage: i('current_page'), lastPage: i('last_page'), total: i('total'));
  }

  String _categoryName(String slug) => const {
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
