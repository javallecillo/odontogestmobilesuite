// NuevaCitaScreen — cita conectada a la API real.
// Flujo: odontólogo → fecha → slot → paciente + notas → confirmar.
import 'package:flutter/material.dart';
import '../../../core/constants/app_theme.dart';
import '../../../core/widgets/gradient_app_bar.dart';
import '../../../data/services/agenda_service.dart';
import '../../../data/services/expediente_service.dart';

class NuevaCitaScreen extends StatefulWidget {
  const NuevaCitaScreen({super.key});

  @override
  State<NuevaCitaScreen> createState() => _NuevaCitaScreenState();
}

class _NuevaCitaScreenState extends State<NuevaCitaScreen> {
  bool    _guardando = false;
  String? _error;

  // Step 0: Odontólogo
  bool   _loadingOd = true;
  List<Map<String, dynamic>> _odontologos = [];
  Map<String, dynamic>? _odSelec;

  // Step 1: Fecha
  DateTime _fecha = DateTime.now();

  // Step 2: Slot
  bool   _loadingSlots = false;
  List<Map<String, dynamic>> _slots = [];
  String? _horaSelec;

  // Step 3: Paciente
  bool   _loadingPac = true;
  List<BusquedaPaciente> _todos     = [];
  List<BusquedaPaciente> _filtrados = [];
  BusquedaPaciente? _pacSelec;
  final _buscarCtrl = TextEditingController();
  final _notasCtrl  = TextEditingController();

  int _paso = 0;

  @override
  void initState() {
    super.initState();
    _cargarOdontologos();
    _cargarPacientes();
    _buscarCtrl.addListener(() => _filtrar(_buscarCtrl.text));
  }

  @override
  void dispose() {
    _buscarCtrl.dispose();
    _notasCtrl.dispose();
    super.dispose();
  }

  Future<void> _cargarOdontologos() async {
    setState(() => _loadingOd = true);
    final list = await AgendaService.listarOdontologos();
    if (!mounted) return;
    setState(() { _odontologos = list; _loadingOd = false; });
  }

  Future<void> _cargarPacientes() async {
    setState(() => _loadingPac = true);
    final list = await ExpedienteService.listarTodos();
    if (!mounted) return;
    setState(() { _todos = list; _filtrados = list; _loadingPac = false; });
  }

  Future<void> _cargarSlots() async {
    if (_odSelec == null) return;
    setState(() { _loadingSlots = true; _horaSelec = null; _slots = []; });
    final fechaStr = _fecha.toIso8601String().substring(0, 10);
    final list = await AgendaService.slotsDisponibles(
        (_odSelec!['id_odontologo'] as num).toInt(), fechaStr);
    if (!mounted) return;
    setState(() { _slots = list; _loadingSlots = false; });
  }

  void _filtrar(String q) {
    final lo = q.toLowerCase();
    setState(() {
      _filtrados = q.isEmpty
          ? _todos
          : _todos.where((p) =>
              p.nombre.toLowerCase().contains(lo) ||
              (p.numExpediente ?? '').toLowerCase().contains(lo) ||
              (p.telefono ?? '').contains(lo)).toList();
    });
  }

