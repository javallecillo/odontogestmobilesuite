# OdontoGest — Documento de Presentación
**Proyecto Final · Desarrollo Móvil II**  
Universidad / Carrera: Ingeniería en Sistemas  
Estudiante: Jhair Ríos · 2025

---

## 1. Descripción del proyecto

OdontoGest es un sistema de gestión integral para clínicas odontológicas, desarrollado como proyecto final de la materia de Desarrollo Móvil II. El objetivo fue implementar una solución completa con tres componentes conectados: una aplicación web MVC para administración clínica, una REST API como capa intermedia, y una aplicación Flutter dirigida a los odontólogos para uso en consulta.

El sistema resuelve la problemática real de las clínicas dentales pequeñas que gestionan pacientes, citas, expedientes e inventario con herramientas desconectadas (papel, Excel, WhatsApp), integrando todo en una plataforma centralizada y segura.

---

## 2. Arquitectura del sistema

```
┌─────────────────────┐       HTTP/JSON        ┌──────────────────────┐
│   odontogest_web    │ ──── (uso interno) ───▶ │   odontogest_api     │
│  PHP MVC (web)      │                         │   REST API PHP       │
│  Apache / XAMPP     │                         │   Bearer Token Auth  │
└─────────────────────┘                         └──────────────────────┘
                                                          ▲
                                                          │ HTTP/JSON
                                                          │ Bearer Token
                                                 ┌────────┴─────────┐
                                                 │   odontogest     │
                                                 │  Flutter Web App │
                                                 │  localhost:8080  │
                                                 └──────────────────┘
                                                          │
                                             ┌────────────▼─────────────┐
                                             │     MySQL / MariaDB      │
                                             │     odonto_gest (BD)     │
                                             └──────────────────────────┘
```

### Decisiones de arquitectura

**PHP MVC puro (sin framework):** Se optó por implementar el patrón MVC sin usar Laravel ni Symfony para demostrar comprensión del patrón de diseño. El router personalizado (`JRouter`) mapea URLs a controllers, y cada módulo tiene su Controller y Model independiente. La lógica de negocio vive exclusivamente en los Models; los Controllers solo coordinan.

**REST API separada:** La aplicación web accede directamente a la BD, pero la app Flutter consume exclusivamente la API REST. Esta separación garantiza que la capa móvil nunca acceda a la BD directamente, protege las credenciales y permite escalar el backend de forma independiente.

**Flutter Web:** Se eligió Flutter Web en lugar de Android nativo para que la app del odontólogo funcione en cualquier dispositivo (tablet, laptop, smartphone) desde el navegador, sin necesidad de instalar APK ni permisos de distribución.

---

## 3. Stack tecnológico

| Capa | Tecnología | Versión |
|---|---|---|
| Servidor web | Apache (XAMPP) | 8.x |
| Backend web | PHP (MVC puro, sin framework) | 8.2 |
| Backend API | PHP REST | 8.2 |
| Base de datos | MySQL / MariaDB | 8.0 / 10.6 |
| App móvil/web | Flutter (Dart) | SDK 3.9.2 |
| HTTP client (Flutter) | package:http | ^1.2.2 |
| Selector de imágenes | image_picker | ^1.1.2 |
| Frontend web | HTML5, CSS3, JavaScript vanilla | — |
| Autenticación API | Bearer Token (base64, TTL 24h) | — |

---

## 4. Módulos implementados

### 4.1 Aplicación web (`odontogest_web`)

16 Controllers · 14 Models · 20+ Views

