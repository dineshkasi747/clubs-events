import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/app_provider.dart';

class FirebaseHandler {
  static void setupFirebase(BuildContext context) async {
    final appProvider = Provider.of<AppProvider>(context, listen: false);

    // Request permissions for device messaging
    FirebaseMessaging messaging = FirebaseMessaging.instance;
    try {
      await messaging.requestPermission(
        alert: true,
        badge: true,
        sound: true,
      );
    } catch (_) {}

    // Fetch and register Device Token
    try {
      String? token = await messaging.getToken();
      if (token != null) {
        print("🔥 FCM Device Token: $token");
        appProvider.setFcmToken(token);
      }

      // Listen for token refreshes
      messaging.onTokenRefresh.listen((newToken) {
        appProvider.setFcmToken(newToken);
      });
    } catch (e) {
      print("⚠️ FCM Token retrieval skipped (Firebase credential JSONs pending): $e");
    }

    // Handle Foreground Notifications
    FirebaseMessaging.onMessage.listen((RemoteMessage message) {
      print("🔥 Foreground message received: ${message.notification?.title}");
      
      if (message.notification != null) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            backgroundColor: const Color(0xFF4F46E5),
            behavior: SnackBarBehavior.floating,
            margin: const EdgeInsets.all(16),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            content: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  message.notification!.title ?? 'New Alert',
                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                ),
                const SizedBox(height: 2),
                Text(
                  message.notification!.body ?? '',
                  style: const TextStyle(color: Colors.white70, fontSize: 11),
                ),
              ],
            ),
          ),
        );
      }
    });

    // Handle Background Click open action
    FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) {
      print("🔥 App opened from push notification: ${message.data}");
    });
  }
}