  Future<void> _guardar() async {
    if (_odSelec == null || _horaSelec == null || _pacSelec == null) return;
    setState(() { _guardando = true; _error = null; });
    final fechaCita =
        '${_fecha.toIso8601String().substring(0, 10)} $_horaSelec';
    final res = await AgendaService.crearCita(
      idPaciente:   _pacSelec!.idPaciente,
      idOdontologo: (_odSelec!['id_odontologo'] as num).toInt(),
      fechaCita:    fechaCita,
      notas: _notasCtrl.text.trim().isEmpty ? null : _notasCtrl.text.trim(),
    );
    if (!mounted) return;
    setState(() => _guardando = false);
    if (res['success'] == true) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
        content: Text('¡Cita registrada correctamente!'),
        backgroundColor: AppColors.success));
      Navigator.pop(context, true);
    } else {
      setState(() => _error = res['mensaje'] ?? 'Error al crear la cita');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: GradientAppBar(
        title: 'Nueva Cita',
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios, size: 18, color: Colors.white),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: Column(
        children: [
          _StepIndicator(paso: _paso, total: 4),
          Expanded(
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  if (_error != null) _ErrorBox(msg: _error!),

                  // ── Paso 0: Odontólogo
                  _PasoHeader(
                    num: 1, label: 'Odontólogo',
                    done: _odSelec != null,
                    sub: _odSelec?['nombre_completo'],
                    onTap: () => setState(() => _paso = 0),
                  ),
                  if (_paso == 0) ...[
                    const SizedBox(height: 12),
                    _loadingOd
                        ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
                        : _odontologos.isEmpty
                            ? _infoChip('Sin odontólogos activos')
                            : Column(
                                children: _odontologos.map((od) => _OpcionCard(
                                  label:    od['nombre_completo'] ?? '',
                                  sub:      od['especialidad']    ?? '',
                                  selected: _odSelec?['id_odontologo'] == od['id_odontologo'],
                                  onTap: () {
                                    setState(() {
                                      _odSelec   = od;
                                      _horaSelec = null;
                                      _paso      = 1;
                                    });
                                    _cargarSlots();
                                  },
                                )).toList(),
                              ),
                  ],

                  const SizedBox(height: 20),

                  // ── Paso 1: Fecha
                  _PasoHeader(
                    num: 2, label: 'Fecha',
                    done: _paso > 1,
                    sub: _paso >= 1
                        ? '${_fecha.day.toString().padLeft(2, "0")}/${_fecha.month.toString().padLeft(2, "0")}/${_fecha.year}'
                        : null,
                    onTap: _odSelec == null ? null : () => setState(() => _paso = 1),
                  ),
                  if (_paso == 1) ...[
                    const SizedBox(height: 8),
                    CalendarDatePicker(
                      initialDate: _fecha,
                      firstDate: DateTime.now(),
                      lastDate: DateTime.now().add(const Duration(days: 90)),
                      onDateChanged: (d) {
                        setState(() { _fecha = d; _horaSelec = null; });
                        _cargarSlots();
                      },
                    ),
                    Align(
                      alignment: Alignment.centerRight,
                      child: _BtnContinuar(onTap: () => setState(() => _paso = 2)),
                    ),
                  ],

                  const SizedBox(height: 20),

                  // ── Paso 2: Slot
                  _PasoHeader(
                    num: 3, label: 'Horario',
                    done: _horaSelec != null,
                    sub: _horaSelec,
                    onTap: _paso < 2 ? null : () => setState(() => _paso = 2),
                  ),
                  if (_paso == 2) ...[
                    const SizedBox(height: 12),
                    _loadingSlots
                        ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
                        : _slots.isEmpty
                            ? _infoChip('Sin horarios disponibles para este día')
                            : Wrap(
                                spacing: 8, runSpacing: 8,
                                children: _slots.map((s) {
                                  final hora  = s['hora']       as String;
                                  final libre = s['disponible'] as bool;
                                  final sel   = _horaSelec == hora;
                                  return GestureDetector(
                                    onTap: libre
                                        ? () => setState(() { _horaSelec = hora; _paso = 3; })
                                        : null,
                                    child: Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                                      decoration: BoxDecoration(
                                        color: !libre ? AppColors.border : sel ? AppColors.primary : AppColors.surface,
                                        borderRadius: BorderRadius.circular(10),
                                        border: Border.all(color: sel ? AppColors.primary : AppColors.border),
                                      ),
                                      child: Text(hora,
                                          style: AppTypography.bodyMedium(
                                            color: !libre ? AppColors.textMuted : sel ? Colors.white : AppColors.textDark,
                                          )),
                                    ),
                                  );
                                }).toList(),
                              ),
                  ],

                  const SizedBox(height: 20),

                  // ── Paso 3: Paciente
                  _PasoHeader(
                    num: 4, label: 'Paciente',
                    done: _pacSelec != null,
                    sub: _pacSelec?.nombre,
                    onTap: _paso < 3 ? null : () => setState(() => _paso = 3),
                  ),
                  if (_paso == 3) ...[
                    const SizedBox(height: 12),
                    TextField(
                      controller: _buscarCtrl,
                      style: AppTypography.body(color: AppColors.textDark),
                      decoration: InputDecoration(
                        hintText: 'Buscar por nombre o expediente',
                        hintStyle: AppTypography.body(color: AppColors.textMuted),
                        prefixIcon: const Icon(Icons.search, color: AppColors.textMuted, size: 20),
                        filled: true,
                        fillColor: AppColors.surface,
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppColors.border)),
                        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppColors.border)),
                        focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppColors.primary, width: 1.5)),
                        contentPadding: const EdgeInsets.symmetric(vertical: 12, horizontal: 14),
                      ),
                    ),
                    const SizedBox(height: 10),
                    _loadingPac
                        ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
                        : _filtrados.isEmpty
                            ? _infoChip('Sin resultados')
                            : Column(
                                children: _filtrados.take(8).map((p) {
                                  final sel = _pacSelec?.idPaciente == p.idPaciente;
                                  return GestureDetector(
                                    onTap: () => setState(() => _pacSelec = p),
                                    child: Container(
                                      margin: const EdgeInsets.only(bottom: 6),
                                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                                      decoration: BoxDecoration(
                                        color: sel ? AppColors.primaryLight : AppColors.surface,
                                        borderRadius: BorderRadius.circular(10),
                                        border: Border.all(color: sel ? AppColors.primary : AppColors.border),
                                      ),
                                      child: Row(children: [
                                        Icon(Icons.person_outline, size: 18, color: sel ? AppColors.primary : AppColors.textMuted),
                                        const SizedBox(width: 10),
                                        Expanded(
                                          child: Column(
                                            crossAxisAlignment: CrossAxisAlignment.start,
                                            children: [
                                              Text(p.nombre, style: AppTypography.bodyMedium(color: sel ? AppColors.primary : AppColors.textDark)),
                                              if (p.numExpediente != null)
                                                Text('Exp. ${p.numExpediente}', style: AppTypography.caption(color: AppColors.textMuted)),
                                            ],
                                          ),
                                        ),
                                        if (sel) const Icon(Icons.check_circle, color: AppColors.primary, size: 18),
                                      ]),
                                    ),
                                  );
                                }).toList(),
                              ),

                    if (_pacSelec != null) ...[
                      const SizedBox(height: 16),
                      TextField(
                        controller: _notasCtrl,
                        maxLines: 2,
                        style: AppTypography.body(color: AppColors.textDark),
                        decoration: InputDecoration(
                          hintText: 'Notas adicionales (opcional)',
                          hintStyle: AppTypography.body(color: AppColors.textMuted),
                          filled: true,
                          fillColor: AppColors.surface,
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppColors.border)),
                          enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppColors.border)),
                          focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppColors.primary, width: 1.5)),
                          contentPadding: const EdgeInsets.symmetric(vertical: 12, horizontal: 14),
                        ),
                      ),
                      const SizedBox(height: 20),
                      _ResumenCita(
                        odontologo: _odSelec?['nombre_completo'] ?? '',
                        fecha: '${_fecha.day.toString().padLeft(2, "0")}/${_fecha.month.toString().padLeft(2, "0")}/${_fecha.year}',
                        hora:     _horaSelec ?? '',
                        paciente: _pacSelec!.nombre,
                      ),
                      const SizedBox(height: 20),
                      SizedBox(
                        width: double.infinity,
                        height: 52,
                        child: ElevatedButton(
                          onPressed: _guardando ? null : _guardar,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.primary,
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                          ),
                          child: _guardando
                              ? const SizedBox(width: 22, height: 22,
                                  child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2.5))
                              : Text('Confirmar Cita', style: AppTypography.button(color: Colors.white)),
                        ),
                      ),
                    ],
                  ],
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _infoChip(String label) => Container(
        margin: const EdgeInsets.symmetric(vertical: 8),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
        decoration: BoxDecoration(color: AppColors.inputFill, borderRadius: BorderRadius.circular(10)),
        child: Text(label, style: AppTypography.body(color: AppColors.textMuted)),
      );
}

