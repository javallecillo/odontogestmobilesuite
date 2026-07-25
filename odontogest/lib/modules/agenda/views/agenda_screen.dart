import 'package:flutter/material.dart';
import '../../../core/constants/app_theme.dart';
import '../../../data/services/agenda_service.dart';
import '../../expedientes/views/buscar_paciente_screen.dart';

class AgendaScreen extends StatefulWidget {
  const AgendaScreen({super.key});
  @override
  State<AgendaScreen> createState() => _AgendaScreenState();
}

class _AgendaScreenState extends State<AgendaScreen> {
  static const _primary      = AppColors.primary;
  static const _primaryLight = AppColors.primaryLight;

  DateTime         _fecha   = DateTime.now();
  String           _filtro  = 'all';
  List<CitaAgenda> _citas   = [];
  bool             _loading = true;
  bool             _saving  = false;

  @override
  void initState() { super.initState(); _cargar(); }

  Future<void> _cargar() async {
    setState(() => _loading = true);
    final f = _fecha.toIso8601String().substring(0, 10);
    final data = await AgendaService.listar(fecha: f, estado: _filtro);
    if (mounted) setState(() { _citas = data; _loading = false; });
  }

  Future<void> _pickFecha() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _fecha,
      firstDate: DateTime(2020),
      lastDate: DateTime(2030),
      builder: (c, child) => Theme(
        data: Theme.of(c).copyWith(colorScheme: const ColorScheme.light(primary: _primary)),
        child: child!,
      ),
    );
    if (picked != null && picked != _fecha) {
      setState(() => _fecha = picked);
      _cargar();
    }
  }

  Future<void> _accionEstado(CitaAgenda cita) async {
    final opts = <String, String>{};
    if (cita.estado != 'confirmada')     opts['Confirmar']   = 'confirmada';
    if (cita.estado != 'completada')     opts['Completar']   = 'completada';
    if (cita.estado != 'cancelada')      opts['Cancelar']    = 'cancelada';
    if (cita.asistencia == 'pendiente')  opts['Asistió']     = '__asistio';
    if (cita.asistencia != 'no_asistio') opts['No asistió']  = '__noasistio';

    final sel = await showModalBottomSheet<String>(
      context: context,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (_) => Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const SizedBox(height: 12),
          Container(width: 40, height: 4, decoration: BoxDecoration(color: Colors.grey[300], borderRadius: BorderRadius.circular(2))),
          const Padding(
            padding: EdgeInsets.all(16),
            child: Text('Cambiar estado', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
          ),
          ...opts.entries.map((e) => ListTile(
            title: Text(e.key),
            leading: const Icon(Icons.edit_note, color: _primary),
            onTap: () => Navigator.pop(context, e.value),
          )),
          const SizedBox(height: 20),
        ],
      ),
    );

    if (sel == null || !mounted) return;
    setState(() => _saving = true);

    bool ok;
    if (sel == '__asistio') {
      ok = await AgendaService.cambiarEstado(cita.idCita, cita.estado, asistencia: 'asistio');
    } else if (sel == '__noasistio') {
      ok = await AgendaService.cambiarEstado(cita.idCita, cita.estado, asistencia: 'no_asistio');
    } else {
      ok = await AgendaService.cambiarEstado(cita.idCita, sel);
    }

    if (mounted) {
      setState(() => _saving = false);
      if (ok) {
        _cargar();
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Error al cambiar estado'), backgroundColor: Colors.red),
        );
      }
    }
  }

  String _formatFecha() {
    const m = ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
                'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
    return '${_fecha.day} de ${m[_fecha.month - 1]} ${_fecha.year}';
  }

  Color _colorEstado(String e) => switch(e) {
    'confirmada'  => Colors.blue,
    'completada'  => _primary,
    'cancelada'   => Colors.red,
    'no_asistio'  => Colors.orange,
    _             => Colors.grey,
  };

  String _labelEstado(String e) => switch(e) {
    'pendiente'   => 'Pendiente',
    'confirmada'  => 'Confirmada',
    'completada'  => 'Completada',
    'cancelada'   => 'Cancelada',
    'no_asistio'  => 'No asistió',
    _             => e,
  };

  // Etiqueta corta para los chips de filtro
  String _chipLabel(String f) => switch(f) {
    'all'         => 'Todas',
    'pendiente'   => 'Pend.',
    'confirmada'  => 'Confirm.',
    'completada'  => 'Complet.',
    'cancelada'   => 'Cancel.',
    _             => f,
  };

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF5F5F5),
      appBar: AppBar(
        title: const Text('Agenda', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
        backgroundColor: _primary,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.calendar_month, color: Colors.white),
            tooltip: 'Cambiar fecha',
            onPressed: _pickFecha,
          ),
        ],
      ),
      body: Column(
        children: [
          // Cabecera fecha + filtros
          Container(
            color: _primary,
            padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                GestureDetector(
                  onTap: _pickFecha,
                  child: Row(children: [
                    Text(_formatFecha(), style: const TextStyle(color: Colors.white70, fontSize: 14)),
                    const Icon(Icons.arrow_drop_down, color: Colors.white70),
                  ]),
                ),
                const SizedBox(height: 10),
                Wrap(
                  spacing: 6,
                  runSpacing: 6,
                  children: ['all','pendiente','confirmada','completada','cancelada'].map((f) {
                    final sel = _filtro == f;
                    return GestureDetector(
                      onTap: () { setState(() => _filtro = f); _cargar(); },
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 11, vertical: 5),
                        decoration: BoxDecoration(
                          color:  sel ? _primaryLight : Colors.transparent,
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(
                            color: sel ? _primaryLight : Colors.white60,
                            width: 1.2,
                          ),
                        ),
                        child: Text(
                          _chipLabel(f),
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.w600,
                            color: sel ? Colors.black87 : Colors.white,
                          ),
                        ),
                      ),
                    );
                  }).toList(),
                ),
              ],
            ),
          ),

          if (_saving)
            const LinearProgressIndicator(color: _primaryLight, backgroundColor: Colors.transparent),

          Expanded(
            child: RefreshIndicator(
              color: _primary,
              onRefresh: _cargar,
              child: _loading
                ? const Center(child: CircularProgressIndicator(color: _primary))
                : _citas.isEmpty
                  ? ListView(children: [
                      SizedBox(
                        height: MediaQuery.of(context).size.height * 0.55,
                        child: const Center(child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.event_busy, size: 64, color: Colors.grey),
                            SizedBox(height: 12),
                            Text('Sin citas para esta fecha', style: TextStyle(color: Colors.grey, fontSize: 15)),
                          ],
                        )),
                      ),
                    ])
                  : ListView.builder(
                      padding: const EdgeInsets.all(12),
                      itemCount: _citas.length,
                      itemBuilder: (_, i) {
                        final c = _citas[i];
                        return _CitaCard(
                          cita: c,
                          colorEstado: _colorEstado(c.estado),
                          labelEstado: _labelEstado(c.estado),
                          onAccion: () => _accionEstado(c),
                          onExpediente: () => Navigator.push(context,
                            MaterialPageRoute(builder: (_) => const BuscarPacienteScreen())),
                        );
                      },
                    ),
            ),
          ),
        ],
      ),
    );
  }
}

