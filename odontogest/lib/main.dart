import 'package:flutter/material.dart';
import 'core/constants/app_strings.dart';
import 'core/constants/app_theme.dart';
import 'core/session/app_session.dart';
import 'modules/auth/screens/login_screen.dart';
import 'modules/seguridad/views/home_shell.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Restaura la sesión persistida (token + datos de usuario).
  // Si no hay sesión guardada, muestra LoginScreen.
  final sessionRestored = await AppSession.restore();

  runApp(OdontoGestApp(sessionRestored: sessionRestored));
}

class OdontoGestApp extends StatelessWidget {
  const OdontoGestApp({super.key, required this.sessionRestored});

  final bool sessionRestored;

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: AppStrings.appName,
      debugShowCheckedModeBanner: false,
      theme: AppTheme.light(),
      home: sessionRestored
          ? HomeShell(rol: AppSession.instance.rol ?? 'Odontologo')
          : const LoginScreen(),
    );
  }
}
