<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Показать страницу управления e-mail уведомлениями
     */
    public function index(): View
    {
        return view('notification.index');
    }
}
