// ── OgModal — componentes reutilizables de modales OdontoGest ────────────────
// Usar en toda la app para mantener consistencia visual.
//
//  · OgBottomSheet.show(...)   → bottom sheet con header degradado
//  · OgActionItem              → ítem de acción con ícono de color
//  · OgDialog.confirm(...)     → diálogo de confirmación estilizado
//  · OgDialog.input(...)       → diálogo con campo de texto estilizado

import 'package:flutter/material.dart';
import '../constants/app_theme.dart';

// ══════════════════════════════════════════════════════════════════════════════
// BOTTOM SHEET
// ══════════════════════════════════════════════════════════════════════════════

class OgBottomSheet {
  /// Muestra un bottom sheet con header de degradado OdontoGest.
  ///
  /// [title]   — texto del encabezado
  /// [icon]    — ícono del encabezado (opcional)
  /// [items]   — lista de [OgActionItem]
  /// [onTap]   — callback con el [value] del ítem seleccionado
  static Future<T?> show<T>({
    required BuildContext context,
    required String title,
    IconData icon = Icons.tune_rounded,
    required List<OgActionItem> items,
  }) {
    return showModalBottomSheet<T>(
      context: context,
      backgroundColor: Colors.transparent,
      isScrollControlled: true,
      builder: (_) => _OgBottomSheetContent<T>(
        title: title,
        icon: icon,
        items: items,
      ),
    );
  }

  /// Bottom sheet libre: el caller construye el contenido completo.
  static Future<T?> showCustom<T>({
    required BuildContext context,
    required String title,
    IconData icon = Icons.tune_rounded,
    required Widget body,
    bool scrollable = true,
  }) {
    return showModalBottomSheet<T>(
      context: context,
      backgroundColor: Colors.transparent,
      isScrollControlled: scrollable,
      builder: (_) => _OgBottomSheetShell(
        title: title,
        icon: icon,
        body: body,
      ),
    );
  }
}

class _OgBottomSheetContent<T> extends StatelessWidget {
  const _OgBottomSheetContent({
    required this.title,
    required this.icon,
    required this.items,
  });

  final String            title;
  final IconData          icon;
  final List<OgActionItem> items;

  @override
  Widget build(BuildContext context) {
    return _OgBottomSheetShell(
      title: title,
      icon: icon,
      body: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          ...items.map((item) => _ActionTile<T>(item: item)),
          const SizedBox(height: 8),
        ],
      ),
    );
  }
}

class _OgBottomSheetShell extends StatelessWidget {
  const _OgBottomSheetShell({
    required this.title,
    required this.icon,
    required this.body,
  });

  final String   title;
  final IconData icon;
  final Widget   body;

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          // ── Drag handle ───────────────────────────────────────────
          const SizedBox(height: 12),
          Center(
            child: Container(
              width: 40, height: 4,
              decoration: BoxDecoration(
                color: AppColors.border,
                borderRadius: BorderRadius.circular(2),
              ),
            ),
          ),
          const SizedBox(height: 4),

          // ── Header con degradado ──────────────────────────────────
          Container(
            margin: const EdgeInsets.fromLTRB(16, 10, 16, 4),
            padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 14),
            decoration: BoxDecoration(
              gradient: AppGradients.primary,
              borderRadius: BorderRadius.circular(16),
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: Colors.white.withAlpha(38),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Icon(icon, color: Colors.white, size: 20),
                ),
                const SizedBox(width: 12),
                Text(
                  title,
                  style: AppTypography.titleSmall(color: Colors.white),
                ),
              ],
            ),
          ),

          const SizedBox(height: 4),

          // ── Contenido ─────────────────────────────────────────────
          body,
        ],
      ),
    );
  }
}

// ── Ítem de acción ────────────────────────────────────────────────────────────

class OgActionItem {
  const OgActionItem({
    required this.label,
    required this.icon,
    required this.color,
    required this.value,
    this.subtitle,
    this.destructive = false,
  });

