// AppBar con gradiente de la marca.
// Reemplaza el AppBar estándar en todas las pantallas de módulos.
import 'package:flutter/material.dart';
import '../constants/app_theme.dart';

class GradientAppBar extends StatelessWidget implements PreferredSizeWidget {
  const GradientAppBar({
    super.key,
    required this.title,
    this.actions,
    this.leading,
    this.bottom,
    this.centerTitle = false,
  });

  final String title;
  final List<Widget>? actions;
  final Widget? leading;
  final PreferredSizeWidget? bottom;
  final bool centerTitle;

  @override
  Size get preferredSize => Size.fromHeight(
    kToolbarHeight + (bottom?.preferredSize.height ?? 0),
  );

  @override
  Widget build(BuildContext context) {
    return AppBar(
      flexibleSpace: Container(
        decoration: const BoxDecoration(gradient: AppGradients.primary),
      ),
      backgroundColor: Colors.transparent,
      elevation: 0,
      centerTitle: centerTitle,
      leading: leading,
      title: Text(title, style: AppTypography.title(color: Colors.white)),
      actions: actions,
      bottom: bottom,
      iconTheme: const IconThemeData(color: Colors.white),
    );
  }
}
