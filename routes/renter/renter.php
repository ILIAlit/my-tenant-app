

<?php

use Illuminate\Support\Facades\Route;



Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('renter', 'admin/renter')->name('renters.get');
});
