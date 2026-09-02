<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        if (!Auth::user()->hasPermission('settings.manage')) {
            abort(403, 'Unauthorized access to System Settings.');
        }

        $settings = Setting::all()->groupBy('group');

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        if (!Auth::user()->hasPermission('settings.manage')) {
            abort(403, 'Unauthorized access to System Settings.');
        }

        $validated = $request->validate([
            'settings' => ['required', 'array'],
        ]);

        $oldValues = Setting::pluck('value', 'key')->toArray();

        foreach ($validated['settings'] as $key => $value) {
            $setting = Setting::where('key', $key)->first();
            if ($setting) {
                $setting->update(['value' => $value]);
            }
        }

        $newValues = Setting::pluck('value', 'key')->toArray();

        AuditLogService::log('settings.updated', 'settings', null, $oldValues, $newValues);

        return redirect()->route('admin.settings.index')->with('success', 'System settings updated successfully.');
    }
}
