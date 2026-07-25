// CrearUsuarioScreen — formulario para registrar nuevos usuarios.
// Rol requerido: Administrador o Recepcionista.
// POST → /usuarios/crear.php
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import '../../../core/constants/app_theme.dart';
import '../../../core/session/app_session.dart';

const String _kBase = 'http://localhost/odontogest_api';

class CrearUsuarioScreen extends StatefulWidget {
  const CrearUsuarioScreen({super.key});

  @override
  State<CrearUsuarioScreen> createState() => _CrearUsuarioScreenState();
}

class _CrearUsuarioScreenState extends State<CrearUsuarioScreen> {
  final _formKey   = GlobalKey<FormState>();
  bool _loading    = false;
  bool _obscure    = true;

  // Controladores
  final _nombreCtrl    = TextEditingController();
  final _apellidoCtrl  = TextEditingController();
  final _correoCtrl    = TextEditingController();
  final _usuarioCtrl   = TextEditingController();
  final _passCtrl      = TextEditingController();

  // Roles cargados del servidor
  List<Map<String, dynamic>> _roles       = [];
  int?                       _idRolSelect;
  bool                       _loadingRoles = true;

  @override
  void initState() {
    super.initState();
    _cargarRoles();
  }

  @override
  void dispose() {
    _nombreCtrl.dispose();
    _apellidoCtrl.dispose();
    _correoCtrl.dispose();
    _usuarioCtrl.dispose();
    _passCtrl.dispose();
    super.dispose();
  }

  // ── Carga de roles desde la API ───────────────────────────────
  Future<void> _cargarRoles() async {
    try {
      final res = await http.get(
        Uri.parse('$_kBase/usuarios/listar_roles.php'),
        headers: {
          'Authorization': 'Bearer ${AppSession.instance.token}',
        },
      ).timeout(const Duration(seconds: 8));

      if (res.statusCode == 200) {
        final body = jsonDecode(res.body) as Map<String, dynamic>;
        if (body['success'] == true) {
          setState(() {
            _roles = List<Map<String, dynamic>>.from(body['roles'] as List);
            _loadingRoles = false;
          });
          return;
        }
      }
    } catch (_) {}

    // Fallback si no conecta — roles base del seed
    setState(() {
      _roles = [
        {'id_rol': 1, 'nombre': 'Administrador'},
        {'id_rol': 2, 'nombre': 'Odontologo'},
        {'id_rol': 3, 'nombre': 'Recepcionista'},
        {'id_rol': 4, 'nombre': 'Asistente'},
      ];
      _loadingRoles = false;
    });
  }

  // ── Submit ────────────────────────────────────────────────────
  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    if (_idRolSelect == null) {
      _snack('Selecciona un rol', isError: true);
      return;
    }

    setState(() => _loading = true);

