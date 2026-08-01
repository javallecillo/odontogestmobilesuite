# OdontoGest Mobile Suite

**Ecosistema informático multiplataforma para la gestión integral de clínicas dentales.**

> Cliente piloto: Clínica Dental Paz — Santa Bárbara, Honduras  
> Asignatura: Programación Móvil II — Universidad Tecnológica de Honduras — Periodo 2-2026  
> Docente: Máster Luis Fernando Teruel Umanzor

---

## Arquitectura del sistema

```
┌──────────────────────────┐        ┌──────────────────────────┐
│     Flutter (móvil)      │        │    PHP MVC (web admin)   │
│  Rol: uso clínico en     │        │  CRUD completo: usuarios,│
│  sala — odontograma,     │◄──────►│  pacientes, agenda,      │
│  recetas, expedientes,   │        │  facturación, inventario │
│  agenda, inventario      │        │  (web browser en red LAN)│
└──────────────────────────┘        └──────────────────────────┘
              │                                │
              └─────────────────┬──────────────┘
                                ▼
                    ┌───────────────────────┐
                    │   REST API PHP puro   │
                    │   Bearer Token 24h    │
                    │   Rate Limit 60/min   │
                    └───────────┬───────────┘
                                ▼
                    ┌───────────────────────┐
                    │  MySQL/MariaDB 10.4   │
                    │  odonto_gest          │
                    │  ~36 tablas           │
                    └───────────────────────┘
```

**Regla de login (no negociable):** El backend determina el rol a partir de las credenciales. Nunca hay selector de rol en el cliente.

---

## Stack tecnológico

| Capa | Tecnología |
|------|-----------|
| App móvil | Flutter 3.x (Dart) — Android / iOS / Web / Windows |
| Panel web | PHP MVC puro sin frameworks |
| REST API | PHP — Bearer Token (base64) — TTL 24 h |
| Base de datos | MariaDB 10.4 vía XAMPP |
| Contraseñas | bcrypt cost 12 (`password_hash` / `password_verify`) |
| Seguridad API | Token expiry · Rate limiting 60 req/min/IP · XSS sanitización |

---

## Estructura del proyecto

