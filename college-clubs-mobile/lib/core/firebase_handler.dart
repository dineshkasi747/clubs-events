import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/app_provider.dart';

class FirebaseHandler {
  static void setupFirebase(BuildContext context) async {
    final appProvider = Provider.of<AppProvider>(context, listen: false);
    
    // Load persisted local notification history at startup
    appProvider.loadNotifications();

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
      String? token = await messaging.getToken().timeout(const Duration(seconds: 3));
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
        final payload = Map<String, dynamic>.from(message.data);
        appProvider.addNotification(
          message.notification!.title ?? 'New Alert',
          message.notification!.body ?? '',
          payload,
        );

        _showInAppNotification(
          context,
          message.notification!.title ?? 'New Alert',
          message.notification!.body ?? '',
        );
      }
    });

    // Handle Background Click open action
    FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) {
      print("🔥 App opened from push notification: ${message.data}");
      if (message.notification != null) {
        final payload = Map<String, dynamic>.from(message.data);
        appProvider.addNotification(
          message.notification!.title ?? 'New Alert',
          message.notification!.body ?? '',
          payload,
        );
      }
    });

    // Handle Terminated State Click open action
    try {
      final initialMessage = await FirebaseMessaging.instance.getInitialMessage();
      if (initialMessage != null && initialMessage.notification != null) {
        print("🔥 App launched from terminated state via push: ${initialMessage.data}");
        final payload = Map<String, dynamic>.from(initialMessage.data);
        appProvider.addNotification(
          initialMessage.notification!.title ?? 'New Alert',
          initialMessage.notification!.body ?? '',
          payload,
        );
      }
    } catch (e) {
      print("⚠️ getInitialMessage failed: $e");
    }
  }

  /// Displays a premium custom floating notification banner at the top of the screen.
  static void _showInAppNotification(BuildContext context, String title, String body) {
    late OverlayEntry overlayEntry;
    
    overlayEntry = OverlayEntry(
      builder: (context) => Positioned(
        top: MediaQuery.of(context).padding.top + 12,
        left: 16,
        right: 16,
        child: Material(
          color: Colors.transparent,
          child: Dismissible(
            key: UniqueKey(),
            direction: DismissDirection.up,
            onDismissed: (_) {
              overlayEntry.remove();
            },
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFF1E1B4B), Color(0xFF0F172A)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: const Color(0xFF4F46E5).withOpacity(0.4), width: 1.5),
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFF4F46E5).withOpacity(0.2),
                    blurRadius: 16,
                    offset: const Offset(0, 8),
                  ),
                ],
              ),
              child: Row(
                children: [
                  Container(
                    height: 40,
                    width: 40,
                    decoration: BoxDecoration(
                      color: const Color(0xFF4F46E5).withOpacity(0.2),
                      shape: BoxShape.circle,
                      border: Border.all(color: const Color(0xFF6366F1), width: 1),
                    ),
                    child: const Icon(Icons.notifications_active, color: Color(0xFF818CF8), size: 20),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(
                          title,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                            fontWeight: FontWeight.bold,
                            color: Colors.white,
                            fontSize: 13,
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          body,
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                            color: Color(0xFF94A3B8),
                            fontSize: 11,
                          ),
                        ),
                      ],
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close, color: Color(0xFF64748B), size: 16),
                    onPressed: () {
                      if (overlayEntry.mounted) {
                        overlayEntry.remove();
                      }
                    },
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );

    try {
      Overlay.of(context).insert(overlayEntry);
      
      // Auto-dismiss after 4 seconds
      Future.delayed(const Duration(seconds: 4), () {
        if (overlayEntry.mounted) {
          overlayEntry.remove();
        }
      });
    } catch (e) {
      print("⚠️ Failed to display floating overlay notification: $e");
    }
  }
}
