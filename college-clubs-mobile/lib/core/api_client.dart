import 'package:dio/dio.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class ApiClient {
  // Use http://10.0.2.2:8000/api for Android Emulator, http://localhost:8000/api for iOS/Web
  static const String baseUrl = 'https://gyrostatically-unstatistic-kelsi.ngrok-free.dev/api';
  static const _storage = FlutterSecureStorage(
    aOptions: AndroidOptions(
      encryptedSharedPreferences: true,
    ),
  );

  static final Dio client = Dio(BaseOptions(
    baseUrl: baseUrl,
    connectTimeout: const Duration(seconds: 10),
    receiveTimeout: const Duration(seconds: 10),
  ))..interceptors.add(
    InterceptorsWrapper(
      onRequest: (options, handler) async {
        String? token;
        try {
          token = await _storage.read(key: 'auth_token').timeout(const Duration(seconds: 1));
        } catch (e) {
          print("⚠️ Secure Storage read failed or timed out: $e");
        }

        if (token != null) {
          options.headers['Authorization'] = 'Bearer $token';
        }
        options.headers['Accept'] = 'application/json';
        options.headers['ngrok-skip-browser-warning'] = 'true';
        return handler.next(options);
      },
      onError: (DioException e, handler) {
        print("🔴 API Client Error: ${e.response?.statusCode} - ${e.message}");
        return handler.next(e);
      },
    ),
  );

  static Future<void> saveToken(String token) async {
    await _storage.write(key: 'auth_token', value: token);
  }

  static Future<void> clearToken() async {
    await _storage.delete(key: 'auth_token');
  }
}
