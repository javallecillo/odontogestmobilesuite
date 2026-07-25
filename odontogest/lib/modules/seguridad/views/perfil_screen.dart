// PerfilScreen — datos reales del usuario autenticado + edición inline.
// Permite cambiar: nombre, usuario, correo, teléfono y contraseña.
import 'package:flutter/material.dart';
import '../../../core/constants/app_theme.dart';
import '../../../core/widgets/app_card.dart';
import '../../../core/widgets/gradient_app_bar.dart';
import '../../../core/session/app_session.dart';
import '../../../data/services/auth_service.dart';
import '../../auth/screens/login_screen.dart';

class PerfilScreen extends StatefulWidget {
  const PerfilScreen({super.key});

  @override
  State<PerfilScreen> createState() => _PerfilScreenState();
}

class _PerfilScreenState extends State<PerfilScreen> {
  bool _guardando = false;

  // Controladores — prellenados desde AppSession
  late final TextEditingController _nombreCtrl;
  late final TextEditingController _usuarioCtrl;
  late final TextEditingController _correoCtrl;
  late final TextEditingController _telefonoCtrl;

  // Contraseña (vacíos por seguridad — el usuario escribe si quiere cambiar)
  final _passActualCtrl = TextEditingController();
  final _passNuevaCtrl  = TextEditingController();
  final _passConfCtrl   = TextEditingController();

  bool _showPassActual = false;
  bool _showPassNueva  = false;
  bool _showPassConf   = false;

  final _formKey = GlobalKey<FormState>();

  @override
  void initState() {
    super.initState();
    final s = AppSession.instance;
    _nombreCtrl   = TextEditingController(text: s.nombre   ?? '');
    _usuarioCtrl  = TextEditingController(text: s.usuario  ?? '');
    _correoCtrl   = TextEditingController(text: s.correo   ?? '');
    _telefonoCtrl = TextEditingController(text: s.telefono ?? '');
  }

  @override
  void dispose() {
    _nombreCtrl.dispose();   _usuarioCtrl.dispose();
    _correoCtrl.dispose();   _telefonoCtrl.dispose();
    _passActualCtrl.dispose(); _passNuevaCtrl.dispose(); _passConfCtrl.dispose();
    super.dispose();
  }

  Future<void> _guardar() async {
    if (!_formKey.currentState!.validate()) return;

    // Validar contraseñas si el usuario quiere cambiarlas
    final cambiandoPass = _passNuevaCtrl.text.isNotEmpty;
    if (cambiandoPass) {
      if (_passActualCtrl.text.isEmpty) {
        _snack('Ingresa tu contraseña actual para cambiarla', error: true);
        return;
      }
      if (_passNuevaCtrl.text != _passConfCtrl.text) {
        _snack('Las contraseñas nuevas no coinciden', error: true);
        return;
      }
      if (_passNuevaCtrl.text.length < 8) {
        _snack('La contraseña debe tener al menos 8 caracteres', error: true);
        return;
      }
    }

    setState(() => _guardando = true);

    final res = await AuthService.actualizarPerfil(
      nombre:            _nombreCtrl.text.trim(),
      usuario:           _usuarioCtrl.text.trim(),
      correo:            _correoCtrl.text.trim(),
      telefono:          _telefonoCtrl.text.trim(),
      nuevaContrasena:   cambiandoPass ? _passNuevaCtrl.text : null,
      contrasenaActual:  cambiandoPass ? _passActualCtrl.text : null,
    );

    if (!mounted) return;
    setState(() => _guardando = false);

    if (res['success'] == true) {
      // Actualizar sesión local
      AppSession.instance.updatePerfil(
        nombre:   _nombreCtrl.text.trim(),
        usuario:  _usuarioCtrl.text.trim(),
        correo:   _correoCtrl.text.trim(),
        telefono: _telefonoCtrl.text.trim(),
      );
      // Limpiar campos de contraseña
      _passActualCtrl.clear();
      _passNuevaCtrl.clear();
      _passConfCtrl.clear();
      _snack('Perfil actualizado correctamente');
    } else {
      _snack(res['mensaje'] ?? 'Error al actualizar', error: true);
    }
  }

