<?php
/*
* @author: Husein JavaDLE
* https://github.com/JavaDle
*/

Route::get('updater.check', [javadle\updater\UpdaterController::class, 'check']);
Route::get('updater.currentVersion', [javadle\updater\UpdaterController::class, 'getCurrentVersion']);

Route::group(['middleware' => config('updater.middleware')], function () {
    Route::get('updater.update', [javadle\updater\UpdaterController::class, 'update']);
});
