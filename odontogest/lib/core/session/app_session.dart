// ── AppSession — singleton de sesión activa ───────────────────
// Almacena token + datos en memoria Y los persiste en SharedPreferences
// para que sobrevivan reinicios de la app.
import 'package:shared_preferences/shared_preferences.dart';

class AppSession {
  AppSession._();
  static final AppSession _instance = AppSession._();
  static AppSession get instance => _instance;

  // ── Campos en memoria ────────────────────────────────────────
  String? token;
  int?    idUsuario;
  String? rol;
  String? nombre;
  String? usuario;   // username de login
  String? correo;
  String? telefono;
  String? fotoUrl;   // URL relativa o absoluta de la foto de perfil

  bool get isLoggedIn => token != null && token!.isNotEmpty;

  // ── Claves SharedPreferences ─────────────────────────────────
  static const _kToken    = 'session_token';
  static const _kId       = 'session_id';
  static const _kRol      = 'session_rol';
  static const _kNombre   = 'session_nombre';
  static const _kUsuario  = 'session_usuario';
  static const _kCorreo   = 'session_correo';
  static const _kTelefono = 'session_telefono';
  static const _kFotoUrl  = 'session_foto_url';

  // ── Restaurar sesión al arrancar la app ──────────────────────
  /// Llama este método en main() antes de runApp().
  /// Devuelve true si había sesión guardada válida.
  static Future<bool> restore() async {
    final prefs = await SharedPreferences.getInstance();
    final t = prefs.getString(_kToken);
    if (t == null || t.isEmpty) return false;

    _instance.token     = t;
    _instance.idUsuario = prefs.getInt(_kId);
    _instance.rol       = prefs.getString(_kRol);
    _instance.nombre    = prefs.getString(_kNombre);
    _instance.usuario   = prefs.getString(_kUsuario);
    _instance.correo    = prefs.getString(_kCorreo);
    _instance.telefono  = prefs.getString(_kTelefono);
    _instance.fotoUrl   = prefs.getString(_kFotoUrl);
    return true;
  }

  // ── Guardar sesión tras login exitoso ────────────────────────
  void set({
    required String token,
    required int    idUsuario,
    required String rol,
    required String nombre,
    String? usuario,
    String? correo,
    String? telefono,
    String? fotoUrl,
  }) {
    this.token     = token;
    this.idUsuario = idUsuario;
    this.rol       = rol;
    this.nombre    = nombre;
    this.usuario   = usuario;
    this.correo    = correo;
    this.telefono  = telefono;
    this.fotoUrl   = fotoUrl;
    _persist();
  }

  /// Actualiza sólo los campos de perfil (sin tocar token/rol)
  void updatePerfil({
    String? nombre,
    String? usuario,
    String? correo,
    String? telefono,
    String? fotoUrl,
  }) {
    if (nombre   != null) this.nombre   = nombre;
    if (usuario  != null) this.usuario  = usuario;
    if (correo   != null) this.correo   = correo;
    if (telefono != null) this.telefono = telefono;
    if (fotoUrl  != null) this.fotoUrl  = fotoUrl;
    _persist();
  }

  // ── Limpiar sesión al cerrar sesión ──────────────────────────
  void clear() {
    token     = null;
    idUsuario = null;
    rol       = null;
    nombre    = null;
    usuario   = null;
    correo    = null;
    telefono  = null;
    fotoUrl   = null;
    SharedPreferences.getInstance().then((p) => p.clear());
  }

  // ── Persistencia interna (fire-and-forget) ───────────────────
  void _persist() {
    SharedPreferences.getInstance().then((prefs) {
      prefs.setString(_kToken,    token     ?? '');
      prefs.setInt   (_kId,       idUsuario ?? 0);
      prefs.setString(_kRol,      rol       ?? '');
      prefs.setString(_kNombre,   nombre    ?? '');
      prefs.setString(_kUsuario,  usuario   ?? '');
      prefs.setString(_kCorreo,   correo    ?? '');
      prefs.setString(_kTelefono, telefono  ?? '');
      prefs.setString(_kFotoUrl,  fotoUrl   ?? '');
    });
  }
}
