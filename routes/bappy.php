<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\NoticeController;
use App\Http\Controllers\Admin\ShopByController;
use App\Http\Controllers\Admin\TeamMemberController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\SubscribeController;
use App\Http\Controllers\Admin\VideoFeatureController;

Route::middleware(['auth'])->group(function () {
    //Brand
    Route::get('/brands', [BrandController::class, 'index'])->name('brand.index');
    Route::post('/brands/paginate/filters', [BrandController::class, 'index'])->name('brand.index');
    Route::get('/brands/paginate/filters', [BrandController::class, 'index'])->name('brand.index');

    Route::get('/brands/create', [BrandController::class, 'create'])->name('brand.create');
    Route::post('/brands', [BrandController::class, 'store'])->name('brand.store');
    Route::get('/brands/{id}', [BrandController::class, 'show'])->name('brand.show');
    Route::get('/brands/{id}/edit', [BrandController::class, 'edit'])->name('brand.edit');
    Route::post('/brands/{id}', [BrandController::class, 'update'])->name('brand.update');

    Route::delete('/brands/{id}', [BrandController::class, 'destroy'])->name('brand.destroy');

    //Cateogry
    Route::get('/categories', [CategoryController::class, 'index'])->name('category.index');
    Route::post('/categories/paginate/filters', [CategoryController::class, 'index'])->name('category.index');
    Route::get('/categories/paginate/filters', [CategoryController::class, 'index'])->name('category.index');

    Route::get('/categories/create', [CategoryController::class, 'create'])->name('category.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('category.store');
    Route::post('/categories/{id}/featured/toggle', [CategoryController::class, 'toggleFeatured'])->name('category.featured.toggle');
    Route::get('/categories/{id}', [CategoryController::class, 'show'])->name('category.show');
    Route::get('/categories/{id}/edit', [CategoryController::class, 'edit'])->name('category.edit');
    Route::post('/categories/{id}', [CategoryController::class, 'update'])->name('category.update');
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('category.destroy');

    //Notice
    Route::get('/notices', [NoticeController::class, 'index'])->name('notice.index');
    Route::post('/notices/paginate/filters', [NoticeController::class, 'index'])->name('notice.index');
    Route::get('/notices/paginate/filters', [NoticeController::class, 'index'])->name('notice.index');
    Route::get('/notices/create', [NoticeController::class, 'create'])->name('notice.create');
    Route::post('/notices', [NoticeController::class, 'store'])->name('notice.store');
    Route::get('/notices/{id}', [NoticeController::class, 'show'])->name('notice.show');
    Route::get('/notices/{id}/edit', [NoticeController::class, 'edit'])->name('notice.edit');
    Route::post('/notices/{id}', [NoticeController::class, 'update'])->name('notice.update');
    Route::delete('/notices/{id}', [NoticeController::class, 'destroy'])->name('notice.destroy');

    //Video Feature
    Route::post('/video/feature', [VideoFeatureController::class, 'store'])->name('video.store');
    Route::post('/video/feature/paginate/filters', [VideoFeatureController::class, 'index'])->name('video.index');
    Route::post('/video/feature/{id}', [VideoFeatureController::class, 'update'])->name('video.update');
    Route::get('/video/feature', [VideoFeatureController::class, 'index'])->name('video.index');
    Route::get('/video/feature/paginate/filters', [VideoFeatureController::class, 'index'])->name('video.index');
    Route::get('/video/feature/create', [VideoFeatureController::class, 'create'])->name('video.create');
    Route::get('/video/feature/{id}', [VideoFeatureController::class, 'show'])->name('video.show');
    Route::get('/video/feature/{id}/edit', [VideoFeatureController::class, 'edit'])->name('video.edit');
    Route::delete('/video/feature/{id}', [VideoFeatureController::class, 'destroy'])->name('video.destroy');

     //Banner
    Route::post('/banner', [BannerController::class, 'store'])->name('banner.store');
    Route::post('/banner/paginate/filters', [BannerController::class, 'index'])->name('banner.index');
    Route::post('/banner/{id}', [BannerController::class, 'update'])->name('banner.update');
    Route::get('/banner', [BannerController::class, 'index'])->name('banner.index');
    Route::get('/banner/paginate/filters', [BannerController::class, 'index'])->name('banner.index');
    Route::get('/banner/create', [BannerController::class, 'create'])->name('banner.create');
    Route::get('/banner/{id}', [BannerController::class, 'show'])->name('banner.show');
    Route::get('/banner/{id}/edit', [BannerController::class, 'edit'])->name('banner.edit');
    Route::delete('/banner/{id}', [BannerController::class, 'destroy'])->name('banner.destroy');

     //Subscribe
    Route::post('/subscribe/paginate/filters', [SubscribeController::class, 'index'])->name('subscribe.index');
    Route::post('/subscribe/{id}', [SubscribeController::class, 'update'])->name('subscribe.update');
    Route::get('/subscribe', [SubscribeController::class, 'index'])->name('subscribe.index');
    Route::get('/subscribe/paginate/filters', [SubscribeController::class, 'index'])->name('subscribe.index');
    Route::get('/subscribe/{id}', [SubscribeController::class, 'show'])->name('subscribe.show');
    Route::get('/subscribe/{id}/edit', [SubscribeController::class, 'edit'])->name('subscribe.edit');
    Route::delete('/subscribe/{id}', [SubscribeController::class, 'destroy'])->name('subscribe.destroy');

     //Contact
    Route::post('/contact/paginate/filters', [ContactController::class, 'index'])->name('contact.index');
    Route::post('/contact/{id}', [ContactController::class, 'update'])->name('contact.update');
    Route::get('/contact', [ContactController::class, 'index'])->name('contact.index');
    Route::get('/contact/paginate/filters', [ContactController::class, 'index'])->name('contact.index');
    Route::get('/contact/{id}', [ContactController::class, 'show'])->name('contact.show');
    Route::get('/contact/{id}/edit', [ContactController::class, 'edit'])->name('contact.edit');
    Route::delete('/contact/{id}', [ContactController::class, 'destroy'])->name('contact.destroy');

    //Customer
    Route::post('/customer', [CustomerController::class, 'store'])->name('customer.store');
    Route::post('/customer/pos/quick', [CustomerController::class, 'posStore'])->name('customer.pos.store');
    Route::post('/customer/paginate/filters', [CustomerController::class, 'index'])->name('customer.index');
    Route::post('/customer/{id}', [CustomerController::class, 'update'])->name('customer.update');

    Route::get('/customer', [CustomerController::class, 'index'])->name('customer.index');
    Route::get('/customer/paginate/filters', [CustomerController::class, 'index'])->name('customer.index');
    Route::get('/customer/create', [CustomerController::class, 'create'])->name('customer.create');
    Route::get('/customer/{id}', [CustomerController::class, 'show'])->name('customer.show');
    Route::get('/customer/{id}/edit', [CustomerController::class, 'edit'])->name('customer.edit');

    Route::delete('/customer/{id}', [CustomerController::class, 'destroy'])->name('customer.destroy');

    //Shop By
    Route::post('/shop_by', [ShopByController::class, 'store'])->name('shop.by.store');
    Route::post('/shop_by/paginate/filters', [ShopByController::class, 'index'])->name('shop.by.index');
    Route::post('/shop_by/{id}', [ShopByController::class, 'update'])->name('shop.by.update');

    Route::get('/shop_by', [ShopByController::class, 'index'])->name('shop.by.index');
    Route::get('/shop_by/paginate/filters', [ShopByController::class, 'index'])->name('shop.by.index');
    Route::get('/shop_by/create', [ShopByController::class, 'create'])->name('shop.by.create');
    Route::get('/shop_by/{id}', [ShopByController::class, 'show'])->name('shop.by.show');
    Route::get('/shop_by/{id}/edit', [ShopByController::class, 'edit'])->name('shop.by.edit');

    Route::delete('/shop_by/{id}', [ShopByController::class, 'destroy'])->name('shop.by.destroy');

    //Team Member
    Route::post('/team_member', [TeamMemberController::class, 'store'])->name('team.member.store');
    Route::post('/team_member/paginate/filters', [TeamMemberController::class, 'index'])->name('team.member.index');
    Route::post('/team_member/{id}', [TeamMemberController::class, 'update'])->name('team.member.update');

    Route::get('/team_member', [TeamMemberController::class, 'index'])->name('team.member.index');
    Route::get('/team_member/paginate/filters', [TeamMemberController::class, 'index'])->name('team.member.index');
    Route::get('/team_member/create', [TeamMemberController::class, 'create'])->name('team.member.create');
    Route::get('/team_member/{id}', [TeamMemberController::class, 'show'])->name('team.member.show');
    Route::get('/team_member/{id}/edit', [TeamMemberController::class, 'edit'])->name('team.member.edit');

    Route::delete('/team_member/{id}', [TeamMemberController::class, 'destroy'])->name('team.member.destroy');
});
