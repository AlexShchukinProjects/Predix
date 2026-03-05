<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\TemplateTlgXlsx;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $messages = TemplateTlgXlsx::orderBy('Service')->get();
        return view('Settings.Messages.index', compact('messages'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('Settings.Messages.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'service' => 'required|string|max:255',
            'template' => 'nullable|string',
            'group' => 'nullable|string|max:255',
        ]);

        TemplateTlgXlsx::create([
            'Service' => $request->service,
            'Template' => $request->template,
            'Group' => $request->group,
        ]);

        return redirect()->route('messages.index')->with('success', 'Сообщение успешно создано!');
    }

    /**
     * Display the specified resource.
     */
    public function show(TemplateTlgXlsx $message)
    {
        return view('Settings.Messages.show', compact('message'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TemplateTlgXlsx $message)
    {
        return view('Settings.Messages.edit', compact('message'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TemplateTlgXlsx $message)
    {
        $request->validate([
            'service' => 'required|string|max:255',
            'template' => 'nullable|string',
            'group' => 'nullable|string|max:255',
        ]);

        $message->update([
            'Service' => $request->service,
            'Template' => $request->template,
            'Group' => $request->group,
        ]);

        return redirect()->route('messages.index')->with('success', 'Сообщение успешно обновлено!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TemplateTlgXlsx $message)
    {
        $message->delete();
        return redirect()->route('messages.index')->with('success', 'Сообщение успешно удалено!');
    }
}
