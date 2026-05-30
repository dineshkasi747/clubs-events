import 'package:dio/dio.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class ApiClient {
  // Use http://10.0.2.2:8000/api for Android Emulator, http://localhost:8000/api for iOS/Web
  static const String baseUrl = 'https://gyrostatically-unstatistic-kelsi.ngrok-free.dev/api';
  static final Dio _dio = Dio(BaseOptions(baseUrl: baseUrl));
  static const _storage = FlutterSecureStorage();

  static Dio get client {
    _dio.interceptors.clear();
    _dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          final token = await _storage.read(key: 'auth_token');
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
    return _dio;
  }

  static Future<void> saveToken(String token) async {
    await _storage.write(key: 'auth_token', value: token);
  }

  static Future<void> clearToken() async {
    await _storage.delete(key: 'auth_token');
  }
}