| Módulo | Controller | Model | Estado |
|---|---|---|---|
| Autenticación | AuthController | — | ✅ Completo |
| Dashboard | DashboardController | DashboardModel | ✅ Métricas en tiempo real |
| Pacientes | PacientesController | PacientesModel | ✅ CRUD completo |
| Expedientes | ExpedientesController | ExpedientesModel | ✅ Historial clínico |
| Agenda / Citas | AgendaController | AgendaModel | ✅ Con validación de colisión |
| Odontólogos | OdontologosController | OdontologosModel | ✅ CRUD completo |
| Servicios | ServiciosController | ServiciosModel | ✅ Catálogo |
| Facturación | FacturacionController | FacturacionModel | ✅ ISV 0/15/18% |
| Inventario | InventarioController | InventarioModel | ✅ Stock + alertas |
| Reportes | ReportesController | ReportesModel | ✅ PDF vía navegador |
| Usuarios | UsuariosController | UsuarioModel | ✅ CRUD + hash SHA-256 |
| Roles | RolesController | RolesModel | ✅ RBAC por módulo |
| Configuración | ConfiguracionController | ConfiguracionModel | ✅ 8 secciones |
| Auditoría | AuditoriaController | AuditoriaModel | ✅ Log completo |
| Notificaciones | NotificacionesController | — | ✅ Alertas stock/citas |
| Perfil | PerfilController | PerfilModel | ✅ Editar perfil |

### 4.2 REST API (`odontogest_api`)

32 endpoints organizados en 10 grupos:

```
auth/
  login.php           POST  Autenticación → devuelve Bearer Token

agenda/
  listar.php          GET   Citas por fecha y estado (filtros)
  crear.php           POST  Nueva cita con validación de colisión
  cambiar_estado.php  POST  Cambiar estado + asistencia
  odontologos.php     GET   Lista odontólogos activos
  slots_disponibles.php GET Disponibilidad horaria por día

citas/
  hoy.php             GET   Citas del día actual

dashboard/
  metricas.php        GET   KPIs para pantalla principal

expediente/
  resumen.php         GET   Datos del paciente + contadores
  odontograma/
    get.php           GET   Condiciones por pieza dental
    guardar.php       POST  Guardar estado del odontograma
  recetas/
    listar.php        GET   Recetas del expediente
    crear.php         POST  Nueva receta
  tratamientos/
    listar.php        GET   Historial de tratamientos
    crear.php         POST  Nuevo tratamiento
    catalogo.php      GET   Catálogo de tipos de tratamiento
  fotos/
    listar.php        GET   Imágenes del expediente
    subir.php         POST  Upload multipart (web-compatible XFile)

facturacion/
  listar.php          GET   Facturas del sistema

inventario/
  listar.php          GET   Productos con niveles de stock

notificaciones/
  listar.php          GET   Notificaciones del usuario
  marcar_leida.php    POST  Marcar como leída
  generar_citas.php   POST  Generar alertas de citas próximas

pacientes/
  listar.php          GET   Lista paginada de pacientes activos
  buscar.php          GET   Búsqueda por nombre/DNI/teléfono

usuarios/
  crear.php           POST  Crear usuario desde Flutter
  listar_roles.php    GET   Roles disponibles

tools/
  generar_hash.php    GET   Utilidad SHA-256 para passwords
```

### 4.3 Aplicación Flutter (`odontogest`)

13 pantallas · 3 services · módulos organizados por feature

| Pantalla | Módulo | Función |
|---|---|---|
| Login | seguridad | Autenticación + guardado de token |
| Dashboard | seguridad | Métricas del día |
| Home Shell | seguridad | Navegación principal (bottom bar) |
| Perfil | seguridad | Ver y editar perfil de usuario |
| Notificaciones | seguridad | Alertas del sistema |
| Crear Usuario | seguridad | Formulario nuevo usuario |
| Agenda | agenda | Lista de citas del día + cambio de estado |
| Nueva Cita | agenda | Flujo stepper de 4 pasos conectado a API |
| Buscar Paciente | expedientes | Buscador con navegación al expediente |
| Pacientes | expedientes | Lista de pacientes |
| Expediente | expedientes | 5 pestañas: Resumen, Odontograma, Recetas, Tratamientos, Fotos |
| Odontograma | expedientes | Canvas interactivo 32 piezas |
| Facturación | facturacion | Lista de facturas |
| Inventario | inventario | Lista de productos con stock |

---

## 5. Diseño de base de datos

### Tablas principales (22 tablas)

