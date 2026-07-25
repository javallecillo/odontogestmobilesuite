// LoginScreen — pantalla de inicio de sesión.
// Extraída de main.dart para permitir navegación desde otras pantallas.
import 'package:flutter/material.dart';
import '../../../core/constants/app_theme.dart';
import '../../../core/session/app_session.dart';
import '../../../data/services/auth_service.dart';
import '../../seguridad/views/home_shell.dart';

// ─── Wave Clipper ─────────────────────────────────────────────
class WaveClipper extends CustomClipper<Path> {
  @override
  Path getClip(Size size) {
    final path = Path();
    path.lineTo(0, size.height - 60);
    path.cubicTo(
      size.width * 0.35, size.height + 20,
      size.width * 0.65, size.height - 90,
      size.width, size.height - 30,
    );
    path.lineTo(size.width, 0);
    path.close();
    return path;
  }

  @override
  bool shouldReclip(WaveClipper oldClipper) => false;
}

// ─── Login Screen ─────────────────────────────────────────────
class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  bool _obscurePassword = true;
  bool _loading = false;

  final _userController = TextEditingController();
  final _passController = TextEditingController();

  @override
  void dispose() {
    _userController.dispose();
    _passController.dispose();
    super.dispose();
  }

  Future<void> _handleLogin() async {
    final usuario    = _userController.text.trim();
    final contrasena = _passController.text;

    if (usuario.isEmpty || contrasena.isEmpty) {
      _showSnackBar('Ingresa usuario y contraseña para iniciar sesión', isError: true);
      return;
    }

    setState(() => _loading = true);

    final result = await AuthService.login(
      usuario:    usuario,
      contrasena: contrasena,
    );

    if (!mounted) return;
    setState(() => _loading = false);

    if (result.success) {
      AppSession.instance.set(
        token:     result.token!,
        idUsuario: result.idUsuario ?? 0,
        rol:       result.rol       ?? 'Odontologo',
        nombre:    result.nombre    ?? usuario,
        usuario:   result.usuario   ?? usuario,
        correo:    result.correo,
        telefono:  result.telefono,
      );
      _showSnackBar('Bienvenido, ${result.nombre ?? usuario}', isError: false);
      await Future.delayed(const Duration(milliseconds: 600));
      if (!mounted) return;
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(
          builder: (_) => HomeShell(rol: result.rol ?? 'Odontologo'),
        ),
      );
    } else {
      _showSnackBar(result.errorMsg ?? 'Error al iniciar sesión', isError: true);
    }
  }

  void _showSnackBar(String msg, {required bool isError}) {
    ScaffoldMessenger.of(context).clearSnackBars();
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            Icon(
              isError ? Icons.error_outline : Icons.check_circle_outline,
              color: Colors.white, size: 18,
            ),
            const SizedBox(width: 8),
            Expanded(child: Text(msg)),
          ],
        ),
        backgroundColor: isError ? const Color.fromARGB(255, 235, 46, 46) : const Color.fromARGB(255, 33, 176, 85),
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(25)),
        margin: const EdgeInsets.all(16),
        duration: Duration(seconds: isError ? 4 : 2),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final screenHeight = MediaQuery.of(context).size.height;

    return Scaffold(
      backgroundColor: AppColors.background,
      body: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // ── Header con ola ──
            ClipPath(
              clipper: WaveClipper(),
              child: Container(
                height: screenHeight * 0.45,
                decoration: const BoxDecoration(
                  gradient: LinearGradient(
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                    colors: [AppColors.primary, AppColors.primaryDark],
                  ),
                ),
                child: SafeArea(
                  child: Center(
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Image(
                          image: AssetImage('assets/images/logo_ortonova.png'),
                          width: 250, height: 250,
                        )
                      ],
                    ),
                  ),
                ),
              ),
            ),

            // ── Formulario ──
            Padding(
              padding: const EdgeInsets.fromLTRB(28, 4, 28, 24),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Iniciar sesión',
                      style: AppTypography.headline(color: AppColors.primary)),
                  const SizedBox(height: 24),

                  Text('Usuario',
                      style: AppTypography.label(color: AppColors.primary)),
                  const SizedBox(height: 8),
                  _inputField(
                    controller: _userController,
                    enabled: !_loading,
                    textInputAction: TextInputAction.next,
                    style: AppTypography.body(color: AppColors.textDark),
                  ),
                  const SizedBox(height: 20),

                  Text('Contraseña',
                      style: AppTypography.label(color: AppColors.primary)),
                  const SizedBox(height: 8),
                  _inputField(
                    controller: _passController,
                    obscureText: _obscurePassword,
                    enabled: !_loading,
                    textInputAction: TextInputAction.done,
                    onSubmitted: (_) => _handleLogin(),
                    style: AppTypography.body(color: AppColors.textDark),
                    suffix: IconButton(
                      icon: Icon(
                        _obscurePassword
                            ? Icons.visibility_off_outlined
                            : Icons.visibility_outlined,
                        color: AppColors.textMuted, size: 20,
                      ),
                      onPressed: () => setState(
                          () => _obscurePassword = !_obscurePassword),
                    ),
                  ),

                  const SizedBox(height: 28),
                  Center(
                    child: SizedBox(
                      width: 180, height: 48,
                      child: ElevatedButton(
                        onPressed: _loading ? null : _handleLogin,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppColors.primary,
                          foregroundColor: AppColors.surface,
                          disabledBackgroundColor:
                              AppColors.primary.withAlpha(120),
                          shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(30)),
                          elevation: 2,
                        ),
                        child: _loading
                            ? const SizedBox(
                                width: 22, height: 22,
                                child: CircularProgressIndicator(
                                    color: Colors.white, strokeWidth: 2.5))
                            : Text('Ingresar',
                                style: AppTypography.button(
                                    color: AppColors.surface)),
                      ),
                    ),
                  ),

                  const SizedBox(height: 32),
                  Center(
                    child: Text('OdontoGest v1.0',
                        style: AppTypography.caption(
                            color: AppColors.textMuted)),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _inputField({
    required TextEditingController controller,
    required bool enabled,
    required TextInputAction textInputAction,
    required TextStyle style,
    bool obscureText = false,
    Widget? suffix,
    ValueChanged<String>? onSubmitted,
  }) =>
      Container(
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(25),
          boxShadow: [
            BoxShadow(
              color: AppColors.primary.withAlpha(38),
              blurRadius: 18,
              spreadRadius: -6,
              offset: const Offset(0, 10),
            ),
          ],
        ),
        child: TextField(
          controller: controller,
          obscureText: obscureText,
          enabled: enabled,
          textInputAction: textInputAction,
          onSubmitted: onSubmitted,
          style: style,
          decoration: _inputDeco(suffix: suffix),
        ),
      );

  InputDecoration _inputDeco({Widget? suffix}) => InputDecoration(
        suffixIcon: suffix,
        filled: true,
        fillColor: AppColors.surface,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(25),
          borderSide: const BorderSide(color: Colors.transparent, width: 1.5),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(25),
          borderSide: const BorderSide(color: Colors.transparent, width: 1.5),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(25),
          borderSide: const BorderSide(color: AppColors.primary, width: 1.5),
        ),
        disabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(25),
          borderSide: const BorderSide(color: Colors.transparent, width: 1.5),
        ),
        contentPadding:
            const EdgeInsets.symmetric(vertical: 16, horizontal: 20),
      );
}
