<?php
$csrf    = Csrf::token();
$tab     = $_GET['tab'] ?? 'sistema';

// Mapa de navegacion lateral
$navGroups = [
    'general' => [
        'label' => 'General',
        'items' => [
            'sistema'     => ['label' => 'Sistema',           'icon' => 'fa-gear'],
            'facturacion' => ['label' => 'Facturación / SAR', 'icon' => 'fa-file-invoice'],
        ],
    ],
    'catalogos' => [
        'label' => 'Catálogos clínicos',
        'items' => [
            'alergias'       => ['label' => 'Alergias',             'icon' => 'fa-triangle-exclamation'],
            'enfermedades'   => ['label' => 'Enfermedades',         'icon' => 'fa-heart-pulse'],
            'medicamentos'   => ['label' => 'Medicamentos',         'icon' => 'fa-pills'],
            'tratamientos'   => ['label' => 'Tratamientos',         'icon' => 'fa-tooth'],
            'especialidades' => ['label' => 'Especialidades',       'icon' => 'fa-stethoscope'],
            'cargos'         => ['label' => 'Cargos',               'icon' => 'fa-id-badge'],
            'sangres'        => ['label' => 'Tipos de sangre',      'icon' => 'fa-droplet'],
        ],
    ],
    'inventario' => [
        'label' => 'Inventario',
        'items' => [
            'proveedores' => ['label' => 'Proveedores', 'icon' => 'fa-truck'],
        ],
    ],
];

// Datos del tab activo
$datosCatalogo = [];
$tituloTab     = '';
foreach ($navGroups as $group) {
    if (isset($group['items'][$tab])) {
        $tituloTab = $group['items'][$tab]['label'];
        break;
    }
}
?>
<div style="padding:24px 28px;">

<!-- Encabezado -->
<div style="margin-bottom:20px;">
    <h4 style="margin:0;font-size:18px;font-weight:700;color:var(--body-text);">
        <i class="fas fa-gear me-2" style="color:#1A56AB;"></i>Configuración del Sistema
    </h4>
</div>

