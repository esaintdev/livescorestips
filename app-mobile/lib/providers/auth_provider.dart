import 'dart:convert';

import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../models/user.dart';
import '../services/api_service.dart';

class AuthProvider extends ChangeNotifier {
  AuthProvider(this._api);

  final ApiService _api;

  AppUser? _user;
  String? _token;
  bool _initialised = false;

  AppUser? get user => _user;
  bool get isAuthenticated => _token != null && _user != null;
  bool get initialised => _initialised;

  static const _kToken = 'auth_token';
  static const _kUser = 'auth_user';

  Future<void> init() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString(_kToken);
    final userJson = prefs.getString(_kUser);

    if (token != null) {
      _token = token;
      _api.token = token;
    }
    if (userJson != null) {
      try {
        _user = AppUser.fromJson(
          Map<String, dynamic>.from(jsonDecode(userJson) as Map),
        );
      } catch (_) {
        _user = null;
      }
    }
    _initialised = true;
    notifyListeners();
  }

  Future<void> login(String email, String password) async {
    final result = await _api.login(email, password);
    await _persist(result.token, result.user);
    notifyListeners();
  }

  Future<void> register({
    required String name,
    required String email,
    required String password,
    String? phone,
  }) async {
    final result = await _api.register(
      name: name,
      email: email,
      password: password,
      phone: phone,
    );
    await _persist(result.token, result.user);
    notifyListeners();
  }

  Future<void> logout() async {
    try {
      await _api.logout();
    } catch (_) {
      // Ignore server-side failures on logout; clear local state regardless.
    }
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_kToken);
    await prefs.remove(_kUser);
    _token = null;
    _user = null;
    _api.token = null;
    notifyListeners();
  }

  Future<void> _persist(String token, AppUser user) async {
    _token = token;
    _user = user;
    _api.token = token;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_kToken, token);
    await prefs.setString(_kUser, jsonEncode(user.toJson()));
  }
}
