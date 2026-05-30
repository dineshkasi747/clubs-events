import 'package:flutter/material.dart';
import '../models/club.dart';
import '../models/event.dart';
import '../core/api_client.dart';

class AppProvider with ChangeNotifier {
  bool _isLoading = false;
  bool get isLoading => _isLoading;

  String? _fcmToken;
  String? get fcmToken => _fcmToken;

  Map<String, dynamic>? _user;
  Map<String, dynamic>? get user => _user;

  List<Club> _clubs = [];
  List<Club> get clubs => _clubs;

  List<Event> _events = [];
  List<Event> get events => _events;

  void setFcmToken(String token) {
    _fcmToken = token;
    notifyListeners();
    // Register immediately if user is already logged in
    if (_user != null) {
      _registerFcmTokenWithBackend();
    }
  }

  Future<bool> login(String email, String password) async {
    _isLoading = true;
    notifyListeners();

    try {
      final response = await ApiClient.client.post('/login', data: {
        'email': email,
        'password': password,
      });

      if (response.statusCode == 200) {
        final token = response.data['token'];
        _user = response.data['user'];
        await ApiClient.saveToken(token);
        
        // Register device token with backend upon successful login
        if (_fcmToken == null) {
          // Fallback to a mock FCM token for dev/emulator environments without Google Play Services
          _fcmToken = "mock_fcm_token_student_" + _user!['id'].toString();
        }
        await _registerFcmTokenWithBackend();

        _isLoading = false;
        notifyListeners();
        return true;
      }
    } catch (e) {
      print("🔴 Login Error: $e");
    }

    _isLoading = false;
    notifyListeners();
    return false;
  }

  Future<void> logout() async {
    try {
      await ApiClient.client.post('/logout');
    } catch (_) {}
    
    _user = null;
    await ApiClient.clearToken();
    notifyListeners();
  }

  Future<void> _registerFcmTokenWithBackend() async {
    if (_fcmToken == null) return;
    try {
      await ApiClient.client.post('/fcm-token', data: {
        'fcm_token': _fcmToken,
      });
      print("🔥 FCM Token registered successfully on backend!");
    } catch (e) {
      print("⚠️ Failed to register FCM token with backend: $e");
    }
  }

  Future<void> fetchClubs() async {
    _isLoading = true;
    notifyListeners();

    try {
      final response = await ApiClient.client.get('/clubs');
      if (response.statusCode == 200) {
        final List data = response.data;
        _clubs = data.map((c) => Club.fromJson(c)).toList();
      }
    } catch (e) {
      print("🔴 Fetch Clubs Error: $e");
    }

    _isLoading = false;
    notifyListeners();
  }

  Future<void> fetchClubEvents(int clubId) async {
    _isLoading = true;
    notifyListeners();

    try {
      final response = await ApiClient.client.get('/clubs/$clubId');
      if (response.statusCode == 200) {
        // Assume backend returns club details with an associated events array
        final List data = response.data['events'] ?? [];
        _events = data.map((e) => Event.fromJson(e)).toList();
      }
    } catch (e) {
      print("🔴 Fetch Club Events Error: $e");
      _events = [];
    }

    _isLoading = false;
    notifyListeners();
  }

  /// Extracts distinct academic years (e.g. "2023-2024") from the club's event history
  List<String> getEventYears() {
    final Set<String> years = {};
    for (var evt in _events) {
      // Regex matching standard 4-digit years (e.g. 2023)
      final match = RegExp(r'\b(20\d{2})\b').firstMatch(evt.formattedDate);
      if (match != null) {
        final year = match.group(1)!;
        final nextYearShort = (int.parse(year) + 1).toString();
        years.add("$year-$nextYearShort");
      } else {
        years.add("Current");
      }
    }
    // Sort years descending
    return years.toList()..sort((a, b) => b.compareTo(a));
  }

  /// Filters events by a specific year label (e.g. "2023-2024")
  List<Event> getEventsForYear(String yearLabel) {
    if (yearLabel == "Current") {
      return _events.where((e) => !RegExp(r'\b(20\d{2})\b').hasMatch(e.formattedDate)).toList();
    }
    final targetYear = yearLabel.split('-')[0];
    return _events.where((e) => e.formattedDate.contains(targetYear)).toList();
  }

  /// Handles event registration booking with paid amounts
  Future<bool> registerForEvent(int eventId, {String paymentMethod = 'card'}) async {
    _isLoading = true;
    notifyListeners();

    try {
      final response = await ApiClient.client.post(
        '/events/$eventId/register',
        data: {
          'payment_method': paymentMethod,
        },
      );
      if (response.statusCode == 200 || response.statusCode == 201) {
        _isLoading = false;
        notifyListeners();
        return true;
      }
    } catch (e) {
      print("🔴 Event Registration Error: $e");
    }

    _isLoading = false;
    notifyListeners();
    return false;
  }
}