<!-- Layout: sidebar + contenido -->
<div style="display:flex;gap:20px;align-items:flex-start;">

    <!-- ── SIDEBAR IZQUIERDO ──────────────────────────────── -->
    <div style="width:220px;flex-shrink:0;">
        <?php foreach ($navGroups as $groupKey => $group): ?>
        <div style="margin-bottom:18px;">
            <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#9CA3AF;padding:0 10px 6px;"><?= $group['label'] ?></div>
            <?php foreach ($group['items'] as $key => $item): $isActive = $tab === $key; ?>
            <a href="?tab=<?= $key ?>"
               style="display:flex;align-items:center;gap:9px;padding:8px 12px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:<?= $isActive?'600':'400' ?>;margin-bottom:2px;
                      background:<?= $isActive?'rgba(26,86,171,.1)':'transparent' ?>;
                      color:<?= $isActive?'#1A56AB':'var(--body-text)' ?>;
                      transition:.15s;">
                <i class="fas <?= $item['icon'] ?>" style="font-size:13px;width:16px;text-align:center;opacity:<?= $isActive?'1':'.6' ?>;"></i>
                <?= $item['label'] ?>
                <?php if ($isActive): ?>
                <i class="fas fa-chevron-right" style="margin-left:auto;font-size:9px;opacity:.6;"></i>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ── ÁREA DE CONTENIDO ──────────────────────────────── -->
    <div style="flex:1;min-width:0;">

    <?php if(isset($_GET['ok'])): ?>
    <div style="background:#F0FDF4;border:1px solid #86EFAC;border-radius:8px;padding:10px 14px;color:#16A34A;font-size:13px;margin-bottom:16px;">
        <i class="fas fa-check-circle me-2"></i>Cambios guardados correctamente.
    </div>
    <?php endif; ?>

    <!-- ════════ SISTEMA ════════════════════════════════════ -->
    <?php if($tab === 'sistema'): ?>
    <form method="POST" action="<?= APP_URL ?>Configuracion/guardar">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <input type="hidden" name="tipo" value="sistema">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
            <div class="kpi-card">
                <div style="font-weight:700;font-size:14px;color:var(--body-text);margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid var(--card-border);">
                    <i class="fas fa-hospital me-2" style="color:#1A56AB;"></i>Datos de la Clínica
                </div>
                <div class="mb-3"><label class="form-label">Nombre de la Clínica</label>
                    <input type="text" name="clinica_nombre" class="form-control" value="<?= htmlspecialchars($config['clinica_nombre']['valor']??'') ?>" required></div>
                <div class="mb-3"><label class="form-label">RTN Fiscal</label>
                    <input type="text" name="clinica_rtn" class="form-control" value="<?= htmlspecialchars($config['clinica_rtn']['valor']??'') ?>" placeholder="08011985123456"></div>
                <div class="mb-0"><label class="form-label">Símbolo de Moneda</label>
                    <input type="text" name="moneda_simbolo" class="form-control" value="<?= htmlspecialchars($config['moneda_simbolo']['valor']??'L') ?>" maxlength="5"></div>
            </div>
            <div class="kpi-card">
                <div style="font-weight:700;font-size:14px;color:var(--body-text);margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid var(--card-border);">
                    <i class="fas fa-percent me-2" style="color:#F59E0B;"></i>Tasas ISV Honduras
                </div>
                <div class="mb-3">
                    <label class="form-label">ISV Servicios %</label>
                    <select name="tasa_isv_reducida" class="form-select">
                        <?php foreach(['0','15','18'] as $t): ?>
                        <option value="<?= $t ?>" <?= ($config['tasa_isv_reducida']['valor']??'15')===$t?'selected':'' ?>><?= $t ?>%</option>
                        <?php endforeach; ?>
                    </select>
                    <div style="font-size:11px;color:#9CA3AF;margin-top:4px;">Aplica a servicios odontológicos</div>
                </div>
                <div class="mb-0">
                    <label class="form-label">ISV Bienes %</label>
                    <select name="tasa_isv_general" class="form-select">
                        <?php foreach(['0','15','18'] as $t): ?>
                        <option value="<?= $t ?>" <?= ($config['tasa_isv_general']['valor']??'18')===$t?'selected':'' ?>><?= $t ?>%</option>
                        <?php endforeach; ?>
                    </select>
                    <div style="font-size:11px;color:#9CA3AF;margin-top:4px;">Aplica a productos/bienes</div>
                </div>
            </div>
        </div>
        <div style="margin-top:16px;display:flex;justify-content:flex-end;">
            <button type="submit" class="btn-og-primary"><i class="fas fa-save me-1"></i>Guardar configuración</button>
        </div>
    </form>

    <!-- ════════ FACTURACIÓN / SAR ══════════════════════════ -->
    <?php elseif($tab === 'facturacion'): ?>
    <form method="POST" action="<?= APP_URL ?>Configuracion/guardar">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <input type="hidden" name="tipo" value="sucursal">
        <div class="kpi-card">
            <div style="font-weight:700;font-size:14px;color:var(--body-text);margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid var(--card-border);">
                <i class="fas fa-building me-2" style="color:#1A56AB;"></i>Sucursal — Datos SAR Honduras
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div><label class="form-label">Nombre sucursal</label>
                    <input type="text" name="sucursal_nombre" class="form-control" value="<?= htmlspecialchars($sucursal['nombre']??'') ?>"></div>
                <div><label class="form-label">Teléfono</label>
                    <input type="text" name="sucursal_telefono" class="form-control" value="<?= htmlspecialchars($sucursal['telefono']??'') ?>"></div>
                <div><label class="form-label">RTN de la sucursal</label>
                    <input type="text" name="sucursal_rtn" class="form-control" value="<?= htmlspecialchars($sucursal['rtn']??'') ?>"></div>
                <div><label class="form-label">Contacto administrativo</label>
                    <input type="text" name="sucursal_contacto" class="form-control" value="<?= htmlspecialchars($sucursal['contacto']??'') ?>"></div>
                <div style="grid-column:1/-1;"><label class="form-label">Dirección / Ubicación</label>
                    <input type="text" name="sucursal_ubicacion" class="form-control" value="<?= htmlspecialchars($sucursal['ubicacion']??'') ?>"></div>
                <div style="grid-column:1/-1;">
                    <label class="form-label">CAI — Código de Autorización de Impresión (SAR)</label>
                    <input type="text" name="sucursal_cai" class="form-control" style="font-family:monospace;"
                           value="<?= htmlspecialchars($sucursal['cai']??'') ?>"
                           placeholder="XXXXXX-XXXXXX-XXXXXX-XXXXXX-XXXXXX-XX">
                    <div style="font-size:11px;color:#9CA3AF;margin-top:4px;">CAI habilitado por el SAR para emisión de facturas. 6 bloques separados por guiones.</div>
                </div>
            </div>
        </div>
        <div style="margin-top:16px;display:flex;justify-content:flex-end;">
            <button type="submit" class="btn-og-primary"><i class="fas fa-save me-1"></i>Guardar datos SAR</button>
        </div>
    </form>

    <!-- ════════ CATÁLOGOS INDIVIDUALES ══════════════════════ -->
    <?php elseif(in_array($tab, ['alergias','enfermedades','medicamentos','tratamientos','especialidades','cargos'])): ?>
    <?php
        $catMap = [
            'alergias'       => ['cat'=>'alergia',      'items'=>$alergias,       'idCol'=>'id_alergia',      'labelCol'=>'descripcion', 'conPrecio'=>false],
            'enfermedades'   => ['cat'=>'enfermedad',   'items'=>$enfermedades,   'idCol'=>'id_enfermedad',   'labelCol'=>'descripcion', 'conPrecio'=>false],
            'medicamentos'   => ['cat'=>'medicamento',  'items'=>$medicamentos,   'idCol'=>'id_medicamento',  'labelCol'=>'descripcion', 'conPrecio'=>false],
            'tratamientos'   => ['cat'=>'tratamiento',  'items'=>$tratamientos,   'idCol'=>'id_tratamiento',  'labelCol'=>'descripcion', 'conPrecio'=>true],
            'especialidades' => ['cat'=>'especialidad', 'items'=>$especialidades, 'idCol'=>'id_especialidad', 'labelCol'=>'nombre',      'conPrecio'=>false],
            'cargos'         => ['cat'=>'cargo',        'items'=>$cargos,         'idCol'=>'id_cargo',        'labelCol'=>'nombre',      'conPrecio'=>false],
        ];
        $cm = $catMap[$tab];
        $cat        = $cm['cat'];
        $items      = $cm['items'];
        $idCol      = $cm['idCol'];
        $labelCol   = $cm['labelCol'];
        $conPrecio  = $cm['conPrecio'];
        $sinEstado  = ($cat === 'especialidad');
    ?>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
        <div>
            <div style="font-size:16px;font-weight:700;color:var(--body-text);"><?= $tituloTab ?></div>
            <div style="font-size:12px;color:#9CA3AF;margin-top:2px;"><?= count($items) ?> registros</div>
        </div>
        <button class="btn-og-primary" onclick="abrirModal('<?= $cat ?>')"><i class="fas fa-plus me-1"></i>Agregar <?= $tituloTab ?></button>
    </div>
    <div class="kpi-card" style="padding:0;overflow:hidden;">
        <table class="tabla-og">
            <thead>
                <tr>
                    <th><?= ucfirst($labelCol) === 'Descripcion' ? 'Descripción' : ucfirst($labelCol) ?></th>
                    <?php if($conPrecio): ?><th>Precio base</th><th>ISV</th><?php endif; ?>
                    <?php if(!$sinEstado): ?><th>Estado</th><?php endif; ?>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php if(empty($items)): ?>
            <tr><td colspan="<?= 2 + ($conPrecio?2:0) + ($sinEstado?0:1) ?>" style="text-align:center;padding:40px;color:#9CA3AF;">
                <i class="fas fa-inbox fa-2x d-block mb-2" style="opacity:.3;"></i>Sin registros aún
            </td></tr>
            <?php else: foreach($items as $it): ?>
            <?php
                $estado    = $it['estado'] ?? 'activo';
                $esActivo  = in_array($estado, ['activo','activa']);
                $idVal     = $it[$idCol];
                $labelVal  = htmlspecialchars(addslashes($it[$labelCol] ?? ''));
                $extraArgs = $conPrecio ? ",'" . ($it['precio_base']??0) . "','" . ($it['tasa_impuesto']??'15') . "'" : ",'',''";
            ?>
            <tr>
                <td style="font-weight:500;"><?= htmlspecialchars($it[$labelCol] ?? '') ?></td>
                <?php if($conPrecio): ?>
                <td style="font-family:monospace;">L. <?= number_format((float)($it['precio_base']??0),2) ?></td>
                <td><span style="padding:2px 8px;border-radius:12px;background:#FEF3C7;color:#92400E;font-size:12px;font-weight:600;"><?= $it['tasa_impuesto']??'15' ?>%</span></td>
                <?php endif; ?>
                <?php if(!$sinEstado): ?>
                <td><span class="badge <?= $esActivo?'badge-green':'badge-gray' ?>"><?= ucfirst($estado) ?></span></td>
                <?php endif; ?>
                <td>
                    <div style="display:flex;gap:6px;">
                        <button class="btn-og-icon" onclick="editarItem('<?= $cat ?>',<?= $idVal ?>,'<?= $labelVal ?>','<?= $estado ?>'<?= $extraArgs ?>)" title="Editar">
                            <i class="fas fa-pen"></i>
                        </button>
                        <?php if(!$sinEstado): ?>
                        <button class="btn-og-icon <?= $esActivo?'btn-danger-icon':'' ?>"
                                onclick="toggleItem('<?= $cat ?>',<?= $idVal ?>)"
                                title="<?= $esActivo?'Desactivar':'Activar' ?>">
                            <i class="fas fa-<?= $esActivo?'ban':'check' ?>"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <!-- ════════ TIPOS DE SANGRE ════════════════════════════ -->
    <?php elseif($tab === 'sangres'): ?>
    <div style="font-size:16px;font-weight:700;color:var(--body-text);margin-bottom:16px;">Tipos de Sangre</div>
    <div class="kpi-card">
        <div style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:12px;">
        <?php foreach($sangres as $s): ?>
            <span style="padding:8px 20px;border-radius:24px;background:#FEF2F2;color:#DC2626;font-weight:700;font-size:15px;border:1.5px solid #FECACA;">
                <?= htmlspecialchars($s['descripcion']) ?>
            </span>
        <?php endforeach; ?>
        </div>
        <p style="font-size:12px;color:#9CA3AF;margin:0;">Los tipos de sangre ABO + Rh son de sistema y no requieren gestión manual.</p>
    </div>

    <!-- ════════ PROVEEDORES ═════════════════════════════════ -->
    <?php elseif($tab === 'proveedores'): ?>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
        <div>
            <div style="font-size:16px;font-weight:700;color:var(--body-text);">Proveedores</div>
            <div style="font-size:12px;color:#9CA3AF;margin-top:2px;"><?= count($proveedores) ?> registros</div>
        </div>
        <button class="btn-og-primary" onclick="abrirModalProveedor(0)"><i class="fas fa-plus me-1"></i>Nuevo Proveedor</button>
    </div>
    <div class="kpi-card" style="padding:0;overflow:hidden;">
        <table class="tabla-og">
            <thead><tr><th>Proveedor</th><th>RTN</th><th>Teléfono</th><th>Correo</th><th>Contacto</th><th>Estado</th><th>Acc.</th></tr></thead>
            <tbody>
            <?php if(empty($proveedores)): ?>
            <tr><td colspan="7" style="text-align:center;padding:40px;color:#9CA3AF;"><i class="fas fa-inbox fa-2x d-block mb-2" style="opacity:.3;"></i>Sin proveedores</td></tr>
            <?php else: foreach($proveedores as $p): $act=$p['estado']==='activo'; ?>
            <tr>
                <td style="font-weight:600;"><?= htmlspecialchars($p['proveedor']) ?></td>
                <td style="font-family:monospace;font-size:12px;"><?= htmlspecialchars($p['rtn']??'—') ?></td>
                <td><?= htmlspecialchars($p['telefono']??'—') ?></td>
                <td><?= htmlspecialchars($p['correo']??'—') ?></td>
                <td><?= htmlspecialchars($p['contacto_nombre']??'—') ?></td>
                <td><span class="badge <?= $act?'badge-green':'badge-gray' ?>"><?= $act?'Activo':'Inactivo' ?></span></td>
                <td>
                    <div style="display:flex;gap:6px;">
                        <button class="btn-og-icon" onclick="abrirModalProveedor(<?= $p['id_proveedor'] ?>,'<?= addslashes($p['proveedor']) ?>','<?= addslashes($p['rtn']??'') ?>','<?= addslashes($p['telefono']??'') ?>','<?= addslashes($p['correo']??'') ?>','<?= addslashes($p['ubicacion']??'') ?>','<?= addslashes($p['contacto_nombre']??'') ?>','<?= $p['estado'] ?>')" title="Editar">
                            <i class="fas fa-pen"></i></button>
                        <button class="btn-og-icon <?= $act?'btn-danger-icon':'' ?>" onclick="toggleItem('proveedor',<?= $p['id_proveedor'] ?>)" title="<?= $act?'Desactivar':'Activar' ?>">
                            <i class="fas fa-<?= $act?'ban':'check' ?>"></i></button>
                    </div>
                </td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    </div><!-- /contenido -->
