<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Settings\SettingsController as Settings;
use App\Http\Controllers\Settings\DirectoryController as Directory;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GeneralSettingsController;

// Защищенные маршруты - требуют авторизации
Route::middleware('auth')->group(function () {

    // Главная страница дашборда
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

    // Общие настройки системы
    Route::get('/general-settings', [GeneralSettingsController::class, 'index'])->name('general-settings.index');
    Route::post('/general-settings', [GeneralSettingsController::class, 'update'])->name('general-settings.update');

    // Управление e-mail уведомлениями
    Route::get('/notification', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notification.index');
    Route::get('/notification/logs', [\App\Http\Controllers\NotificationLogController::class, 'index'])->name('notification.logs');
    Route::get('/notification/{module}', [\App\Http\Controllers\ModuleNotificationController::class, 'index'])->name('notification.module');
    Route::get('/notification/{module}/{template}', [\App\Http\Controllers\NotificationTemplateController::class, 'edit'])->name('notification.template.edit');
    Route::put('/notification/{module}/{template}', [\App\Http\Controllers\NotificationTemplateController::class, 'update'])->name('notification.template.update');
    Route::post('/notification/{module}/{template}/preview', [\App\Http\Controllers\NotificationTemplateController::class, 'preview'])->name('notification.template.preview');

    // Чат
    Route::prefix('chat')->name('chat.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ChatController::class, 'index'])->name('index');
        Route::get('/unread-count', [\App\Http\Controllers\ChatController::class, 'getUnreadCount'])->name('unreadCount');
        Route::get('/chats', [\App\Http\Controllers\ChatController::class, 'getChats'])->name('chats');
        Route::get('/search-users', [\App\Http\Controllers\ChatController::class, 'searchUsers'])->name('searchUsers');
        Route::get('/messages/{chatId}', [\App\Http\Controllers\ChatController::class, 'getChatMessages'])->name('messages');
        Route::get('/new-messages/{chatId}', [\App\Http\Controllers\MessageController::class, 'getNewMessages'])->name('newMessages');
        Route::get('/{userId}', [\App\Http\Controllers\ChatController::class, 'getOrCreatePrivateChat'])->name('private')->where('userId', '[0-9]+');
        Route::post('/group', [\App\Http\Controllers\ChatController::class, 'createGroup'])->name('group.create');
        Route::post('/group/{chatId}/participants', [\App\Http\Controllers\ChatController::class, 'addParticipants'])->name('group.addParticipants');
        Route::get('/group/{chatId}/participants', [\App\Http\Controllers\ChatController::class, 'getParticipants'])->name('group.getParticipants');
        Route::delete('/group/{chatId}/participants', [\App\Http\Controllers\ChatController::class, 'removeParticipant'])->name('group.removeParticipant');
        Route::delete('/group/{chatId}', [\App\Http\Controllers\ChatController::class, 'deleteGroup'])->name('group.delete');
        Route::post('/group/{chatId}/leave', [\App\Http\Controllers\ChatController::class, 'leaveGroup'])->name('group.leave');
        Route::post('/message', [\App\Http\Controllers\MessageController::class, 'store'])->name('message.store');
        Route::put('/message/{messageId}', [\App\Http\Controllers\MessageController::class, 'update'])->name('message.update');
        Route::delete('/message/{messageId}', [\App\Http\Controllers\MessageController::class, 'destroy'])->name('message.destroy');
        Route::post('/read/{chatId}', [\App\Http\Controllers\MessageController::class, 'markAsRead'])->name('read');
    });

    // Модули системы — только Надежность
    Route::prefix('modules')->name('modules.')->group(function () {
        // Надежность
        Route::get('/reliability', [\App\Http\Controllers\Modules\Reliability\ReliabilityController::class, 'index'])->name('reliability.index');
        Route::get('/reliability/export-excel', [\App\Http\Controllers\Modules\Reliability\ReliabilityController::class, 'exportFailuresToExcel'])->name('reliability.export-excel');
        Route::get('/reliability/export-buf', [\App\Http\Controllers\Modules\Reliability\ReliabilityController::class, 'exportFailuresToBuf'])->name('reliability.export-buf');
        Route::get('/reliability/aggregates', [\App\Http\Controllers\Modules\Reliability\ReliabilityController::class, 'getAggregatesForFilter'])->name('reliability.aggregates');
        Route::get('/reliability/aggregates-modal', [\App\Http\Controllers\Modules\Reliability\ReliabilityController::class, 'getAggregatesForModal'])->name('reliability.aggregates.modal');
        Route::post('/reliability/aggregates-from-modal', [\App\Http\Controllers\Modules\Reliability\ReliabilityController::class, 'storeAggregateFromModal'])->name('reliability.aggregates.store-from-modal');
        Route::get('/reliability/failures/create', [\App\Http\Controllers\Modules\Reliability\ReliabilityController::class, 'createFailureForm'])->name('reliability.failures.create');
        Route::get('/reliability/failures/{id}/edit', [\App\Http\Controllers\Modules\Reliability\ReliabilityController::class, 'editFailureForm'])->name('reliability.failures.edit');
        Route::post('/reliability/failures', [\App\Http\Controllers\Modules\Reliability\ReliabilityController::class, 'storeFailure'])->name('reliability.failures.store');
        Route::get('/reliability/failures/{id}', [\App\Http\Controllers\Modules\Reliability\ReliabilityController::class, 'getFailure'])->name('reliability.failures.show');
        Route::match(['PUT', 'PATCH'], '/reliability/failures/{id}', [\App\Http\Controllers\Modules\Reliability\ReliabilityController::class, 'updateFailure'])->name('reliability.failures.update');
        Route::match(['PUT', 'PATCH'], '/reliability/failures/{id}/include-in-buf', [\App\Http\Controllers\Modules\Reliability\ReliabilityController::class, 'updateIncludeInBuf'])->name('reliability.failures.include-in-buf');
        Route::get('/reliability/failures/{id}/export-card', [\App\Http\Controllers\Modules\Reliability\ReliabilityController::class, 'exportFailureCard'])->name('reliability.failures.export-card');
        Route::get('/reliability/monitoring-chart-data', [\App\Http\Controllers\Modules\Reliability\ReliabilityController::class, 'monitoringChartData'])->name('reliability.monitoring-chart-data');
        Route::post('/reliability/failures/{id}/attachments', [\App\Http\Controllers\Modules\Reliability\ReliabilityController::class, 'uploadFailureAttachment'])->name('reliability.failures.attachments.upload');
        Route::post('/reliability/failures/{id}/attachments/delete', [\App\Http\Controllers\Modules\Reliability\ReliabilityController::class, 'deleteFailureAttachment'])->name('reliability.failures.attachments.delete');
        Route::get('/reliability/failures/{id}/attachment', [\App\Http\Controllers\Modules\Reliability\ReliabilityController::class, 'serveFailureAttachment'])->name('reliability.failures.attachments.serve');
        Route::get('/reliability/failures/{id}/attachment/download', [\App\Http\Controllers\Modules\Reliability\ReliabilityController::class, 'downloadFailureAttachment'])->name('reliability.failures.attachments.download');

        // Настройки модуля "Надёжность"
        Route::prefix('/reliability/settings')->name('reliability.settings.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Modules\Reliability\InspectionDataController::class, 'index'])->name('index');
            // Inspection data (projects, aircrafts, work_cards, …)
            Route::get('/projects', [\App\Http\Controllers\Modules\Reliability\InspectionDataController::class, 'projects'])->name('inspection.projects');
            Route::post('/projects/upload', [\App\Http\Controllers\Modules\Reliability\InspectionDataController::class, 'projectsUpload'])->name('inspection.projects.upload');
            Route::post('/projects/delete', [\App\Http\Controllers\Modules\Reliability\InspectionDataController::class, 'projectsDelete'])->name('inspection.projects.delete');
            Route::get('/aircrafts', [\App\Http\Controllers\Modules\Reliability\InspectionDataController::class, 'aircrafts'])->name('inspection.aircrafts');
            Route::post('/aircrafts/upload', [\App\Http\Controllers\Modules\Reliability\InspectionDataController::class, 'aircraftsUpload'])->name('inspection.aircrafts.upload');
            Route::post('/aircrafts/delete', [\App\Http\Controllers\Modules\Reliability\InspectionDataController::class, 'aircraftsDelete'])->name('inspection.aircrafts.delete');
            Route::get('/work-cards', [\App\Http\Controllers\Modules\Reliability\InspectionDataController::class, 'workCards'])->name('inspection.work-cards');
            Route::post('/work-cards/count', [\App\Http\Controllers\Modules\Reliability\InspectionDataController::class, 'workCardsCount'])->name('inspection.work-cards.count');
            Route::post('/work-cards/upload', [\App\Http\Controllers\Modules\Reliability\InspectionDataController::class, 'workCardsUpload'])->name('inspection.work-cards.upload');
            Route::post('/work-cards/import-local', [\App\Http\Controllers\Modules\Reliability\InspectionDataController::class, 'workCardsImportLocal'])->name('inspection.work-cards.import-local');
            Route::post('/work-cards/delete', [\App\Http\Controllers\Modules\Reliability\InspectionDataController::class, 'workCardsDelete'])->name('inspection.work-cards.delete');
            Route::get('/eef-registry', [\App\Http\Controllers\Modules\Reliability\InspectionDataController::class, 'eefRegistry'])->name('inspection.eef-registry');
            Route::post('/eef-registry/count', [\App\Http\Controllers\Modules\Reliability\InspectionDataController::class, 'eefRegistryCount'])->name('inspection.eef-registry.count');
            Route::post('/eef-registry/upload', [\App\Http\Controllers\Modules\Reliability\InspectionDataController::class, 'eefRegistryUpload'])->name('inspection.eef-registry.upload');
            Route::post('/eef-registry/import-local', [\App\Http\Controllers\Modules\Reliability\InspectionDataController::class, 'eefRegistryImportLocal'])->name('inspection.eef-registry.import-local');
            Route::post('/eef-registry/clear', [\App\Http\Controllers\Modules\Reliability\InspectionDataController::class, 'eefRegistryClear'])->name('inspection.eef-registry.clear');
            Route::post('/eef-registry/delete', [\App\Http\Controllers\Modules\Reliability\InspectionDataController::class, 'eefRegistryDelete'])->name('inspection.eef-registry.delete');
            Route::get('/work-card-materials', [\App\Http\Controllers\Modules\Reliability\InspectionDataController::class, 'workCardMaterials'])->name('inspection.work-card-materials');
            Route::post('/work-card-materials/upload', [\App\Http\Controllers\Modules\Reliability\InspectionDataController::class, 'workCardMaterialsUpload'])->name('inspection.work-card-materials.upload');
            Route::post('/work-card-materials/delete', [\App\Http\Controllers\Modules\Reliability\InspectionDataController::class, 'workCardMaterialsDelete'])->name('inspection.work-card-materials.delete');
            Route::get('/source-card-refs', [\App\Http\Controllers\Modules\Reliability\InspectionDataController::class, 'sourceCardRefs'])->name('inspection.source-card-refs');
            Route::post('/source-card-refs/upload', [\App\Http\Controllers\Modules\Reliability\InspectionDataController::class, 'sourceCardRefsUpload'])->name('inspection.source-card-refs.upload');
            Route::post('/source-card-refs/delete', [\App\Http\Controllers\Modules\Reliability\InspectionDataController::class, 'sourceCardRefsDelete'])->name('inspection.source-card-refs.delete');
            Route::get('/case-analyses', [\App\Http\Controllers\Modules\Reliability\InspectionDataController::class, 'caseAnalyses'])->name('inspection.case-analyses');
            Route::post('/case-analyses/upload', [\App\Http\Controllers\Modules\Reliability\InspectionDataController::class, 'caseAnalysesUpload'])->name('inspection.case-analyses.upload');
            Route::post('/case-analyses/delete', [\App\Http\Controllers\Modules\Reliability\InspectionDataController::class, 'caseAnalysesDelete'])->name('inspection.case-analyses.delete');

            Route::get('/failure-form', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'failureFormIndex'])->name('failure-form.index');
            Route::post('/failure-form', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'failureFormUpdate'])->name('failure-form.update');
            Route::get('/tabs', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'tabsIndex'])->name('tabs.index');
            Route::post('/tabs', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'tabsUpdate'])->name('tabs.update');
            Route::get('/report-structure-buf', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'reportStructureBuf'])->name('report-structure-buf.index');
            Route::post('/report-structure-buf', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'reportStructureBufUpdate'])->name('report-structure-buf.update');
            Route::get('/detection-stages', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'detectionStagesIndex'])->name('detection-stages.index');
            Route::get('/detection-stages/create', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'detectionStagesCreate'])->name('detection-stages.create');
            Route::post('/detection-stages', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'detectionStagesStore'])->name('detection-stages.store');
            Route::get('/detection-stages/{stage}/edit', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'detectionStagesEdit'])->name('detection-stages.edit');
            Route::patch('/detection-stages/{stage}', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'detectionStagesUpdate'])->name('detection-stages.update');
            Route::delete('/detection-stages/{stage}', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'detectionStagesDestroy'])->name('detection-stages.destroy');
            Route::get('/consequences', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'consequencesIndex'])->name('consequences.index');
            Route::get('/consequences/create', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'consequencesCreate'])->name('consequences.create');
            Route::post('/consequences', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'consequencesStore'])->name('consequences.store');
            Route::get('/consequences/{consequence}/edit', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'consequencesEdit'])->name('consequences.edit');
            Route::patch('/consequences/{consequence}', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'consequencesUpdate'])->name('consequences.update');
            Route::delete('/consequences/{consequence}', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'consequencesDestroy'])->name('consequences.destroy');
            Route::get('/wo-statuses', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'woStatusesIndex'])->name('wo-statuses.index');
            Route::get('/wo-statuses/create', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'woStatusesCreate'])->name('wo-statuses.create');
            Route::post('/wo-statuses', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'woStatusesStore'])->name('wo-statuses.store');
            Route::get('/wo-statuses/{status}/edit', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'woStatusesEdit'])->name('wo-statuses.edit');
            Route::patch('/wo-statuses/{status}', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'woStatusesUpdate'])->name('wo-statuses.update');
            Route::delete('/wo-statuses/{status}', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'woStatusesDestroy'])->name('wo-statuses.destroy');
            Route::get('/systems', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'systemsIndex'])->name('systems.index');
            Route::get('/systems/create', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'systemsCreate'])->name('systems.create');
            Route::post('/systems', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'systemsStore'])->name('systems.store');
            Route::get('/systems/{system}/edit', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'systemsEdit'])->name('systems.edit');
            Route::patch('/systems/{system}', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'systemsUpdate'])->name('systems.update');
            Route::patch('/systems/{system}/rename', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'systemsRename'])->name('systems.rename');
            Route::delete('/systems/{system}', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'systemsDestroy'])->name('systems.destroy');
            Route::post('/aggregates', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'aggregatesStore'])->name('aggregates.store');
            Route::get('/engine-types', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'engineTypesIndex'])->name('engine-types.index');
            Route::get('/engine-types/create', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'engineTypesCreate'])->name('engine-types.create');
            Route::post('/engine-types', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'engineTypesStore'])->name('engine-types.store');
            Route::get('/engine-types/{engineType}/edit', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'engineTypesEdit'])->name('engine-types.edit');
            Route::patch('/engine-types/{engineType}', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'engineTypesUpdate'])->name('engine-types.update');
            Route::delete('/engine-types/{engineType}', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'engineTypesDestroy'])->name('engine-types.destroy');
            Route::get('/engine-numbers', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'engineNumbersIndex'])->name('engine-numbers.index');
            Route::get('/engine-numbers/create', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'engineNumbersCreate'])->name('engine-numbers.create');
            Route::post('/engine-numbers', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'engineNumbersStore'])->name('engine-numbers.store');
            Route::get('/engine-numbers/{engineNumber}/edit', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'engineNumbersEdit'])->name('engine-numbers.edit');
            Route::patch('/engine-numbers/{engineNumber}', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'engineNumbersUpdate'])->name('engine-numbers.update');
            Route::delete('/engine-numbers/{engineNumber}', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'engineNumbersDestroy'])->name('engine-numbers.destroy');
            Route::get('/aircraft-type-codes', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'aircraftTypeCodesIndex'])->name('aircraft-type-codes.index');
            Route::post('/aircraft-type-codes', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'aircraftTypeCodesUpdate'])->name('aircraft-type-codes.update');
            Route::get('/org-code', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'orgCodeIndex'])->name('org-code.index');
            Route::post('/org-code', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'orgCodeUpdate'])->name('org-code.update');
            Route::get('/taken-measures', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'takenMeasuresIndex'])->name('taken-measures.index');
            Route::get('/taken-measures/create', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'takenMeasuresCreate'])->name('taken-measures.create');
            Route::post('/taken-measures', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'takenMeasuresStore'])->name('taken-measures.store');
            Route::get('/taken-measures/{takenMeasure}/edit', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'takenMeasuresEdit'])->name('taken-measures.edit');
            Route::patch('/taken-measures/{takenMeasure}', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'takenMeasuresUpdate'])->name('taken-measures.update');
            Route::delete('/taken-measures/{takenMeasure}', [\App\Http\Controllers\Modules\Reliability\ReliabilitySettingsController::class, 'takenMeasuresDestroy'])->name('taken-measures.destroy');
        });
    });

    // Управление пользователями (из настроек)
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [\App\Http\Controllers\Admin\UserManagementController::class, 'index'])->name('users.index');
        Route::get('/users/create', [\App\Http\Controllers\Admin\UserManagementController::class, 'create'])->name('users.create');
        Route::post('/users', [\App\Http\Controllers\Admin\UserManagementController::class, 'store'])->name('users.store');
        Route::post('/users/bulk-destroy', [\App\Http\Controllers\Admin\UserManagementController::class, 'bulkDestroy'])->name('users.bulk-destroy');
        Route::get('/users/{user}/edit', [\App\Http\Controllers\Admin\UserManagementController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [\App\Http\Controllers\Admin\UserManagementController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [\App\Http\Controllers\Admin\UserManagementController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{user}/reset-password', [\App\Http\Controllers\Admin\UserManagementController::class, 'resetPassword'])->name('users.reset-password');
        Route::post('/users/{user}/login-as', [\App\Http\Controllers\Admin\UserManagementController::class, 'loginAsUser'])->name('users.login-as');
        Route::post('/users/stop-impersonating', [\App\Http\Controllers\Admin\UserManagementController::class, 'stopImpersonating'])->name('users.stop-impersonating');
        Route::get('/roles', [\App\Http\Controllers\Admin\RoleManagementController::class, 'index'])->name('roles.index');
        Route::post('/roles/reorder', [\App\Http\Controllers\Admin\RoleManagementController::class, 'reorder'])->name('roles.reorder');
        Route::post('/roles', [\App\Http\Controllers\Admin\RoleManagementController::class, 'store'])->name('roles.store');
        Route::put('/roles/{role}', [\App\Http\Controllers\Admin\RoleManagementController::class, 'update'])->name('roles.update');
        Route::post('/roles/update-permissions', [\App\Http\Controllers\Admin\RoleManagementController::class, 'updatePermissions'])->name('roles.update-permissions');
        Route::delete('/roles/{role}', [\App\Http\Controllers\Admin\RoleManagementController::class, 'destroy'])->name('roles.destroy');
        Route::post('/roles/permissions', [\App\Http\Controllers\Admin\RoleManagementController::class, 'storePermission'])->name('roles.permissions.store');
    });

    Route::get('/', function () {
        return redirect()->route('dashboard');
    })->name('schedule.index');

    // Настройки (всё из /settings)
    Route::get('/settings', [Settings::class, 'index'])->name('settings.index');
    Route::get('/settings/modules', [\App\Http\Controllers\Settings\DashboardModulesController::class, 'index'])->name('settings.modules');
    Route::post('/settings/modules', [\App\Http\Controllers\Settings\DashboardModulesController::class, 'update'])->name('settings.modules.update');
    Route::get('/settings/directory', [Directory::class, 'index'])->name('directory.index');

    Route::get('/CreateDirectory/{ModelName}', [DirectoryChange::class, 'create'])->name('DirectoryChange.create');
    Route::post('/CreateDirectory/{ModelName}', [DirectoryChange::class, 'store'])->name('DirectoryChange.store');

    Route::get('/fleet', [\App\Http\Controllers\Settings\FleetController::class, 'index'])->name('fleet.index');
    Route::get('/CreateFleet', [\App\Http\Controllers\Settings\FleetController::class, 'create'])->name('fleet.create');
    Route::post('/CreateFleet', [\App\Http\Controllers\Settings\FleetController::class, 'store'])->name('fleet.store');
    Route::get('/fleet/{aircraft}/edit', [\App\Http\Controllers\Settings\FleetController::class, 'edit'])->name('fleet.edit');
    Route::post('/fleet/upload-type-codes', [\App\Http\Controllers\Settings\FleetController::class, 'uploadTypeCodes'])->name('fleet.upload-type-codes');
    Route::delete('/fleet/{aircraft}', [\App\Http\Controllers\Settings\FleetController::class, 'destroy'])->name('fleet.destroy');
    Route::patch('/fleet/{aircraft}', [\App\Http\Controllers\Settings\FleetController::class, 'update'])->name('fleet.update');

    Route::get('/settings/ReadinessType', [ReadinessTypeController::class, 'index'])->name('ReadinessType.index');
    Route::get('/settings/ReadinessType/create', [ReadinessTypeController::class, 'create'])->name('ReadinessType.create');
    Route::post('/settings/ReadinessType', [ReadinessTypeController::class, 'store'])->name('ReadinessType.store');
    Route::get('/settings/ReadinessType/{id}', [ReadinessTypeController::class, 'show'])->name('ReadinessType.show');
    Route::get('/settings/ReadinessType/{id}/edit', [ReadinessTypeController::class, 'edit'])->name('ReadinessType.edit');
    Route::patch('/settings/ReadinessType/{id}', [ReadinessTypeController::class, 'update'])->name('ReadinessType.update');
    Route::delete('/settings/ReadinessType/{id}', [ReadinessTypeController::class, 'destroy'])->name('ReadinessType.destroy');
    Route::get('/api/readiness-types', [ReadinessTypeController::class, 'getActiveTypes'])->name('readiness-types.api');

    Route::get('/settings/Position', [\App\Http\Controllers\Settings\PositionController::class, 'index'])->name('Position.index');
    Route::get('/settings/Position/create', [\App\Http\Controllers\Settings\PositionController::class, 'create'])->name('Position.create');
    Route::post('/settings/Position', [\App\Http\Controllers\Settings\PositionController::class, 'store'])->name('Position.store');
    Route::get('/settings/Position/{id}', [\App\Http\Controllers\Settings\PositionController::class, 'show'])->name('Position.show');
    Route::get('/settings/Position/{id}/edit', [\App\Http\Controllers\Settings\PositionController::class, 'edit'])->name('Position.edit');
    Route::patch('/settings/Position/{id}', [\App\Http\Controllers\Settings\PositionController::class, 'update'])->name('Position.update');
    Route::delete('/settings/Position/{id}', [\App\Http\Controllers\Settings\PositionController::class, 'destroy'])->name('Position.destroy');
    Route::get('/api/positions', [\App\Http\Controllers\Settings\PositionController::class, 'getActivePositions'])->name('positions.api');
    Route::get('/api/positions/for-selection', [\App\Http\Controllers\Settings\PositionController::class, 'getActivePositions'])->name('positions.selection.api');

    Route::get('/minimum_crew', [\App\Http\Controllers\Settings\MinimumCrewController::class, 'index'])->name('minimum_crew.index');
    Route::post('/minimum_crew', [\App\Http\Controllers\Settings\MinimumCrewController::class, 'bulkStore'])->name('minimum_crew.store');
    Route::get('/api/minimum-crew/by-aircraft-type', [\App\Http\Controllers\Settings\MinimumCrewController::class, 'getByAircraftType'])->name('minimum_crew.api.by_aircraft_type');
    Route::get('/api/aircraft-type-by-regn', [\App\Http\Controllers\Settings\MinimumCrewController::class, 'getAircraftTypeIdByRegN'])->name('minimum_crew.api.aircraft_type_by_regn');

    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/create', [MessageController::class, 'create'])->name('messages.create');
    Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');
    Route::get('/messages/{message}', [MessageController::class, 'show'])->name('messages.show');
    Route::get('/messages/{message}/edit', [MessageController::class, 'edit'])->name('messages.edit');
    Route::patch('/messages/{message}', [MessageController::class, 'update'])->name('messages.update');
    Route::delete('/messages/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');

    Route::get('/api/lookup/{reference}', [\App\Http\Controllers\LookupController::class, 'get'])->name('api.lookup');

    // Settings: Events
    Route::get('/settings/Events', [\App\Http\Controllers\Settings\EventsController::class, 'index'])->name('events.index');
    Route::get('/settings/Events/create', [\App\Http\Controllers\Settings\EventsController::class, 'create'])->name('events.create');
    Route::post('/settings/Events', [\App\Http\Controllers\Settings\EventsController::class, 'store'])->name('events.store');
    Route::get('/settings/Events/{event}/edit', [\App\Http\Controllers\Settings\EventsController::class, 'edit'])->name('events.edit');
    Route::patch('/settings/Events/{event}', [\App\Http\Controllers\Settings\EventsController::class, 'update'])->name('events.update');
    Route::delete('/settings/Events/{event}', [\App\Http\Controllers\Settings\EventsController::class, 'destroy'])->name('events.destroy');
    Route::get('/api/active-events', [\App\Http\Controllers\Settings\EventsController::class, 'getActiveEvents'])->name('events.active');

    // Settings: Flight Statuses
    Route::get('/settings/FlightStatuses', [\App\Http\Controllers\Settings\FlightStatusesController::class, 'index'])->name('settings.flight-statuses.index');
    Route::get('/settings/FlightStatuses/create', [\App\Http\Controllers\Settings\FlightStatusesController::class, 'create'])->name('settings.flight-statuses.create');
    Route::post('/settings/FlightStatuses', [\App\Http\Controllers\Settings\FlightStatusesController::class, 'store'])->name('settings.flight-statuses.store');
    Route::get('/settings/FlightStatuses/{flightStatus}/edit', [\App\Http\Controllers\Settings\FlightStatusesController::class, 'edit'])->name('settings.flight-statuses.edit');
    Route::put('/settings/FlightStatuses/{flightStatus}', [\App\Http\Controllers\Settings\FlightStatusesController::class, 'update'])->name('settings.flight-statuses.update');
    Route::patch('/settings/FlightStatuses/{flightStatus}', [\App\Http\Controllers\Settings\FlightStatusesController::class, 'update']);
    Route::delete('/settings/FlightStatuses/{flightStatus}', [\App\Http\Controllers\Settings\FlightStatusesController::class, 'destroy'])->name('settings.flight-statuses.destroy');
    Route::post('/settings/FlightStatuses/special-color', [\App\Http\Controllers\Settings\FlightStatusesController::class, 'updateSpecialColor'])->name('settings.flight-statuses.update-special-color');

    // Settings: Fleet Aircraft Types
    Route::get('/settings/Fleet/AircraftTypes', [\App\Http\Controllers\Settings\AircraftTypesController::class, 'index'])->name('settings.fleet.aircraft-types.index');
    Route::get('/settings/Fleet/AircraftTypes/create', [\App\Http\Controllers\Settings\AircraftTypesController::class, 'create'])->name('settings.fleet.aircraft-types.create');
    Route::post('/settings/Fleet/AircraftTypes', [\App\Http\Controllers\Settings\AircraftTypesController::class, 'store'])->name('settings.fleet.aircraft-types.store');
    Route::get('/settings/Fleet/AircraftTypes/{aircraftType}/edit', [\App\Http\Controllers\Settings\AircraftTypesController::class, 'edit'])->name('settings.fleet.aircraft-types.edit');
    Route::patch('/settings/Fleet/AircraftTypes/{aircraftType}', [\App\Http\Controllers\Settings\AircraftTypesController::class, 'update'])->name('settings.fleet.aircraft-types.update');
    Route::delete('/settings/Fleet/AircraftTypes/{aircraftType}', [\App\Http\Controllers\Settings\AircraftTypesController::class, 'destroy'])->name('settings.fleet.aircraft-types.destroy');

    Route::resource('airports', AirportsController::class);
    Route::get('airports-search', [AirportsController::class, 'search'])->name('airports.search');

    // Профиль пользователя
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
