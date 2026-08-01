// ─────────────────────────────────────────────────────────────────────────────
// app_theme.dart
//
// PROPÓSITO: Centraliza TODOS los tokens de diseño del sistema OdontoGest.
// Cualquier cambio visual (color, tipografía, espaciado, radio) se hace aquí
// y se propaga automáticamente a toda la app.
//
// USO:
//   import '../core/constants/app_theme.dart';
//   Theme.of(context) ya carga AppTheme.light()
//   Para acceder directamente: AppColors.primary, AppSpacing.md, etc.
// ─────────────────────────────────────────────────────────────────────────────

import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

// ══════════════════════════════════════════════════════════════════════════════
// SECCIÓN 1: COLORES
// ══════════════════════════════════════════════════════════════════════════════

/// Todos los colores de la app. Modifica aquí para rebranding instantáneo.
class AppColors {
  AppColors._();

  // ── Marca principal ────────────────────────────────────────────────────────
  /// Azul principal de la clínica — botones, AppBar, íconos activos
  static const Color primary     = Color(0xFF1A56AB);

  /// Azul oscuro — gradientes, hover states
  static const Color primaryDark = Color(0xFF1847A0);

  /// Azul muy claro — fondos de chips, avatares, indicadores
  static const Color primaryLight = Color(0xFFE0ECFF);

  // ── Fondos ─────────────────────────────────────────────────────────────────
  /// Fondo general de pantallas
  static const Color background  = Color(0xFFECF1F8);

  /// Fondo de tarjetas y superficies elevadas
  static const Color surface     = Colors.white;

  /// Fondo de campos de entrada
  static const Color inputFill   = Color(0xFFEEF3FC);

  // ── Texto ──────────────────────────────────────────────────────────────────
  /// Texto principal — títulos, valores importantes
  static const Color textDark    = Color(0xFF1A2940);

  /// Texto secundario — subtítulos, metadatos, placeholders
  static const Color textMuted   = Color(0xFF6B7280);

  /// Texto sobre fondo oscuro (AppBar, botones llenos)
  static const Color textOnPrimary = Colors.white;

  // ── Estados semánticos ─────────────────────────────────────────────────────
  /// Éxito — cita atendida, factura pagada, stock OK
  static const Color success     = Color(0xFF16A34A);
  static const Color successLight = Color(0xFFE0F5E9);

  /// Advertencia — pendiente, stock bajo
  static const Color warning     = Color(0xFFF59E0B);
  static const Color warningLight = Color(0xFFFEF3C7);

  /// Error — cancelado, stock crítico, validaciones
  static const Color error       = Color(0xFFDC2626);
  static const Color errorLight  = Color(0xFFFFE4E4);

  /// Información — notificaciones neutras
  static const Color info        = Color(0xFF2563EB);
  static const Color infoLight   = Color(0xFFDBEAFE);

  // ── Bordes y divisores ─────────────────────────────────────────────────────
  static const Color border      = Color(0xFFDDE4EF);
  static const Color divider     = Color(0xFFEEF2F8);

  // ── Sombras ────────────────────────────────────────────────────────────────
  static const Color shadow      = Color(0x12000000); // 7% opacidad
}

// ══════════════════════════════════════════════════════════════════════════════
// SECCIÓN 2: ESPACIADO
// ══════════════════════════════════════════════════════════════════════════════

/// Sistema de espaciado en múltiplos de 4px (escala 8pt grid).
/// Evita números mágicos en el código de UI.
class AppSpacing {
  AppSpacing._();

  static const double xs   = 4.0;   // Separación mínima interna
  static const double sm   = 8.0;   // Padding de chips, íconos
  static const double md   = 12.0;  // Gap entre elementos en fila
  static const double lg   = 16.0;  // Padding estándar de tarjetas
  static const double xl   = 20.0;  // Separación entre secciones
  static const double xxl  = 24.0;  // Padding de pantalla
  static const double xxxl = 32.0;  // Separación grande

  /// Padding horizontal estándar de pantallas
  static const double screenH = 16.0;

  /// Padding vertical estándar de pantallas
  static const double screenV = 20.0;