</div><!-- /layout -->
</div><!-- /padding -->

<!-- ── MODAL CATÁLOGO GENÉRICO ───────────────────────────────── -->
<div id="modalCat" style="display:none;position:fixed;inset:0;z-index:1060;align-items:center;justify-content:center;">
    <div style="position:absolute;inset:0;background:rgba(0,0,0,.45);" onclick="cerrarModal()"></div>
    <div style="position:relative;background:var(--card-bg);border-radius:14px;box-shadow:0 20px 60px rgba(0,0,0,.25);width:100%;max-width:440px;margin:16px;">
        <div style="padding:16px 22px;border-bottom:1px solid var(--card-border);display:flex;justify-content:space-between;align-items:center;">
            <h5 id="mCatTitulo" style="margin:0;font-size:15px;font-weight:700;color:var(--body-text);">Agregar</h5>
            <button onclick="cerrarModal()" style="background:none;border:none;cursor:pointer;color:#9CA3AF;font-size:16px;"><i class="fas fa-times"></i></button>
        </div>
        <form id="formCat" onsubmit="submitCatalogo(event)">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <input type="hidden" name="catalogo" id="mCatCat">
            <input type="hidden" name="id_item"  id="mCatId">
            <div style="padding:20px 22px;">
                <div class="mb-3">
                    <label class="form-label" id="mCatLabel">Descripción</label>
                    <input type="text" name="descripcion" id="mCatDesc" class="form-control" required>
                </div>
                <div id="mCatPrecioWrap" style="display:none;">
                    <div class="mb-3"><label class="form-label">Precio base (L.)</label>
                        <input type="number" name="precio_base" id="mCatPrecio" class="form-control" step="0.01" min="0" value="0"></div>
                    <div class="mb-3"><label class="form-label">Tasa ISV</label>
                        <select name="tasa_impuesto" id="mCatIsv" class="form-select">
                            <option value="0">0% — Exento</option>
                            <option value="15" selected>15% — Servicios</option>
                            <option value="18">18% — Bienes</option>
                        </select></div>
                </div>
                <div id="mCatEstadoWrap" style="display:none;" class="mb-0">
                    <label class="form-label">Estado</label>
                    <select name="estado" id="mCatEstado" class="form-select">
                        <option value="activo">Activo</option>
                        <option value="activa">Activa</option>
                        <option value="inactivo">Inactivo</option>
                        <option value="inactiva">Inactiva</option>
                    </select>
                </div>
            </div>
            <div style="padding:14px 22px;border-top:1px solid var(--card-border);display:flex;justify-content:flex-end;gap:10px;">
                <button type="button" class="btn-og-secondary" onclick="cerrarModal()">Cancelar</button>
                <button type="submit" id="mCatBtn" class="btn-og-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- ── MODAL PROVEEDOR ───────────────────────────────────────── -->
