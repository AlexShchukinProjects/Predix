<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\EmailLog;
use Illuminate\View\View;

class NotificationLogController extends Controller
{
    /**
     * Показать логи отправки e-mail сообщений.
     */
    public function index(): View
    {
        $logs = EmailLog::query()
            ->orderByDesc('created_at')
            ->paginate(50);

        return view('notification.logs', compact('logs'));
    }
}