  void _snack(String msg, {bool error = false}) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(msg),
      backgroundColor: error ? AppColors.error : AppColors.success,
    ));
  }

  void _confirmLogout() {
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: AppRadius.cardRadius),
        title: Text('Cerrar sesión',
            style: AppTypography.titleSmall(color: AppColors.textDark)),
        content: Text('¿Está seguro que desea cerrar sesión?',
            style: AppTypography.body(color: AppColors.textMuted)),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text('Cancelar',
                style: AppTypography.buttonSmall(color: AppColors.textMuted)),
          ),
          ElevatedButton(
            onPressed: () {
              AppSession.instance.clear();
              Navigator.of(context).pushAndRemoveUntil(
                MaterialPageRoute(builder: (_) => const LoginScreen()),
                (_) => false,
              );
            },
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.error),
            child: Text('Cerrar sesión',
                style: AppTypography.buttonSmall(color: Colors.white)),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final session = AppSession.instance;
    final initials = (session.nombre ?? 'U').isNotEmpty
        ? (session.nombre ?? 'U')[0].toUpperCase()
        : 'U';

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: GradientAppBar(
        title: 'Mi Perfil',
        actions: [
          if (_guardando)
            const Padding(
              padding: EdgeInsets.only(right: 16),
              child: Center(
                child: SizedBox(
                  width: 20, height: 20,
                  child: CircularProgressIndicator(
                      color: Colors.white, strokeWidth: 2)),
              ),
            )
          else
            IconButton(
              icon: const Icon(Icons.save_outlined, color: Colors.white),
              tooltip: 'Guardar cambios',
              onPressed: _guardar,
            ),
        ],
      ),
      body: SingleChildScrollView(
        child: Form(
          key: _formKey,
          child: Column(
            children: [
              // ── Header avatar ──────────────────────────────────
              Container(
                width: double.infinity,
                padding: const EdgeInsets.fromLTRB(16, 28, 16, 28),
                decoration: const BoxDecoration(
                    gradient: AppGradients.primaryVertical),
                child: Column(
                  children: [
                    Stack(
                      alignment: Alignment.bottomRight,
                      children: [
                        CircleAvatar(
                          radius: 42,
                          backgroundColor: Colors.white.withAlpha(51),
                          child: Text(
                            initials,
                            style: AppTypography.headline(color: Colors.white),
                          ),
                        ),
                        // Botón placeholder — la foto de perfil queda para v2
                        Container(
                          padding: const EdgeInsets.all(4),
                          decoration: BoxDecoration(
                            color: AppColors.primary,
                            shape: BoxShape.circle,
                            border: Border.all(color: Colors.white, width: 2),
                          ),
                          child: const Icon(Icons.camera_alt,
                              size: 14, color: Colors.white),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Text(session.nombre ?? 'Sin nombre',
                        style: AppTypography.title(color: Colors.white)),
                    const SizedBox(height: 4),
                    Text(session.rol ?? '',
                        style:
                            AppTypography.caption(color: Colors.white70)),
                    if ((session.correo ?? '').isNotEmpty) ...[
                      const SizedBox(height: 4),
                      Text(session.correo!,
                          style:
                              AppTypography.captionXs(color: Colors.white60)),
                    ],
                  ],
                ),
              ),

              Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // ── Datos personales ───────────────────────
                    _sectionLabel('Datos personales'),
                    AppCard(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        children: [
                          _campo(
                            ctrl:  _nombreCtrl,
                            label: 'Nombre completo',
                            icon:  Icons.person_outline,
                            validator: (v) => (v == null || v.trim().isEmpty)
                                ? 'Requerido'
                                : null,
                          ),
                          const SizedBox(height: 12),
                          _campo(
                            ctrl:  _telefonoCtrl,
                            label: 'Teléfono / celular',
                            icon:  Icons.phone_outlined,
                            keyboard: TextInputType.phone,
                          ),
                          const SizedBox(height: 12),
                          _campo(
                            ctrl:  _correoCtrl,
                            label: 'Correo electrónico',
                            icon:  Icons.email_outlined,
                            keyboard: TextInputType.emailAddress,
                            validator: (v) {
                              if (v != null && v.isNotEmpty &&
                                  !v.contains('@')) {
                                return 'Correo inválido';
                              }
                              return null;
                            },
                          ),
                        ],
                      ),
                    ),

                    const SizedBox(height: 16),

                    // ── Cuenta ─────────────────────────────────
                    _sectionLabel('Cuenta'),
                    AppCard(
                      padding: const EdgeInsets.all(16),
                      child: _campo(
                        ctrl:  _usuarioCtrl,
                        label: 'Nombre de usuario',
                        icon:  Icons.alternate_email,
                        validator: (v) {
                          if (v == null || v.trim().isEmpty) return 'Requerido';
                          if (v.trim().length < 4) {
                            return 'Mínimo 4 caracteres';
                          }
                          return null;
                        },
                      ),
                    ),

                    const SizedBox(height: 16),

                    // ── Cambiar contraseña ─────────────────────
                    _sectionLabel('Cambiar contraseña'),
                    AppCard(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        children: [
                          _campoPass(
                            ctrl:    _passActualCtrl,
                            label:   'Contraseña actual',
                            visible: _showPassActual,
                            onToggle: () => setState(
                                () => _showPassActual = !_showPassActual),
                          ),
                          const SizedBox(height: 12),
                          _campoPass(
                            ctrl:    _passNuevaCtrl,
                            label:   'Nueva contraseña (mín. 8 chars)',
                            visible: _showPassNueva,
                            onToggle: () => setState(
                                () => _showPassNueva = !_showPassNueva),
                          ),
                          const SizedBox(height: 12),
                          _campoPass(
                            ctrl:    _passConfCtrl,
                            label:   'Confirmar nueva contraseña',
                            visible: _showPassConf,
                            onToggle: () => setState(
                                () => _showPassConf = !_showPassConf),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'Deja vacío si no deseas cambiar la contraseña.',
                            style: AppTypography.captionXs(
                                color: AppColors.textMuted),
                          ),
                        ],
                      ),
                    ),

                    const SizedBox(height: 24),

                    // ── Botón Guardar ──────────────────────────
                    SizedBox(
                      width: double.infinity,
                      height: 52,
                      child: ElevatedButton.icon(
                        onPressed: _guardando ? null : _guardar,
                        icon: _guardando
                            ? const SizedBox(
                                width: 18, height: 18,
                                child: CircularProgressIndicator(
                                    color: Colors.white, strokeWidth: 2))
                            : const Icon(Icons.save_outlined,
                                color: Colors.white, size: 18),
                        label: Text(
                          _guardando ? 'Guardando…' : 'Guardar cambios',
                          style: AppTypography.button(color: Colors.white),
                        ),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppColors.primary,
                          shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(14)),
                        ),
                      ),
                    ),

                    const SizedBox(height: 16),

                    // ── Cerrar sesión ──────────────────────────
                    SizedBox(
                      width: double.infinity,
                      height: 48,
                      child: OutlinedButton.icon(
                        onPressed: _confirmLogout,
                        icon: const Icon(Icons.logout,
                            color: AppColors.error, size: 18),
                        label: Text('Cerrar sesión',
                            style: AppTypography.buttonSmall(
                                color: AppColors.error)),
                        style: OutlinedButton.styleFrom(
                          side: const BorderSide(color: AppColors.error),
                          shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(14)),
                        ),
                      ),
                    ),
                    const SizedBox(height: 40),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  // ── Helpers ────────────────────────────────────────────────

  Widget _sectionLabel(String label) => Padding(
        padding: const EdgeInsets.only(left: 4, bottom: 8),
        child: Text(label,
            style: AppTypography.label(color: AppColors.textMuted)),
      );

  Widget _campo({
    required TextEditingController ctrl,
    required String label,
    required IconData icon,
    TextInputType keyboard = TextInputType.text,
    String? Function(String?)? validator,
  }) =>
      TextFormField(
        controller: ctrl,
        keyboardType: keyboard,
        style: AppTypography.body(color: AppColors.textDark),
        validator: validator,
        decoration: InputDecoration(
          labelText: label,
          prefixIcon: Icon(icon, size: 18, color: AppColors.textMuted),
          filled: true,
          fillColor: AppColors.background,
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: const BorderSide(color: AppColors.border)),
          enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: const BorderSide(color: AppColors.border)),
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide:
                const BorderSide(color: AppColors.primary, width: 1.5)),
          contentPadding:
              const EdgeInsets.symmetric(vertical: 12, horizontal: 14),
        ),
      );

  Widget _campoPass({
    required TextEditingController ctrl,
    required String label,
    required bool visible,
    required VoidCallback onToggle,
  }) =>
      TextFormField(
        controller: ctrl,
        obscureText: !visible,
        style: AppTypography.body(color: AppColors.textDark),
        decoration: InputDecoration(
          labelText: label,
          prefixIcon: const Icon(Icons.lock_outline,
              size: 18, color: AppColors.textMuted),
          suffixIcon: IconButton(
            icon: Icon(
              visible ? Icons.visibility_off : Icons.visibility,
              size: 18,
              color: AppColors.textMuted,
            ),
            onPressed: onToggle,
          ),
          filled: true,
          fillColor: AppColors.background,
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: const BorderSide(color: AppColors.border)),
          enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: const BorderSide(color: AppColors.border)),
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide:
                const BorderSide(color: AppColors.primary, width: 1.5)),
          contentPadding:
              const EdgeInsets.symmetric(vertical: 12, horizontal: 14),
        ),
      );
}
