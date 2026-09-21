<?php

use App\Http\Controllers\Admin\Manual\ManualController;
use App\Http\Controllers\Admin\Pos\ExtraPosController;
use App\Http\Controllers\Admin\Pos\PosController;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth'])->group(function () {
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');

    Route::get('/offline', [PosController::class, 'offline'])->name('pos.offline');
    Route::get('/pos/paginate/filters', [PosController::class, 'ajaxLoad'])->name('pos.filter.index');
    Route::get('/offline/paginate/filters', [PosController::class, 'offline'])->name('pos.offline.filter');


    Route::get('/offline/extra', [ExtraPosController::class, 'offline'])->name('extra.pos.offline');
    Route::get('/offline/paginate/filters/extra', [ExtraPosController::class, 'offline'])->name('extra.pos.offline.filter');



    Route::get('offline/{order}/view', [PosController::class, 'view'])->name('orders.view');
    Route::post('/pos', [PosController::class, 'sale'])->name('pos.sale');
    Route::get('offline/{order}/print', [PosController::class, 'print'])->name('pos.print');


    Route::get('/manual', [ManualController::class, 'index'])->name('manual.index');
    Route::post('/manual', [ManualController::class, 'sale'])->name('manual.sale');
    Route::get('/manual/paginate/filters', [ManualController::class, 'ajaxLoad'])->name('manual.filter.index');

});
