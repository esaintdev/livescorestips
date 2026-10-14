import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import '../core/app_colors.dart';
import '../models/article.dart';
import 'app_network_image.dart';
import 'app_ui.dart';

class ArticleCard extends StatelessWidget {
  const ArticleCard({super.key, required this.article, this.onTap});

  final Article article;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    final Color title = AppTokens.textPrimary(context);
    final Color excerpt = AppTokens.textSecondary(context);

    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(AppTokens.radius),
      child: Container(
        decoration: BoxDecoration(
          color: AppTokens.surface(context),
          borderRadius: BorderRadius.circular(AppTokens.radius),
          border: Border.all(color: AppTokens.border(context)),
        ),
        clipBehavior: Clip.antiAlias,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            AspectRatio(
              aspectRatio: 16 / 9,
              child: AppNetworkImage(
                url: article.image,
                width: double.infinity,
                height: double.infinity,
                fit: BoxFit.cover,
                fallbackIcon: Icons.article_outlined,
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(14),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      const Icon(Icons.calendar_today_outlined,
                          size: 11, color: AppColors.accent),
                      const SizedBox(width: 5),
                      Text(
                        _dateLabel(),
                        style: TextStyle(
                            fontSize: 11, color: AppTokens.textMuted(context)),
                      ),
                    ],
                  ),
                  const SizedBox(height: 9),
                  Text(
                    article.title,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(
                      fontSize: 15,
                      fontWeight: FontWeight.w700,
                      color: title,
                      height: 1.3,
                    ),
                  ),
                  if (article.excerpt != null &&
                      article.excerpt!.isNotEmpty) ...[
                    const SizedBox(height: 8),
                    Text(
                      article.excerpt!,
                      maxLines: 3,
                      overflow: TextOverflow.ellipsis,
                      style: TextStyle(
                          fontSize: 12.5, color: excerpt, height: 1.45),
                    ),
                  ],
                  const SizedBox(height: 12),
                  Row(
                    children: const [
                      Text('Read more',
                          style: TextStyle(
                              fontSize: 11.5,
                              fontWeight: FontWeight.w700,
                              letterSpacing: 0.2,
                              color: AppColors.accent)),
                      SizedBox(width: 4),
                      Icon(Icons.arrow_forward,
                          size: 12, color: AppColors.accent),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  String _dateLabel() {
    final raw = article.publishedAt;
    if (raw == null || raw.isEmpty) return '';
    try {
      return DateFormat('MMM d, yyyy').format(DateTime.parse(raw));
    } catch (_) {
      return raw;
    }
  }
}
