<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Ideatocode\NovaKanban\Http\Controllers\ToolController;

/*
|--------------------------------------------------------------------------
| Tool API Routes
|--------------------------------------------------------------------------
|
| Here is where you may register API routes for your tool. These routes
| are loaded by the ServiceProvider of your tool. They are protected
| by your tool's "Authorize" middleware by default. Now, go build!
|
*/

// Route::get('/', function (Request $request) {
//     //
// });

Route::post('/board/{kanbanBoard}/sync-upstream', [ToolController::class, 'syncUpstream'])->name('nova-kanban.sync-upstream');
Route::put('/board/{kanbanBoard}/sync', [ToolController::class, 'sync'])->name('nova-kanban.sync');
Route::post('/board/{kanbanBoard}/columns', [ToolController::class, 'storeColumn'])->name('nova-kanban.store-column');
Route::put('/board/{kanbanBoard}/items/{item}', [ToolController::class, 'update'])->name('nova-kanban.update-items');
Route::put('/board/{kanbanBoard}/columns/{column}', [ToolController::class, 'updateColumn'])->name('nova-kanban.update-column');
Route::post('/board/{kanbanBoard}/items', [ToolController::class, 'storeItem'])->name('nova-kanban.store-items');
Route::post('/delete', [ToolController::class, 'delete'])->name('nova-kanban.delete');