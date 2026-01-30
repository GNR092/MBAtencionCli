<?php
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\ContractController;
use App\Http\Middleware\AuthUser;
use App\Http\Controllers\CfdiValidatorController;
use App\Http\Controllers\AvisoController;
use App\Http\Controllers\PasswordCheckController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\ListController;
use App\Http\Controllers\GenerateController;
use App\Http\Controllers\UploadFactura;
use App\Http\Controllers\crudUser;
use App\Http\Controllers\ImpuestoController;
use App\Http\Controllers\CuentasPorPagar;
use App\Http\Controllers\CuentasPorCobrar;
use App\Http\Controllers\UserViewController;
use App\Http\Controllers\EstadoController;
use App\Http\Controllers\AdminChatController; // Added for admin chat directory
use App\Http\Controllers\UserChatController; // Added for user chat functionality
use Barryvdh\DomPDF\Facade\Pdf;

// Rutas públicas
Route::get('/', function () {
    return view('welcome');
});

// Login
Route::get('/inicio-de-sesion', [AuthController::class, 'showLoginForm'])->name('login.form');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Rutas para usuarios
Route::middleware([AuthUser::class.':usuario'])->group(function () {
    Route::get('/vista-usuario', [UserViewController::class, 'index']);
    //rutas de factura
    Route::get('/facturacion', [CfdiValidatorController::class, 'index'])->name('facturacion');
    Route::post('/upload-xml', [CfdiValidatorController::class, 'uploadXmlFiles'])->name('upload-xml');
    Route::post('/upload-pdf', [CfdiValidatorController::class, 'uploadPdf'])->name('upload-pdf');
    Route::post('/reset-batch', [CfdiValidatorController::class, 'resetBatch'])->name('reset-batch');
    Route::post('/validar-xml', [CfdiValidatorController::class, 'store'])->name('validar-xml');
    
    //ruta de notificaciones
    Route::get('/notificaciones', [AvisoController::class, 'index'])->name('notificaciones.index');
    Route::post('/notificaciones/{id}/leer', [AvisoController::class, 'markAsRead'])->name('notifications.read');
    Route::delete('/notificaciones/delete/{id}', [AvisoController::class, 'delete'])->name('notifications.delete');
    Route::get('/api/notifications/unread-count', [AvisoController::class, 'unreadCount'])->name('notifications.unreadCount'); // New API endpoint
   //rutas de cuentas por cobrar
    Route::get('/cuentas-por-cobrar',[CuentasPorCobrar::class,'index'])->name('cuentasCobrar');
    Route::get('/cuentas-por-cobrar/limpiar',[CuentasPorCobrar::class,'limpiar'])->name('cuentasCobrar.limpiar');
    Route::get('/cuentas-por-cobrar/grafica-anual/{year}', [CuentasPorCobrar::class, 'graficaAnualNoPagados']);
    
    //ruta de estados de cuenta
    Route::get('/estados-de-cuenta', [EstadoController::class, 'index'])->name('estadosDeCuenta');
    Route::get('/estados-de-cuenta/limpiar', [EstadoController::class, 'limpiar'])->name('estadosDeCuenta.limpiar');
    Route::get('/estados-de-cuenta/grafica-anual-pagados/{year}', [EstadoController::class, 'graficaAnualPagados']);
    //Route::get('/estado-de-cuenta/pdf/{id}', [EstadoController::class, 'descargarPdf'])->name('estadoCuenta.pdf');
    Route::post('/estado-de-cuenta/pdf', [EstadoController::class, 'descargarPdf'])->name('estadoCuenta.descargarPdf');


//rutas de contratos
    Route::get('/contratos', [ContractController::class, 'index'])->name('contratos.index');
    //rutas para buscar y limpiar filtros contrato
    Route::post('/contratos/buscar', [ContractController::class, 'buscar'])->name('contratos.buscar');
    Route::get('/contratos/limpiar', [ContractController::class, 'limpiar'])->name('contratos.limpiar');
    Route::get('/contratos/descargar/{id}', [ContractController::class, 'descargar'])->name('contratos.descargar');
    Route::put('/perfil/foto', [UserViewController::class, 'actualizarFoto'])->name('perfil.foto');

    // Chat routes for user
    Route::get('/chat/messages', [UserChatController::class, 'getMessages'])->name('chat.getMessages');
    Route::post('/chat/messages', [UserChatController::class, 'sendMessage'])->name('chat.sendMessage');
});

