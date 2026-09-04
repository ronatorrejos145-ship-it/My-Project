
Route::middleware(['auth:sanctum'])->prefix('v1/maintenance')->group(function () {
    Route::get('/work-orders', [App\Http\Controllers\Api\WorkOrderApiController::class, 'index']);
    Route::get('/work-orders/{id}', [App\Http\Controllers\Api\WorkOrderApiController::class, 'show']);
    Route::post('/work-orders/{id}/gps', [App\Http\Controllers\Api\WorkOrderApiController::class, 'recordGps']);
});
