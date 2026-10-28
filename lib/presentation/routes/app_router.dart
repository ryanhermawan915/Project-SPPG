import 'package:flutter/material.dart';
import '../pages/auth/login_page.dart';
import '../pages/auth/register_page.dart';
import '../pages/face_registration/face_scan_page.dart';
import '../pages/home/home_page.dart';

class AppRoutes {
  static const String login = '/login';
  static const String register = '/register';
  static const String faceRegistration = '/face-registration';
  static const String home = '/home';

  static Map<String, WidgetBuilder> get routes => {
        login: (_) => const LoginPage(),
        register: (_) => const RegisterPage(),
        faceRegistration: (_) => const FaceScanPage(),
        home: (_) => const HomePage(),
      };
}