// Rutas para administradores
Route::middleware([AuthUser::class.':administrador'])->group(function () {
    //ruta de registro de usuarios
    Route::get('/registro_user', [GenerateController::class, 'index'])->name('registroUsuarios.index');
    Route::post('/registro_user', [GenerateController::class, 'datos'])->name('registroUsuarios.datos');
    //ruta de administracion de usuarios
    Route::get('/admi_user', [crudUser::class, 'index'])->name('admiUsers');
    Route::post('/users/confirm-password', [crudUser::class, 'confirmPassword'])->name('users.confirmPassword');
    Route::get('/users/edit/{id}', [crudUser::class, 'showEditForm'])->name('users.edit');
    Route::post('/users/update', [crudUser::class, 'editar'])->name('users.update');
    Route::post('/users/delete', [crudUser::class, 'eliminar'])->name('users.eliminar');
    Route::post('/users/buscar', [crudUser::class, 'buscar'])->name('users.buscar');
    Route::get('/users/limpiar', [crudUser::class, 'limpiar'])->name('users.limpiar');
    //cuentas por pagar
    Route::get('/cuentas-por-pagar', [CuentasPorPagar::class,'index'])->name('viewAdministrador');
    Route::post('/cuentasporpagar/{id}/estado', [App\Http\Controllers\CuentasPorPagar::class, 'actualizarEstado']);
    Route::get('/cuentas-por-pagar/limpiar',[CuentasPorPagar::class,'limpiar'])->name('viewAdministrador.limpiar');
    Route::post('/cuentas-por-pagar/export', [CuentasPorPagar::class, 'export'])->name('viewAdministrador.export');
    // Gráfica anual
    Route::get('/cuentas/grafica-anual/{year}', [CuentasPorPagar::class, 'graficaAnual']);
    Route::get('/cuentas/grafica-anual-proyecto/{year}/{proyecto}', [CuentasPorPagar::class, 'graficaAnualProyecto']);
    //rutas de administracion de contratos

    Route::post('/subir-archivo', [ContractController::class, 'subir']);
    Route::get('/subir-archivo', [ContractController::class, 'show'])->name('contratos.show');
    Route::post('/subir-archivo/confirm-password', [ContractController::class, 'confirmPassword'])->name('contratos.confirmPassword');
    Route::get('/subir-archivo/crear', [ContractController::class, 'crear'])->name('contratos.crear');
    Route::post('/subir-archivo/delete', [ContractController::class, 'delete'])->name('contratos.delete');
    Route::post('/subir-archivo/confirm-password-editar', [ContractController::class, 'confirmPasswordEdit'])->name('contratos.confirmPasswordEdit');
    Route::get('/subir-archivo/{id}/editar', [ContractController::class, 'editar'])->name('contratos.editar');
    Route::put('/subir-archivo/{id}/actualizar', [ContractController::class, 'actualizar'])->name('contratos.actualizar');
    Route::get('/subir-archivo/clean', [ContractController::class, 'clean'])->name('contratos.clean');
    Route::post('/subir-archivo/search', [ContractController::class, 'search'])->name('contratos.search');
    //rutas de envio de avisos
    Route::get('/enviar-avisos', fn() => view('enviarAvisos'));
    //Route::post('/enviar-aviso', [AvisoController::class, 'store'])->name('avisos.store');
    Route::post('/avisos', [AvisoController::class, 'store'])->name('avisos.store');
    //lista de inversionistas
    Route::get('/lista-de-inversionistas', [ListController::class,'index'])->name('listInver');
    Route::get('/lista-de-inversionistas/limpiar', [ListController::class, 'limpiar'])->name('listInver.limpiar');
    //ruta de generar facturas
    Route::get('/facturas', [UploadFactura::class, 'index'])->name('facturas');
    Route::get('/facturas/descargar/{id}', [UploadFactura::class, 'descargar'])->name('facturas.descargar');
    Route::get('/facturas/pdf/{id}', [UploadFactura::class, 'descargarPdf'])->name('facturas.descargarPdf');
    route::post('/facturas/buscar', [UploadFactura::class, 'buscar'])->name('facturas.buscar');
    route::get ('/facturas/limpiar',[UploadFactura::class,'limpiar'])->name('facturas.limpiar');

    //ruta de impuestos
    Route::get('/inpuestos', [ImpuestoController::class,'index'])->name('inpuestos');
    Route::get('/inpuestos/limpiar',[ImpuestoController::class,'limpiar'])->name('inpuestos.limpiar');
    Route::post('/inpuestos/export', [ImpuestoController::class, 'export'])->name('inpuestos.export');

        Route::resource('incrementos', IncrementoImporteController::class);

    

        // Admin User Chat Directory

        Route::get('/admin/users/chat-directory', [AdminChatController::class, 'showUserChatDirectory'])->name('admin.users.chat-directory');

    

        // Chat routes for admin

        Route::get('/admin/chat/messages/{userId}', [AdminChatController::class, 'getMessages'])->name('admin.chat.getMessages');

        Route::post('/admin/chat/messages/{userId}', [AdminChatController::class, 'sendMessage'])->name('admin.chat.sendMessage');

    });

// Validación de contraseña AJAX
Route::post('/password-check', [PasswordCheckController::class, 'check'])
    ->name('password.check');

    