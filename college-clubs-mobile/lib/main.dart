import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'providers/app_provider.dart';
import 'core/firebase_handler.dart';
import 'screens/login_screen.dart';

// Firebase background message handler
Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  await Firebase.initializeApp();
  print("🔥 Handling background message: ${message.messageId}");
}

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  
  // Initialize Firebase (safely failing over if Firebase config is missing)
  try {
    await Firebase.initializeApp();
    FirebaseMessaging.onBackgroundMessage(_firebaseMessagingBackgroundHandler);
  } catch (e) {
    print("⚠️ Firebase initialization skipped or failed: $e");
  }

  runApp(
    MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AppProvider()),
      ],
      child: const CollegeClubsApp(),
    ),
  );
}

class CollegeClubsApp extends StatelessWidget {
  const CollegeClubsApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'College Clubs & Events',
      debugShowCheckedModeBanner: false,
      theme: ThemeData.dark().copyWith(
        scaffoldBackgroundColor: const Color(0xFF030712), // Deep black-grey
        primaryColor: const Color(0xFF4F46E5), // Indigo brand color
        colorScheme: const ColorScheme.dark(
          primary: Color(0xFF4F46E5),
          secondary: Color(0xFF6366F1),
          surface: Color(0xFF111827),
        ),
        cardTheme: const CardTheme(
          color: Color(0xFF1F2937),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.all(Radius.circular(16))),
        ),
      ),
      home: const FirebaseInitializerWrapper(child: LoginScreen()),
    );
  }
}

class FirebaseInitializerWrapper extends StatefulWidget {
  final Widget child;
  const FirebaseInitializerWrapper({super.key, required this.child});

  @override
  State<FirebaseInitializerWrapper> createState() => _FirebaseInitializerWrapperState();
}

class _FirebaseInitializerWrapperState extends State<FirebaseInitializerWrapper> {
  @override
  void initState() {
    super.initState();
    // Setup foreground notifications and token registers
    FirebaseHandler.setupFirebase(context);
  }

  @override
  Widget build(BuildContext context) {
    return widget.child;
  }
}