// ── Card de cita ─────────────────────────────────────────────────────────────
class _CitaCard extends StatelessWidget {
  final CitaAgenda cita;
  final Color      colorEstado;
  final String     labelEstado;
  final VoidCallback onAccion;
  final VoidCallback onExpediente;

  const _CitaCard({
    required this.cita, required this.colorEstado, required this.labelEstado,
    required this.onAccion, required this.onExpediente,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.only(bottom: 10),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      elevation: 2,
      child: IntrinsicHeight(
        child: Row(
          children: [
            Container(
              width: 6,
              decoration: BoxDecoration(
                color: colorEstado,
                borderRadius: const BorderRadius.only(
                  topLeft: Radius.circular(12), bottomLeft: Radius.circular(12)),
              ),
            ),
            Expanded(
              child: Padding(
                padding: const EdgeInsets.all(12),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(children: [
                      const Icon(Icons.access_time, size: 14, color: Colors.grey),
                      const SizedBox(width: 4),
                      Text(cita.hora.length >= 5 ? cita.hora.substring(0, 5) : cita.hora,
                           style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                      const Spacer(),
                      Chip(
                        label: Text(labelEstado, style: const TextStyle(fontSize: 11)),
                        backgroundColor: colorEstado.withOpacity(0.15),
                        side: BorderSide(color: colorEstado.withOpacity(0.4)),
                        padding: EdgeInsets.zero,
                        visualDensity: VisualDensity.compact,
                      ),
                    ]),
                    const SizedBox(height: 6),
                    Text(cita.paciente,   style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
                    Text(cita.servicio,   style: const TextStyle(color: Colors.grey, fontSize: 13)),
                    Text('Dr. ${cita.odontologo}', style: const TextStyle(color: Colors.grey, fontSize: 12)),
                    const SizedBox(height: 8),
                    Row(children: [
                      TextButton.icon(
                        onPressed: onExpediente,
                        icon: const Icon(Icons.folder_open, size: 16, color: AppColors.primary),
                        label: const Text('Expediente', style: TextStyle(color: AppColors.primary, fontSize: 12)),
                        style: TextButton.styleFrom(
                          padding: EdgeInsets.zero, minimumSize: Size.zero,
                          tapTargetSize: MaterialTapTargetSize.shrinkWrap),
                      ),
                      const Spacer(),
                      TextButton(
                        onPressed: onAccion,
                        child: const Text('Cambiar estado', style: TextStyle(fontSize: 12)),
                      ),
                    ]),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
