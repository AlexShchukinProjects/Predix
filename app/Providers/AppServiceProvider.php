<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\TemplateTlgXlsx;
use App\Console\Commands\ImportAsobpFromExcel;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->commands([
            ImportAsobpFromExcel::class,
        ]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $expire = config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            return (new MailMessage)
                ->subject('Уведомление о сбросе пароля')
                ->view('mail.reset-password', [
                    'introLines' => [
                        'Вы получили это письмо, так как был запрошен сброс пароля для вашей учётной записи.',
                    ],
                    'actionUrl' => $url,
                    'actionText' => 'Сбросить пароль',
                    'outroLines' => [
                        'Ссылка для сброса пароля действительна в течение :count минут.',
                        'Если вы не запрашивали сброс пароля, никаких действий не требуется.',
                    ],
                    'expireMinutes' => $expire,
                    'userLogin' => $notifiable->login,
                ]);
        });

        Route::model('message', TemplateTlgXlsx::class);
        Route::model('auditType', \App\Models\Inspections\AuditType::class);
        Route::model('auditSubtype', \App\Models\Inspections\AuditSubtype::class);

        View::composer('layout.main', function ($view) {
            $user = auth()->user();
            $view->with('userAllowedRoutes', $user ? $user->getAllowedRouteSlugs() : []);
        });
    }
}
