import 'package:flutter/material.dart';
import 'package:flutter_widget_from_html/flutter_widget_from_html.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';

import '../core/app_colors.dart';
import '../models/article.dart';
import '../services/api_service.dart';
import '../widgets/app_network_image.dart';

class BlogDetailScreen extends StatefulWidget {
  const BlogDetailScreen({super.key, required this.slug});

  final String slug;

  @override
  State<BlogDetailScreen> createState() => _BlogDetailScreenState();
}

class _BlogDetailScreenState extends State<BlogDetailScreen> {
  Article? _article;
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    final api = context.read<ApiService>();
    try {
      final article = await api.getArticle(widget.slug);
      if (!mounted) return;
      setState(() {
        _article = article;
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
    final bool dark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      appBar: AppBar(
        title: Text(
          _article?.title ?? 'Article',
          maxLines: 1,
          overflow: TextOverflow.ellipsis,
        ),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(
                  child: Padding(
                    padding: const EdgeInsets.all(24),
                    child: Text(_error ?? 'Something went wrong.',
                        style: const TextStyle(
                            fontSize: 13, color: AppColors.danger)),
                  ),
                )
              : _content(context, dark),
    );
  }

  Widget _content(BuildContext context, bool dark) {
    final article = _article!;
    final Color titleColor = dark ? AppColors.darkTextBody : AppColors.lightText900;
    final Color metaColor =
        dark ? AppColors.darkTextMuted : AppColors.lightText400;

    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
      children: [
        _breadcrumb(context, dark),
        const SizedBox(height: 14),
        Text(
          article.title,
          style: TextStyle(
            fontSize: 22,
            fontWeight: FontWeight.w800,
            color: titleColor,
            height: 1.25,
          ),
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            const Icon(Icons.person_outline, size: 14, color: AppColors.accent),
            const SizedBox(width: 5),
            Text('Admin',
                style: TextStyle(fontSize: 12, color: metaColor)),
            const SizedBox(width: 14),
            const Icon(Icons.calendar_today_outlined,
                size: 13, color: AppColors.accent),
            const SizedBox(width: 5),
            Text(_dateLabel(article.publishedAt),
                style: TextStyle(fontSize: 12, color: metaColor)),
          ],
        ),
        if (article.image != null && article.image!.isNotEmpty) ...[
          const SizedBox(height: 16),
          ClipRRect(
            borderRadius: BorderRadius.circular(12),
            child: AppNetworkImage(
              url: article.image,
              width: double.infinity,
              height: 220,
              fit: BoxFit.cover,
              fallbackIcon: Icons.article_outlined,
            ),
          ),
        ],
        const SizedBox(height: 16),
        if (article.content != null && article.content!.isNotEmpty)
          _html(article.content!, dark),
      ],
    );
  }

  Widget _breadcrumb(BuildContext context, bool dark) {
    final Color muted = dark ? AppColors.darkTextMuted : AppColors.lightText400;
    return Row(
      children: [
        Text('Home', style: TextStyle(fontSize: 12, color: muted)),
        Text(' / ', style: TextStyle(fontSize: 12, color: muted)),
        Text('Blog', style: TextStyle(fontSize: 12, color: muted)),
        Text(' / ', style: TextStyle(fontSize: 12, color: muted)),
        Expanded(
          child: Text(
            _article?.title ?? '',
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.w600,
              color: AppColors.accent,
            ),
          ),
        ),
      ],
    );
  }

  Widget _html(String html, bool dark) {
    final Color textColor =
        dark ? AppColors.darkTextBody : AppColors.lightText900;

    return HtmlWidget(
      html,
      textStyle: TextStyle(
        fontSize: 15,
        height: 1.6,
        color: textColor,
      ),
      customStylesBuilder: (element) {
        if (element.localName == 'h2') {
          return {
            'font-size': '20px',
            'font-weight': '700',
            'margin': '18px 0 8px',
          };
        }
        if (element.localName == 'h3') {
          return {
            'font-size': '17px',
            'font-weight': '700',
            'margin': '14px 0 6px',
          };
        }
        if (element.localName == 'a') {
          return {'color': '#FF6B00'};
        }
        return null;
      },
      onTapUrl: (url) async {
        final uri = Uri.tryParse(url);
        if (uri == null) return false;
        await launchUrl(uri, mode: LaunchMode.externalApplication);
        return true;
      },
    );
  }

  String _dateLabel(String? raw) {
    if (raw == null || raw.isEmpty) return '';
    try {
      return DateFormat('MMMM d, yyyy').format(DateTime.parse(raw));
    } catch (_) {
      return raw;
    }
  }
}