```
Proyecto_Final/
├── odontogest/                              # App Flutter
│   ├── lib/
│   │   ├── main.dart                        # Entry point + LoginScreen
│   │   ├── core/
│   │   │   ├── constants/
│   │   │   │   ├── app_theme.dart           # Tokens: AppColors, AppTypography, AppGradients
│   │   │   │   ├── app_assets.dart          # Rutas de assets (imágenes placeholder)
│   │   │   │   └── app_strings.dart
│   │   │   ├── session/
│   │   │   │   └── app_session.dart         # Singleton con token, idUsuario, rol, nombre
│   │   │   └── widgets/
│   │   │       ├── app_card.dart
│   │   │       ├── gradient_app_bar.dart
│   │   │       └── status_badge.dart
│   │   ├── data/
│   │   │   └── services/
│   │   │       ├── auth_service.dart        # POST /auth/login → token + rol + nombre
│   │   │       ├── dashboard_service.dart   # GET métricas + citas del día
│   │   │       ├── expediente_service.dart  # Buscar pacientes, expediente, odontograma, recetas, tratamientos, fotos
│   │   │       ├── agenda_service.dart         # Listar citas por fecha, cambiar estado
│   │   │       ├── pacientes_service.dart      # Listar paginado con búsqueda
│   │   │       ├── facturacion_service.dart    # Listar facturas + resumen financiero
│   │   │       ├── inventario_service.dart     # Listar productos + alertas de stock
│   │   │       └── notificaciones_service.dart # Listar, marcar leída/todas, generar alertas
│   │   └── modules/
│   │       ├── seguridad/views/
│   │       │   ├── home_shell.dart             # BottomNav con 5 pestañas
│   │       │   ├── dashboard_screen.dart       # Métricas reales + citas del día + campana con badge
│   │       │   └── notificaciones_screen.dart  # Lista de alertas, marcar leída/todas
│   │       ├── agenda/views/
│   │       │   ├── agenda_screen.dart       # Agenda por fecha, filtros, cambio de estado
│   │       │   └── nueva_cita_screen.dart
│   │       ├── expedientes/views/
│   │       │   ├── buscar_paciente_screen.dart        # Búsqueda live con debounce
│   │       │   ├── expediente_paciente_screen.dart    # 5 tabs: Resumen/Odontograma/Recetas/Tratamientos/Fotos
│   │       │   ├── odontogram_screen.dart             # FDI, multi-condición por diente + brackets
│   │       │   └── pacientes_screen.dart              # Lista paginada de pacientes
│   │       ├── facturacion/views/
│   │       │   └── facturacion_screen.dart  # Facturas + resumen cobrado/pendiente
│   │       └── inventario/views/
│   │           └── inventario_screen.dart   # Productos + alertas stock bajo/agotado
│   └── pubspec.yaml
│
├── odontogest_api/                          # REST API PHP (htdocs/odontogest_api)
│   ├── core/
│   │   ├── db.php                           # PDO singleton a odonto_gest
│   │   ├── Response.php                     # ok() / error() helpers JSON
│   │   ├── Auth.php                         # getAuthUser() + corsHeaders()
│   │   └── RateLimit.php                    # 60 req/min/IP — file-based
│   ├── auth/
│   │   └── login.php                        # POST — bcrypt verify → token base64
│   ├── dashboard/
│   │   └── metricas.php                     # GET — KPIs del día
│   ├── citas/
│   │   └── hoy.php                          # GET — citas de hoy con paciente/odontólogo
│   ├── agenda/
│   │   ├── listar.php                       # GET ?fecha&estado — role-aware
│   │   └── cambiar_estado.php               # POST — actualiza estado/asistencia
│   ├── pacientes/
│   │   ├── listar.php                       # GET ?q&estado&page — paginado
│   │   └── buscar.php                       # GET ?q — búsqueda rápida expediente
│   ├── expediente/
│   │   ├── resumen.php                      # GET ?id_paciente — datos + conteos
│   │   ├── odontograma/
│   │   │   ├── get.php                      # GET ?id_expediente
│   │   │   └── guardar.php                  # POST — DELETE+INSERT por expediente
│   │   ├── recetas/
│   │   │   ├── listar.php                   # GET ?id_expediente
│   │   │   └── crear.php                    # POST
│   │   ├── tratamientos/
│   │   │   ├── listar.php                   # GET ?id_paciente
│   │   │   └── crear.php                    # POST
│   │   └── fotos/
│   │       ├── listar.php                   # GET ?id_expediente
│   │       └── subir.php                    # POST multipart — max 5 MB
│   ├── facturacion/
│   │   └── listar.php                       # GET ?estado&page — facturas + resumen
│   ├── inventario/
│   │   └── listar.php                       # GET ?q&stock_bajo — productos + resumen
│   ├── notificaciones/
│   │   ├── listar.php                       # GET ?solo_no_leidas=1 — lista + total_no_leidas
│   │   ├── marcar_leida.php                 # POST {id_notificacion} o {todas:true}
│   │   └── generar_citas.php               # POST — crea alertas citas hoy+mañana (idempotente)
│   ├── usuarios/
│   │   ├── crear.php                        # POST — para panel web
│   │   └── listar_roles.php                 # GET — catálogo de roles
│   └── tools/
│       └── generar_hash.php                 # Utilidad bcrypt — solo desarrollo
│
└── BD_OdontoGest/
    ├── odonto_gest_v3.sql                   # Schema completo + datos semilla
    ├── migracion_v3_1.sql                   # ADD: tabla recetas + expediente_fotos
    └── ER_OdontoGest_v3.png                 # Diagrama E-R
```

---

## Base de datos — módulos

| Bloque | Tablas principales |
|--------|-------------------|
| Seguridad | roles, permisos, usuarios, permisos_roles |
| Personal | cargo, especialidades, odontologos, empleados, horario_laboral |
| Catálogos clínicos | sangres, alergias, enfermedades, medicamentos, tratamientos |
| Pacientes | pacientes, expedientes, odontograma, tratamientos_historial |
| Recetas *(v3.1)* | recetas |
| Agenda | horarios, servicios, citas |
| Inventario | proveedores, kv_producto, producto, reportes_inventario |
| Facturación | sucursal, factura, detalle_factura, historial_facturacion |
| Sistema | kv_img, imagenes, expediente_fotos *(v3.1)*, auditoria, notificaciones, configuracion |

---

## API — Endpoints completos

