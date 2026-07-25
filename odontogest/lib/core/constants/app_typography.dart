import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

class AppTypography {
  AppTypography._();

  static TextStyle _inter({
    double fontSize = 14,
    FontWeight fontWeight = FontWeight.w400,
    Color? color,
    double? letterSpacing,
    FontStyle fontStyle = FontStyle.normal,
  }) =>
      GoogleFonts.inter(
        fontSize: fontSize,
        fontWeight: fontWeight,
        fontStyle: fontStyle,
        color: color,
        letterSpacing: letterSpacing,
      );

  // Display
  static TextStyle headline({Color? color}) =>
      _inter(fontSize: 28, fontWeight: FontWeight.w700, color: color);

  // Títulos
  static TextStyle title({Color? color}) =>
      _inter(fontSize: 20, fontWeight: FontWeight.w600, color: color);

  static TextStyle titleSmall({Color? color}) =>
      _inter(fontSize: 16, fontWeight: FontWeight.w600, color: color);

  // Cuerpo
  static TextStyle body({Color? color}) =>
      _inter(fontSize: 14, fontWeight: FontWeight.w400, color: color);

  static TextStyle bodyMedium({Color? color}) =>
      _inter(fontSize: 14, fontWeight: FontWeight.w500, color: color);

  // Labels
  static TextStyle label({Color? color}) =>
      _inter(fontSize: 14, fontWeight: FontWeight.w600, color: color);

  // Botones
  static TextStyle button({Color? color}) =>
      _inter(fontSize: 16, fontWeight: FontWeight.w700,
          color: color, letterSpacing: 0.5);

  // Caption
  static TextStyle caption({Color? color}) =>
      _inter(fontSize: 12, fontWeight: FontWeight.w400, color: color);
}