// ══════════════════════════════════════════════════════════════
// WIDGETS DE APOYO
// ══════════════════════════════════════════════════════════════

class _StepIndicator extends StatelessWidget {
  const _StepIndicator({required this.paso, required this.total});
  final int paso;
  final int total;

  @override
  Widget build(BuildContext context) {
    return Container(
      color: AppColors.surface,
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
      child: Row(
        children: List.generate(total * 2 - 1, (i) {
          if (i.isOdd) {
            final idx = i ~/ 2;
            return Expanded(child: Container(height: 2, color: idx < paso ? AppColors.primary : AppColors.border));
          }
          final idx    = i ~/ 2;
          final done   = idx < paso;
          final active = idx == paso;
          return Container(
            width: 28, height: 28,
            decoration: BoxDecoration(
              color: done || active ? AppColors.primary : AppColors.border,
              shape: BoxShape.circle,
            ),
            child: Center(
              child: done
                  ? const Icon(Icons.check, size: 14, color: Colors.white)
                  : Text('${idx + 1}',
                      style: TextStyle(
                        color: active ? Colors.white : AppColors.textMuted,
                        fontSize: 12,
                        fontWeight: FontWeight.bold)),
            ),
          );
        }),
      ),
    );
  }
}

class _PasoHeader extends StatelessWidget {
  const _PasoHeader({required this.num, required this.label, required this.done, this.sub, this.onTap});
  final int     num;
  final String  label;
  final bool    done;
  final String? sub;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Row(children: [
        Container(
          width: 24, height: 24,
          decoration: BoxDecoration(
            color: done ? AppColors.success : AppColors.primary,
            shape: BoxShape.circle,
          ),
          child: Center(
            child: done
                ? const Icon(Icons.check, size: 13, color: Colors.white)
                : Text('$num', style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold)),
          ),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(label, style: AppTypography.label(color: AppColors.textDark)),
              if (sub != null) Text(sub!, style: AppTypography.caption(color: AppColors.textMuted)),
            ],
          ),
        ),
        if (onTap != null && done) const Icon(Icons.edit_outlined, size: 16, color: AppColors.primary),
      ]),
    );
  }
}

