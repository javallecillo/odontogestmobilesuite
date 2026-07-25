// Tarjeta base reutilizable en toda la app.
// Todos los módulos la importan en lugar de definir BoxDecoration repetido.
import 'package:flutter/material.dart';
import '../constants/app_theme.dart';

class AppCard extends StatelessWidget {
  const AppCard({
    super.key,
    required this.child,
    this.padding,
    this.margin,
    this.color,
    this.borderRadius,
    this.border,
    this.onTap,
  });

  final Widget child;
  final EdgeInsetsGeometry? padding;
  final EdgeInsetsGeometry? margin;
  final Color? color;
  final BorderRadiusGeometry? borderRadius;
  final BoxBorder? border;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    final content = Container(
      margin: margin ?? const EdgeInsets.only(bottom: 8),
      padding: padding ?? AppSpacing.cardPadding,
      decoration: BoxDecoration(
        color: color ?? AppColors.surface,
        borderRadius: borderRadius ?? AppRadius.cardRadius,
        border: border,
        boxShadow: AppShadows.card,
      ),
      child: child,
    );
    if (onTap != null) {
      return GestureDetector(onTap: onTap, child: content);
    }
    return content;
  }
}
