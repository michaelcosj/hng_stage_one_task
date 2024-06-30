<?php

use App\Http\Controllers\AppController;
use Illuminate\Support\Facades\Route;

Route::get("/hello", AppController::class)->name("hello");
