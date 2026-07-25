import 'package:flutter/material.dart';
import '../../../core/constants/app_theme.dart';
import '../../../data/services/notificaciones_service.dart';

class NotificacionesScreen extends StatefulWidget {
  const NotificacionesScreen({super.key});
  @override
  State<NotificacionesScreen> createState() => _NotificacionesScreenState();
}

class _NotificacionesScreenState extends State<NotificacionesScreen> {
  List<Notificacion> _lista    = [];
  int                _noLeidas = 0;
  bool               _loading  = true;

  @override
  void initState() { super.initState(); _cargar(); }

  Future<void> _cargar() async {
    setState(() => _loading = true);
    final r = await NotificacionesService.listar();
    if (mounted) setState(() {
      _lista    = r.lista;
      _noLeidas = r.noLeidas;
      _loading  = false;
    });
  }

  Future<void> _marcarLeida(Notificacion n) async {
    if (n.leida) return;
    await NotificacionesService.marcarLeida(n.id);
    setState(() {
      final i = _lista.indexWhere((x) => x.id == n.id);
      if (i >= 0) {
        _lista[i] = Notificacion(
          id: n.id, titulo: n.titulo, mensaje: n.mensaje,
          leida: true, fecha: n.fecha,
        );
        if (_noLeidas > 0) _noLeidas--;
      }
    });
  }

  Future<void> _marcarTodas() async {
    await NotificacionesService.marcarTodasLeidas();
    setState(() {
      _lista = _lista.map((n) => Notificacion(
        id: n.id, titulo: n.titulo, mensaje: n.mensaje,
        leida: true, fecha: n.fecha,
      )).toList();
      _noLeidas = 0;
    });
  }

  String _formatFecha(String fecha) {
    if (fecha.isEmpty) return '';
    try {
      final dt = DateTime.parse(fecha);
      final now = DateTime.now();
      final diff = now.difference(dt);
      if (diff.inMinutes < 60) return 'Hace ${diff.inMinutes} min';
      if (diff.inHours < 24)   return 'Hace ${diff.inHours} h';
      if (diff.inDays == 1)    return 'Ayer';
      return '${dt.day}/${dt.month}/${dt.year}';
    } catch (_) { return fecha; }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Row(children: [
          const Text('Notificaciones',
              style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
          if (_noLeidas > 0) ...[
            const SizedBox(width: 8),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
              decoration: BoxDecoration(
                color: Colors.red, borderRadius: BorderRadius.circular(12)),
              child: Text('$_noLeidas',
                  style: const TextStyle(color: Colors.white, fontSize: 11,
                      fontWeight: FontWeight.bold)),
            ),
          ],
        ]),
        backgroundColor: AppColors.primary,
        elevation: 0,
        iconTheme: const IconThemeData(color: Colors.white),
        actions: [
          if (_noLeidas > 0)
            TextButton(
              onPressed: _marcarTodas,
              child: const Text('Leer todas',
                  style: TextStyle(color: Colors.white70, fontSize: 13)),
            ),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
          : _lista.isEmpty
              ? _buildVacia()
              : RefreshIndicator(
                  color: AppColors.primary,
                  onRefresh: _cargar,
                  child: ListView.separated(
                    padding: const EdgeInsets.symmetric(vertical: 8),
                    itemCount: _lista.length,
                    separatorBuilder: (_, __) =>
                        const Divider(height: 1, indent: 56),
                    itemBuilder: (_, i) => _NotifTile(
                      n: _lista[i],
                      formatFecha: _formatFecha,
                      onTap: () => _marcarLeida(_lista[i]),
                    ),
                  ),
                ),
    );
  }

  Widget _buildVacia() {
    return ListView(children: [
      SizedBox(
        height: MediaQuery.of(context).size.height * 0.6,
        child: const Center(
          child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
            Icon(Icons.notifications_none, size: 64, color: Colors.grey),
            SizedBox(height: 12),
            Text('Sin notificaciones', style: TextStyle(color: Colors.grey, fontSize: 15)),
            SizedBox(height: 6),
            Text('Las alertas de citas aparecerán aquí',
                style: TextStyle(color: Colors.grey, fontSize: 12)),
          ]),
        ),
      ),
    ]);
  }
}

class _NotifTile extends StatelessWidget {
  const _NotifTile({required this.n, required this.formatFecha, required this.onTap});
  final Notificacion n;
  final String Function(String) formatFecha;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final leida = n.leida;
    return InkWell(
      onTap: onTap,
      child: Container(
        color: leida ? Colors.transparent : AppColors.primary.withOpacity(0.05),
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Ícono
            Container(
              width: 40, height: 40,
              decoration: BoxDecoration(
                color: leida
                    ? AppColors.border
                    : AppColors.primary.withOpacity(0.12),
                shape: BoxShape.circle,
              ),
              child: Icon(
                _iconTitulo(n.titulo),
                size: 20,
                color: leida ? AppColors.textMuted : AppColors.primary,
              ),
            ),
            const SizedBox(width: 12),
            // Contenido
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(children: [
                    Expanded(
                      child: Text(n.titulo,
                          style: TextStyle(
                            fontWeight: leida ? FontWeight.normal : FontWeight.bold,
                            fontSize: 13,
                            color: leida ? AppColors.textMuted : AppColors.textDark,
                          )),
                    ),
                    Text(formatFecha(n.fecha),
                        style: const TextStyle(fontSize: 11, color: AppColors.textMuted)),
                  ]),
                  const SizedBox(height: 3),
                  Text(n.mensaje,
                      style: TextStyle(
                        fontSize: 12,
                        color: leida ? AppColors.textMuted : AppColors.textDark,
                      )),
                ],
              ),
            ),
            // Punto no leído
            if (!leida)
              const Padding(
                padding: EdgeInsets.only(left: 8, top: 4),
                child: CircleAvatar(radius: 4, backgroundColor: AppColors.primaryLight),
              ),
          ],
        ),
      ),
    );
  }

  IconData _iconTitulo(String t) {
    final lower = t.toLowerCase();
    if (lower.contains('cita') || lower.contains('hoy') || lower.contains('mañana'))
      return Icons.event_note;
    if (lower.contains('stock') || lower.contains('inventario'))
      return Icons.inventory_2_outlined;
    return Icons.notifications_outlined;
  }
}
