// ── AppSession — singleton de sesión activa ───────────────────
// Almacena el token y datos del usuario autenticado en memoria.
// Se popula en main.dart / AuthController tras el login exitoso.

class AppSession {
  AppSession._();
  static final AppSession _instance = AppSession._();
  static AppSession get instance => _instance;

  String? token;
  int?    idUsuario;
  String? rol;
  String? nombre;
  String? usuario;   // username de login
  String? correo;
  String? telefono;
  String? fotoUrl;   // URL relativa o absoluta de la foto de perfil

  bool get isLoggedIn => token != null;

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
  }

  void clear() {
    token     = null;
    idUsuario = null;
    rol       = null;
    nombre    = null;
    usuario   = null;
    correo    = null;
    telefono  = null;
    fotoUrl   = null;
  }
}
