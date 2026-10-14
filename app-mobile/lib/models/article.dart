class Article {
  final int id;
  final String title;
  final String slug;
  final String? excerpt;
  final String? image;
  final String? publishedAt;
  final String? url;

  /// Only populated by the detail endpoint.
  final String? content;
  final String? metaDescription;
  final String? categoryName;

  const Article({
    required this.id,
    required this.title,
    required this.slug,
    this.excerpt,
    this.image,
    this.publishedAt,
    this.url,
    this.content,
    this.metaDescription,
    this.categoryName,
  });

  factory Article.fromSummaryJson(Map<String, dynamic> json) {
    return Article(
      id: json['id'] as int,
      title: json['title']?.toString() ?? '',
      slug: json['slug']?.toString() ?? '',
      excerpt: json['excerpt']?.toString(),
      image: json['image']?.toString(),
      publishedAt: json['published_at']?.toString(),
      url: json['url']?.toString(),
    );
  }

  factory Article.fromDetailJson(Map<String, dynamic> json) {
    return Article(
      id: json['id'] as int,
      title: json['title']?.toString() ?? '',
      slug: json['slug']?.toString() ?? '',
      excerpt: json['excerpt']?.toString(),
      image: json['image']?.toString(),
      publishedAt: json['published_at']?.toString(),
      url: json['url']?.toString(),
      content: json['content']?.toString(),
      metaDescription: json['meta_description']?.toString(),
      categoryName: json['category']?.toString(),
    );
  }
}