class _OpcionCard extends StatelessWidget {
  const _OpcionCard({required this.label, required this.sub, required this.selected, required this.onTap});
  final String       label;
  final String       sub;
  final bool         selected;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        margin: const EdgeInsets.only(bottom: 8),
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: selected ? AppColors.primaryLight : AppColors.surface,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: selected ? AppColors.primary : AppColors.border, width: selected ? 1.5 : 1),
        ),
        child: Row(children: [
          Icon(Icons.person_outline, color: selected ? AppColors.primary : AppColors.textMuted),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(label, style: AppTypography.bodyMedium(color: selected ? AppColors.primary : AppColors.textDark)),
                if (sub.isNotEmpty) Text(sub, style: AppTypography.caption(color: AppColors.textMuted)),
              ],
            ),
          ),
          if (selected) const Icon(Icons.check_circle, color: AppColors.primary, size: 18),
        ]),
      ),
    );
  }
}

class _BtnContinuar extends StatelessWidget {
  const _BtnContinuar({required this.onTap});
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return TextButton.icon(
      onPressed: onTap,
      icon: const Icon(Icons.arrow_forward, size: 16, color: AppColors.primary),
      label: Text('Continuar', style: AppTypography.buttonSmall(color: AppColors.primary)),
    );
  }
}

class _ResumenCita extends StatelessWidget {
  const _ResumenCita({required this.odontologo, required this.fecha, required this.hora, required this.paciente});
  final String odontologo;
  final String fecha;
  final String hora;
  final String paciente;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.primaryLight,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.primary.withAlpha(60)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('Resumen de la cita', style: AppTypography.label(color: AppColors.primary)),
          const SizedBox(height: 12),
          _row(Icons.person_outline, 'Odontólogo', odontologo),
          const SizedBox(height: 6),
          _row(Icons.calendar_today, 'Fecha', '$fecha a las $hora'),
          const SizedBox(height: 6),
          _row(Icons.face_outlined,  'Paciente',   paciente),
        ],
      ),
    );
  }

  Widget _row(IconData icon, String label, String value) => Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, size: 16, color: AppColors.primary),
          const SizedBox(width: 8),
          SizedBox(width: 80, child: Text(label, style: AppTypography.caption(color: AppColors.textMuted))),
          Expanded(child: Text(value, style: AppTypography.bodyMedium(color: AppColors.textDark))),
        ],
      );
}

class _ErrorBox extends StatelessWidget {
  const _ErrorBox({required this.msg});
  final String msg;

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: AppColors.errorLight,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: AppColors.error.withAlpha(80)),
      ),
      child: Row(children: [
        const Icon(Icons.error_outline, color: AppColors.error, size: 18),
        const SizedBox(width: 8),
        Expanded(child: Text(msg, style: AppTypography.body(color: AppColors.error))),
      ]),
    );
  }
}