  final String  label;
  final String? subtitle;
  final IconData icon;
  final Color   color;
  final dynamic value;
  final bool    destructive;
}

class _ActionTile<T> extends StatelessWidget {
  const _ActionTile({required this.item});
  final OgActionItem item;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 3),
      child: Material(
        color: item.color.withAlpha(15),
        borderRadius: BorderRadius.circular(14),
        child: InkWell(
          borderRadius: BorderRadius.circular(14),
          onTap: () => Navigator.pop(context, item.value as T),
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 13),
            child: Row(
              children: [
                Container(
                  width: 40, height: 40,
                  decoration: BoxDecoration(
                    color: item.color.withAlpha(30),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Icon(item.icon, color: item.color, size: 20),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(item.label,
                          style: AppTypography.bodyMedium(color: AppColors.textDark)),
                      if (item.subtitle != null)
                        Text(item.subtitle!,
                            style: AppTypography.captionXs(color: AppColors.textMuted)),
                    ],
                  ),
                ),
                Icon(Icons.arrow_forward_ios_rounded,
                    size: 14, color: item.color.withAlpha(180)),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

// ══════════════════════════════════════════════════════════════════════════════
// DIALOGS
// ══════════════════════════════════════════════════════════════════════════════

class OgDialog {
  /// Diálogo de confirmación con botón destructivo opcional.
  static Future<bool> confirm({
    required BuildContext context,
    required String title,
    required String message,
    String confirmLabel  = 'Confirmar',
    String cancelLabel   = 'Cancelar',
    IconData icon        = Icons.help_outline_rounded,
    Color confirmColor   = AppColors.primary,
    bool  destructive    = false,
  }) async {
    final result = await showDialog<bool>(
      context: context,
      builder: (_) => _OgConfirmDialog(
        title:        title,
        message:      message,
        confirmLabel: confirmLabel,
        cancelLabel:  cancelLabel,
        icon:         icon,
        confirmColor: destructive ? AppColors.error : confirmColor,
      ),
    );
    return result ?? false;
  }

  /// Diálogo con un campo de texto.
  /// Devuelve el texto ingresado o null si se canceló.
  static Future<String?> input({
    required BuildContext   context,
    required String         title,
    String?                 hint,
    String?                 initialValue,
    String confirmLabel   = 'Aceptar',
    String cancelLabel    = 'Cancelar',
    IconData icon         = Icons.edit_outlined,
    int maxLines          = 1,
  }) async {
    return showDialog<String>(
      context: context,
      builder: (_) => _OgInputDialog(
        title:        title,
        hint:         hint,
        initialValue: initialValue,
        confirmLabel: confirmLabel,
        cancelLabel:  cancelLabel,
        icon:         icon,
        maxLines:     maxLines,
      ),
    );
  }
}

// ── Confirm dialog ─────────────────────────────────────────────────────────

class _OgConfirmDialog extends StatelessWidget {
  const _OgConfirmDialog({
    required this.title,
    required this.message,
    required this.confirmLabel,
    required this.cancelLabel,
    required this.icon,
    required this.confirmColor,
  });

  final String   title, message, confirmLabel, cancelLabel;
  final IconData icon;
  final Color    confirmColor;

  @override
  Widget build(BuildContext context) {
    return Dialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
      backgroundColor: AppColors.surface,
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          // Header
          Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(vertical: 24),
            decoration: const BoxDecoration(
              gradient: AppGradients.primary,
              borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
            ),
            child: Column(
              children: [
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.white.withAlpha(38),
                    shape: BoxShape.circle,
                  ),
                  child: Icon(icon, color: Colors.white, size: 28),
                ),
                const SizedBox(height: 10),
                Text(title,
                    style: AppTypography.titleSmall(color: Colors.white),
                    textAlign: TextAlign.center),
              ],
            ),
          ),

          // Cuerpo
          Padding(
            padding: const EdgeInsets.fromLTRB(20, 20, 20, 8),
            child: Text(
              message,
              style: AppTypography.body(color: AppColors.textMuted),
              textAlign: TextAlign.center,
            ),
          ),

          // Botones
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 20),
            child: Row(
              children: [
                Expanded(
                  child: OutlinedButton(
                    onPressed: () => Navigator.pop(context, false),
                    style: OutlinedButton.styleFrom(
                      side: const BorderSide(color: AppColors.border),
                      shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12)),
                      padding: const EdgeInsets.symmetric(vertical: 13),
                    ),
                    child: Text(cancelLabel,
                        style: AppTypography.buttonSmall(
                            color: AppColors.textMuted)),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: ElevatedButton(
                    onPressed: () => Navigator.pop(context, true),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: confirmColor,
                      shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12)),
                      padding: const EdgeInsets.symmetric(vertical: 13),
                      elevation: 0,
                    ),
                    child: Text(confirmLabel,
                        style: AppTypography.buttonSmall(
                            color: Colors.white)),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

