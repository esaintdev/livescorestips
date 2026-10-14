import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../core/app_colors.dart';
import '../models/article.dart';
import '../services/api_service.dart';
import '../widgets/article_card.dart';
import '../widgets/page_header.dart';
import 'blog_detail_screen.dart';

class BlogScreen extends StatefulWidget {
  const BlogScreen({super.key});

  @override
  State<BlogScreen> createState() => _BlogScreenState();
}

class _BlogScreenState extends State<BlogScreen>
    with AutomaticKeepAliveClientMixin {
  final ScrollController _scroll = ScrollController();

  final List<Article> _articles = [];
  bool _loading = true;
  bool _loadingMore = false;
  String? _error;

  int _page = 1;
  int _lastPage = 1;

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
    if (_scroll.position.pixels >=
        _scroll.position.maxScrollExtent - 400) {
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
      final result = await api.getArticles(page: 1);
      if (!mounted) return;
      setState(() {
        _articles
          ..clear()
          ..addAll(result.items);
        _page = result.page.currentPage;
        _lastPage = result.page.lastPage;
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
      final result = await api.getArticles(page: _page + 1);
      if (!mounted) return;
      setState(() {
        _articles.addAll(result.items);
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
    super.build(context);
    return RefreshIndicator(
      onRefresh: _load,
      color: AppColors.accent,
      child: CustomScrollView(
        controller: _scroll,
        physics: const AlwaysScrollableScrollPhysics(),
        slivers: [
          const SliverToBoxAdapter(
            child: PageHeader(
              title: 'Blog',
              subtitle: 'Football betting guides, strategies and news.',
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
          else if (_articles.isEmpty)
            const SliverToBoxAdapter(
              child: Padding(
                padding: EdgeInsets.all(24),
                child: Center(child: Text('No articles found.')),
              ),
            )
          else
            SliverPadding(
              padding: const EdgeInsets.fromLTRB(16, 4, 16, 8),
              sliver: SliverList(
                delegate: SliverChildBuilderDelegate(
                  (context, index) {
                    final article = _articles[index];
                    return Padding(
                      padding: const EdgeInsets.only(bottom: 16),
                      child: ArticleCard(
                        article: article,
                        onTap: () => Navigator.of(context).push(
                          MaterialPageRoute(
                            builder: (_) =>
                                BlogDetailScreen(slug: article.slug),
                          ),
                        ),
                      ),
                    );
                  },
                  childCount: _articles.length,
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
}
