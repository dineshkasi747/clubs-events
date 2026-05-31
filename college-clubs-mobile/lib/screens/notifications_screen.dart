import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../models/in_app_notification.dart';
import '../providers/app_provider.dart';
import 'event_detail_screen.dart';
import 'years_screen.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  @override
  Widget build(BuildContext context) {
    final appProvider = Provider.of<AppProvider>(context);
    final notifications = appProvider.notifications;

    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'Notifications Center',
          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
        ),
        backgroundColor: const Color(0xFF111827),
        elevation: 0,
        actions: [
          if (notifications.isNotEmpty) ...[
            IconButton(
              icon: const Icon(Icons.done_all, color: Color(0xFF818CF8)),
              tooltip: 'Mark all as read',
              onPressed: () async {
                await appProvider.markAllAsRead();
                if (mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      content: Text('All notifications marked as read.'),
                      behavior: SnackBarBehavior.floating,
                      backgroundColor: Color(0xFF4F46E5),
                    ),
                  );
                }
              },
            ),
            IconButton(
              icon: const Icon(Icons.delete_sweep, color: Colors.redAccent),
              tooltip: 'Clear inbox',
              onPressed: () => _confirmClearInbox(context, appProvider),
            ),
          ]
        ],
      ),
      body: Container(
        color: const Color(0xFF030712),
        child: notifications.isEmpty
            ? Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Container(
                      height: 80,
                      width: 80,
                      decoration: BoxDecoration(
                        color: const Color(0xFF1F2937).withOpacity(0.4),
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(Icons.notifications_none, color: Color(0xFF4B5563), size: 40),
                    ),
                    const SizedBox(height: 16),
                    const Text(
                      'Your notification inbox is clean.',
                      style: TextStyle(fontSize: 15, color: Colors.white70, fontWeight: FontWeight.bold),
                    ),
                    const SizedBox(height: 6),
                    const Text(
                      'Announcements and new event alerts will appear here.',
                      style: TextStyle(fontSize: 11, color: Color(0xFF64748B)),
                    ),
                  ],
                ),
              )
            : ListView.builder(
                padding: const EdgeInsets.all(16),
                itemCount: notifications.length,
                itemBuilder: (context, index) {
                  final notif = notifications[index];
                  return Container(
                    margin: const EdgeInsets.only(bottom: 12),
                    child: Dismissible(
                      key: Key(notif.id),
                      direction: DismissDirection.endToStart,
                      background: Container(
                        alignment: Alignment.centerRight,
                        padding: const EdgeInsets.symmetric(horizontal: 24),
                        decoration: BoxDecoration(
                          color: Colors.redAccent.shade700,
                          borderRadius: BorderRadius.circular(16),
                        ),
                        child: const Icon(Icons.delete, color: Colors.white),
                      ),
                      onDismissed: (_) {
                        appProvider.clearAllNotifications(); // Wipes specific notification (handled easily by wiping/re-storing, or custom single item deletes)
                        // To keep it precise, we just remove the item from local list
                        notifications.removeAt(index);
                        appProvider.notifyListeners();
                      },
                      child: GestureDetector(
                        onTap: () => _handleNotificationTap(context, notif),
                        child: Card(
                          margin: EdgeInsets.zero,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(16),
                            side: BorderSide(
                              color: notif.isRead
                                  ? Colors.white.withOpacity(0.05)
                                  : const Color(0xFF4F46E5).withOpacity(0.3),
                              width: 1.5,
                            ),
                          ),
                          child: Container(
                            decoration: BoxDecoration(
                              gradient: LinearGradient(
                                begin: Alignment.topRight,
                                end: Alignment.bottomLeft,
                                colors: notif.isRead
                                    ? [const Color(0xFF1F2937), const Color(0xFF111827)]
                                    : [const Color(0xFF1E1B4B).withOpacity(0.6), const Color(0xFF111827)],
                              ),
                              borderRadius: BorderRadius.circular(16),
                            ),
                            padding: const EdgeInsets.all(16),
                            child: Row(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                // 1. Glow Unread Indicator Pin
                                if (!notif.isRead)
                                  Container(
                                    margin: const EdgeInsets.only(top: 6, right: 12),
                                    height: 8,
                                    width: 8,
                                    decoration: const BoxDecoration(
                                      color: Color(0xFF818CF8),
                                      shape: BoxShape.circle,
                                      boxShadow: [
                                        BoxShadow(
                                          color: Color(0xFF818CF8),
                                          blurRadius: 8,
                                          spreadRadius: 2,
                                        )
                                      ],
                                    ),
                                  )
                                else
                                  const SizedBox(width: 8),

                                // 2. Notification Type Specific Icon
                                Container(
                                  height: 38,
                                  width: 38,
                                  decoration: BoxDecoration(
                                    color: _getIconBackgroundColor(notif.payload['type']?.toString()),
                                    shape: BoxShape.circle,
                                  ),
                                  child: Icon(
                                    _getIcon(notif.payload['type']?.toString()),
                                    color: _getIconColor(notif.payload['type']?.toString()),
                                    size: 18,
                                  ),
                                ),
                                const SizedBox(width: 14),

                                // 3. Text Message Contents
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        notif.title,
                                        style: TextStyle(
                                          fontWeight: notif.isRead ? FontWeight.w600 : FontWeight.bold,
                                          fontSize: 13.5,
                                          color: notif.isRead ? Colors.white70 : Colors.white,
                                        ),
                                      ),
                                      const SizedBox(height: 4),
                                      Text(
                                        notif.body,
                                        style: const TextStyle(
                                          fontSize: 11.5,
                                          color: Color(0xFF94A3B8),
                                          height: 1.4,
                                        ),
                                      ),
                                      const SizedBox(height: 8),
                                      Text(
                                        DateFormat('MMM dd, hh:mm a').format(notif.timestamp),
                                        style: const TextStyle(
                                          fontSize: 10,
                                          color: Color(0xFF4B5563),
                                          fontWeight: FontWeight.w600,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ),
                    ),
                  );
                },
              ),
      ),
    );
  }

  void _confirmClearInbox(BuildContext context, AppProvider appProvider) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        backgroundColor: const Color(0xFF111827),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Clear Inbox history?', style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold)),
        content: const Text('This will permanently delete all notifications logs on this device.', style: TextStyle(color: Color(0xFF94A3B8), fontSize: 13)),
        actions: [
          TextButton(
            child: const Text('Cancel', style: TextStyle(color: Color(0xFF64748B))),
            onPressed: () => Navigator.pop(context),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: Colors.redAccent),
            child: const Text('Wipe All', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
            onPressed: () async {
              await appProvider.clearAllNotifications();
              if (mounted) {
                Navigator.pop(context);
              }
            },
          ),
        ],
      ),
    );
  }

  void _handleNotificationTap(BuildContext context, InAppNotification notif) async {
    final appProvider = Provider.of<AppProvider>(context, listen: false);
    await appProvider.markAsRead(notif.id);

    final payload = notif.payload;
    final type = payload['type']?.toString();
    if (type == null) return;

    String? targetId;
    if (type == 'new_event') {
      targetId = payload['event_id']?.toString();
    } else if (type == 'club_broadcast') {
      targetId = payload['club_id']?.toString();
    }

    if (targetId == null) return;

    // Display fetching progress dialog overlay
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => Center(
        child: Container(
          padding: const EdgeInsets.all(24),
          decoration: BoxDecoration(
            color: const Color(0xFF111827),
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: const Color(0xFF4F46E5).withOpacity(0.3)),
          ),
          child: const Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              CircularProgressIndicator(color: Color(0xFF4F46E5)),
              SizedBox(height: 16),
              Text(
                'Opening broadcast event...',
                style: TextStyle(color: Colors.white, fontSize: 12, decoration: TextDecoration.none, fontWeight: FontWeight.normal),
              )
            ],
          ),
        ),
      ),
    );

    final details = await appProvider.fetchDeepLinkDetails(type, targetId);

    // Pop overlay
    if (context.mounted) {
      Navigator.pop(context);
    }

    if (details != null && context.mounted) {
      if (type == 'new_event') {
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => EventDetailScreen(event: details['event']),
          ),
        );
      } else if (type == 'club_broadcast') {
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => YearsScreen(club: details['club']),
          ),
        );
      }
    } else {
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Could not open details. The club or event may no longer exist.'),
            backgroundColor: Colors.redAccent,
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    }
  }

  IconData _getIcon(String? type) {
    if (type == 'new_event') return Icons.event_available;
    if (type == 'club_broadcast') return Icons.campaign;
    return Icons.notifications;
  }

  Color _getIconColor(String? type) {
    if (type == 'new_event') return const Color(0xFF34D399); // green
    if (type == 'club_broadcast') return const Color(0xFFFBBF24); // amber
    return const Color(0xFF818CF8); // indigo
  }

  Color _getIconBackgroundColor(String? type) {
    if (type == 'new_event') return const Color(0xFF10B981).withOpacity(0.15);
    if (type == 'club_broadcast') return const Color(0xFFD97706).withOpacity(0.15);
    return const Color(0xFF4F46E5).withOpacity(0.15);
  }
}
