<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PastebinController;

Route::get("/", function () {
    return redirect()->route("home");
});


Route::get("/new", [PastebinController::class, 'index'])->name("new");

Route::post('/paste', [PastebinController::class,'handleNewPastebin'])->name('handleNewPastebin');

Route::get('/paste/{hash}', [PastebinController::class, 'viewPastebin'])->name('viewPastebin');