<div id="modalProv" style="display:none;position:fixed;inset:0;z-index:1060;align-items:center;justify-content:center;">
    <div style="position:absolute;inset:0;background:rgba(0,0,0,.45);" onclick="document.getElementById('modalProv').style.display='none'"></div>
    <div style="position:relative;background:var(--card-bg);border-radius:14px;box-shadow:0 20px 60px rgba(0,0,0,.25);width:100%;max-width:520px;margin:16px;max-height:92vh;overflow-y:auto;">
        <div style="padding:16px 22px;border-bottom:1px solid var(--card-border);display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;background:var(--card-bg);z-index:1;">
            <h5 id="mProvTitulo" style="margin:0;font-size:15px;font-weight:700;color:var(--body-text);">Proveedor</h5>
            <button onclick="document.getElementById('modalProv').style.display='none'" style="background:none;border:none;cursor:pointer;color:#9CA3AF;font-size:16px;"><i class="fas fa-times"></i></button>
        </div>
        <form id="formProv" onsubmit="submitProveedor(event)">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <input type="hidden" name="catalogo" value="proveedor">
            <input type="hidden" name="id_proveedor" id="mProvId">
            <div style="padding:20px 22px;display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                <div style="grid-column:1/-1;"><label class="form-label">Nombre del proveedor *</label>
                    <input type="text" name="proveedor" id="mProvNom" class="form-control" required></div>
                <div><label class="form-label">RTN</label><input type="text" name="rtn" id="mProvRtn" class="form-control"></div>
                <div><label class="form-label">Teléfono</label><input type="text" name="telefono" id="mProvTel" class="form-control"></div>
                <div><label class="form-label">Correo</label><input type="email" name="correo" id="mProvCor" class="form-control"></div>
                <div><label class="form-label">Contacto</label><input type="text" name="contacto_nombre" id="mProvCont" class="form-control"></div>
                <div style="grid-column:1/-1;"><label class="form-label">Dirección</label>
                    <input type="text" name="ubicacion" id="mProvUb" class="form-control"></div>
                <div style="grid-column:1/-1;"><label class="form-label">Estado</label>
                    <select name="estado" id="mProvEst" class="form-select">
                        <option value="activo">Activo</option><option value="inactivo">Inactivo</option>
                    </select></div>
            </div>
            <div style="padding:14px 22px;border-top:1px solid var(--card-border);display:flex;justify-content:flex-end;gap:10px;position:sticky;bottom:0;background:var(--card-bg);">
                <button type="button" class="btn-og-secondary" onclick="document.getElementById('modalProv').style.display='none'">Cancelar</button>
                <button type="submit" class="btn-og-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<style>
