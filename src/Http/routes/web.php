<?php

use Oktalogin\SamlOktaLogin\Http\Controllers\OktaLaravelController;
use Illuminate\Support\Facades\Route;

Route::get(config('saml.routes.oktaSamlMetadata'), [OktaLaravelController::class, 'oktaSamlMetadata'])->name('saml.metadata');
Route::get(config('saml.routes.oktaSamlLogin'), [OktaLaravelController::class, 'oktaSamlLogin'])->name('saml.login');
Route::post(config('saml.routes.oktaSamlLoginResponse'), [OktaLaravelController::class, 'oktaSamlLoginResponse']);
Route::get(config('saml.routes.oktaSamlAcs'), [OktaLaravelController::class, 'oktaSamlAcs'])->name('saml.acs');
Route::get(config('saml.routes.oktaSamlLogout'), [OktaLaravelController::class, 'oktaSamlLogout'])->name('saml.logout');
