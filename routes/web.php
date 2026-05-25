<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{SolicitudController, FacturaController, OcrController, ResenasController};

// ── Páginas cliente ───────────────────────────────────────────────
Route::get('/',                  fn() => view('cliente.index'))->name('home');
Route::get('/carta',             fn() => view('cliente.carta'))->name('carta');
Route::get('/galeria',           fn() => view('cliente.galeria'))->name('galeria');
Route::get('/reservar',          fn() => view('cliente.reservar'))->name('reservar');
Route::get('/contacto',          fn() => view('cliente.contacto'))->name('contacto');
Route::get('/politica-cookies',  fn() => view('cliente.politica-cookies'))->name('politica.cookies');
Route::get('/solicitar-factura', [SolicitudController::class, 'index'])->name('solicitar.factura');

// ── API cliente ───────────────────────────────────────────────────
Route::post('/api/solicitudes',  [SolicitudController::class, 'store'])->name('api.solicitudes.store');
Route::post('/api/ocr/ticket',   [OcrController::class, 'procesarTicket'])->name('api.ocr.ticket');
Route::get('/api/resenas',       [ResenasController::class, 'index'])->name('api.resenas');

// ── Admin (página) ────────────────────────────────────────────────
Route::get('/admin', [FacturaController::class, 'index'])->name('admin.index');

// ── API admin ─────────────────────────────────────────────────────
Route::prefix('api/facturas')->group(function () {
    Route::get('/',                [FacturaController::class, 'listar']);
    Route::post('/',               [FacturaController::class, 'crear']);
    Route::get('/zip',             [FacturaController::class, 'downloadZip']); // debe ir antes de /{id}/pdf
    Route::post('/{id}/estado',    [FacturaController::class, 'cambiarEstado']);
    Route::post('/{id}/delete',    [FacturaController::class, 'eliminar']);   // POST alias para DELETE
    Route::post('/{id}/email',     [FacturaController::class, 'enviarPorEmail']);
    Route::delete('/{id}',         [FacturaController::class, 'eliminar']);   // DELETE real
    Route::post('/{id}',           [FacturaController::class, 'actualizar']); // debe ir después de /estado, /delete y /email
    Route::get('/{id}/pdf',        [FacturaController::class, 'descargarPdf']);
});

// ── Contacto (formulario de mensaje) ──────────────────────────────
Route::post('/api/contacto', [App\Http\Controllers\ContactoController::class, 'enviar'])->name('api.contacto');

// ── Reservas ──────────────────────────────────────────────────────
Route::post('/api/reserva', [App\Http\Controllers\ReservaController::class, 'enviar'])->name('api.reserva');