```
pacientes           → Datos personales + contacto emergencia + responsable_pago
usuarios            → nombre_completo, username, password_hash, id_rol
roles               → RBAC con permisos por módulo
odontologos         → Perfil clínico + FK a usuarios + especialidades
especialidades      → Catálogo de especialidades
expedientes         → 1:1 con pacientes, historial clínico
odontograma         → Por pieza dental + condiciones JSON
recetas             → Medicamento, dosis, frecuencia, duración
historial_tratamientos → FK a tratamientos (catálogo) + costo + estado
tratamientos        → Catálogo con precio_base
fotos_expediente    → URL + descripción + FK expediente
citas               → FK paciente + odontólogo + horario + estado + asistencia
horarios            → Slots por odontólogo + día + hora
servicios           → Catálogo de servicios con precio
facturas            → Cabecera con FK paciente + totales + ISV
detalle_factura     → Líneas de ítem con cantidad + precio
inventario          → Producto + stock + proveedor + tasa_impuesto
proveedores         → Catálogo de proveedores
movimientos_inventario → Log de entradas/salidas/ajustes
configuracion       → KV store para settings del sistema
notificaciones      → Alertas por usuario
auditoria           → Log de acciones con módulo + usuario + detalle
```

### Stored Procedures implementados

```sql
sp_pagos_registrar_con_cuenta   -- Registrar pago con actualización de cuenta
sp_dashboard_metricas            -- KPIs del dashboard (citas, pacientes, ingresos)
sp_planes_obtener_por_id         -- (heredado del sistema base)
```

### Diagramas

El archivo `BD_OdontoGest/ER_OdontoGest_v3.png` contiene el diagrama Entidad-Relación completo del sistema.

---

## 6. Seguridad implementada

### Autenticación web (sesiones PHP)

- Login con verificación de `password_hash` SHA-256.
- Sesión PHP con TTL configurable.
- Protección de rutas: cada Controller verifica sesión activa antes de ejecutar.
- RBAC: el rol del usuario determina qué módulos puede ver y qué acciones puede ejecutar.

### Autenticación API (Bearer Token)

```
POST /auth/login.php
  → Valida usuario + hash
  → Genera token = base64(userId:hash:timestamp:salt)
  → Almacena en tabla sesiones con TTL 24 horas
  → Devuelve { token, expires_at, rol, nombre_completo }

Endpoints protegidos:
  → Auth.php extrae token del header Authorization
  → Fix Apache: RewriteRule en .htaccess para pasar Authorization header
     (sin este fix, Apache elimina el header antes de que PHP lo reciba)
  → Verifica token en BD, TTL y usuario activo
```

### Rate Limiting

`core/RateLimit.php` implementado: límite de intentos por IP con ventana temporal. Aplicado en el endpoint de login para prevenir ataques de fuerza bruta.

### Auditoría

Toda acción de creación, modificación o eliminación en la app web queda registrada en la tabla `auditoria` con: usuario, módulo, acción, registro afectado, IP y timestamp.

---

## 7. Funcionalidades destacadas

### Validación de colisión de citas (doble validación)

El endpoint `agenda/crear.php` y el `AgendaModel.php` de la app web validan de forma independiente que no existan dos citas para el mismo odontólogo en el mismo horario. La validación excluye citas canceladas y no_asistio. Al crear desde Flutter, la API valida en el servidor, no solo en el cliente.

### Odontograma interactivo

Pantalla con canvas de 32 piezas dentales (numeración estándar FDI). Cada pieza acepta múltiples condiciones simultáneas (caries, corona, puente, implante, extracción, bracket, etc.). Los datos se sincronizan con `expediente/odontograma/guardar.php`.

### Upload de fotos web-compatible

En Flutter Web, `dart:io File` no está disponible. La solución usa `image_picker: ^1.1.2` con `XFile` y `readAsBytes()` para construir un `MultipartFile.fromBytes()`. Esto funciona tanto en navegador como en mobile sin cambiar el código.

### Catálogo de tratamientos con fallback