  /// Padding interno de tarjetas
  static const EdgeInsets cardPadding = EdgeInsets.all(16.0);

  /// Padding de pantalla completa
  static const EdgeInsets screenPadding = EdgeInsets.symmetric(
    horizontal: screenH,
    vertical: screenV,
  );
}

// ══════════════════════════════════════════════════════════════════════════════
// SECCIÓN 3: RADIO DE BORDES
// ══════════════════════════════════════════════════════════════════════════════

/// Radios de borde consistentes en toda la app.
class AppRadius {
  AppRadius._();

  static const double xs   = 4.0;   // Badges pequeños
  static const double sm   = 8.0;   // Chips, botones outline pequeños
  static const double md   = 12.0;  // Tarjetas estándar
  static const double lg   = 16.0;  // Bottom sheets, modales
  static const double xl   = 20.0;  // Bottom nav, drawers
  static const double pill = 100.0; // Chips de estado, FAB

  static const BorderRadius cardRadius    = BorderRadius.all(Radius.circular(md));
  static const BorderRadius buttonRadius  = BorderRadius.all(Radius.circular(sm));
  static const BorderRadius inputRadius   = BorderRadius.all(Radius.circular(sm));
  static const BorderRadius pillRadius    = BorderRadius.all(Radius.circular(pill));
  static const BorderRadius modalRadius   = BorderRadius.vertical(top: Radius.circular(lg));
}

// ══════════════════════════════════════════════════════════════════════════════
// SECCIÓN 4: TIPOGRAFÍA
// ══════════════════════════════════════════════════════════════════════════════

/// Escala tipográfica completa basada en Inter (Google Fonts).
/// Todos los tamaños y pesos en un solo lugar.
class AppTypography {
  AppTypography._();

  static TextStyle _inter({
    double fontSize = 14,
    FontWeight fontWeight = FontWeight.w400,
    Color? color,
    double? letterSpacing,
    double? height,
  }) =>
      GoogleFonts.inter(
        fontSize: fontSize,
        fontWeight: fontWeight,
        color: color,
        letterSpacing: letterSpacing,
        height: height,
      );

  // ── Display / Headline ─────────────────────────────────────────────────────
  /// 28px Bold — pantallas de bienvenida, valores grandes
  static TextStyle headline({Color? color}) =>
      _inter(fontSize: 28, fontWeight: FontWeight.w700, color: color, height: 1.2);

  /// 24px SemiBold — subtítulos principales
  static TextStyle headlineSmall({Color? color}) =>
      _inter(fontSize: 24, fontWeight: FontWeight.w600, color: color, height: 1.3);

  // ── Títulos ────────────────────────────────────────────────────────────────
  /// 20px SemiBold — AppBar, títulos de sección
  static TextStyle title({Color? color}) =>
      _inter(fontSize: 20, fontWeight: FontWeight.w600, color: color, height: 1.3);

  /// 18px SemiBold — títulos de modal, bottom sheet
  static TextStyle titleMedium({Color? color}) =>
      _inter(fontSize: 18, fontWeight: FontWeight.w600, color: color, height: 1.3);

  /// 16px SemiBold — títulos de tarjeta, nombres de pacientes
  static TextStyle titleSmall({Color? color}) =>
      _inter(fontSize: 16, fontWeight: FontWeight.w600, color: color, height: 1.4);

  // ── Cuerpo ─────────────────────────────────────────────────────────────────
  /// 14px Regular — texto general, descripciones
  static TextStyle body({Color? color}) =>
      _inter(fontSize: 14, fontWeight: FontWeight.w400, color: color, height: 1.5);

  /// 14px Medium — texto con énfasis leve
  static TextStyle bodyMedium({Color? color}) =>
      _inter(fontSize: 14, fontWeight: FontWeight.w500, color: color, height: 1.5);

  /// 14px SemiBold — texto con énfasis fuerte
  static TextStyle bodySemibold({Color? color}) =>
      _inter(fontSize: 14, fontWeight: FontWeight.w600, color: color, height: 1.5);