> Base URL: `http://localhost/odontogest_api`  
> Todos los endpoints (excepto `/auth/login`) requieren `Authorization: Bearer <token>`.

### Autenticación

| Método | Ruta | Body / Params | Respuesta |
|--------|------|---------------|-----------|
| POST | `/auth/login.php` | `{usuario, contrasena}` | `{token, rol, nombre, id_usuario}` |

### Dashboard

| Método | Ruta | Params | Respuesta |
|--------|------|--------|-----------|
| GET | `/dashboard/metricas.php` | — | `{citas_hoy, atendidas, pendientes, pacientes_total}` |
| GET | `/citas/hoy.php` | — | `{citas:[{id_cita, hora, paciente, servicio, estado, id_paciente}]}` |

### Agenda

| Método | Ruta | Params | Descripción |
|--------|------|--------|-------------|
| GET | `/agenda/listar.php` | `fecha`, `estado` | Rol-aware: odontólogo ve solo las suyas |
| POST | `/agenda/cambiar_estado.php` | `{id_cita, estado, asistencia?}` | Actualiza estado de la cita |

### Pacientes

| Método | Ruta | Params | Descripción |
|--------|------|--------|-------------|
| GET | `/pacientes/listar.php` | `q`, `estado`, `page`, `limit` | Lista paginada |
| GET | `/pacientes/buscar.php` | `q` (mín 2 chars) | Búsqueda rápida — top 20 |

### Expediente clínico

| Método | Ruta | Params | Descripción |
|--------|------|--------|-------------|
| GET | `/expediente/resumen.php` | `id_paciente` | Resumen + alergias + conteos |
| GET | `/expediente/odontograma/get.php` | `id_expediente` | Piezas con array de condiciones |
| POST | `/expediente/odontograma/guardar.php` | `{id_expediente, dientes:{pieza:[condicion]}}` | DELETE + INSERT atómico |
| GET | `/expediente/recetas/listar.php` | `id_expediente` | — |
| POST | `/expediente/recetas/crear.php` | `{id_expediente, medicamento, dosis, frecuencia, duracion, notas?}` | — |
| GET | `/expediente/tratamientos/listar.php` | `id_paciente` | — |
| POST | `/expediente/tratamientos/crear.php` | `{id_paciente, id_tratamiento, descripcion?, fecha_inicio, costo}` | — |
| GET | `/expediente/fotos/listar.php` | `id_expediente` | URLs de imágenes |
| POST | `/expediente/fotos/subir.php` | `multipart: foto, id_expediente` | max 5 MB — jpg/png/webp/gif |

### Facturación

| Método | Ruta | Params | Descripción |
|--------|------|--------|-------------|
| GET | `/facturacion/listar.php` | `estado` (all/emitida/pagada/anulada), `page` | Facturas + resumen total_cobrado / total_pendiente |

### Inventario

| Método | Ruta | Params | Descripción |
|--------|------|--------|-------------|
| GET | `/inventario/listar.php` | `q`, `stock_bajo=1`, `estado`, `page` | Productos con nivel_stock (ok/bajo/agotado) |

### Notificaciones

| Método | Ruta | Params | Descripción |
|--------|------|--------|-------------|
| GET | `/notificaciones/listar.php` | `solo_no_leidas=1` | Lista + `total_no_leidas` para badge |
| POST | `/notificaciones/marcar_leida.php` | `{id_notificacion}` o `{todas:true}` | Marca una o todas como leídas |
| POST | `/notificaciones/generar_citas.php` | — | Genera alertas de citas hoy+mañana — idempotente, no duplica |

---

## Seguridad de la API

### Token Bearer
- Formato: `base64(id_usuario|rol|timestamp|random_hex)`
- TTL: 24 horas — validado en cada request en `core/Auth.php`
- `id_usuario` validado con `ctype_digit()` — no acepta valores no numéricos
- `rol` sanitizado con `htmlspecialchars(strip_tags())`

### Rate Limiting (`core/RateLimit.php`)
- 60 requests/minuto por IP
- Almacenado en archivos temporales del sistema (`sys_get_temp_dir()`)
- Responde HTTP 429 + header `Retry-After: 60` si se excede

### CORS
- `Access-Control-Allow-Origin: *` en todos los endpoints vía `corsHeaders()`
- Preflight OPTIONS respondido con 204