    try {
      final res = await http.post(
        Uri.parse('$_kBase/usuarios/crear.php'),
        headers: {
          'Content-Type':  'application/json',
          'Authorization': 'Bearer ${AppSession.instance.token}',
        },
        body: jsonEncode({
          'nombre':     _nombreCtrl.text.trim(),
          'apellido':   _apellidoCtrl.text.trim(),
          'correo':     _correoCtrl.text.trim(),
          'usuario':    _usuarioCtrl.text.trim(),
          'contrasena': _passCtrl.text,
          'id_rol':     _idRolSelect,
        }),
      ).timeout(const Duration(seconds: 10));

      if (!mounted) return;

      final body = jsonDecode(res.body) as Map<String, dynamic>;

      if (res.statusCode == 200 && body['success'] == true) {
        _snack('Usuario creado correctamente ✓', isError: false);
        await Future.delayed(const Duration(seconds: 1));
        if (mounted) Navigator.pop(context, true); // true = refrescar lista
      } else {
        _snack(body['mensaje'] ?? 'Error al crear usuario', isError: true);
      }
    } catch (e) {
      _snack('Error de conexión: $e', isError: true);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  void _snack(String msg, {required bool isError}) {
    ScaffoldMessenger.of(context)
      ..clearSnackBars()
      ..showSnackBar(SnackBar(
        content: Row(children: [
          Icon(
            isError ? Icons.error_outline : Icons.check_circle_outline,
            color: Colors.white, size: 18,
          ),
          const SizedBox(width: 8),
          Expanded(child: Text(msg)),
        ]),
        backgroundColor: isError ? AppColors.error : AppColors.success,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        margin: const EdgeInsets.all(16),
        duration: Duration(seconds: isError ? 4 : 2),
      ));
  }

  // ── UI ────────────────────────────────────────────────────────
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.primary,
        foregroundColor: Colors.white,
        title: Text('Crear Usuario',
            style: AppTypography.titleSmall(color: Colors.white)),
        centerTitle: false,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // ── Sección datos personales ──
              _sectionHeader('Datos personales', Icons.person_outline),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(child: _field('Nombre *', _nombreCtrl,
                      validator: _requerido)),
                  const SizedBox(width: 12),
                  Expanded(child: _field('Apellido *', _apellidoCtrl,
                      validator: _requerido)),
                ],
              ),
              const SizedBox(height: 12),
              _field('Correo electrónico *', _correoCtrl,
                  keyboardType: TextInputType.emailAddress,
                  validator: (v) {
                    if (v == null || v.trim().isEmpty) return 'Requerido';
                    if (!v.contains('@')) return 'Correo inválido';
                    return null;
                  }),
              const SizedBox(height: 24),

              // ── Sección cuenta ──
              _sectionHeader('Cuenta de acceso', Icons.lock_outline),
              const SizedBox(height: 12),
              _field('Usuario *', _usuarioCtrl, validator: (v) {
                if (v == null || v.trim().isEmpty) return 'Requerido';
                if (v.trim().length < 4) return 'Mínimo 4 caracteres';
                return null;
              }),
              const SizedBox(height: 12),
              _field(
                'Contraseña *',
                _passCtrl,
                obscure: _obscure,
                validator: (v) {
                  if (v == null || v.isEmpty) return 'Requerido';
                  if (v.length < 8) return 'Mínimo 8 caracteres';
                  return null;
                },
                suffix: IconButton(
                  icon: Icon(
                    _obscure
                        ? Icons.visibility_off_outlined
                        : Icons.visibility_outlined,
                    color: AppColors.textMuted,
                    size: 20,
                  ),
                  onPressed: () => setState(() => _obscure = !_obscure),
                ),
              ),
              const SizedBox(height: 24),

              // ── Rol ──
              _sectionHeader('Rol del sistema', Icons.admin_panel_settings_outlined),
              const SizedBox(height: 12),
              _loadingRoles
                  ? const Center(
                      child: CircularProgressIndicator(color: AppColors.primary))
                  : DropdownButtonFormField<int>(
                      value: _idRolSelect,
                      decoration: _inputDeco('Selecciona un rol'),
                      items: _roles
                          .map((r) => DropdownMenuItem<int>(
                                value: r['id_rol'] as int,
                                child: Text(r['nombre'] as String),
                              ))
                          .toList(),
                      onChanged: (v) => setState(() => _idRolSelect = v),
                      validator: (v) => v == null ? 'Selecciona un rol' : null,
                    ),
              const SizedBox(height: 36),

              // ── Botón crear ──
              SizedBox(
                width: double.infinity,
                height: 50,
                child: ElevatedButton(
                  onPressed: _loading ? null : _submit,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.primary,
                    foregroundColor: Colors.white,
                    disabledBackgroundColor: AppColors.primary.withAlpha(120),
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(14)),
                    elevation: 2,
                  ),
                  child: _loading
                      ? const SizedBox(
                          width: 22,
                          height: 22,
                          child: CircularProgressIndicator(
                              color: Colors.white, strokeWidth: 2.5))
                      : Text('Crear Usuario',
                          style: AppTypography.button(color: Colors.white)),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  // ── Helpers ───────────────────────────────────────────────────

  Widget _sectionHeader(String label, IconData icon) => Row(
        children: [
          Icon(icon, color: AppColors.primary, size: 18),
          const SizedBox(width: 8),
          Text(label, style: AppTypography.label(color: AppColors.primary)),
        ],
      );

  Widget _field(
    String label,
    TextEditingController ctrl, {
    bool                   obscure      = false,
    Widget?                suffix,
    TextInputType          keyboardType = TextInputType.text,
    String? Function(String?)? validator,
  }) =>
      TextFormField(
        controller:   ctrl,
        obscureText:  obscure,
        keyboardType: keyboardType,
        enabled:      !_loading,
        validator:    validator,
        style: AppTypography.body(color: AppColors.textDark),
        decoration: _inputDeco(label, suffix: suffix),
      );

  InputDecoration _inputDeco(String hint, {Widget? suffix}) => InputDecoration(
        hintText:    hint,
        suffixIcon:  suffix,
        filled:      true,
        fillColor:   AppColors.surface,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: BorderSide.none,
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide:
              const BorderSide(color: AppColors.primary, width: 1.5),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: const BorderSide(color: AppColors.error, width: 1),
        ),
        focusedErrorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: const BorderSide(color: AppColors.error, width: 1.5),
        ),
        contentPadding:
            const EdgeInsets.symmetric(vertical: 14, horizontal: 16),
      );

  String? _requerido(String? v) =>
      (v == null || v.trim().isEmpty) ? 'Requerido' : null;
}