  // ── Labels ─────────────────────────────────────────────────────────────────
  /// 14px SemiBold — etiquetas de campos de formulario, encabezados de tabla
  static TextStyle label({Color? color}) =>
      _inter(fontSize: 14, fontWeight: FontWeight.w600, color: color);

  /// 12px SemiBold — etiquetas pequeñas, chips de estado
  static TextStyle labelSmall({Color? color}) =>
      _inter(fontSize: 12, fontWeight: FontWeight.w600, color: color);

  // ── Botones ────────────────────────────────────────────────────────────────
  /// 16px Bold — botones primarios
  static TextStyle button({Color? color}) =>
      _inter(fontSize: 16, fontWeight: FontWeight.w700, color: color, letterSpacing: 0.3);

  /// 14px SemiBold — botones secundarios / outline
  static TextStyle buttonSmall({Color? color}) =>
      _inter(fontSize: 14, fontWeight: FontWeight.w600, color: color);

  // ── Captions / Metadatos ───────────────────────────────────────────────────
  /// 12px Regular — fechas, metadatos, texto de apoyo
  static TextStyle caption({Color? color}) =>
      _inter(fontSize: 12, fontWeight: FontWeight.w400, color: color, height: 1.4);

  /// 11px Regular — texto muy pequeño, timestamps
  static TextStyle captionXs({Color? color}) =>
      _inter(fontSize: 11, fontWeight: FontWeight.w400, color: color, height: 1.4);

  /// 10px SemiBold — badges, indicadores
  static TextStyle badge({Color? color}) =>
      _inter(fontSize: 10, fontWeight: FontWeight.w600, color: color, letterSpacing: 0.3);
}

// ══════════════════════════════════════════════════════════════════════════════
// SECCIÓN 5: SOMBRAS
// ══════════════════════════════════════════════════════════════════════════════

/// Elevaciones consistentes usando BoxShadow.
class AppShadows {
  AppShadows._();

  /// Sombra sutil — tarjetas en reposo
  static const List<BoxShadow> card = [
    BoxShadow(
      color: Color(0x0F000000),
      blurRadius: 8,
      offset: Offset(0, 2),
    ),
  ];

  /// Sombra media — modales, dropdowns
  static const List<BoxShadow> modal = [
    BoxShadow(
      color: Color(0x1A000000),
      blurRadius: 20,
      offset: Offset(0, 8),
    ),
  ];

  /// Sombra de botón primario (con color de marca)
  static final List<BoxShadow> primaryButton = [
    BoxShadow(
      color: AppColors.primary.withAlpha(90),
      blurRadius: 14,
      offset: const Offset(0, 4),
    ),
  ];
}

// ══════════════════════════════════════════════════════════════════════════════
// SECCIÓN 6: GRADIENTES
// ══════════════════════════════════════════════════════════════════════════════

/// Gradientes reutilizables de la marca.
class AppGradients {
  AppGradients._();

  /// Gradiente principal — AppBar, headers de pantalla
  static const LinearGradient primary = LinearGradient(
    colors: [AppColors.primary, AppColors.primaryDark],
    begin: Alignment.centerLeft,
    end: Alignment.centerRight,
  );

  /// Gradiente vertical — fondos de login, splash
  static const LinearGradient primaryVertical = LinearGradient(
    colors: [AppColors.primary, AppColors.primaryDark],
    begin: Alignment.topCenter,
    end: Alignment.bottomCenter,
  );
}

// ══════════════════════════════════════════════════════════════════════════════
// SECCIÓN 7: TEMA MATERIAL 3
// ══════════════════════════════════════════════════════════════════════════════

/// ThemeData completo para MaterialApp.
/// Consume todos los tokens definidos arriba.
class AppTheme {
  AppTheme._();

