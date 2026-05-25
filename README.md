# 🍽️ Miss Whitney · Documentación Técnica Completa

> **Sistema web de un bar-restaurante** con dos áreas: una web pública para clientes (reservas, contacto, solicitud de facturas) y un panel privado de administración para gestionar todas las facturas. Construido con **Laravel 12 + PHP 8.2 + MySQL** sobre arquitectura MVC tradicional renderizada con Blade.

---

## 📑 Índice

1. [Visión general del proyecto](#1-visión-general-del-proyecto)
2. [Mapa global del sistema](#2-mapa-global-del-sistema)
3. [Estructura completa de carpetas](#3-estructura-completa-de-carpetas)
4. [Funcionalidades del sistema](#4-funcionalidades-del-sistema)
   - 4.1 [Solicitud de factura por el cliente (con OCR)](#41-solicitud-de-factura-por-el-cliente-con-ocr-)
   - 4.2 [Panel de administración de facturas](#42-panel-de-administración-de-facturas-)
   - 4.3 [Generación y envío de PDF de factura](#43-generación-y-envío-de-pdf-de-factura-)
   - 4.4 [Sistema de reseñas Google Places](#44-sistema-de-reseñas-google-places-)
   - 4.5 [Formulario de reservas](#45-formulario-de-reservas-)
   - 4.6 [Formulario de contacto](#46-formulario-de-contacto-)
   - 4.7 [Banner de cookies (RGPD)](#47-banner-de-cookies-rgpd-)
   - 4.8 [Modal global Reservar/Factura](#48-modal-global-reservarfactura-)
5. [Base de datos](#5-base-de-datos)
6. [APIs externas y servicios](#6-apis-externas-y-servicios)
7. [Frontend](#7-frontend)
8. [Autenticación y seguridad](#8-autenticación-y-seguridad)
9. [Flujos importantes detallados](#9-flujos-importantes-detallados)
10. [Métodos importantes](#10-métodos-importantes)
11. [Dependencias](#11-dependencias)
12. [Configuración y despliegue](#12-configuración-y-despliegue)
13. [Mejoras futuras](#13-mejoras-futuras)

---

## 1. Visión general del proyecto

### ¿Qué hace?

Web profesional del restaurante **Miss Whitney** (Huelva) que permite:

| Función | Quién la usa |
|---|---|
| Consultar la web (galería, contacto, dónde estamos, horarios) | Clientes |
| Reservar mesa (formulario que envía email al restaurante) | Clientes |
| Mandar mensaje de contacto general | Clientes |
| **Solicitar factura subiendo foto del ticket → OCR automático → PDF generado** | Clientes |
| **Gestionar todas las facturas** (crear, editar, cambiar estado, enviar por email, descargar PDF) | Administradores |
| Ver reseñas reales de Google Places integradas en el index | Clientes |

### Tipo de usuarios

- **Cliente anónimo (público)**: navega, reserva, contacta, pide factura. No requiere registro.
- **Administrador**: accede al panel en `/admin` para gestionar facturas. Actualmente **sin autenticación** (acceso público a la URL — pendiente añadir capa de seguridad).

### Tecnologías utilizadas

| Capa | Tecnología |
|---|---|
| Backend | **Laravel 12** sobre **PHP 8.2** |
| Base de datos | **MySQL 5.7+** (compatible con MariaDB) |
| Renderizado | **Blade** (server-side templates) |
| PDF | **DomPDF** (`barryvdh/laravel-dompdf` v3) |
| OCR de tickets | **Google Cloud Vision API** (`DOCUMENT_TEXT_DETECTION`) |
| Reseñas | **Google Places API** (Place Details) |
| Email | **SMTP Gmail** (`smtp.gmail.com:587` TLS) |
| Front | **HTML + CSS + JS vanilla** (sin frameworks; Vite presente pero infrautilizado) |
| Build tool | **Vite 7** + **Tailwind 4** (preparados pero opcionales) |
| Cache / sesiones | Driver `file` (almacena en `storage/framework/`) |

### Arquitectura general

Patrón **MVC clásico de Laravel**:

```
Cliente HTTP  →  Router (routes/web.php)  →  Controller  →  Model (Eloquent)  →  MySQL
                                                  ↓
                                            View (Blade)  →  HTML al cliente
```

No usa Inertia, Livewire, ni API RESTful headless. Las "API" del proyecto son rutas `POST/GET` de Laravel que devuelven JSON, consumidas por JavaScript `fetch()` desde las propias vistas Blade — un híbrido **MVC + endpoints AJAX**.

### Cómo se organiza

```
mw_laravelWeb/
├── app/Http/Controllers/   ← 7 controladores (1 por área funcional)
├── app/Models/             ← 3 modelos Eloquent
├── app/Mail/               ← 1 Mailable (FacturaMail)
├── database/migrations/    ← 8 migraciones (3 propias del dominio + 2 de modificación + 3 base Laravel)
├── routes/web.php          ← TODAS las rutas (web + api)
├── resources/views/
│   ├── layouts/app.blade.php       ← Layout compartido (header, footer, dock móvil, modales, cookies)
│   ├── cliente/                    ← 7 vistas públicas
│   ├── admin/index.blade.php       ← Panel admin (1 sola vista, SPA-like vía JS)
│   ├── emails/factura.blade.php    ← Plantilla del email de factura
│   └── pdf/factura.blade.php       ← Plantilla del PDF de factura
└── public/
    ├── js/admin.js                 ← Lógica completa del panel admin (~820 líneas)
    ├── css/admin.css + shared.css  ← Estilos del proyecto
    └── images/logo.png             ← Logo del restaurante
```

---

## 2. Mapa global del sistema

### Flujo de una petición típica

```txt
┌─────────────────────────────────────────────────────────────────┐
│                    Navegador del cliente                        │
│  GET /solicitar-factura  /  POST /api/ocr/ticket  / etc.       │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│             public/index.php (Front controller)                 │
│  Bootstrap Laravel → bootstrap/app.php                          │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│                   routes/web.php (Router)                       │
│  Matchea URL + método → resuelve controlador y método           │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│           Middleware global de Laravel (CSRF, sesión)           │
│  Verifica token CSRF en POST (excepto endpoints público GET)    │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│      app/Http/Controllers/XxxController.php · método()         │
│  · Valida ($request->validate(...))                             │
│  · Llama a Model::create() / Model::find() / DB::query()        │
│  · Llama a servicios externos (Vision, Places, Mail)            │
│  · Genera PDF (DomPDF) o JSON                                   │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                ┌──────────┴──────────┐
                ▼                     ▼
       ┌────────────────┐    ┌────────────────┐
       │  Eloquent      │    │  HTTP Client   │
       │  → MySQL       │    │  → Google APIs │
       └────────────────┘    └────────────────┘
                │                     │
                └──────────┬──────────┘
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│  Response: vista Blade renderizada  /  JSON  /  PDF binario     │
└─────────────────────────────────────────────────────────────────┘
```

### Mapa de archivos por funcionalidad

```
┌────────────────────┬───────────────────────────────────────────────────────┐
│ FUNCIONALIDAD      │ ARCHIVOS IMPLICADOS                                   │
├────────────────────┼───────────────────────────────────────────────────────┤
│ Solicitud factura  │ SolicitudController + OcrController                   │
│                    │  + SolicitudFactura + Factura models                  │
│                    │  + cliente/solicitar-factura.blade.php (3 pasos)      │
│                    │  + pdf/factura.blade.php (plantilla DomPDF)           │
│                    │  + Google Vision API                                  │
├────────────────────┼───────────────────────────────────────────────────────┤
│ Panel admin        │ FacturaController (8 métodos CRUD)                    │
│                    │  + admin/index.blade.php (HTML estático)              │
│                    │  + public/js/admin.js (lógica completa, ~820 líneas)  │
│                    │  + FacturaMail (envío email con PDF adjunto)          │
├────────────────────┼───────────────────────────────────────────────────────┤
│ Reservas           │ ReservaController + cliente/reservar.blade.php        │
│                    │  + SMTP Gmail (no se guarda en BD, solo email)        │
├────────────────────┼───────────────────────────────────────────────────────┤
│ Contacto           │ ContactoController + cliente/contacto.blade.php       │
│                    │  + SMTP Gmail (no se guarda en BD)                    │
├────────────────────┼───────────────────────────────────────────────────────┤
│ Reseñas Google     │ ResenasController + Cache (12h)                       │
│                    │  + Google Places API (multi-idioma)                   │
│                    │  + cliente/index.blade.php (render JS)                │
├────────────────────┼───────────────────────────────────────────────────────┤
│ Layout global      │ layouts/app.blade.php (~773 líneas):                  │
│                    │  · Topbar desktop                                     │
│                    │  · Mob-header móvil                                   │
│                    │  · Dock inferior móvil                                │
│                    │  · Footer multi-columna                               │
│                    │  · Banner cookies RGPD                                │
│                    │  · Modal global iframe (Reservar/Factura)             │
│                    │  · Loader animado                                     │
│                    │  · IntersectionObserver scroll-reveal                 │
└────────────────────┴───────────────────────────────────────────────────────┘
```

---

## 3. Estructura completa de carpetas

### `/app/Http/Controllers/`

Controladores HTTP. Cada uno maneja un área funcional del proyecto. **No hay servicios separados**: la lógica de negocio vive directamente en los controladores.

| Archivo | Responsabilidad | Métodos públicos |
|---|---|---|
| `Controller.php` | Clase base de Laravel (vacía) | — |
| `SolicitudController.php` | Recibe la solicitud de factura del cliente, valida, sube ticket, genera PDF | `index()`, `store(Request)`, `generarReferencia(serie)` |
| `OcrController.php` | Procesa la imagen del ticket con Google Vision API y devuelve datos estructurados | `procesarTicket(Request)` + helpers privados |
| `FacturaController.php` | Panel admin: listar, crear, actualizar, cambiar estado, eliminar, descargar PDF, enviar email | `index()`, `listar()`, `crear()`, `actualizar()`, `cambiarEstado()`, `eliminar()`, `descargarPdf()`, `enviarPorEmail()` |
| `ResenasController.php` | Consulta reseñas reales del restaurante en Google Places | `index()` |
| `ContactoController.php` | Procesa el formulario de contacto del cliente y envía email al restaurante | `enviar(Request)` |
| `ReservaController.php` | Procesa el formulario de reserva y envía email formateado al restaurante | `enviar(Request)` |

### `/app/Models/`

Modelos Eloquent. Tres en total:

| Modelo | Tabla | Relaciones |
|---|---|---|
| `User.php` | `users` | Modelo base Laravel (no se usa en el dominio, viene de Breeze/scaffold) |
| `SolicitudFactura.php` | `solicitudes_factura` | `hasOne(Factura)` |
| `Factura.php` | `facturas` | `belongsTo(SolicitudFactura)` |

> **Importante**: el modelo `User` está pero **no se utiliza** en ninguna parte del flujo de negocio actual. Probablemente se preparó para una futura zona admin autenticada.

### `/app/Mail/`

Mailables.

| Archivo | Tipo | Plantilla | Adjuntos |
|---|---|---|---|
| `FacturaMail.php` | Mailable con `Queueable + SerializesModels` | `emails/factura.blade.php` | PDF de la factura |

### `/database/migrations/`

| Migración | Fecha | Crea/Altera |
|---|---|---|
| `0001_01_01_000000_create_users_table.php` | base | `users`, `password_reset_tokens`, `sessions` (scaffold Laravel) |
| `0001_01_01_000001_create_cache_table.php` | base | `cache`, `cache_locks` (caché en BD opcional) |
| `0001_01_01_000002_create_jobs_table.php` | base | `jobs`, `job_batches`, `failed_jobs` |
| `2024_01_01_000010_create_contador_referencias_table.php` | dominio | Tabla `contador_referencias` (genera referencias MW-YYYY-0001) |
| `2024_01_01_000011_create_solicitudes_factura_table.php` | dominio | Tabla `solicitudes_factura` |
| `2024_01_01_000012_create_facturas_table.php` | dominio | Tabla `facturas` con FK a `solicitudes_factura` |
| `2026_05_09_000001_add_atendido_por_to_solicitudes_factura.php` | parche | Añade columna `atendido_por` (detectada del OCR) |
| `2026_05_09_000002_add_lineas_ticket_to_facturas.php` | parche | Añade columna JSON `lineas_ticket` |

> **Patrón defensivo**: todas las migraciones del dominio usan `if (!Schema::hasTable(...))` y `if (!Schema::hasColumn(...))` antes de crear/alterar. Esto permite **re-ejecutarlas sin riesgo** y convivir con datos creados por scripts PHP puros anteriores al uso de Eloquent.

### `/routes/`

| Archivo | Contenido |
|---|---|
| `web.php` | **Todas** las rutas del proyecto (web + api) — Laravel 12 unifica el router |
| `console.php` | Comandos Artisan personalizados (vacío) |

### `/resources/views/`

| Carpeta/archivo | Propósito |
|---|---|
| `layouts/app.blade.php` | Layout compartido por **todas** las vistas cliente. 773 líneas. Contiene topbar, dock móvil, mob-header, footer, banner cookies, modal global, JS de animaciones, loader |
| `cliente/index.blade.php` | Home: hero + carrusel de platos + reseñas Google + cómo llegar |
| `cliente/galeria.blade.php` | Grid de platos + placeholder Instagram/Facebook + CTA reservar |
| `cliente/reservar.blade.php` | Formulario de reserva + modal "Información útil" |
| `cliente/contacto.blade.php` | Card de información + formulario mensaje + mapa + horarios |
| `cliente/solicitar-factura.blade.php` | Formulario 3 pasos: datos fiscales / ticket+OCR / confirmar (945 líneas) |
| `cliente/carta.blade.php` | Carta del restaurante (no enlazada actualmente desde nav) |
| `cliente/politica-cookies.blade.php` | Política legal de cookies |
| `admin/index.blade.php` | Panel admin: sidebar + topbar + tabla facturas + modal crear/editar. 360 líneas HTML, la lógica está en `public/js/admin.js` |
| `emails/factura.blade.php` | Plantilla HTML del email de factura |
| `pdf/factura.blade.php` | Plantilla DomPDF de la factura (formato A4, IVA desglosado, líneas, totales) |

### `/public/`

| Archivo | Propósito |
|---|---|
| `index.php` | Front controller Laravel |
| `js/admin.js` | **820 líneas** de JS vanilla para el panel admin (CRUD completo, filtros, ordenación, búsqueda, paginación, OCR, modal, export CSV, envío email) |
| `css/shared.css` | Estilos comunes (topbar, mob-header, dock, footer, cookies, modal, animaciones) |
| `css/admin.css` | Estilos exclusivos del panel admin |
| `images/logo.png` | Logo Miss Whitney |
| `favicon.ico` | Icono pestaña navegador |

### `/storage/`

| Subcarpeta | Contenido |
|---|---|
| `app/public/tickets/` | Imágenes de tickets subidas por clientes (después de `php artisan storage:link`) |
| `app/public/facturas/` | PDFs generados de facturas |
| `framework/cache/` | Caché de aplicación (driver=file). Contiene `resenas_google_v3` (reseñas Google, TTL 12h) |
| `framework/sessions/` | Sesiones (driver=file) |
| `framework/views/` | Vistas Blade compiladas |
| `logs/laravel.log` | Log de errores y mensajes `Log::error()`, `Log::warning()`, `Log::debug()` |

### `/bootstrap/`

| Archivo | Propósito |
|---|---|
| `app.php` | Punto de entrada de Laravel 12. Configura routing, middleware global y exceptions. **Sin middleware personalizado**. |
| `cache/` | Bootstrap cache (config, routes, services compilados) |

### `/config/`

Configuración estándar Laravel + `dompdf.php` añadido por la dependencia.

---

## 4. Funcionalidades del sistema

### 4.1 Solicitud de factura por el cliente (con OCR) 🧾

**LA FUNCIONALIDAD ESTRELLA DEL PROYECTO**.

#### Objetivo

Permitir que cualquier cliente del restaurante que haya consumido pueda **subir la foto de su ticket**, que el sistema **extraiga automáticamente** los datos (fecha, importe, líneas de productos, camarero) mediante OCR, rellene un formulario fiscal, valide los datos, **genere un PDF de factura legalmente válido** y lo guarde en BD para que el administrador lo procese.

#### Flujo completo de funcionamiento

```
[1] Cliente entra a /solicitar-factura
       ↓
[2] Ve un formulario en 3 pasos
       ↓
    PASO 1 — Datos fiscales
       · Selecciona "Particular" o "Empresa"
       · Rellena nombre, NIF, email, dirección, CP, ciudad
       · El JS valida formato del NIF con regex
       · Pulsa "Siguiente"
       ↓
    PASO 2 — Ticket
       · Pulsa zona de subida → abre cámara o galería
       · Selecciona/captura foto del ticket (JPG/PNG/WEBP, max 10MB)
       · Inmediatamente JS llama a POST /api/ocr/ticket con la imagen
       ↓
       OcrController@procesarTicket:
         · Codifica la imagen en base64
         · POST a Google Vision API (DOCUMENT_TEXT_DETECTION)
         · Recibe texto bruto
         · parsearTicket(): regex para fecha, hora, total, líneas, atendido_por
         · Devuelve JSON { fecha, total, lineas, atendido_por, texto_raw }
       ↓
       JS rellena automáticamente fecha e importe en el formulario
       JS guarda lineas_ticket y atendido_por en variables globales
       Si OCR falla → muestra mensaje y permite edición manual
       ↓
       Pulsa "Revisar solicitud"
       ↓
    PASO 3 — Confirmar
       · Muestra un resumen de todos los datos
       · Checkbox aceptación LOPD
       · Pulsa "Enviar solicitud"
       ↓
[3] JS construye FormData con TODOS los campos + archivo
    POST a /api/solicitudes
       ↓
    SolicitudController@store:
       a) $request->validate(...) — 13 reglas
       b) Sube el ticket al disco 'public' → storage/app/public/tickets/
       c) Calcula base imponible e IVA desde el total (10% inverso)
       d) DB::beginTransaction()
       e) Genera referencia: generarReferencia('MW-2026')
          → INSERT en contador_referencias si no existe
          → SELECT FOR UPDATE el último número
          → UPDATE +1
          → Devuelve "MW-2026-0001"
       f) SolicitudFactura::create(...) ← guarda solicitud con todos los datos cliente
       g) Factura::create(...) ← genera el registro de factura asociado, estado="pendiente"
       h) DB::commit()
       i) Intenta generar PDF con DomPDF cargando pdf/factura.blade.php
       j) Guarda PDF en storage/app/public/facturas/Factura_MW-2026-0001.pdf
       k) Actualiza factura.pdf_path
       l) Devuelve JSON { ok, referencia, email, pdf_ok, pdf_url, factura_id }
       ↓
[4] JS muestra pantalla de éxito con:
       · Referencia generada
       · Botón "Descargar PDF" si pdf_url existe
       · Bloque para enviar el PDF por email (opcional, llamada posterior)
```

#### Ruta técnica por archivos

##### Archivo 1 — `routes/web.php`

```php
Route::get ('/solicitar-factura', [SolicitudController::class, 'index'])->name('solicitar.factura');
Route::post('/api/solicitudes',   [SolicitudController::class, 'store'])->name('api.solicitudes.store');
Route::post('/api/ocr/ticket',    [OcrController::class, 'procesarTicket'])->name('api.ocr.ticket');
```

- `GET /solicitar-factura` → renderiza la vista del formulario
- `POST /api/solicitudes` → guarda la solicitud + factura (multipart/form-data porque incluye archivo)
- `POST /api/ocr/ticket` → endpoint AJAX para OCR (se llama en paralelo)

Sin middleware personalizado. Solo el web global (CSRF).

##### Archivo 2 — `resources/views/cliente/solicitar-factura.blade.php`

**945 líneas**. Tres bloques principales:

- **`@push('styles')`** (líneas 3-270): CSS embebido específico para esta vista — pasos, ticket-area drag&drop, preview, OCR fields, summary, howto modal.
- **`@section('content')`** (líneas 272-547): HTML de los 3 pasos + modal "¿Cómo funciona?".
- **`@push('scripts')`** (líneas 552-944): JS que controla todo:
  - `toggleTipo()` — cambia entre particular/empresa
  - `validateNIF(input)` — regex `^[0-9]{8}[A-Z]$` o `^[A-Z][0-9]{8}$`
  - `goStep(n)` — navegación entre pasos con validación
  - `handleFile(input)` — recibe el File, lo previsualiza, dispara OCR
  - `procesarTicketOCR(file)` — llama a `/api/ocr/ticket`
  - `submitFactura()` — construye FormData y llama a `/api/solicitudes`
  - `openHowto() / closeHowto()` — modal de ayuda

##### Archivo 3 — `app/Http/Controllers/SolicitudController.php`

Métodos:

- **`index()`** → `view('cliente.solicitar-factura')`
- **`store(Request $request)`** → método principal, ~140 líneas. Validación, subida archivo, transacción BD, generación PDF, respuesta JSON.
- **`generarReferencia(string $serie)`** → genera la referencia única con `lockForUpdate()` para evitar race conditions.

##### Archivo 4 — `app/Http/Controllers/OcrController.php`

- **`procesarTicket(Request)`** → llamada a Google Vision API. ~50 líneas.
- **`parsearTicket(string $texto)`** → parser regex específico para tickets del TPV del restaurante. Detecta:
  - Fecha+hora con `/(\d{2}\/\d{2}\/\d{4})\s+(\d{1,2}:\d{2})/`
  - Total con `/^total\s*:?\s*([\d.,]+)\s*€?/i`
  - "Le atendió: Carmen" con `/le\s+atendi[óo]\s*:?\s*(.+)/i`
  - Líneas de productos con patrón "N PRODUCTO" seguido de dos líneas con precios

- **`normalizarFecha()`** → DD/MM/YYYY → YYYY-MM-DD
- **`parseNumero()`** → "1,50 €" → 1.50 (maneja formatos europeo y mixto)

##### Archivo 5 — `app/Models/SolicitudFactura.php`

```php
protected $table    = 'solicitudes_factura';
public    $timestamps = true;
protected $fillable = [21 campos del formulario];
public    function factura() { return $this->hasOne(Factura::class, 'id_solicitud'); }
```

##### Archivo 6 — `app/Models/Factura.php`

```php
protected $table    = 'facturas';
protected $fillable = [20 campos];
protected $casts    = [
    'fecha_emision'  => 'datetime',
    'base_imponible' => 'float',
    'cuota_iva'      => 'float',
    'total_factura'  => 'float',
    'lineas_ticket'  => 'array',   // ← serialización automática JSON
];
public    function solicitud() { return $this->belongsTo(SolicitudFactura::class, 'id_solicitud'); }
```

##### Archivo 7 — `resources/views/pdf/factura.blade.php`

Plantilla HTML que DomPDF renderiza a PDF (A4). Contiene:

- Cabecera con marca "Miss Whitney" en burdeos
- Tabla de receptor (nombre/empresa, NIF, dirección)
- Tabla de líneas (cantidad, concepto, importe) — si hay `lineas_ticket`
- Bloque de totales (base, IVA 10%, total)
- Pie con datos fiscales del emisor

##### Archivo 8 — `database/migrations/...`

Las tres migraciones del dominio crean la estructura necesaria. Ver sección [5. Base de datos](#5-base-de-datos).

#### Relaciones internas

```
SolicitudFactura  (hasOne)  ───────►  Factura
       ▲                                  │
       │                                  │ belongsTo
       └──────────── (id_solicitud) ◄─────┘
       │
       └── contador_referencias (serie) ← generarReferencia()
```

- Una solicitud puede tener una factura (1:1)
- La factura referencia a la solicitud por `id_solicitud`
- El contador de referencias es global por serie (`MW-2024`, `MW-2025`, …)

#### Validaciones

| Campo | Backend (SolicitudController@store) | Frontend (JS) |
|---|---|---|
| `tipo_receptor` | `required\|in:particular,empresa` | select obligatorio |
| `nombre_cliente` | `required\|string\|max:150` | trim no vacío |
| `nif_cif` | `required\|string\|max:15` | regex `validateNIF()` |
| `email` | `required\|email\|max:200` | `type="email"` |
| `direccion` | `required\|string\|max:255` | trim no vacío |
| `codigo_postal` | `required\|string\|max:10` | maxlength 5 |
| `ciudad` | `required\|string\|max:100` | trim no vacío |
| `fecha_consumo` | `required\|date` | autorellenado por OCR |
| `importe_ticket` | `required\|numeric\|min:0.01` | autorellenado por OCR, `min=0, step=0.01` |
| `acepta_lopd` | `required\|accepted` | checkbox obligatorio paso 3 |
| `ticket` | `required\|file\|mimes:jpg,jpeg,png,webp\|max:10240` | `accept="image/*"` |

#### APIs utilizadas

**Google Cloud Vision API**:

- Endpoint: `https://vision.googleapis.com/v1/images:annotate?key=GOOGLE_VISION_API_KEY`
- Feature: `DOCUMENT_TEXT_DETECTION` (mejor que `TEXT_DETECTION` para tickets densos)
- Language hint: `es`
- Devuelve `responses[0].fullTextAnnotation.text` con todo el texto reconocido
- Coste: $1.50 por cada 1000 imágenes (las primeras 1000/mes gratis)

#### Seguridad

- Token **CSRF** obligatorio (Laravel lo añade automáticamente en `<meta name="csrf-token">` del layout; el JS lo lee y lo envía en cabecera `X-CSRF-TOKEN`)
- Validación estricta en backend (Laravel `Request::validate()`)
- **Sanitización**: `strtolower(email)`, `strtoupper(nif)`
- Archivo del ticket validado por **MIME** + **extensión** + **tamaño**
- Nombre del archivo aleatorizado: `uniqid('ticket_', true)` para evitar colisiones y exposición de nombres originales
- IP del solicitante guardada para trazabilidad: `ip_solicitante`
- User-Agent guardado (truncado a 500 caracteres)
- LOPD: solo se procesa la solicitud si `acepta_lopd === true`
- Transacción `DB::beginTransaction()` para garantizar atomicidad: si falla algo, no se crea solicitud ni factura

#### Problemas potenciales

- **OCR falla con tickets borrosos / mal iluminados** → el usuario puede editar manualmente con `permitirEdicion()`
- **Race condition en `generarReferencia`**: mitigada con `lockForUpdate()`. Pero si dos requests llegan simultáneamente, MySQL puede deadlockear → no se ha implementado retry
- **PDF puede fallar** (fonts no instaladas, ruta storage no escribible) → manejado con try-catch, solicitud se guarda igualmente y el admin puede regenerar el PDF
- **Sin rate limiting**: un bot podría enviar miles de solicitudes. Pendiente añadir `throttle` middleware
- **El PDF queda en `storage/app/public/facturas/`**, accesible públicamente vía URL si alguien adivina el path. Como el nombre incluye la referencia (`Factura_MW-2026-0001.pdf`), es predecible. Pendiente: añadir middleware de autenticación al `descargarPdf()`.

#### Resumen rápido

> El cliente sube una foto del ticket → Google Vision saca el texto → un parser regex extrae fecha/total/líneas → el cliente confirma sus datos fiscales → Laravel crea registros `solicitudes_factura` + `facturas` en una transacción atómica con número secuencial por serie → DomPDF genera el PDF → respuesta JSON con la URL de descarga. Toda la lógica en `SolicitudController@store` (140 líneas) y `OcrController@procesarTicket` (50 líneas + parser).

---

### 4.2 Panel de administración de facturas 🗂️

#### Objetivo

Vista única (SPA-like) en `/admin` donde el administrador del restaurante:

- Ve **TODAS las solicitudes + facturas** en una tabla ordenable y filtrable
- Crea facturas manuales (sin cliente previo, p.ej. para una empresa que llama por teléfono)
- Edita cualquier dato de una factura existente
- Cambia el estado: `pendiente → procesando → emitida → cancelada`
- Descarga el PDF generado
- Envía el PDF por email al cliente con un clic
- Elimina facturas (con confirmación)
- Busca por nombre/NIF/empresa/referencia
- Filtra por estado
- Ordena por cualquier columna
- Exporta a CSV
- OCR en el modal de creación (igual que el cliente)

#### Flujo completo

```
[1] Admin navega a /admin
       ↓
    FacturaController@index → renderiza admin/index.blade.php
       ↓
[2] La vista carga estática (HTML + CSS) + admin.js
       ↓
[3] admin.js al cargar:
       · loadFacturas()        → GET /api/facturas
       · initSearch()          → listener para campo de búsqueda
       · initMobileMenu()      → toggle sidebar móvil
       ↓
[4] loadFacturas():
       fetch /api/facturas → FacturaController@listar
         → DB::raw JOIN solicitudes + facturas
         → devuelve array con COALESCE de campos (factura > solicitud)
       ↓
    STATE.facturas = response
    applyFilters() → renderTable() + renderPagination()
       ↓
[5] El admin interactúa:
       - Tipea búsqueda → applyFilters() filtra STATE.facturas localmente y re-renderiza
       - Click en filtro estado (chip) → setStatusFilter() + applyFilters()
       - Click columna tabla → sortBy() → applyFilters()
       - Click "Editar" en una fila → openEdit(id) → modal con datos rellenos → saveModal() → POST /api/facturas/{id}
       - Click "Cambiar estado" → promptChangeStatus(id) → cycle pend→proc→emit→pend → POST /api/facturas/{id}/estado
       - Click "Descargar PDF" → <a href="/api/facturas/{id}/pdf?modo=descargar"> → FacturaController@descargarPdf
       - Click "Enviar email" → enviarFacturaEmail(id) → POST /api/facturas/{id}/email
       - Click "Eliminar" → confirm → POST /api/facturas/{id}/delete
       - Click "Nueva factura" → openCreate() → modal vacío → permite OCR opcional → saveModal() → POST /api/facturas
       - Click "Exportar CSV" → exportCSV() local (no servidor)
       ↓
[6] Cada cambio actualiza STATE.facturas in-place y re-renderiza la tabla
    sin necesidad de recargar página → comportamiento SPA en una sola vista Blade
```

#### Ruta técnica por archivos

##### Archivo 1 — `routes/web.php`

```php
Route::get('/admin', [FacturaController::class, 'index'])->name('admin.index');

Route::prefix('api/facturas')->group(function () {
    Route::get   ('/',            [FacturaController::class, 'listar']);
    Route::post  ('/',            [FacturaController::class, 'crear']);
    Route::post  ('/{id}/estado', [FacturaController::class, 'cambiarEstado']);
    Route::post  ('/{id}/delete', [FacturaController::class, 'eliminar']);
    Route::post  ('/{id}/email',  [FacturaController::class, 'enviarPorEmail']);
    Route::delete('/{id}',        [FacturaController::class, 'eliminar']);
    Route::post  ('/{id}',        [FacturaController::class, 'actualizar']);  // ¡debe ir DESPUÉS!
    Route::get   ('/{id}/pdf',    [FacturaController::class, 'descargarPdf']);
});
```

> ⚠️ El orden importa: la ruta genérica `POST /{id}` debe ir **después** de `/{id}/estado`, `/{id}/delete`, `/{id}/email` para que Laravel no la matchee primero.

##### Archivo 2 — `resources/views/admin/index.blade.php`

360 líneas de **HTML estático puro** (sin loops Blade). Contiene:

- `<aside class="sidebar">` con logo, nav-items, footer con avatar + año
- `<header class="topbar">` con título dinámico, búsqueda, filtros (chips), botones export/nueva
- `<table class="factura-table">` (vacía al cargar, se rellena con JS)
- `<div class="modal-backdrop">` con todos los campos del formulario crear/editar
- Inicializa `window.MW_API_BASE = '{{ rtrim(url("/"), "/") }}'` y carga `public/js/admin.js`

##### Archivo 3 — `public/js/admin.js`

**820 líneas**. Es prácticamente el "corazón" del admin. Organizado así:

```js
// Bloques principales
const STATE = { facturas: [], filtered: [], query: '', statusFilter: 'all',
                sortCol: 'fecha_solicitud', sortDir: 'desc',
                page: 1, perPage: 10, editId: null, loading: false };

// API CALLS
async function loadFacturas()        // GET /api/facturas
async function apiCreate(data)       // POST /api/facturas
async function apiUpdate(id, data)   // POST /api/facturas/{id}
async function apiChangeStatus(...)  // POST /api/facturas/{id}/estado
async function apiDelete(id)         // POST /api/facturas/{id}/delete

// FILTROS Y BÚSQUEDA
function initSearch()
function setStatusFilter(val, btn)
function applyFilters()
function sortBy(col)
function sortFiltered()
function updateSortHeaders()

// RENDER
function renderTable()
function renderPagination(total)
function goPage(n)
function refreshStats()      // 4 KPIs: total, pendientes, emitidas, importe acumulado
function updateSidebarBadge()
function updateSearchLabel()

// MODAL CRUD
function openCreate()
function openEdit(id)
function saveModal()
function closeModal()
function clearModalForm()

// OCR DESDE EL ADMIN (mismo endpoint que cliente)
async function handleAdminOcr(input)

// LÍNEAS DE TICKET (editor)
function renderLineas()
function recalcularTotalDesdeLineas()
function bloquearImporte() / desbloquearImporte()
function agregarLinea() / quitarLinea(i)

// ACCIONES TABLA
async function promptChangeStatus(id)
async function deleteFactura(id)
async function enviarFacturaEmail(id, emailPredeterminado)
async function crearFacturaDesde(referencia)

// UTIL
function exportCSV()
function showToast(msg, type)
function escHtml(s) / escReg(s) / ucFirst(s)
function setLoading(on)
function renderEmptyWithError()
function initMobileMenu() / closeSidebar()
```

##### Archivo 4 — `app/Http/Controllers/FacturaController.php`

Controlador grande, 8 métodos públicos + 2 helpers privados. Cada método:

| Método | Endpoint | Qué hace | Respuesta |
|---|---|---|---|
| `index()` | GET `/admin` | Renderiza vista | HTML |
| `listar()` | GET `/api/facturas` | JOIN solicitudes+facturas con COALESCE | JSON array |
| `crear(Request)` | POST `/api/facturas` | Si no hay solicitud previa, crea una "manual" + factura; si existe la solicitud (referencia_solicitud), solo crea factura | JSON objeto |
| `actualizar(Request, $id)` | POST `/api/facturas/{id}` | Update factura. Recalcula IVA. Si pasa a "emitida" y no tenía `fecha_emision`, la setea a `now()` | JSON objeto |
| `cambiarEstado(Request, $id)` | POST `/api/facturas/{id}/estado` | Update solo del campo `estado` (transición rápida) | JSON `{ok, id, estado}` |
| `eliminar($id)` | POST/DELETE | Borra PDF de disco si existe + delete BD | JSON `{ok, id, numero_factura}` |
| `descargarPdf($id, Request)` | GET `/api/facturas/{id}/pdf?modo=ver\|descargar` | Si existe el PDF en disco lo devuelve; si no, lo regenera con DomPDF, lo guarda y lo devuelve | Binary PDF |
| `enviarPorEmail($id, Request)` | POST `/api/facturas/{id}/email` | Si no hay PDF lo regenera; envía email vía `FacturaMail` | JSON `{ok, mensaje, email}` |

##### Archivo 5 — `app/Mail/FacturaMail.php`

Mailable. Recibe en constructor:
- `numeroFactura: string`
- `nombreReceptor: string` (empresa si existe, si no nombre)
- `pdfPath: string` (ruta absoluta)
- `pdfNombre: string` (nombre del adjunto)

- `envelope()` → asunto: `"Tu factura de Miss Whitney · MW-2026-0001"`
- `content()` → vista `emails.factura`
- `attachments()` → `Attachment::fromPath()` con MIME `application/pdf`

##### Archivo 6 — `resources/views/emails/factura.blade.php`

Email HTML inline-styled (compatible con todos los clientes de email). Banner burdeos, saludo personalizado, caja con número de factura destacado, contacto del restaurante en el footer.

##### Archivo 7 — `public/css/admin.css`

Estilos exclusivos del panel: layout sidebar + main, tabla con hover, chips de filtro, modal, badges de estado con colores semánticos.

#### Validaciones (FacturaController)

**`crear`** y **`actualizar`** validan:

```php
'nombre_cliente' => 'required|string|max:150',
'empresa'        => 'nullable|string|max:150',
'nif_cif'        => 'required|string|max:15',
'email'          => 'required|email|max:200',
'fecha_consumo'  => 'required|date',
'importe'        => 'required|numeric|min:0.01',
'estado'         => 'required|in:pendiente,procesando,emitida,cancelada',
'obs_cliente'    => 'nullable|string|max:500',
'lineas_ticket'              => 'nullable|array',
'lineas_ticket.*.cantidad'   => 'nullable|integer|min:1',
'lineas_ticket.*.concepto'   => 'nullable|string|max:200',
'lineas_ticket.*.importe'    => 'nullable|numeric|min:0',
```

`cambiarEstado` solo valida `estado`. Es el endpoint más ligero.

#### Seguridad

⚠️ **Riesgo crítico actual**: las rutas `/admin` y `/api/facturas/*` **no tienen middleware de autenticación**. Cualquiera con la URL puede acceder y operar.

Otras protecciones presentes:
- CSRF en POST/PUT/DELETE
- Validación estricta de inputs
- `Factura::findOrFail($id)` → 404 si no existe
- Logging de errores en `Log::error('MW ...')`

#### Problemas potenciales

- **Sin auth** → urgente
- **Sin paginación servidor**: `listar()` devuelve TODAS las facturas. Con 1000+ filas el frontend se resentirá. Pendiente paginar en BD.
- **Filtros/búsqueda son client-side**: filtran sobre `STATE.facturas`. No funcionarán bien con dataset grande.
- **El `exportCSV` también es client-side**: solo exporta lo cargado en memoria.
- **El método `crear` con `referencia_solicitud` reutiliza solicitud previa pero no verifica que no exista ya una factura para ella** → podría duplicar.

#### Resumen rápido

> Una sola vista Blade que sirve HTML estático y carga un JS de 820 líneas. El JS arranca pidiendo todas las facturas, las guarda en `STATE`, y todo (filtrar, ordenar, buscar, paginar) se hace en memoria del navegador. Las acciones CRUD llaman a 8 endpoints REST que viven en `FacturaController`. CRUD completo + cambio rápido de estado + descarga PDF + envío email + OCR para crear facturas manualmente.

---

### 4.3 Generación y envío de PDF de factura 📄

#### Objetivo

Convertir los datos de una factura en un documento PDF profesional A4 con todos los campos legales (datos del emisor, datos del receptor, líneas detalladas, base imponible, IVA al 10%, total) — y permitir descargarlo o enviarlo por email al cliente.

#### Flujo completo

```
DISPARADORES (3 momentos):

A) Tras solicitar factura el cliente:
   SolicitudController@store
     → Pdf::loadView('pdf.factura', $datos)->setPaper('a4')
     → Storage::disk('public')->put('facturas/Factura_MW-2026-0001.pdf', $pdf->output())
     → factura.pdf_path = 'facturas/Factura_MW-2026-0001.pdf'

B) Cuando el admin pide descargar:
   FacturaController@descargarPdf($id, Request)
     → Si pdf_path existe y archivo existe en disco → lo devuelve
     → Si no, regenera con DomPDF y lo guarda
     → modo=descargar → headers Content-Disposition: attachment
     → modo=ver       → inline en navegador

C) Cuando el admin pulsa "enviar email":
   FacturaController@enviarPorEmail($id, Request)
     → Si no existe PDF, regenera
     → Mail::to($destino)->send(new FacturaMail(...))
     → Gmail SMTP envía con PDF adjunto
```

#### Ruta técnica por archivos

##### Archivo 1 — `composer.json`

```json
"require": {
    "barryvdh/laravel-dompdf": "^3.0"
}
```

DomPDF se autoregistra via service provider de Laravel. Disponible como `\Barryvdh\DomPDF\Facade\Pdf`.

##### Archivo 2 — `config/dompdf.php`

Configuración por defecto: papel A4, codificación UTF-8, font dir, options de seguridad.

##### Archivo 3 — `resources/views/pdf/factura.blade.php`

Plantilla HTML (346 líneas) optimizada para DomPDF. **Importante**:

- **NO se usa Flexbox ni Grid** (DomPDF no los soporta). Solo `display: table`, `table-cell`, `inline-block`.
- Fonts: `Arial, Helvetica, sans-serif` (built-in en DomPDF).
- Colores planos, no `var(--...)` (DomPDF no procesa CSS variables).
- Imágenes inline o desde URL absoluta (ruta `asset()`).

Estructura del PDF:
1. **Header** burdeos con marca "Miss Whitney" + número factura
2. **Bloque receptor**: nombre/empresa, NIF, dirección
3. **Bloque fechas**: emisión, consumo
4. **Tabla líneas**: cantidad, concepto, importe — si `$lineas_ticket` no está vacío
5. **Tabla totales**: base imponible, IVA 10%, total
6. **Footer legal**: datos fiscales del emisor

##### Archivo 4 — `SolicitudController::store()` (cliente)

```php
$pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.factura', [
    'numero_factura'  => $referencia,
    'receptor_nombre' => $data['nombre_cliente'],
    // ... 14 variables
])->setPaper('a4');

Storage::disk('public')->put('facturas/' . $nombre, $pdf->output());
$factura->update(['pdf_path' => 'facturas/' . $nombre]);
```

> Disk `public` → resuelve a `storage/app/public/`. Requiere `php artisan storage:link` para servirse vía web.

##### Archivo 5 — `FacturaController::descargarPdf()` (admin)

Doble función: **lazy-generate**. Si el PDF ya existe en disco lo devuelve directo (rápido). Si no, lo regenera al vuelo:

```php
if ($f->pdf_path && Storage::disk('public')->exists($f->pdf_path)) {
    return $modo === 'descargar'
        ? Storage::disk('public')->download($f->pdf_path, $nombre)
        : response(Storage::disk('public')->get($f->pdf_path), 200, ['Content-Type' => 'application/pdf']);
}
// Si no existe, regenera y guarda
```

##### Archivo 6 — `FacturaController::enviarPorEmail()`

```php
\Mail::to($destino)->send(new \App\Mail\FacturaMail(
    $f->numero_factura,
    $nombreReceptor,
    $absPath,
    $pdfNombre
));
```

##### Archivo 7 — `app/Mail/FacturaMail.php`

Mailable con:
- `attachments()` → `Attachment::fromPath($absPath)->as($pdfNombre)->withMime('application/pdf')`
- `view('emails.factura')` → plantilla del email
- `envelope.subject` → `"Tu factura de Miss Whitney · {{numero}}"`

#### Seguridad

⚠️ Los PDFs viven en `storage/app/public/facturas/` accesible sin auth. El nombre del archivo es predecible (`Factura_MW-2026-0001.pdf`).

Para producción: mover a `storage/app/facturas/` (privado), exigir auth para descargar, opcionalmente expirar URLs firmadas con `Storage::temporaryUrl()`.

#### Problemas potenciales

- **DomPDF y caracteres especiales/emojis**: a veces se renderizan mal. Mitigado usando solo ASCII en el template.
- **Fonts UTF-8**: las built-in (Arial, Helvetica) soportan español. Si quisieras añadir fuentes personalizadas habría que cargarlas vía `config/dompdf.php`.
- **Memoria**: DomPDF carga todo en memoria. Para PDFs muy grandes (50+ páginas) puede agotar memoria. No es el caso aquí.

#### Resumen rápido

> DomPDF carga la vista `pdf.factura` con los datos como variables, llama a `setPaper('a4')` y `output()` devuelve bytes binarios. Se guarda en disco con nombre predecible (`Factura_{referencia}.pdf`) y se referencia desde la tabla `facturas.pdf_path`. Se regenera al vuelo si se elimina del disco. Para envío, `FacturaMail` lo adjunta y SMTP Gmail lo envía.

---

### 4.4 Sistema de reseñas Google Places ⭐

#### Objetivo

Mostrar en la home **reseñas reales** del restaurante obtenidas de Google Places API, con:
- Rating global y total de reseñas reales (ej. "★ 4.0 sobre 519 reseñas verificadas")
- Tarjetas individuales con foto del autor, texto, fecha relativa, estrellas
- Carrusel infinito de 2 filas en direcciones opuestas
- Botón "Valorarnos" que enlaza al formulario directo de Google

#### Flujo completo

```
[1] Usuario carga la home → index.blade.php
       ↓
[2] JS al final del index hace fetch('{{ route("api.resenas") }}')
       ↓
[3] ResenasController@index:
       a) Lee GOOGLE_PLACE_ID y GOOGLE_VISION_API_KEY de .env
       b) Cache::remember('resenas_google_v3', 12h, function() { ... })
          ↓
          Si NO está en caché:
          · Itera idiomas [es, en, fr, it, de]
          · Para cada idioma:
            GET https://maps.googleapis.com/maps/api/place/details/json
              ?place_id=...&fields=name,rating,user_ratings_total,reviews,url
              &language=XX&reviews_sort=most_relevant|newest
              &reviews_no_translations=true&key=...
          · Consolida las reseñas por fingerprint (autor+timestamp)
          · Si una reseña ya está pero llega en otro idioma con texto más largo, la actualiza
          · Filtra solo ratings >= 4
       c) Devuelve JSON {
              ok, rating_global, total_resenas, url_google, url_escribir,
              resenas: [{ autor, autor_foto, rating, texto, tiempo_relativo, timestamp }]
          }
       ↓
[4] JS del index:
       · Pinta el resumen con rating y total reales
       · Renderiza 2 filas (A izquierda, B derecha) con shuffle independiente
       · Calcula cuántas copias del set necesita para llenar el viewport
       · Arranca 2 carruseles con requestAnimationFrame (uno -1, otro +1)
       · Botón "Valorarnos" usa url_escribir (link directo a escribir reseña en Google)
```

#### Truco multi-idioma

Google Places devuelve **máximo 5 reseñas por llamada**. Pero **a veces devuelve reseñas distintas según el idioma** (porque prioriza traducciones recientes a cada idioma). Iterando 5 idiomas (`es, en, fr, it, de`) se pueden conseguir hasta **15-20 reseñas distintas reales**.

Implementación:

```php
$idiomas = ['es', 'en', 'fr', 'it', 'de'];
foreach ($idiomas as $lang) {
    $r = Http::timeout(15)->get('...', [
        'place_id' => $placeId,
        'language' => $lang,
        'reviews_sort' => $lang === 'es' ? 'most_relevant' : 'newest',
        // ...
    ]);
    // ... consolidar por fingerprint = hash(autor + timestamp)
    if (isset($resenas[$fp]) && mb_strlen($texto) <= mb_strlen($resenas[$fp]['texto'])) continue;
    $resenas[$fp] = [...];
}
```

#### Ruta técnica por archivos

##### Archivo 1 — `routes/web.php`

```php
Route::get('/api/resenas', [ResenasController::class, 'index'])->name('api.resenas');
```

##### Archivo 2 — `app/Http/Controllers/ResenasController.php`

105 líneas. Un solo método público.

##### Archivo 3 — `.env`

```env
GOOGLE_PLACE_ID='ChIJWRmXLD3QEQ0R-r9q51ktegw'
GOOGLE_VISION_API_KEY=AIzaSy...   # ⚠️ Misma key, debe tener habilitadas tanto Vision como Places
```

##### Archivo 4 — `resources/views/cliente/index.blade.php`

JS al final del archivo. Funciones clave:
- `cargarResenas()` — fetch + render
- `renderCard(r)` — construye HTML de una tarjeta
- `shuffle(arr)` — Fisher-Yates
- `startCarousel(trackId, direction, copies)` — bucle requestAnimationFrame

##### Archivo 5 — `storage/framework/cache/data/` (caché file)

Donde se almacena la clave `resenas_google_v3`. TTL 12h.

#### Validaciones / fallbacks

Si falla la API:
- `$placeId` o `$apiKey` vacíos → JSON `{ok: false, mensaje: 'Reseñas no configuradas'}` con HTTP 500
- Google responde con status != "OK" → ese idioma se descarta, sigue probando otros
- Excepción → `Log::debug()` y continúa
- Si **ningún idioma** devuelve nada → reseñas vacías, frontend muestra mensaje de error

#### Seguridad

- API key se queda en backend (nunca expuesta al cliente)
- Cache 12h evita golpear la API en cada request → controla costes y respeta cuota
- El JSON devuelto al cliente es público pero **no contiene la API key**

#### Problemas potenciales

- **Coste**: cada miss de caché hace 5 llamadas (una por idioma). Con TTL 12h son 10 llamadas/día. Places Details cuesta $17/1000 → ~$0.005/día. Totalmente asumible.
- **Foto de autor**: la URL devuelta por Google a veces falla cargar (CORS o foto removida). El JS tiene `onerror` que la reemplaza por la inicial del nombre.
- **Reseñas <4★ se filtran** intencionalmente (los pocos negativos suelen ser injustos o spam). Decisión de producto.

#### Resumen rápido

> Cada 12 horas, el backend pide reseñas a Google Places en 5 idiomas distintos para sortear el límite de 5 reseñas/llamada y conseguir hasta 15-20 reales distintas. Cachea el resultado en disco. El frontend pinta 2 filas de carrusel infinito con shuffle independiente, y el botón "Valorarnos" lleva directamente al formulario de Google. Todo se actualiza solo sin intervención manual.

---

### 4.5 Formulario de reservas 📅

#### Objetivo

Permitir al cliente reservar mesa enviando un email formateado al restaurante. **No se guarda en BD** — es un sistema de notificación pura.

#### Flujo completo

```
[1] Cliente abre /reservar (o el modal global que carga esta misma vista con ?embedded=1)
       ↓
[2] cliente/reservar.blade.php muestra:
       - Datos: nombre, teléfono, email, fecha, hora, personas (1-6+), notas
       - Botón "Información útil" → abre modal interno con horarios, política, etc.
       ↓
[3] Cliente rellena y pulsa "Enviar reserva"
       ↓
    JS submitReserva():
       - Valida campos no vacíos en cliente
       - POST a {{ route('api.reserva') }}  con CSRF token
       ↓
    ReservaController@enviar:
       - Valida (7 reglas)
       - Construye HTML del email con tabla de datos (formateo bonito)
       - Mail::send([], [], function($m) { ... })  ← envío inline (sin Mailable)
         · to: env('MAIL_FROM_ADDRESS')  ← propio gmail del restaurante
         · subject: "🍽️ Nueva reserva — Juan · 2026-05-15 21:30"
         · html: render formateado
         · replyTo: email del cliente (si lo dio) — para que el dueño responda directo
       ↓
[4] Respuesta JSON {ok: true}
       JS muestra pantalla de éxito con botón "Nueva reserva"
```

#### Ruta técnica por archivos

##### Archivo 1 — `routes/web.php`

```php
Route::post('/api/reserva', [App\Http\Controllers\ReservaController::class, 'enviar'])->name('api.reserva');
```

##### Archivo 2 — `resources/views/cliente/reservar.blade.php`

346 líneas. Una sola sección con card centrado + modal "Información útil" interno + JS al final.

##### Archivo 3 — `app/Http/Controllers/ReservaController.php`

Validación:
```php
'nombre'   => 'required|string|max:120',
'telefono' => 'required|string|max:20',
'email'    => 'nullable|email|max:200',   // opcional
'fecha'    => 'required|date',
'hora'     => 'required|string|max:10',
'personas' => 'required|integer|min:1|max:50',
'notas'    => 'nullable|string|max:500',
```

Localización española de la fecha:
```php
$fecha_fmt = \Carbon\Carbon::parse($data['fecha'])->translatedFormat('l, j \d\e F \d\e Y');
// → "miércoles, 15 de mayo de 2026"
```

Requires `APP_LOCALE=es` (configurado en `.env`).

##### Archivo 4 — `config/mail.php`

Configurado para SMTP Gmail:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=misswhitneyfacturas@gmail.com
MAIL_PASSWORD=...        # App password de Gmail
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=misswhitneyfacturas@gmail.com
MAIL_FROM_NAME="Miss Whitney"
```

#### Seguridad

- CSRF en POST
- Sin almacenar en BD → cero riesgo de SQL injection en este flujo
- `htmlspecialchars()` en cada campo antes de meterlo en el HTML del email → previene inyección HTML

#### Problemas potenciales

- **Sin rate limiting**: un bot podría enviar miles de reservas falsas saturando el inbox
- **Sin captcha / honeypot**: lo mismo
- **No queda registro**: si se pierde un email, no hay manera de recuperarlo
- **Si Gmail bloquea por exceso de envío**, el restaurante se entera tarde

#### Resumen rápido

> Formulario → POST `/api/reserva` → validación → email HTML al restaurante con `reply-to` al cliente. Sin persistencia en BD. Sin captcha. Es un sistema de notificación pura.

---

### 4.6 Formulario de contacto 📬

#### Objetivo

Similar a reservas pero para consultas generales. Email al restaurante.

#### Flujo y arquitectura: idénticos a reservas

Diferencias:
- Endpoint: `/api/contacto`
- Controller: `ContactoController@enviar`
- Validación: solo `nombre`, `email`, `asunto?`, `mensaje`
- Asunto generado: `"[Web MW] {asunto} · {nombre}"`
- HTML del email más simple (sin tabla, solo bloques)

#### Resumen rápido

> Igual que reservas pero más simple. Email con formato HTML al gmail del restaurante.

---

### 4.7 Banner de cookies (RGPD) 🍪

#### Objetivo

Cumplimiento RGPD: mostrar un banner al primer visitante, permitir aceptar/rechazar/configurar cookies, y respetar la decisión (deshabilitar Google Maps si rechaza, etc.).

#### Flujo

```
[1] Usuario entra por primera vez
       ↓
[2] JS al cargar (en layouts/app.blade.php):
       const consent = localStorage.getItem('mw_cookie_consent_v1');
       if (!consent) → mostrar modal completo
       if (consent === 'all') → todo activo, no mostrar nada
       if (consent === 'config') → respetar opciones guardadas
       ↓
[3] El modal tiene 2 vistas:
       a) Principal: botones "Aceptar todas" / "Solo necesarias" / "Configurar"
       b) Configuración: switches para cada categoría:
          - Necesarias (siempre activas, no editable)
          - Funcionales · Google Maps
          - Tipografías · Google Fonts
       ↓
[4] Acción del usuario:
       - "Aceptar todas" → localStorage = {maps:true, fonts:true} → modal cierra
       - "Solo necesarias" → localStorage = {maps:false, fonts:false} → ejecutar aplicarRechazoMaps()
       - "Guardar config" → guarda lo que marcó
       ↓
[5] Si rechazaron Maps:
       aplicarRechazoMaps():
         - Recorre iframes que contienen google.com/maps
         - Los reemplaza por un placeholder con icono 📍 y botón "Abrir en Google Maps"
       ↓
[6] Botón pestaña lateral "🍪" siempre disponible para reabrir y cambiar opciones
```

#### Ruta técnica

Todo dentro de `layouts/app.blade.php`. Sin controlador. Sin BD. Solo `localStorage`.

#### Resumen rápido

> Banner que se muestra al primer visitante. Guarda preferencias en `localStorage`. Si rechazas Google Maps, los iframes se reemplazan por un placeholder con botón externo. Reabrible desde una pestañita 🍪 lateral.

---

### 4.8 Modal global Reservar/Factura 🪟

#### Objetivo

Que pulsar "Reservar" o "Factura" desde cualquier sitio (dock móvil, topbar, footer, botones internos) no navegue a otra página sino que abra un **modal sobre la página actual** que carga el formulario en un iframe.

#### Flujo

```
[1] Layout app.blade.php tiene UN modal global oculto (display:none)
    con un <iframe id="mw-modal-iframe">
       ↓
[2] JS en el layout intercepta TODOS los clicks en <a>:
       if (href contiene /reservar) → mwOpenModal(href, 'Reservar mesa')
       if (href contiene /solicitar-factura) → mwOpenModal(href, 'Solicitar factura')
       ↓
[3] mwOpenModal(url, title):
       - Añade ?embedded=1 a la url
       - iframe.src = url
       - Abre el modal
       ↓
[4] La página cargada en el iframe:
       Detecta que window.self !== window.top
       → añade body.classList.add('is-embedded')
       ↓
[5] CSS reacciona:
       body.is-embedded .topbar { display: none }
       body.is-embedded .dock-wrap { display: none }
       body.is-embedded .mw-footer { display: none }
       body.is-embedded .mob-header { display: none }
       body.is-embedded .page-wrap { padding: ... }
       → la página se ve "limpia" sin layout, solo el formulario
       ↓
[6] Cerrar:
       - Click en X / fuera / ESC → mwCloseModal()
       - iframe.src = 'about:blank' (descarga la página)
```

#### Resumen rápido

> Un iframe oculto en el layout que se abre cargando la página real (`/reservar` o `/solicitar-factura`) con un parámetro `?embedded=1`. La página detecta que está en iframe y oculta su propio chrome (topbar, dock, footer) vía CSS, quedando solo el formulario. Cierra con X, click fuera o ESC.

---

## 5. Base de datos

### Diagrama lógico

```
┌──────────────────────────────────┐
│     contador_referencias         │
├──────────────────────────────────┤
│ serie         VARCHAR(20)  PK    │  ej: "MW-2026"
│ ultimo_numero INT UNSIGNED       │  ej: 0001, 0002, ...
└──────────────────────────────────┘
            ↑
            │ usado por generarReferencia()

┌────────────────────────────────────────────────┐
│           solicitudes_factura                  │
├────────────────────────────────────────────────┤
│ id              BIGINT  PK AUTO                │
│ referencia      VARCHAR(20)  UNIQUE            │ ← MW-2026-0001
│ tipo_receptor   ENUM('particular','empresa')   │
│ nombre_cliente  VARCHAR(150)                   │
│ nombre_empresa  VARCHAR(150) NULL              │
│ nif_cif         VARCHAR(15)                    │
│ email           VARCHAR(200)                   │
│ direccion       VARCHAR(255)                   │
│ codigo_postal   VARCHAR(10)                    │
│ ciudad          VARCHAR(100)                   │
│ fecha_consumo   DATE                           │
│ importe_ticket  DECIMAL(10,2)                  │
│ ticket_filename VARCHAR(255) NULL              │
│ ticket_path     VARCHAR(255) NULL              │ ← tickets/ticket_xxx.jpg
│ ticket_mime     VARCHAR(80)  NULL              │
│ observaciones   VARCHAR(500) NULL              │
│ atendido_por    VARCHAR(80)  NULL              │ ← OCR detectado
│ acepta_lopd     BOOL                           │
│ ip_solicitante  VARCHAR(45)  NULL              │ ← IPv4/IPv6
│ user_agent      VARCHAR(500) NULL              │
│ created_at, updated_at                         │
└────────────────────────────────────────────────┘
                          │
                          │ 1:1
                          │
                          ▼
┌────────────────────────────────────────────────┐
│                  facturas                      │
├────────────────────────────────────────────────┤
│ id                BIGINT  PK AUTO              │
│ id_solicitud      FK → solicitudes_factura.id  │ ← restrict on delete
│ numero_factura    VARCHAR(20) UNIQUE           │ ← misma referencia
│ receptor_nombre   VARCHAR(150)                 │
│ receptor_empresa  VARCHAR(150) NULL            │
│ receptor_nif      VARCHAR(15)                  │
│ receptor_email    VARCHAR(200)                 │
│ receptor_direccion VARCHAR(255)                │
│ receptor_cp       VARCHAR(10)                  │
│ receptor_ciudad   VARCHAR(100)                 │
│ base_imponible    DECIMAL(10,2)                │
│ porcentaje_iva    DECIMAL(5,2) DEFAULT 10      │
│ cuota_iva         DECIMAL(10,2)                │
│ total_factura     DECIMAL(10,2)                │
│ concepto          VARCHAR(500)                 │ ← "Consumicion en Miss Whitney"
│ lineas_ticket     JSON NULL                    │ ← [{cantidad,concepto,importe}, ...]
│ fecha_consumo     DATE                         │
│ estado            ENUM(pendiente,procesando,emitida,cancelada) │
│ fecha_emision     DATETIME NULL                │
│ pdf_path          VARCHAR(500) NULL            │ ← facturas/Factura_xxx.pdf
│ notas_internas    VARCHAR(255) NULL            │
│ admin_usuario     VARCHAR(80)  NULL            │
│ created_at, updated_at                         │
└────────────────────────────────────────────────┘
```

### Otras tablas (no del dominio)

| Tabla | Origen | Uso actual |
|---|---|---|
| `users`, `password_reset_tokens`, `sessions` | Scaffold Laravel | Sesiones se usan; users vacía |
| `cache`, `cache_locks` | Laravel cache table driver | **No se usan** (CACHE_STORE=file) |
| `jobs`, `job_batches`, `failed_jobs` | Laravel queue | **No se usan** (QUEUE_CONNECTION=sync) |

### Flujo de datos en una solicitud

```
1. Cliente sube ticket + datos
       ↓
2. SolicitudController@store:
       INSERT INTO contador_referencias (serie='MW-2026') IF NOT EXISTS
       SELECT ultimo_numero FROM contador_referencias WHERE serie='MW-2026' FOR UPDATE
       UPDATE contador_referencias SET ultimo_numero = +1
       ↓
3. INSERT INTO solicitudes_factura (...) — 21 campos
       ↓
4. INSERT INTO facturas (id_solicitud, ...) — 17 campos
       ↓
5. UPDATE facturas SET pdf_path = '...' (tras generar PDF)
```

### Integridad

- `facturas.id_solicitud` → FK con `restrictOnDelete` (no se puede borrar una solicitud si tiene factura)
- `facturas.numero_factura` UNIQUE (no se pueden duplicar números)
- `solicitudes_factura.referencia` UNIQUE
- Las columnas críticas no son nullable (nombre, email, NIF, fecha, importe)

---

## 6. APIs externas y servicios

### 6.1 Google Cloud Vision API

| Propiedad | Valor |
|---|---|
| Endpoint | `POST https://vision.googleapis.com/v1/images:annotate?key=...` |
| Feature usada | `DOCUMENT_TEXT_DETECTION` |
| Language hint | `es` |
| Usado en | `OcrController@procesarTicket` |
| Configuración | `.env` → `GOOGLE_VISION_API_KEY` |
| Coste | $1.50/1000 imágenes (las primeras 1000/mes gratis) |
| Timeout | 30 segundos |
| Manejo errores | Si responde !ok → `Log::error` + JSON `{ok:false}` con HTTP 500 |

Envío: imagen en `base64`, recibe `responses[0].fullTextAnnotation.text`.

### 6.2 Google Places API (Place Details)

| Propiedad | Valor |
|---|---|
| Endpoint | `GET https://maps.googleapis.com/maps/api/place/details/json` |
| Fields pedidos | `name,rating,user_ratings_total,reviews,url` |
| Usado en | `ResenasController@index` |
| Configuración | `.env` → `GOOGLE_PLACE_ID` + `GOOGLE_VISION_API_KEY` (misma key) |
| Cache | Laravel `Cache::remember('resenas_google_v3', 12h, ...)` |
| Coste | $17/1000 llamadas (con cache 12h × 5 idiomas = ~10 llamadas/día = $5/mes) |
| Truco | 5 llamadas (es/en/fr/it/de) para conseguir más reseñas distintas |

### 6.3 SMTP Gmail

| Propiedad | Valor |
|---|---|
| Host | `smtp.gmail.com:587` (STARTTLS) |
| Usuario | `misswhitneyfacturas@gmail.com` |
| Password | App password de Gmail (NO la contraseña normal) |
| Usado en | `FacturaMail`, `ReservaController`, `ContactoController` |
| Driver Laravel | `MAIL_MAILER=smtp` |

⚠️ Requiere haber activado "Autenticación en 2 pasos" en la cuenta Gmail y generado una contraseña de aplicación.

### 6.4 Google Maps Embed

| Propiedad | Valor |
|---|---|
| Tipo | iframe sin API key (free tier) |
| Usado en | Vistas `index`, `contacto` |
| RGPD | Sustituido por placeholder si el usuario rechaza cookies |

### 6.5 Imágenes externas

- `images.unsplash.com` → fotos de platos en la galería y hero (lazy-loaded)

---

## 7. Frontend

### Tecnologías

- **Sin framework JS**: vanilla JavaScript ES6+
- **Sin framework CSS**: CSS puro con variables CSS (`--burdeos`, `--crema`, etc.)
- **Vite**: configurado pero solo se compila Tailwind opcionalmente. **No es indispensable** — el proyecto funciona sin compilar nada.
- **Tailwind 4**: declarado pero **no se usa en las vistas**. Probablemente residuo del scaffold.

### Estructura visual

```
┌─────────────────────────────────────────────────────────────┐
│                       LAYOUT GLOBAL                         │
│                                                             │
│  [LOADER ANIMADO]  ←  Pantalla de carga inicial            │
│                                                             │
│  ┌─── DESKTOP ──────────────────────────────────────────┐  │
│  │ ┌────────────┐  [Logo]  ┌─────────────────────────┐  │  │
│  │ │ topbar     │          │ Reservar | Factura      │  │  │
│  │ └────────────┘          └─────────────────────────┘  │  │
│  │                                                       │  │
│  │ ┌──────── Contenido (@yield('content')) ──────────┐ │  │
│  │ │                                                  │ │  │
│  │ └──────────────────────────────────────────────────┘ │  │
│  │                                                       │  │
│  │ ┌──── FOOTER MULTI-COLUMNA ───────────────────────┐  │  │
│  │ │ Marca | Web | Contacto | Horario | Redes        │  │  │
│  │ └──────────────────────────────────────────────────┘ │  │
│  └───────────────────────────────────────────────────────┘  │
│                                                             │
│  ┌─── MÓVIL (max-width:860px) ──────────────────────────┐  │
│  │ ┌── MOB-HEADER ──────────────────────────────────┐   │  │
│  │ │ [Logo] Miss Whitney · Bar & Cocina · Huelva    │   │  │
│  │ └─────────────────────────────────────────────────┘   │  │
│  │                                                       │  │
│  │ ┌── Contenido ──────────────────────────────────┐    │  │
│  │ │                                                │    │  │
│  │ └────────────────────────────────────────────────┘    │  │
│  │                                                       │  │
│  │ ┌── DOCK INFERIOR (5 botones + casita central) ──┐   │  │
│  │ │  Galería   Reserva   [🏠]   Factura  Contacto  │   │  │
│  │ └─────────────────────────────────────────────────┘   │  │
│  └───────────────────────────────────────────────────────┘  │
│                                                             │
│  [MODAL GLOBAL iframe — Reservar / Factura]                │
│  [BANNER COOKIES RGPD]                                     │
│  [PESTAÑITA 🍪 para reabrir cookies]                       │
└─────────────────────────────────────────────────────────────┘
```

### Sistema de animaciones global

`layouts/app.blade.php` incluye un `IntersectionObserver` que:

1. Marca automáticamente con clase `.reveal--up` todos los elementos con clases específicas (`.contact-card`, `.factura-card`, `.plato-card`, `.section-title`, etc.)
2. Marca con clase `.stagger` los grids para que sus hijos aparezcan escalonadamente
3. Cuando un elemento entra en viewport → añade clase `.is-in` → CSS lo anima con `opacity` y `transform`
4. `unobserve()` tras revelar (cero coste tras la primera vez)
5. Respeta `prefers-reduced-motion: reduce` deshabilitando todo

### Comunicación frontend-backend

| Componente | Tecnología |
|---|---|
| Formularios GET de navegación | links `<a>` clásicos |
| Formularios POST tradicionales | `<form method="POST">` con `@csrf` (no se usa en este proyecto) |
| **API AJAX** | `fetch()` nativo con header `X-CSRF-TOKEN` leído del meta tag |
| Upload de archivos | `FormData` + `fetch()` |
| Resultados | JSON parseado con `await res.json()` |

### Comportamientos JS notables

- **Cursor seguidor**: punto burdeos que sigue al mouse con interpolación suave (desktop solamente)
- **Dock scroll behavior**: la barra inferior aparece al hacer scroll (arriba o abajo) y se oculta tras 1.4s de inactividad
- **Loader inicial**: logo animado mientras carga la página (fade-out al evento `load`)
- **Carruseles infinitos**: `requestAnimationFrame` con `translate3d` (acelerado por GPU)
- **Modal global**: iframe que carga `/reservar` o `/solicitar-factura` con `?embedded=1`

---

## 8. Autenticación y seguridad

### Estado actual

⚠️ **El proyecto NO tiene sistema de autenticación implementado** para la zona admin.

- `/admin` → accesible públicamente
- `/api/facturas/*` → todos los endpoints públicos
- Existe la tabla `users` y el modelo `App\Models\User` por defecto, pero **no se usa en el código del dominio**

### Protecciones presentes

| Mecanismo | Cubre |
|---|---|
| CSRF middleware | Todos los POST/PUT/DELETE (excepto bypass explícito) |
| Validación servidor | Todos los endpoints validan con `$request->validate(...)` |
| Sanitización | `strtolower(email)`, `strtoupper(nif)`, `htmlspecialchars` en emails |
| MIME + tamaño en uploads | Tickets validados como `mimes:jpg,jpeg,png,webp\|max:10240` |
| Nombre de archivo seguro | `uniqid('ticket_', true)` evita path traversal |
| Transacciones BD | `DB::beginTransaction` en operaciones críticas |
| Logging | `Log::error` y `Log::warning` para auditoría posterior |
| Foreign keys | `facturas.id_solicitud` con `restrictOnDelete` |
| Unique constraints | `referencia` y `numero_factura` |

### Lo que falta para producción

| Pendiente | Riesgo si no se añade |
|---|---|
| Auth de admin (login + middleware) | Cualquiera con la URL puede gestionar facturas |
| Rate limiting | DoS con miles de solicitudes/reservas/contactos |
| Captcha o honeypot | Spam de formularios |
| Logs de auditoría (quién hizo qué) | Forense imposible |
| PDFs en disco privado (no `public/`) | Si adivinan el nombre acceden directamente |
| `APP_DEBUG=false` en producción | Stack traces expuestos al usuario |
| HTTPS obligatorio | Credenciales/datos en claro |
| `SESSION_ENCRYPT=true` | Sesiones legibles en disco |

### Configuración de auth actual (`config/auth.php`)

Solo el guard `web` por defecto (sin modificar):
```php
'guards' => ['web' => ['driver' => 'session', 'provider' => 'users']],
'providers' => ['users' => ['driver' => 'eloquent', 'model' => User::class]],
```

---

## 9. Flujos importantes detallados

### 9.1 Cómo se solicita una factura (cliente)

| Paso | Archivo | Acción |
|---|---|---|
| 1 | Navegador | GET `/solicitar-factura` |
| 2 | `routes/web.php` | Match ruta → `SolicitudController@index` |
| 3 | `SolicitudController@index` | `return view('cliente.solicitar-factura')` |
| 4 | Blade | Renderiza HTML completo con CSS + JS embebido |
| 5 | Navegador | Carga vista, JS inicializa listeners |
| 6 | Usuario | Rellena paso 1, pulsa Siguiente |
| 7 | JS | `goStep(2)` valida y muestra paso 2 |
| 8 | Usuario | Sube foto del ticket |
| 9 | JS | `handleFile()` → previsualiza → `procesarTicketOCR(file)` |
| 10 | JS | `fetch /api/ocr/ticket` con FormData |
| 11 | `OcrController@procesarTicket` | Base64 → POST a Vision API → recibe texto |
| 12 | `OcrController::parsearTicket` | Regex extrae fecha, total, líneas, atendido_por |
| 13 | Response | JSON con datos extraídos |
| 14 | JS | Rellena campos `f-fecha`, `f-importe`, guarda en vars `atendidoPorOcr`, `lineasTicketOcr` |
| 15 | Usuario | Pulsa "Revisar solicitud" → paso 3 |
| 16 | JS | `goStep(3)` rellena summary con datos |
| 17 | Usuario | Marca LOPD, pulsa "Enviar solicitud" |
| 18 | JS | `submitFactura()` construye FormData completo |
| 19 | JS | POST a `/api/solicitudes` |
| 20 | `SolicitudController@store` | Valida 13 reglas |
| 21 | Storage | Sube ticket a `storage/app/public/tickets/ticket_xxx.jpg` |
| 22 | `SolicitudController` | Calcula base e IVA |
| 23 | `DB::beginTransaction()` | Abre transacción |
| 24 | `generarReferencia('MW-2026')` | Lock+update contador → `MW-2026-0042` |
| 25 | `SolicitudFactura::create(...)` | INSERT en `solicitudes_factura` |
| 26 | `Factura::create(...)` | INSERT en `facturas` con FK |
| 27 | `DB::commit()` | Confirma transacción |
| 28 | DomPDF | `Pdf::loadView('pdf.factura', $datos)->setPaper('a4')->output()` |
| 29 | `pdf/factura.blade.php` | Renderiza HTML del PDF |
| 30 | Storage | Guarda en `storage/app/public/facturas/Factura_MW-2026-0042.pdf` |
| 31 | `$factura->update(['pdf_path' => ...])` | Persiste path |
| 32 | Response | JSON `{ok, referencia, factura_id, pdf_url}` |
| 33 | JS | Muestra pantalla de éxito con botón "Descargar PDF" |

### 9.2 Cómo se gestiona una factura desde admin

| Paso | Archivo | Acción |
|---|---|---|
| 1 | Admin | GET `/admin` |
| 2 | `FacturaController@index` | `view('admin.index')` |
| 3 | Blade | HTML estático + carga `admin.js` |
| 4 | `admin.js DOMContentLoaded` | `loadFacturas()` |
| 5 | `loadFacturas()` | `fetch /api/facturas` |
| 6 | `FacturaController@listar` | JOIN solicitudes+facturas con COALESCE |
| 7 | Response | JSON array de filas |
| 8 | `admin.js` | `STATE.facturas = response` → `renderTable()` |
| 9 | Admin | Click "Editar" en fila |
| 10 | `openEdit(id)` | Busca en `STATE.facturas`, rellena modal |
| 11 | Admin | Modifica campos, pulsa "Guardar" |
| 12 | `saveModal()` | POST `/api/facturas/{id}` con payload JSON |
| 13 | `FacturaController@actualizar` | Valida + recalcula IVA + `$f->update(...)` |
| 14 | Response | JSON con datos actualizados |
| 15 | `admin.js` | Actualiza `STATE.facturas[i]` in-place → `renderTable()` |
| 16 | Admin | Click "Enviar por email" |
| 17 | `enviarFacturaEmail(id)` | POST `/api/facturas/{id}/email` |
| 18 | `FacturaController@enviarPorEmail` | Si no hay PDF → regenera → adjunta a `FacturaMail` |
| 19 | `Mail::send` | SMTP Gmail envía con adjunto |
| 20 | Response | JSON `{ok, mensaje, email}` |
| 21 | `admin.js` | `showToast('Factura enviada a x@y.com', 'success')` |

### 9.3 Cómo se autentica un usuario

**N/A — no hay sistema de autenticación implementado.**

### 9.4 Cómo se consume la API de reseñas

| Paso | Archivo | Acción |
|---|---|---|
| 1 | Cliente | Carga `/` |
| 2 | `index.blade.php` JS | `fetch /api/resenas` al cargar |
| 3 | `ResenasController@index` | Lee env, llama `Cache::remember(12h, ...)` |
| 4a | Cache HIT | Devuelve resultado cacheado → JSON al cliente |
| 4b | Cache MISS | Itera 5 idiomas, Http::get a Google Places por cada uno |
| 5 | `ResenasController` | Consolida reseñas por fingerprint (autor+timestamp) |
| 6 | Cache::put | Guarda resultado en `storage/framework/cache/data/...` |
| 7 | Response | JSON `{ok, rating_global, total_resenas, url_escribir, resenas:[...]}` |
| 8 | JS | Render summary + 2 filas con shuffle + arranca carruseles |

---

## 10. Métodos importantes

### `SolicitudController::store(Request $request)`

- **Objetivo**: punto de entrada de toda la creación de facturas desde el cliente
- **Parámetros**: Request con 11+ campos validados + archivo `ticket`
- **Retorno**: JSON con referencia, factura_id, pdf_url
- **Flujo interno**: validar → subir archivo → calcular IVA → transacción → generar referencia → INSERT solicitud → INSERT factura → COMMIT → generar PDF → guardar PDF → response
- **Dependencias**: `SolicitudFactura`, `Factura`, `Storage`, `DB`, `Pdf` (DomPDF)
- **Impacto**: si falla la validación → 422; si falla la BD → rollback + 500; si falla el PDF → solicitud creada igualmente, log error

### `OcrController::parsearTicket(string $texto): array`

- **Objetivo**: convertir el texto bruto del OCR en datos estructurados
- **Parámetros**: string con el texto del ticket
- **Retorno**: `array{fecha, hora, lineas:[], total, atendido_por}`
- **Flujo interno**: split por líneas → for-loop con regex para cada patrón conocido
- **Patrones específicos del TPV del restaurante**: formato DD/MM/YYYY, "Total:" europeo, líneas tipo "1 CAFE CON LECHE / 1,40 € / 1,40 €"
- **Limitación**: tickets de otros TPV pueden no parsearse correctamente; el usuario tiene fallback manual

### `SolicitudController::generarReferencia(string $serie): string`

- **Objetivo**: generar números de factura secuenciales y únicos sin colisiones
- **Parámetros**: `'MW-2026'`
- **Retorno**: `'MW-2026-0001'`, `'MW-2026-0002'`, ...
- **Flujo interno**:
  1. `insertOrIgnore` la serie (idempotente)
  2. `SELECT ... FOR UPDATE` (bloquea fila para esta transacción)
  3. `+1`
  4. `UPDATE` el contador
  5. Devuelve formateado
- **Concurrencia**: el `lockForUpdate()` garantiza que dos peticiones simultáneas no obtengan el mismo número. La 2ª espera a que la 1ª haga commit.

### `FacturaController::listar()`

- **Objetivo**: devolver al admin TODAS las facturas+solicitudes en una sola query
- **Particularidad**: usa `COALESCE(f.campo, s.campo)` para mostrar siempre la versión más actual del dato (si hay factura, sus datos; si solo hay solicitud sin factura asociada, los datos de la solicitud)
- **Devuelve**: array de filas con `nombre_display` ya calculado (empresa si existe, si no nombre persona)

### `ResenasController::index()`

- **Objetivo**: devolver reseñas reales de Google con el truco multi-idioma
- **Cache**: 12h en disco
- **Fallback**: si Google falla → devuelve lo que tenga; si nunca consiguió nada → `ok:false`
- **Filtrado**: solo reseñas ≥ 4★

### `FacturaController::descargarPdf($id, Request)`

- **Lazy-generate**: si el PDF existe lo devuelve; si no, lo regenera al vuelo y lo guarda
- **Doble modo**: `?modo=ver` (inline navegador) vs `?modo=descargar` (Content-Disposition: attachment)

### `admin.js::applyFilters()`

- **Objetivo**: motor de filtrado/búsqueda/orden del panel admin
- **Flujo**:
  1. Parte de `STATE.facturas` (todas)
  2. Si `STATE.query` → filtra por nombre/NIF/empresa/email/referencia
  3. Si `STATE.statusFilter !== 'all'` → filtra por estado
  4. `sortFiltered()` ordena por columna activa
  5. `renderTable()` repinta con los visibles
  6. `renderPagination()` genera botones de página
- **Sin servidor**: todo en memoria del navegador

---

## 11. Dependencias

### Composer (`composer.json`)

```json
"require": {
    "php": "^8.2",
    "laravel/framework": "^12.0",        // ← Framework base
    "laravel/tinker": "^2.10.1",         // ← REPL para debug (php artisan tinker)
    "barryvdh/laravel-dompdf": "^3.0"    // ← Generación PDF
},
"require-dev": {
    "fakerphp/faker": "^1.23",           // ← Datos falsos para tests
    "laravel/pail": "^1.2.2",            // ← Tail logs (php artisan pail)
    "laravel/pint": "^1.24",             // ← Code style fixer
    "laravel/sail": "^1.41",             // ← Docker dev environment
    "mockery/mockery": "^1.6",           // ← Mocking en tests
    "nunomaduro/collision": "^8.6",      // ← Errores bonitos en CLI
    "phpunit/phpunit": "^11.5.50"        // ← Tests
}
```

**Las únicas dependencias "de verdad" del proyecto**:
- `laravel/framework`: framework completo
- `barryvdh/laravel-dompdf`: generación PDF de facturas

Todo lo demás es Laravel base + herramientas dev.

### NPM (`package.json`)

```json
"devDependencies": {
    "@tailwindcss/vite": "^4.0.0",       // ← Tailwind 4 plugin Vite
    "axios": "^1.11.0",                  // ← HTTP client (no se usa, fetch directo)
    "concurrently": "^9.0.1",            // ← Multi-comandos
    "laravel-vite-plugin": "^2.0.0",     // ← Integración Vite+Laravel
    "tailwindcss": "^4.0.0",             // ← CSS framework (no se usa en vistas)
    "vite": "^7.0.7"                     // ← Build tool
}
```

**El frontend NO usa NPM**. Todo el CSS está en `public/css/*.css` servido directamente, y el JS en `public/js/admin.js`. Vite/Tailwind están preparados pero no son obligatorios para ejecutar el proyecto.

---

## 12. Configuración y despliegue

### `.env` requerido

```env
# Aplicación
APP_NAME="Miss Whitney"
APP_ENV=local                  # En producción: production
APP_KEY=base64:...             # Generar con: php artisan key:generate
APP_DEBUG=true                 # En producción: false
APP_URL=https://...            # URL completa con subdir si aplica
APP_LOCALE=es                  # Importante para fechas en español (Carbon)
APP_FALLBACK_LOCALE=es

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=debug

# Base de datos
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tudb
DB_USERNAME=tuuser
DB_PASSWORD=tupass

# Sesiones / cache
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
CACHE_STORE=file
QUEUE_CONNECTION=sync          # Si quieres queues async: database / redis
FILESYSTEM_DISK=local

# Mail (SMTP Gmail)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tuemail@gmail.com
MAIL_PASSWORD=tu_app_password   # NO contraseña normal, App Password de Gmail
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=tuemail@gmail.com
MAIL_FROM_NAME="Miss Whitney"

# APIs externas
GOOGLE_VISION_API_KEY=AIzaSy...       # Usa la misma key para Vision Y Places
GOOGLE_PLACE_ID=ChIJ...               # ID del lugar en Google Places
```

### Instalación

```bash
# 1. Clonar
git clone ... mw_laravelWeb
cd mw_laravelWeb

# 2. Dependencias PHP
composer install

# 3. Variables de entorno
cp .env.example .env
# editar .env con valores reales
php artisan key:generate

# 4. Base de datos
# Crear BD vacía en phpMyAdmin / mysql shell
php artisan migrate

# 5. Storage
php artisan storage:link
# Esto crea public/storage → storage/app/public/
# Para que los tickets/facturas sean accesibles vía web

# 6. (Opcional) Compilar assets
npm install
npm run build

# 7. Servidor
php artisan serve
# o configurar Apache/Nginx
```

### Permisos en producción

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Limpieza de caché tras cambios

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

Si no hay acceso SSH, el proyecto incluye `public/cc.php` (script auxiliar protegido por token) que ejecuta lo equivalente desde el navegador.

---

## 13. Mejoras futuras

<!--
  ESTA SECCIÓN ESTÁ INTENCIONALMENTE VACÍA.
  El propietario del proyecto la rellenará manualmente con sus propias propuestas
  de mejora identificadas tras el análisis del sistema.
-->

---

> 📚 **Documentación generada el 13 de mayo de 2026 — Proyecto Miss Whitney v actual**
> Basado en análisis exhaustivo del código fuente provisto. No incluye especulaciones ni elementos no presentes en el código real.
