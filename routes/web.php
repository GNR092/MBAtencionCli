<?php

use App\Http\Controllers\Admin\LogoController;
use App\Http\Controllers\AdminChatController;
use App\Http\Controllers\AnuncioController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AvisoController;
use App\Http\Controllers\CfdiValidatorController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\crudUser;
use App\Http\Controllers\CuentasPorCobrar;
use App\Http\Controllers\CuentasPorPagar;
use App\Http\Controllers\EfectivoController;
use App\Http\Controllers\EstadoController;
use App\Http\Controllers\Factura\UserFactController;
use App\Http\Controllers\GenerateController;
use App\Http\Controllers\ImpuestoController;
use App\Http\Controllers\IncrementoImporteController;
use App\Http\Controllers\ListController;
use App\Http\Controllers\PasswordCheckController;
use App\Http\Controllers\ProyectoController;
use App\Http\Controllers\RazonSocialController;
use App\Http\Controllers\RegimenFiscalController;
use App\Http\Controllers\RetroactivoController;
use App\Http\Controllers\UploadFactura;
use App\Http\Controllers\UserChatController;
use App\Http\Controllers\UserViewController;
use App\Http\Middleware\AuthUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        if (Auth::user()->role === 'administrador') {
            return redirect('/cuentas-por-pagar');
        }

        return redirect('/user/dashboard');
    }

    return view('welcome');
});

Route::get('/inicio-de-sesion', [AuthController::class, 'showLoginForm'])->name('login.form');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware([AuthUser::class.':usuario'])->group(function () {
    Route::get('/user/dashboard', [UserViewController::class, 'index'])->name('user.dashboard');

    // Facturación
    Route::get('/user/facturacion', [CfdiValidatorController::class, 'index'])->name('user.facturacion');
    Route::post('/upload-xml', [CfdiValidatorController::class, 'uploadXmlFiles'])->name('facturacion.subir-xml');
    Route::post('/upload-pdf', [CfdiValidatorController::class, 'uploadPdf'])->name('facturacion.subir-pdf');
    Route::post('/reset-batch', [CfdiValidatorController::class, 'resetBatch'])->name('facturacion.reset');
    Route::post('/validar-xml', [CfdiValidatorController::class, 'store'])->name('facturacion.validar');
    Route::get('/user-factura/reset', [UserFactController::class, 'resetFactura'])->name('user.factura.reset');
    Route::get('/user-factura/{index?}', [UserFactController::class, 'showInvoice'])->name('user.factura.view');
    Route::get('/user-factura/{index}/view-pdf', [UserFactController::class, 'viewPdf'])->name('user.factura.view-pdf');
    Route::post('/user-factura/{index}/upload-pdf', [UserFactController::class, 'uploadPdf'])->name('user.factura.upload-pdf');
    Route::post('/user-factura/{index}/confirm', [UserFactController::class, 'confirmFactura'])->name('user.factura.confirm');
    Route::delete('/user-factura/{index}/delete', [UserFactController::class, 'deleteFactura'])->name('user.factura.delete');

    // Notificaciones (página completa - solo usuarios)
    Route::get('/notificaciones', [AvisoController::class, 'index'])->name('notificaciones.index');

    // Cuentas por cobrar
    Route::get('/cuentas-por-cobrar', [CuentasPorCobrar::class, 'index'])->name('cuentas-cobrar.index');
    Route::get('/cuentas-por-cobrar/limpiar', [CuentasPorCobrar::class, 'limpiar'])->name('cuentas-cobrar.limpiar');
    Route::get('/cuentas-por-cobrar/grafica-anual/{year}', [CuentasPorCobrar::class, 'graficaAnualNoPagados']);

    // Estados de cuenta
    Route::get('/estados-de-cuenta', [EstadoController::class, 'index'])->name('estados-cuenta.index');
    Route::get('/estados-de-cuenta/limpiar', [EstadoController::class, 'limpiar'])->name('estados-cuenta.limpiar');
    Route::get('/estados-de-cuenta/grafica-anual-pagados/{year}', [EstadoController::class, 'graficaAnualPagados']);
    Route::post('/estado-de-cuenta/pdf', [EstadoController::class, 'descargarPdf'])->name('estados-cuenta.pdf');

    // Contratos (usuario)
    // Efectivo (usuario)
    Route::get('/user/efectivo', [EfectivoController::class, 'userIndex'])->name('user.efectivo.index');

    Route::get('/contratos', [ContractController::class, 'index'])->name('contratos.index');
    Route::post('/contratos/buscar', [ContractController::class, 'buscar'])->name('contratos.buscar');
    Route::get('/contratos/limpiar', [ContractController::class, 'limpiar'])->name('contratos.limpiar');
    Route::get('/contratos/descargar/{id}', [ContractController::class, 'descargar'])->name('contratos.descargar');

    Route::put('/perfil/foto', [UserViewController::class, 'actualizarFoto'])->name('perfil.foto');

    Route::get('/chat/messages', [UserChatController::class, 'getMessages'])->name('chat.getMessages');
    Route::post('/chat/messages', [UserChatController::class, 'sendMessage'])->name('chat.sendMessage');
});