// ── Input dialog ───────────────────────────────────────────────────────────

class _OgInputDialog extends StatefulWidget {
  const _OgInputDialog({
    required this.title,
    this.hint,
    this.initialValue,
    required this.confirmLabel,
    required this.cancelLabel,
    required this.icon,
    required this.maxLines,
  });

  final String   title, confirmLabel, cancelLabel;
  final String?  hint, initialValue;
  final IconData icon;
  final int      maxLines;

  @override
  State<_OgInputDialog> createState() => _OgInputDialogState();
}

class _OgInputDialogState extends State<_OgInputDialog> {
  late final TextEditingController _ctrl;

  @override
  void initState() {
    super.initState();
    _ctrl = TextEditingController(text: widget.initialValue ?? '');
  }

  @override
  void dispose() { _ctrl.dispose(); super.dispose(); }

  @override
  Widget build(BuildContext context) {
    return Dialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
      backgroundColor: AppColors.surface,
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          // Header
          Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(vertical: 20, horizontal: 16),
            decoration: const BoxDecoration(
              gradient: AppGradients.primary,
              borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: Colors.white.withAlpha(38),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Icon(widget.icon, color: Colors.white, size: 20),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Text(widget.title,
                      style: AppTypography.titleSmall(color: Colors.white)),
                ),
              ],
            ),
          ),

          // Campo
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 20, 16, 8),
            child: TextField(
              controller: _ctrl,
              autofocus:  true,
              maxLines:   widget.maxLines,
              style:      AppTypography.body(color: AppColors.textDark),
              decoration: InputDecoration(
                hintText: widget.hint,
                hintStyle: AppTypography.body(color: AppColors.textMuted),
                filled:     true,
                fillColor:  AppColors.background,
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: const BorderSide(color: AppColors.border)),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: const BorderSide(color: AppColors.border)),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: const BorderSide(
                      color: AppColors.primary, width: 1.5)),
                contentPadding: const EdgeInsets.symmetric(
                    horizontal: 14, vertical: 12),
              ),
            ),
          ),

          // Botones
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 8, 16, 20),
            child: Row(
              children: [
                Expanded(
                  child: OutlinedButton(
                    onPressed: () => Navigator.pop(context),
                    style: OutlinedButton.styleFrom(
                      side: const BorderSide(color: AppColors.border),
                      shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12)),
                      padding: const EdgeInsets.symmetric(vertical: 13),
                    ),
                    child: Text(widget.cancelLabel,
                        style: AppTypography.buttonSmall(
                            color: AppColors.textMuted)),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: ElevatedButton(
                    onPressed: () =>
                        Navigator.pop(context, _ctrl.text.trim()),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.primary,
                      shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12)),
                      padding: const EdgeInsets.symmetric(vertical: 13),
                      elevation: 0,
                    ),
                    child: Text(widget.confirmLabel,
                        style: AppTypography.buttonSmall(
                            color: Colors.white)),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
