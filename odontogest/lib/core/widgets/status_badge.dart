// Badge de estado reutilizable (Atendida, Pendiente, Pagada, etc.)
import 'package:flutter/material.dart';
import '../constants/app_theme.dart';

class StatusBadge extends StatelessWidget {
  const StatusBadge({
    super.key,
    required this.label,
    required this.color,
    this.fontSize = 10,
  });

  final String label;
  final Color color;
  final double fontSize;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: color.withAlpha(30),
        borderRadius: AppRadius.pillRadius,
      ),
      child: Text(
        label,
        style: AppTypography.badge(color: color),
      ),
    );
  }
}
