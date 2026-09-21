<?php

use App\Http\Controllers\Admin\AccessController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\BulkDiscountController;
use App\Http\Controllers\Admin\CouponCodeController;
use App\Http\Controllers\Admin\CourierController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExtraDashboardController;
use App\Http\Controllers\Admin\InternationalOrderControler;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\Product\ProductController;
use App\Http\Controllers\Admin\RoleAccessController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\SteadfastController;
use App\Http\Controllers\Admin\StockTransferController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ExtraOrderController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ReportExtraController;

Route::middleware(['auth'])->group(function () {


    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/orders', [DashboardController::class, 'orders'])->name('dashboard.orders');

    Route::get('/dashboard/extra', [ExtraDashboardController::class, 'index'])->name('dashboard.extra');
    Route::get('/dashboard/orders/extra', [ExtraDashboardController::class, 'orders'])->name('dashboard.orders');

    // Order management
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/order/paginate/filters', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/order/paginate/filters/{id}/show', [OrderController::class, 'show'])->name('orders.show');
    Route::get('orders/{order}/view', [OrderController::class, 'view'])->name('orders.view');
    Route::get('orders/{order}/print', [OrderController::class, 'print'])->name('orders.print');
    Route::post('orders/{order}', [OrderController::class, 'update'])->name('orders.update');
    Route::get('orders/{order}/show', [OrderController::class, 'show'])->name('orders.show');
    Route::get('orders/{order}/show/preorder', [OrderController::class, 'preorder'])->name('orders.preorder');
    Route::get('orders/{additionalKey}/items_update', [OrderController::class, 'destroy'])->name('orders.item.update');


    // Order management
    Route::get('orders/extra', [ExtraOrderController::class, 'index'])->name('orders.extra.index');
    Route::get('/order/paginate/filters/extra', [ExtraOrderController::class, 'index'])->name('orders.extra.index');
    Route::get('/order/paginate/filters/{id}/show/extra', [ExtraOrderController::class, 'show'])->name('orders.extra.show');
    Route::get('orders/{order}/view/extra', [ExtraOrderController::class, 'view'])->name('orders.extra.view');
    Route::get('orders/{order}/print/extra', [ExtraOrderController::class, 'print'])->name('orders.extra.print');
    Route::post('orders/{order}/extra', [ExtraOrderController::class, 'update'])->name('orders.extra.update');
    Route::get('orders/{order}/show/extra', [ExtraOrderController::class, 'show'])->name('orders.extra.show');
    Route::get('orders/{order}/show/preorder/extra', [ExtraOrderController::class, 'preorder'])->name('orders.extra.preorder');
    Route::get('orders/{additionalKey}/items_update/extra', [ExtraOrderController::class, 'destroy'])->name('orders.extra.item.update');


    // International order
    Route::get('international_order', [InternationalOrderControler::class, 'index'])->name('interanational.index');
    Route::get('/international/paginate/filters', [InternationalOrderControler::class, 'index'])->name('interanational.filters');
    Route::get('international/{orderId}/print', [InternationalOrderControler::class, 'print'])->name('interanational.print');
    Route::post('international_order/{orderId}', [InternationalOrderControler::class, 'update'])->name('interanational.update');
    Route::get('international/{orderNumber}/show', [InternationalOrderControler::class, 'show'])->name('interanational.show');



    Route::get('/product', [ProductController::class, 'index'])->name('product.index');
    Route::post('/product', [ProductController::class, 'store'])->name('product.store');
    Route::get('/product/create', [ProductController::class, 'create'])->name('product.create');

    Route::get('/product/{id}/medias', [ProductController::class, 'medias'])->name('product.media');
    Route::post('/product/{id}/medias', [ProductController::class, 'mediaUpdate'])->name('product.media.update');

    Route::get('/product/{id}/additional', [ProductController::class, 'additional'])->name('product.additional');
    Route::post('/product/{id}/additional', [ProductController::class, 'additionalUpdate'])->name('product.additional.update');


    Route::get('/product/paginate/filters', [ProductController::class, 'index'])->name('product.paginate');
    Route::get('/product/{id}/edit', [ProductController::class, 'edit'])->name('product.edit');
    Route::post('/product/{id}', [ProductController::class, 'update'])->name('product.update');


    // Stock Transfer
    Route::get('stock_transfer', [StockTransferController::class, 'index'])->name('stock.transfer.index');
    Route::get('/stock_transfer/create', [StockTransferController::class, 'create'])->name('stock.transfer.create');
    Route::post('/stock_transfer', [StockTransferController::class, 'store'])->name('stock.transfer.store');
    Route::get('/stock_transfer/{id}/getStock', [StockTransferController::class, 'getStock'])->name('stock.transfer.getStock');

    Route::post('/stock_transfer/{id}', [StockTransferController::class, 'update'])->name('stock.transfer.update');




    // Branch
    Route::get('/branches', [BranchController::class, 'index'])->name('branch.index');
    Route::get('/branches/{id}/edit', [BranchController::class, 'edit'])->name('branch.edit');
    Route::post('/branches', [BranchController::class, 'store'])->name('branch.store');
    Route::post('/branches/{id}', [BranchController::class, 'update'])->name('branch.update');
    Route::get('branches/create', [BranchController::class, 'create'])->name('branch.create');


    //Brand
    Route::get('/role', [RoleController::class, 'index'])->name('role.index');
    Route::post('/role/paginate/filters', [RoleController::class, 'index'])->name('role.index');
    Route::get('/role/paginate/filters', [RoleController::class, 'index'])->name('role.index');

    Route::get('/role/create', [RoleController::class, 'create'])->name('role.create');
    Route::post('/role', [RoleController::class, 'store'])->name('role.store');
    Route::get('/role/{id}', [RoleController::class, 'show'])->name('role.show');
    Route::get('/role/{id}/edit', [RoleController::class, 'edit'])->name('role.edit');
    Route::post('/role/{id}', [RoleController::class, 'update'])->name('role.update');

    Route::delete('/role/{id}', [RoleController::class, 'destroy'])->name('role.destroy');

    // User Access
    Route::get('user_access', [AccessController::class, 'index'])->name('access.index');
    Route::get('user_access/{id}/edit', [AccessController::class, 'edit'])->name('access.edit');
    Route::post('user_access/{id}/edit', [AccessController::class, 'update'])->name('access.update');

    // Role Access
    Route::get('role_access', [RoleAccessController::class, 'index'])->name('role.access.index');
    Route::get('role_access/{id}/edit', [RoleAccessController::class, 'edit'])->name('role.access.edit');
    Route::post('role_access/{id}/edit', [RoleAccessController::class, 'update'])->name('role.access.update');


    // Steadfast courier
    Route::get('steadfast', [SteadfastController::class, 'index'])->name('steadfast.index');
    Route::get('steadfast/paginate/filters', [SteadfastController::class, 'index'])->name('steadfast.paginate.index');
    Route::get('steadfast/balance', [SteadfastController::class, 'balance'])->name('steadfast.balance');
    Route::post('steadfast/orders/bulk-send', [SteadfastController::class, 'bulkSend'])->name('steadfast.bulk.send');
    Route::post('steadfast/order/{orderNo}/send', [SteadfastController::class, 'send'])->name('steadfast.send');
    Route::get('steadfast/order/{orderNo}/status', [SteadfastController::class, 'status'])->name('steadfast.status');


    // Courier
    Route::get('courier', [CourierController::class, 'index'])->name('role.access.index');
    Route::get('courier/{id}/edit', [CourierController::class, 'edit'])->name('role.access.edit');
    Route::post('courier/{id}/edit', [CourierController::class, 'update'])->name('role.access.update');
    Route::get('/courier/paginate/filters', [CourierController::class, 'index'])->name('role.access.paginate.index');


    //Coupons
    Route::post('/coupon', [CouponCodeController::class, 'store'])->name('coupon.store');
    Route::post('/coupon/paginate/filters', [CouponCodeController::class, 'index'])->name('coupon.index');
    Route::post('/coupon/{id}', [CouponCodeController::class, 'update'])->name('coupon.update');

    Route::get('/coupon', [CouponCodeController::class, 'index'])->name('coupon.index');
    Route::get('/coupon/paginate/filters', [CouponCodeController::class, 'index'])->name('coupon.index');
    Route::get('/coupon/create', [CouponCodeController::class, 'create'])->name('coupon.create');
    Route::get('/coupon/{id}', [CouponCodeController::class, 'show'])->name('coupon.show');
    Route::get('/coupon/{id}/edit', [CouponCodeController::class, 'edit'])->name('coupon.edit');

    Route::delete('/coupon/{id}', [CouponCodeController::class, 'destroy'])->name('coupon.destroy');


    //Staff
    Route::post('/staff', [StaffController::class, 'store'])->name('coupon.store');
    Route::post('/staff/paginate/filters', [StaffController::class, 'index'])->name('staff.index');
    Route::post('/staff/{id}', [StaffController::class, 'update'])->name('staff.update');

    Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
    Route::get('/staff/paginate/filters', [StaffController::class, 'index'])->name('staff.index');
    Route::get('/staff/create', [StaffController::class, 'create'])->name('staff.create');
    Route::get('/staff/{id}', [StaffController::class, 'show'])->name('staff.show');
    Route::get('/staff/{id}/edit', [StaffController::class, 'edit'])->name('staff.edit');

    Route::delete('/staff/{id}', [StaffController::class, 'destroy'])->name('staff.destroy');


    // Inventory Report Controller goes here

    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');


    Route::get('/reports', [ReportController::class, 'index'])->name('report.index');
    Route::get('/report/sale', [ReportController::class, 'sale'])->name('report.sale');
    Route::post('/report/sale', [ReportController::class, 'sale'])->name('report.sale.filter');

    Route::get('/report/stock', [ReportController::class, 'stocks'])->name('report.stocks');
    Route::get('/report/stock/exports', [ReportController::class, 'StockExports'])->name('report.exports');


    Route::get('/reports/extra', [ReportExtraController::class, 'index'])->name('report.index');
    Route::get('/report/sale/extra', [ReportExtraController::class, 'sale'])->name('report.sale');
    Route::post('/report/sale/extra', [ReportExtraController::class, 'sale'])->name('report.sale.filter');

    Route::get('/report/stock/extra', [ReportExtraController::class, 'stocks'])->name('report.stocks');
    Route::get('/report/stock/exports/extra', [ReportExtraController::class, 'StockExports'])->name('report.exports');


    // Bulk Discount campaigns
    Route::get('/bulk_discount', [BulkDiscountController::class, 'index'])->name('bulk_discount.index');
    Route::get('/bulk_discount/paginate/filters', [BulkDiscountController::class, 'index'])->name('bulk_discount.paginate');
    Route::get('/bulk_discount/create', [BulkDiscountController::class, 'create'])->name('bulk_discount.create');
    Route::get('/bulk_discount/products', [BulkDiscountController::class, 'products'])->name('bulk_discount.products');
    Route::post('/bulk_discount/preview', [BulkDiscountController::class, 'preview'])->name('bulk_discount.preview');
    Route::post('/bulk_discount', [BulkDiscountController::class, 'store'])->name('bulk_discount.store');
    Route::get('/bulk_discount/{id}/edit', [BulkDiscountController::class, 'edit'])->name('bulk_discount.edit');
    Route::post('/bulk_discount/{id}', [BulkDiscountController::class, 'update'])->name('bulk_discount.update');
    Route::post('/bulk_discount/{id}/end', [BulkDiscountController::class, 'endNow'])->name('bulk_discount.end');
    Route::delete('/bulk_discount/{id}', [BulkDiscountController::class, 'destroy'])->name('bulk_discount.destroy');


});
