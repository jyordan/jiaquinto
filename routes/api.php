<?php

use App\Http\Controllers\TempOpportunityController;
use Illuminate\Support\Facades\Route;

Route::get('/temp/log/opportunity', TempOpportunityController::class);
