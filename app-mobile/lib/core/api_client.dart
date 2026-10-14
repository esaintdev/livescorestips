import 'dart:convert';

import 'package:http/http.dart' as http;

import 'config.dart';

class ApiException implements Exception {
  final int? statusCode;
  final String message;

  const ApiException(this.message, {this.statusCode});

  @override
  String toString() => message;
}

/// Low-level HTTP client for the LiveScores Tips API.
class ApiClient {
  ApiClient({http.Client? httpClient}) : _http = httpClient ?? http.Client();

  final http.Client _http;
  String? token;

  Map<String, String> get _headers => {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        if (token != null) 'Authorization': 'Bearer $token',
      };

  Uri _uri(String path, [Map<String, String>? query]) {
    return Uri.parse('${AppConfig.apiUrl}$path').replace(
      queryParameters: query == null || query.isEmpty ? null : query,
    );
  }

  Future<Map<String, dynamic>> get(String path,
      {Map<String, String>? query}) async {
    final resp = await _http.get(_uri(path, query), headers: _headers);
    return _decode(resp);
  }

  Future<Map<String, dynamic>> post(String path,
      {Map<String, dynamic>? body}) async {
    final resp = await _http.post(
      _uri(path),
      headers: _headers,
      body: body == null ? null : jsonEncode(body),
    );
    return _decode(resp);
  }

  Map<String, dynamic> _decode(http.Response resp) {
    final String body = resp.body;
    dynamic decoded;
    if (body.isNotEmpty) {
      try {
        decoded = jsonDecode(body);
      } catch (_) {
        // Non-JSON response (e.g. an HTML error page). Treated as null below.
        decoded = null;
      }
    }

    if (resp.statusCode >= 200 && resp.statusCode < 300) {
      if (decoded is Map<String, dynamic>) return decoded;
      if (decoded is Map) return Map<String, dynamic>.from(decoded);
      return <String, dynamic>{'data': decoded};
    }

    String message = _statusMessage(resp.statusCode);
    if (decoded is Map) {
      final m = Map<String, dynamic>.from(decoded);
      if (m['message'] is String && (m['message'] as String).isNotEmpty) {
        message = m['message'] as String;
      } else if (m['errors'] is Map) {
        final errors = Map<String, dynamic>.from(m['errors'] as Map);
        final first = errors.values.firstWhere(
          (v) => v is List && v.isNotEmpty,
          orElse: () => const <String>[],
        );
        if (first is List && first.isNotEmpty) {
          message = first.first.toString();
        }
      }
    }

    throw ApiException(message, statusCode: resp.statusCode);
  }

  String _statusMessage(int code) {
    switch (code) {
      case 404:
        return 'Not found';
      case 401:
        return 'Unauthorized';
      case 403:
        return 'Forbidden';
      case 429:
        return 'Too many requests';
      case 500:
      case 502:
      case 503:
        return 'Server error';
      default:
        return 'Request failed ($code)';
    }
  }
}
