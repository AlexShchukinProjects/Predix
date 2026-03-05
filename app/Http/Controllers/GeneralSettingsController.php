<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;

class GeneralSettingsController extends Controller
{
    public function index()
    {
        $logoUrl = SystemSetting::get('company_logo');

        return view('general-settings.index', compact('logoUrl'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'logo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,svg,webp', 'max:2048'],
        ]);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = 'company_logo_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $filename);

            // Удаляем старый лого файл если он есть
            $oldLogo = SystemSetting::get('company_logo');
            if ($oldLogo) {
                $oldPath = public_path(ltrim(str_replace(asset(''), '', $oldLogo), '/'));
                if (file_exists($oldPath) && str_contains($oldPath, 'company_logo_')) {
                    @unlink($oldPath);
                }
            }

            SystemSetting::set('company_logo', 'images/' . $filename);
        }

        if ($request->input('remove_logo') === '1') {
            $oldLogo = SystemSetting::get('company_logo');
            if ($oldLogo) {
                $oldPath = public_path($oldLogo);
                if (file_exists($oldPath) && str_contains($oldPath, 'company_logo_')) {
                    @unlink($oldPath);
                }
            }
            SystemSetting::set('company_logo', '');
        }

        return redirect()->route('general-settings.index')->with('success', 'Настройки сохранены');
    }
}