Route::middleware([AuthUser::class.':administrador'])->group(function () {

    // Logos
    Route::get('/admin/logos', [LogoController::class, 'index'])->name('admin.logos.index');
    Route::post('/admin/logos', [LogoController::class, 'store'])->name('admin.logos.store');
    Route::post('/admin/logos/{logo}/toggle', [LogoController::class, 'toggle'])->name('admin.logos.toggle');
    Route::delete('/admin/logos/{logo}', [LogoController::class, 'destroy'])->name('admin.logos.destroy');

    // Registro y administración de usuarios
    Route::get('/registro_user', [GenerateController::class, 'index'])->name('usuarios.registro');
    Route::post('/registro_user', [GenerateController::class, 'datos'])->name('usuarios.registro.store');

    Route::get('/admi_user', [crudUser::class, 'index'])->name('usuarios.index');
    Route::post('/users', [crudUser::class, 'store'])->name('usuarios.store');
    Route::post('/users/confirm-password', [crudUser::class, 'confirmPassword'])->name('usuarios.confirmar');
    Route::get('/users/edit/{id}', [crudUser::class, 'showEditForm'])->name('usuarios.editar');
    Route::post('/users/update', [crudUser::class, 'editar'])->name('usuarios.actualizar');
    Route::post('/users/delete', [crudUser::class, 'eliminar'])->name('usuarios.eliminar');
    Route::post('/users/buscar', [crudUser::class, 'buscar'])->name('usuarios.buscar');
    Route::get('/users/limpiar', [crudUser::class, 'limpiar'])->name('usuarios.limpiar');
    Route::get('/cumpleanios', [crudUser::class, 'cumpleanios'])->name('usuarios.cumpleanios');

    // Cuentas por pagar
    Route::get('/cuentas-por-pagar', [CuentasPorPagar::class, 'index'])->name('cuentas-pagar.index');
    Route::post('/cuentasporpagar/{id}/estado', [App\Http\Controllers\CuentasPorPagar::class, 'actualizarEstado']);
    Route::get('/cuentas-por-pagar/limpiar', [CuentasPorPagar::class, 'limpiar'])->name('cuentas-pagar.limpiar');
    Route::post('/cuentas-por-pagar/export', [CuentasPorPagar::class, 'export'])->name('cuentas-pagar.export');
    Route::get('/cuentas/grafica-anual/{year}', [CuentasPorPagar::class, 'graficaAnual']);
    Route::get('/cuentas/grafica-anual-proyecto/{year}/{id_proyecto}', [CuentasPorPagar::class, 'graficaAnualProyecto']);
    Route::get('/api/cuentas/meses-facturados/{year}', [CuentasPorPagar::class, 'mesesConFacturas']);

    // Contratos (administrador)
    Route::post('/subir-archivo', [ContractController::class, 'subir'])->name('admin.contratos.store');
    Route::get('/subir-archivo', [ContractController::class, 'show'])->name('admin.contratos.index');
    Route::post('/subir-archivo/confirm-password', [ContractController::class, 'confirmPassword'])->name('admin.contratos.confirmar');
    Route::get('/subir-archivo/crear', [ContractController::class, 'crear'])->name('admin.contratos.create');
    Route::post('/subir-archivo/delete', [ContractController::class, 'delete'])->name('admin.contratos.eliminar');
    Route::post('/subir-archivo/confirm-password-editar', [ContractController::class, 'confirmPasswordEdit'])->name('admin.contratos.confirmar-editar');
    Route::get('/subir-archivo/{id}/editar', [ContractController::class, 'editar'])->name('admin.contratos.editar');
    Route::put('/subir-archivo/{id}/actualizar', [ContractController::class, 'actualizar'])->name('admin.contratos.actualizar');
    Route::get('/subir-archivo/clean', [ContractController::class, 'clean'])->name('admin.contratos.limpiar');
    Route::post('/subir-archivo/search', [ContractController::class, 'search'])->name('admin.contratos.buscar');
    Route::get('/api/users/{user}/projects', [ContractController::class, 'getProjectsForUser'])->name('api.usuarios.proyectos');

    // Avisos
    Route::get('/enviar-avisos', [AvisoController::class, 'showForm'])->name('avisos.index');
    Route::get('/api/usuarios/buscar', [AvisoController::class, 'buscarUsuarios'])->name('api.usuarios.buscar');
    Route::post('/avisos', [AvisoController::class, 'store'])->name('avisos.store');

    // Lista de inversionistas
    Route::get('/lista-de-inversionistas', [ListController::class, 'index'])->name('inversionistas.index');
    Route::get('/lista-de-inversionistas/limpiar', [ListController::class, 'limpiar'])->name('inversionistas.limpiar');

    // Facturas
    Route::get('/facturas', [UploadFactura::class, 'index'])->name('facturas.index');
    Route::get('/facturas/descargar/{id}', [UploadFactura::class, 'descargar'])->name('facturas.descargar');
    Route::get('/facturas/pdf/{id}', [UploadFactura::class, 'descargarPdf'])->name('facturas.descargarPdf');
    Route::post('/facturas/buscar', [UploadFactura::class, 'buscar'])->name('facturas.buscar');
    Route::get('/facturas/limpiar', [UploadFactura::class, 'limpiar'])->name('facturas.limpiar');

    // Efectivo (admin)
    Route::get('/efectivo', [EfectivoController::class, 'index'])->name('admin.efectivo.index');

    // Retroactivo
    Route::get('/retroactivo', [RetroactivoController::class, 'index'])->name('retroactivo.index');
    Route::get('/retroactivo/eliminados', [RetroactivoController::class, 'eliminados'])->name('retroactivo.eliminados');
    Route::post('/retroactivo/{id}/eliminar', [RetroactivoController::class, 'eliminar'])->name('retroactivo.eliminar');
    Route::post('/retroactivo/eliminados/{id}/restaurar', [RetroactivoController::class, 'restaurar'])->name('retroactivo.restaurar');
    Route::delete('/retroactivo/eliminados/{id}/destroy', [RetroactivoController::class, 'destroyPermanente'])->name('retroactivo.destroy');
    Route::post('/efectivo', [EfectivoController::class, 'store'])->name('admin.efectivo.store');

    // Impuestos
    Route::get('/impuestos', [ImpuestoController::class, 'index'])->name('impuestos.index');
    Route::get('/impuestos/limpiar', [ImpuestoController::class, 'limpiar'])->name('impuestos.limpiar');
    Route::post('/impuestos/export', [ImpuestoController::class, 'export'])->name('impuestos.export');

    Route::resource('incrementos', IncrementoImporteController::class);

    // Chat administrador
    Route::get('/admin/users/chat-directory', [AdminChatController::class, 'showUserChatDirectory'])->name('admin.users.chat-directory');
    Route::get('/admin/chat/messages/{userId}', [AdminChatController::class, 'getMessages'])->name('admin.chat.getMessages');
    Route::post('/admin/chat/messages/{userId}', [AdminChatController::class, 'sendMessage'])->name('admin.chat.sendMessage');

    // Anuncios
    Route::get('/anuncios-admin', [AnuncioController::class, 'index'])->name('admin.anuncios.index');
    Route::post('/anuncios-admin', [AnuncioController::class, 'store'])->name('admin.anuncios.store');
    Route::post('/anuncios-admin/{id}/toggle', [AnuncioController::class, 'toggleStatus'])->name('admin.anuncios.toggle');
    Route::put('/anuncios-admin/{id}', [AnuncioController::class, 'update'])->name('admin.anuncios.update');
    Route::delete('/anuncios-admin/{id}', [AnuncioController::class, 'destroy'])->name('admin.anuncios.destroy');

    Route::resource('regimen-fiscal', RegimenFiscalController::class);
    Route::resource('proyectos', ProyectoController::class);
    Route::resource('razones-sociales', RazonSocialController::class);
});

Route::middleware([AuthUser::class])->group(function () {
    Route::get('/api/notifications/unread-count', [AvisoController::class, 'unreadCount'])->name('notificaciones.no-leidas');
    Route::get('/api/notifications/list', [AvisoController::class, 'apiNotifications'])->name('notificaciones.api-list');
    Route::post('/notificaciones/{id}/leer', [AvisoController::class, 'markAsRead'])->name('notificaciones.leer');
    Route::delete('/notificaciones/delete/{id}', [AvisoController::class, 'delete'])->name('notificaciones.eliminar');
});

Route::post('/password-check', [PasswordCheckController::class, 'check'])->name('password.check');