.btn-og-icon{width:32px;height:32px;border-radius:7px;border:1px solid #DDE4EF;background:#F5F7FB;color:#374151;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;font-size:12px;transition:.15s;}
.btn-og-icon:hover{background:#1A56AB;border-color:#1A56AB;color:#fff;}
.btn-danger-icon:hover{background:#DC2626;border-color:#DC2626;color:#fff;}
[data-theme="dark"] .btn-og-icon{background:#253349;border-color:#334155;color:#CBD5E1;}
</style>

<script>
const CAT_CON_PRECIO = ['tratamiento'];
const CAT_SIN_ESTADO = ['especialidad'];
const CAT_LABELS = {
    alergia:'Alergia', enfermedad:'Enfermedad', medicamento:'Medicamento',
    tratamiento:'Tratamiento', especialidad:'Especialidad', cargo:'Cargo'
};

function abrirModal(cat) {
    document.getElementById('mCatCat').value = cat;
    document.getElementById('mCatId').value  = '';
    document.getElementById('mCatDesc').value = '';
    document.getElementById('mCatPrecio').value = '0';
    document.getElementById('mCatIsv').value = '15';
    document.getElementById('mCatTitulo').textContent = 'Agregar ' + (CAT_LABELS[cat]||cat);
    document.getElementById('mCatPrecioWrap').style.display = CAT_CON_PRECIO.includes(cat) ? '' : 'none';
    document.getElementById('mCatEstadoWrap').style.display = CAT_SIN_ESTADO.includes(cat) ? 'none' : '';
    document.getElementById('modalCat').style.display = 'flex';
    setTimeout(() => document.getElementById('mCatDesc').focus(), 80);
}

function editarItem(cat, id, desc, estado, precio, isv) {
    document.getElementById('mCatCat').value   = cat;
    document.getElementById('mCatId').value    = id;
    document.getElementById('mCatDesc').value  = desc;
    document.getElementById('mCatTitulo').textContent = 'Editar ' + (CAT_LABELS[cat]||cat);
    const hasPrecio = CAT_CON_PRECIO.includes(cat);
    document.getElementById('mCatPrecioWrap').style.display = hasPrecio ? '' : 'none';
    if (hasPrecio) {
        document.getElementById('mCatPrecio').value = precio || 0;
        document.getElementById('mCatIsv').value   = isv || '15';
    }
    document.getElementById('mCatEstadoWrap').style.display = CAT_SIN_ESTADO.includes(cat) ? 'none' : '';
    if (!CAT_SIN_ESTADO.includes(cat)) document.getElementById('mCatEstado').value = estado;
    document.getElementById('modalCat').style.display = 'flex';
}

function cerrarModal() { document.getElementById('modalCat').style.display = 'none'; }

function getIdCol(cat) {
    const m = {alergia:'id_alergia',enfermedad:'id_enfermedad',medicamento:'id_medicamento',
                tratamiento:'id_tratamiento',especialidad:'id_especialidad',cargo:'id_cargo'};
    return m[cat] || 'id';
}

async function submitCatalogo(e) {
    e.preventDefault();
    const fd  = new FormData(document.getElementById('formCat'));
    const cat = fd.get('catalogo');
    const idR = fd.get('id_item');
    // Mapear campo nombre para especialidades y cargos que usan 'nombre' no 'descripcion'
    if (['especialidad','cargo'].includes(cat)) {
        fd.append('nombre', fd.get('descripcion'));
    }
    if (idR) fd.append(getIdCol(cat), idR);
    const btn = document.getElementById('mCatBtn');
    btn.disabled = true; btn.textContent = 'Guardando…';
    try {
        const r = await fetch('<?= APP_URL ?>Configuracion/guardarCatalogo', {method:'POST', body:fd});
        const d = await r.json();
        if (d.success) { cerrarModal(); location.reload(); }
        else Swal.fire({icon:'error', title:'Error', text: d.error||'Error al guardar'});
    } catch { Swal.fire({icon:'error', title:'Error de conexión'}); }
    finally { btn.disabled=false; btn.textContent='Guardar'; }
}

async function toggleItem(cat, id) {
    const r = await Swal.fire({title:'¿Cambiar estado?',icon:'question',showCancelButton:true,
        confirmButtonColor:'#1A56AB',confirmButtonText:'Confirmar',cancelButtonText:'Cancelar'});
    if (!r.isConfirmed) return;
    const fd = new FormData();
    fd.append('csrf_token','<?= $csrf ?>');
    fd.append('catalogo', cat);
    fd.append('id', id);
    await fetch('<?= APP_URL ?>Configuracion/toggleCatalogo', {method:'POST', body:fd});
    location.reload();
}

function abrirModalProveedor(id,nom='',rtn='',tel='',cor='',ub='',cont='',est='activo') {
    document.getElementById('mProvId').value   = id || '';
    document.getElementById('mProvNom').value  = nom;
    document.getElementById('mProvRtn').value  = rtn;
    document.getElementById('mProvTel').value  = tel;
    document.getElementById('mProvCor').value  = cor;
    document.getElementById('mProvUb').value   = ub;
    document.getElementById('mProvCont').value = cont;
    document.getElementById('mProvEst').value  = est;
    document.getElementById('mProvTitulo').textContent = id ? 'Editar Proveedor' : 'Nuevo Proveedor';
    document.getElementById('modalProv').style.display = 'flex';
}

async function submitProveedor(e) {
    e.preventDefault();
    const fd = new FormData(document.getElementById('formProv'));
    const r  = await fetch('<?= APP_URL ?>Configuracion/guardarCatalogo', {method:'POST', body:fd});
    const d  = await r.json();
    if (d.success) location.reload();
    else Swal.fire({icon:'error', title:'Error', text: d.error||'Error al guardar'});
}
</script>
