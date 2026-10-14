import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';

import '../core/app_colors.dart';

/// Network image with a graceful placeholder + fallback icon.
class AppNetworkImage extends StatelessWidget {
  const AppNetworkImage({
    super.key,
    required this.url,
    this.width,
    this.height,
    this.fit = BoxFit.contain,
    this.fallbackIcon = Icons.shield_outlined,
    this.iconSize,
  });

  final String? url;
  final double? width;
  final double? height;
  final BoxFit fit;
  final IconData fallbackIcon;
  final double? iconSize;

  @override
  Widget build(BuildContext context) {
    final double w = width ?? height ?? 24;
    final double h = height ?? width ?? 24;

    if (url == null || url!.isEmpty) {
      return _fallback(context, w, h);
    }

    return CachedNetworkImage(
      imageUrl: url!,
      width: w,
      height: h,
      fit: fit,
      placeholder: (context, url) => SizedBox(
        width: w,
        height: h,
        child: const Center(
          child: SizedBox(
            width: 12,
            height: 12,
            child: CircularProgressIndicator(
              strokeWidth: 1.6,
              color: AppColors.accent,
            ),
          ),
        ),
      ),
      errorWidget: (context, url, error) => _fallback(context, w, h),
    );
  }

  Widget _fallback(BuildContext context, double w, double h) {
    final Color c = Theme.of(context).brightness == Brightness.dark
        ? AppColors.darkTextMuted
        : AppColors.lightText200;
    return SizedBox(
      width: w,
      height: h,
      child: Icon(fallbackIcon, size: iconSize ?? (h * 0.9), color: c),
    );
  }
}