> **Nota para producción:** reemplazar el token base64 por JWT con firma HMAC-SHA256 y migrar rate limiting a Redis.

---

## Vistas Flutter — descripción

### `LoginScreen` (`main.dart`)
- Valida contra `/auth/login.php`
- Guarda token, rol, id y nombre en `AppSession.instance` (singleton in-memory)
- Navega a `HomeShell` según éxito

### `HomeShell`
- BottomNavigationBar: Dashboard · Agenda · Expedientes · Facturación · Inventario

### `DashboardScreen`
- Muestra métricas en tiempo real: citas hoy, atendidas, pendientes, total pacientes
- Lista de citas del día con navegación a `BuscarPacienteScreen`
- Pull-to-refresh con `RefreshIndicator`
- Header sin título duplicado — nombre y rol solo en el espacio expandido
- **Campana con badge rojo** en el AppBar — muestra conteo de notificaciones no leídas
- Al cargar llama a `generar_citas.php` automáticamente para crear alertas de citas del día y mañana
- Acceso rápido "Buscar Paciente" → navega a `BuscarPacienteScreen`

### `AgendaScreen`
- Date picker para navegar entre fechas
- Filtros por estado: Todas / Pendiente / Confirmada / Completada / Cancelada
- Cambio de estado + registro de asistencia desde bottom sheet
- Band de color lateral por estado en cada card

### `PacientesScreen`
- Lista paginada con lazy loading (scroll infinito)
- Búsqueda con debounce 400ms
- Filtro activo/inactivo/todos
- Toca cualquier paciente → `BuscarPacienteScreen`

### `BuscarPacienteScreen`
- Búsqueda live (mín 2 chars) contra `/pacientes/buscar.php`
- Navega a `ExpedientePacienteScreen(idPaciente, nombrePaciente)`

### `ExpedientePacienteScreen`
- 5 pestañas con carga lazy (solo llama a la API cuando la pestaña se activa)
  - **Resumen**: datos del paciente, alergias, enfermedades, conteos
  - **Odontograma**: `OdontogramScreen` embebido
  - **Recetas**: lista + creación en bottom sheet
  - **Tratamientos**: historial + añadir nuevo en bottom sheet
  - **Fotos**: galería + captura / selección de galería

### `OdontogramScreen`
- Notación FDI (11–48), 32 piezas visualizadas
- Multi-condición por diente: `Map<int, Set<ToothCondition>>`
- Condiciones: sano · caries · extracción · corona · obturación · ausente · implante · fractura · bracket
- Bracket coexiste con todas las demás condiciones (barra de color arriba/abajo del diente)
- Extracción/ausente limpia las otras condiciones al aplicarse
- Botón "Guardar" aparece solo cuando hay cambios (`_modified = true`)
- Carga desde API en `initState()`, guarda con DELETE+INSERT atómico

### `FacturacionScreen`
- Resumen: total facturas · cobrado · pendiente
- Filtros por estado, lista con banda de color lateral
- Muestra ISV (0%/15%/18%) y método de pago

### `InventarioScreen`
- Resumen: total productos · agotados · stock bajo · badge de alertas
- Búsqueda con debounce + filtro rápido "Stock bajo"
- Nivel de stock con color: verde (ok) · naranja (bajo) · rojo (agotado)

---

## Odontograma — condiciones y colores

| Condición | Color en UI | Descripción |
|-----------|-------------|-------------|
| sano | Verde | Sin tratamiento registrado |
| caries | Rojo | Caries activa |
| extraccion | Negro | Extracción indicada/realizada |
| corona | Azul | Corona protésica |
| obturacion | Amarillo | Restauración / obturación |
| ausente | Gris | Pieza ausente (congénita o perdida) |
| implante | Cyan | Implante dental |
| fractura | Naranja | Fractura del diente |
| bracket | Morado | Aparato de ortodoncia |

---

## Configuración inicial

### 1. Base de datos

```
HeidiSQL → root (sin contraseña) → Abrir archivo → F9
```

1. Ejecutar `BD_OdontoGest/odonto_gest_v3.sql` — crea toda la estructura base
2. Ejecutar `BD_OdontoGest/migracion_v3_1.sql` — agrega tablas `recetas` y `expediente_fotos`

### 2. API PHP en XAMPP

