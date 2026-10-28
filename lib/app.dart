import 'package:flutter/material.dart';
import 'core/theme/app_theme.dart';
import 'presentation/routes/app_router.dart';

class SppgApp extends StatelessWidget {
  final bool isLoggedIn;
  const SppgApp({super.key, required this.isLoggedIn});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'SPPG',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.lightTheme,
      initialRoute: isLoggedIn ? AppRoutes.home : AppRoutes.login,
      routes: AppRoutes.routes,
    );
  }
}