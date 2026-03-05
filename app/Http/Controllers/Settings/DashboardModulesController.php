<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\DashboardController;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class DashboardModulesController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Доступ только для суперадминистратора.');
        }

        $modules = DashboardController::getModulesList();
        $enabledIds = SystemSetting::get('dashboard_modules');
        if (!is_array($enabledIds) || $enabledIds === []) {
            $enabledIds = array_column($modules, 'id');
        }

        return view('Settings.modules', [
            'modules' => $modules,
            'enabledIds' => $enabledIds,
        ]);
    }

    public function update(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Доступ только для суперадминистратора.');
        }

        $request->validate([
            'modules' => 'nullable|array',
            'modules.*' => 'string|in:reliability',
        ]);

        $ids = array_values($request->input('modules', []));
        SystemSetting::set('dashboard_modules', $ids);

        return redirect()->route('settings.modules')->with('success', 'Набор активных модулей компании сохранён.');
    }
}