```cmd
:: CMD como Administrador
mklink /J "C:\xampp\htdocs\odontogest_api" "C:\Users\jhsir\OneDrive\Documentos\Movil_ll\Proyecto_Final\odontogest_api"
```

Verificar: `http://localhost/odontogest_api/auth/login.php`  
Debe responder: `{"success":false,"mensaje":"Método no permitido"}`

### 3. Flutter

```cmd
cd odontogest
flutter pub get
flutter run -d web-server --web-port 8080
```

---

## Credenciales de prueba

| Campo | Valor |
|-------|-------|
| Usuario | `JhairRios` |
| Contraseña | `JhairRios10` |
| Rol | Administrador |

---

## Cambiar URL de la API

Todos los services usan la constante `_kBase`. Para cambiar el entorno editar el valor en cada service o mover a un archivo de configuración central:

| Entorno | URL |
|---------|-----|
| Web / Windows desktop | `http://localhost/odontogest_api` |
| Emulador Android | `http://10.0.2.2/odontogest_api` |
| Dispositivo físico (WiFi) | `http://192.168.X.X/odontogest_api` |

---

## Gestión de contraseñas

```
# 1. Genera el hash
http://localhost/odontogest_api/tools/generar_hash.php?pass=NuevaContrasenia

# 2. Actualiza en HeidiSQL
UPDATE odonto_gest.usuarios
SET contrasena = '$2y$12$...'
WHERE usuario = 'JhairRios';
```

---

## Problemas comunes

### MySQL no arranca en XAMPP (error 0xc0000005)
Posible corrupción de tablas del sistema. Restaurar desde el backup incluido en XAMPP:
```cmd
xcopy /E /Y "C:\xampp\mysql\backup\*" "C:\xampp\mysql\data\mysql\"
```
Las bases de datos de usuario (odonto_gest, etc.) no se ven afectadas.

### Puerto 80 bloqueado por IIS
```cmd
:: CMD como Administrador
net stop w3svc & net stop was & net stop http /y
```

### Flutter: dispositivo Chrome no detectado
```cmd
flutter run -d web-server --web-port 8080
```

### Hot reload vs Hot restart
Después de convertir un `StatelessWidget` a `StatefulWidget`, usar **`R` mayúscula** (hot restart), no `r` (hot reload).

---

## Estado del proyecto

| Componente | Estado |
|------------|--------|
| Design tokens `app_theme.dart` | ✅ Completo |
| `AppSession` singleton | ✅ Completo |
| Login + `AuthService` | ✅ Completo |
| `HomeShell` BottomNav | ✅ Completo |
| `DashboardScreen` — datos reales | ✅ Completo |
| `AgendaScreen` — datos reales + cambio estado | ✅ Completo |
| `PacientesScreen` — paginado + búsqueda | ✅ Completo |
| `BuscarPacienteScreen` | ✅ Completo |
| `ExpedientePacienteScreen` 5 tabs | ✅ Completo |
| `OdontogramScreen` multi-condición + API | ✅ Completo |
| `FacturacionScreen` — datos reales | ✅ Completo |
| `InventarioScreen` — datos reales + alertas | ✅ Completo |
| `NotificacionesScreen` — alertas de citas + marcar leída | ✅ Completo |
| API `/notificaciones/*` (listar, marcar, generar) | ✅ Completo |
| API `/auth/login` | ✅ Completo |
| API `/dashboard/metricas` | ✅ Completo |
| API `/agenda/*` | ✅ Completo |
| API `/pacientes/*` | ✅ Completo |
| API `/expediente/*` (resumen, odontograma, recetas, tratamientos, fotos) | ✅ Completo |
| API `/facturacion/listar` | ✅ Completo |
| API `/inventario/listar` | ✅ Completo |
| Seguridad: token expiry 24h | ✅ Completo |
| Seguridad: rate limiting 60 req/min | ✅ Completo |
| Seguridad: XSS sanitización | ✅ Completo |
| BD `odonto_gest` — ~36 tablas | ✅ Completo |
| `migracion_v3_1.sql` — recetas + expediente_fotos | ✅ Completo |
| Imágenes placeholder (6 assets) | ✅ Completo |
| Panel web PHP MVC | ⏳ Pendiente (siguiente fase) |
| CSRF tokens en API | ⏳ Pendiente |
| PWA / notificaciones push | ⏳ Pendiente |