`fetchCatalogTratamientos()` llama al endpoint `/expediente/tratamientos/catalogo.php`. Si la API falla o la tabla está vacía, cae a una lista estática de 12 tratamientos comunes, garantizando que el formulario siempre funcione en desarrollo.

### Nueva cita — flujo multi-paso (Stepper)

`nueva_cita_screen.dart` implementa un stepper de 4 pasos conectado 100% a la API:
1. Selección de odontólogo (cargado desde `/agenda/odontologos.php`)
2. Selección de fecha (CalendarDatePicker, máximo +90 días)
3. Selección de horario (grid de slots con disponibilidad real desde `/agenda/slots_disponibles.php`)
4. Selección de paciente (búsqueda local sobre lista cargada) + notas + confirmación

### Inventario con alertas de stock

Los productos tienen `stock_minimo`. El sistema calcula automáticamente el nivel de alerta y lo muestra con color en la tabla (verde/amarillo/rojo). El dashboard muestra el conteo de productos en stock crítico.

---

## 8. Patrones de diseño aplicados

| Patrón | Dónde |
|---|---|
| MVC | Toda la app web (Controller → Model → View) |
| Repository (implícito) | Cada Model encapsula las queries de su entidad |
| Singleton | `AppSession` en Flutter (token y datos de sesión) |
| Service Layer | `AgendaService`, `ExpedienteService` en Flutter |
| Factory | `fromJson()` en cada modelo Dart (CitaAgenda, Receta, Tratamiento, etc.) |
| Fallback / Graceful Degradation | Catálogo de tratamientos con lista estática si la API falla |
| Stepper / Wizard | NuevaCitaScreen — flujo guiado multi-paso |

---

## 9. Audit del proyecto — Estado actual

### Completado ✅

| Componente | Detalle |
|---|---|
| App web — 16 módulos | Todos los Controllers, Models y Views implementados |
| API — 32 endpoints | Auth, Agenda, Expediente, Dashboard, Pacientes, Notificaciones |
| Flutter — 13 pantallas | Agenda, Expediente, Odontograma, Recetas, Tratamientos, Fotos |
| BD + Migrations | odonto_gest_v3.sql + stored_procedures.sql + 2 migraciones |
| RBAC completo | Roles con permisos por módulo en app web |
| Bearer Token auth | Con fix de Apache `.htaccess` para Authorization header |
| Rate limiting | `core/RateLimit.php` (aplicado en login) |
| Auditoría | Log completo de acciones en la app web |
| Inventario | Proveedores reales + tasa_impuesto real (corregido) |
| Pacientes | 5 campos faltantes corregidos (estado_civil, ocupación, contacto emergencia) |
| Fotos expediente | Upload multipart web-compatible con XFile |
| Nueva cita Flutter | Stepper 4 pasos 100% conectado a API |
| Catálogo tratamientos | Endpoint + fallback estático |

### Pendiente / Deuda técnica ⚠️

| Ítem | Prioridad | Detalle |
|---|---|---|
| `facturacion/crear.php` API | Alta | El módulo de facturación en Flutter solo puede listar; falta el endpoint de creación |
| `inventario/crear.php` y `actualizar.php` API | Alta | Mismo caso: Flutter solo puede consultar stock, no crear ni editar productos |
| CSRF tokens en app web | Media | Todos los formularios web requieren token anti-CSRF; no implementado |
| PWA (Progressive Web App) | Baja | `manifest.json` + Service Worker para instalar la Flutter app como PWA |
| Notificaciones en tiempo real | Baja | Actualmente son polling; pendiente WebSocket o Server-Sent Events |
| Tests automatizados | Media | No se implementaron pruebas unitarias ni de integración |
| Paginación en API | Media | `pacientes/listar.php` tiene `limit=100` hardcodeado; para producción requiere paginación real |

---

## 10. Instrucciones de instalación rápida