  static ThemeData light() {
    final colorScheme = ColorScheme.fromSeed(
      seedColor: AppColors.primary,
      brightness: Brightness.light,
      primary: AppColors.primary,
      onPrimary: AppColors.textOnPrimary,
      surface: AppColors.surface,
      onSurface: AppColors.textDark,
      error: AppColors.error,
    );

    return ThemeData(
      useMaterial3: true,
      colorScheme: colorScheme,
      scaffoldBackgroundColor: AppColors.background,
      fontFamily: GoogleFonts.inter().fontFamily,

      // ── AppBar ─────────────────────────────────────────────────────────────
      appBarTheme: AppBarTheme(
        backgroundColor: AppColors.primary,
        foregroundColor: AppColors.textOnPrimary,
        elevation: 0,
        centerTitle: false,
        titleTextStyle: AppTypography.title(color: AppColors.textOnPrimary),
        iconTheme: const IconThemeData(color: AppColors.textOnPrimary, size: 22),
      ),

      // ── ElevatedButton ─────────────────────────────────────────────────────
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: AppColors.primary,
          foregroundColor: AppColors.textOnPrimary,
          textStyle: AppTypography.button(),
          shape: const RoundedRectangleBorder(borderRadius: AppRadius.buttonRadius),
          minimumSize: const Size(double.infinity, 52),
          elevation: 0,
        ),
      ),

      // ── OutlinedButton ─────────────────────────────────────────────────────
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          foregroundColor: AppColors.primary,
          textStyle: AppTypography.buttonSmall(),
          side: const BorderSide(color: AppColors.primary, width: 1.5),
          shape: const RoundedRectangleBorder(borderRadius: AppRadius.buttonRadius),
          minimumSize: const Size(double.infinity, 48),
        ),
      ),

      // ── TextButton ─────────────────────────────────────────────────────────
      textButtonTheme: TextButtonThemeData(
        style: TextButton.styleFrom(
          foregroundColor: AppColors.primary,
          textStyle: AppTypography.buttonSmall(),
        ),
      ),

      // ── InputDecoration ────────────────────────────────────────────────────
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: AppColors.inputFill,
        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
        border: OutlineInputBorder(
          borderRadius: AppRadius.inputRadius,
          borderSide: const BorderSide(color: AppColors.border, width: 1.2),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: AppRadius.inputRadius,
          borderSide: const BorderSide(color: AppColors.border, width: 1.2),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: AppRadius.inputRadius,
          borderSide: const BorderSide(color: AppColors.primary, width: 1.8),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: AppRadius.inputRadius,
          borderSide: const BorderSide(color: AppColors.error, width: 1.5),
        ),
        labelStyle: AppTypography.caption(color: AppColors.textMuted),
        hintStyle: AppTypography.body(color: AppColors.textMuted),
      ),

      // ── Card ────────────────────────────────────────────────────────────────
      cardTheme: CardThemeData(
        color: AppColors.surface,
        elevation: 0,
        shape: const RoundedRectangleBorder(borderRadius: AppRadius.cardRadius),
        margin: const EdgeInsets.symmetric(vertical: 4),
      ),

      // ── BottomNavigationBar ────────────────────────────────────────────────
      bottomNavigationBarTheme: BottomNavigationBarThemeData(
        backgroundColor: AppColors.surface,
        selectedItemColor: AppColors.primary,
        unselectedItemColor: AppColors.textMuted,
        selectedLabelStyle: AppTypography.captionXs(),
        unselectedLabelStyle: AppTypography.captionXs(),
        type: BottomNavigationBarType.fixed,
        elevation: 8,
      ),

      // ── Chip ────────────────────────────────────────────────────────────────
      chipTheme: ChipThemeData(
        backgroundColor: AppColors.inputFill,
        selectedColor: AppColors.primaryLight,
        labelStyle: AppTypography.labelSmall(color: AppColors.textDark),
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
        shape: const StadiumBorder(),
      ),

      // ── Divider ─────────────────────────────────────────────────────────────
      dividerTheme: const DividerThemeData(
        color: AppColors.divider,
        thickness: 1,
        space: 1,
      ),

      // ── FloatingActionButton ────────────────────────────────────────────────
      floatingActionButtonTheme: FloatingActionButtonThemeData(
        backgroundColor: AppColors.primary,
        foregroundColor: AppColors.textOnPrimary,
        elevation: 4,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      ),
    );
  }
}
