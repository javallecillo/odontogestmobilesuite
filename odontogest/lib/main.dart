import 'package:flutter/material.dart';
import 'core/constants/app_strings.dart';
import 'core/constants/app_theme.dart';
import 'modules/auth/screens/login_screen.dart';

void main() {
  runApp(const OdontoGestApp());
}

class OdontoGestApp extends StatelessWidget {
  const OdontoGestApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: AppStrings.appName,
      debugShowCheckedModeBanner: false,
      theme: AppTheme.light(),
      home: const LoginScreen(),
    );
  }
}

// LoginScreen y WaveClipper viven en modules/auth/screens/login_screen.dart