```bash
# 1. Clonar/copiar los tres proyectos a htdocs
cp -r odontogest_web  C:/xampp/htdocs/
cp -r odontogest_api  C:/xampp/htdocs/

# 2. Importar BD en phpMyAdmin
# → Crear BD: odonto_gest
# → Importar: BD_OdontoGest/odonto_gest_v3.sql
# → Importar: BD_OdontoGest/stored_procedures.sql

# 3. Configurar app web
# Editar: odontogest_web/Config/Define.php
# → DB_HOST, DB_NAME, DB_USER, DB_PASS, APP_URL

# 4. Instalar dependencias Flutter
cd odontogest
flutter pub get

# 5. Ejecutar app Flutter
flutter run -d chrome

# 6. Acceder
# Web:    http://localhost/odontogest_web/
# API:    http://localhost/odontogest_api/
# Flutter: http://localhost:8080
```

---

## 11. Estructura de carpetas

```
Proyecto_Final/
├── BD_OdontoGest/
│   ├── odonto_gest_v3.sql          ← Esquema completo (22 tablas)
│   ├── stored_procedures.sql       ← 3 stored procedures
│   ├── Odonto_Gest_v3_migration.sql
│   ├── migracion_v3_1.sql
│   └── ER_OdontoGest_v3.png        ← Diagrama ER
│
├── odontogest_web/                  ← App web PHP MVC
│   ├── Config/                     ← Define.php, DB.php
│   ├── Controllers/                ← 16 controllers
│   ├── Models/                     ← 14 models
│   ├── Views/                      ← 20+ vistas (HTML + PHP)
│   ├── Core/                       ← JRequest, JRouter, JAuth
│   └── index.php                   ← Entry point (router)
│
├── odontogest_api/                  ← REST API PHP
│   ├── core/                       ← Auth.php, RateLimit.php, Response.php, db.php
│   ├── auth/                       ← login.php
│   ├── agenda/                     ← 5 endpoints
│   ├── expediente/                 ← 9 endpoints
│   ├── facturacion/                ← listar.php
│   ├── inventario/                 ← listar.php
│   ├── notificaciones/             ← 3 endpoints
│   ├── pacientes/                  ← 2 endpoints
│   ├── usuarios/                   ← 2 endpoints
│   ├── dashboard/                  ← metricas.php
│   ├── tools/                      ← generar_hash.php
│   └── .htaccess                   ← Fix Authorization header Apache
│
├── odontogest/                      ← Flutter Web App
│   ├── lib/
│   │   ├── core/                   ← AppConfig, AppSession, theme
│   │   ├── data/services/          ← AgendaService, ExpedienteService
│   │   └── modules/
│   │       ├── agenda/             ← AgendaScreen, NuevaCitaScreen
│   │       ├── expedientes/        ← ExpedienteScreen (5 tabs), Odontograma, Buscar
│   │       ├── seguridad/          ← Login, Dashboard, HomeShell, Perfil, Notificaciones
│   │       ├── facturacion/        ← FacturacionScreen
│   │       └── inventario/         ← InventarioScreen
│   └── pubspec.yaml
│
├── MANUAL_USUARIO.md                ← Manual de uso del sistema
└── PRESENTACION_INGE.md             ← Este documento
```

---

## 12. Conclusiones

OdontoGest demuestra la implementación de un sistema full-stack real compuesto por tres capas tecnológicas distintas (PHP MVC web, PHP REST API, Flutter) trabajando sobre una misma base de datos MySQL relacional.

Los retos técnicos más importantes resueltos fueron la compatibilidad de `image_picker` con Flutter Web (usando `XFile` y `readAsBytes()` en lugar de `dart:io File`), el problema del header `Authorization` de Apache que requirió una regla `.htaccess` para preservarlo, y la sincronización del estado entre la app web y la app Flutter a través de la misma BD.

El sistema está listo para demostración funcional en entorno local con XAMPP y puede ser extendido con los endpoints faltantes de facturación e inventario para la API, y con CSRF tokens y PWA para llegar a un nivel de producción real.

---

*OdontoGest — Proyecto Final Desarrollo Móvil II · 2025*
