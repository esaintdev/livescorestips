import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart';

/// Handles background FCM messages. Must be a top-level function.
@pragma('vm:entry-point')
Future<void> firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  await Firebase.initializeApp();
  debugPrint('FCM (background): ${message.messageId}');
}

/// Firebase Cloud Messaging bootstrap.
///
/// Initialisation is defensive: if the platform config files
/// (`google-services.json` / `GoogleService-Info.plist`) are missing, the app
/// still boots — notifications are just disabled.
class NotificationService {
  NotificationService._();

  static bool _ready = false;
  static bool get ready => _ready;

  /// Set once a token is obtained — wire this to the backend later.
  static String? token;

  static Future<void> init() async {
    try {
      await Firebase.initializeApp();
    } catch (e) {
      debugPrint('Firebase not configured — notifications disabled ($e)');
      return;
    }

    _ready = true;
    FirebaseMessaging.onBackgroundMessage(firebaseMessagingBackgroundHandler);

    final messaging = FirebaseMessaging.instance;

    // iOS + Android 13+ show a permission prompt.
    await messaging.requestPermission(alert: true, badge: true, sound: true);

    // Foreground messages.
    FirebaseMessaging.onMessage.listen((m) {
      debugPrint('FCM (foreground): ${m.notification?.title} — ${m.notification?.body}');
    });

    // Tapped a notification and opened the app.
    FirebaseMessaging.onMessageOpenedApp.listen((m) {
      debugPrint('FCM (opened): ${m.data}');
    });

    // Device token.
    try {
      token = await messaging.getToken();
      debugPrint('FCM token: $token');
      // TODO: POST {token} to the backend (device_tokens) so the server can push.
    } catch (e) {
      debugPrint('FCM token error: $e');
    }

    // Refresh handling.
    messaging.onTokenRefresh.listen((t) {
      token = t;
      debugPrint('FCM token refreshed: $t');
    });
  }
}
