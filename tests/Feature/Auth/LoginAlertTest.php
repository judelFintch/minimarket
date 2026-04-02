<?php

namespace Tests\Feature\Auth;

use App\Listeners\SendLoginAlert;
use App\Livewire\System\Index as SystemIndex;
use App\Models\AppSetting;
use App\Models\User;
use App\Notifications\LoginAlertFailedNotification;
use App\Notifications\LoginAlertNotification;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class LoginAlertTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_alert_is_sent_to_dedicated_and_company_recipients(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'role' => 'vendeur',
        ]);

        AppSetting::set('login_alert_enabled', '1');
        AppSetting::set('login_alert_recipient', 'security@example.com');
        AppSetting::set('company_email', 'company@example.com');

        Event::dispatch(new Login('web', $user, false));

        Notification::assertSentOnDemand(LoginAlertNotification::class, function ($notification, array $channels, $notifiable): bool {
            $recipients = $notifiable->routeNotificationFor('mail', $notification);

            $this->assertSame(['security@example.com', 'company@example.com'], $recipients);

            return true;
        });

        $this->assertSame('success', AppSetting::get('login_alert_last_status'));
        $this->assertNull(AppSetting::get('login_alert_last_error'));
        $this->assertNotNull(AppSetting::get('login_alert_last_attempt_at'));
    }

    public function test_login_alert_falls_back_to_company_email(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'role' => 'vendeur',
        ]);

        AppSetting::set('login_alert_enabled', '1');
        AppSetting::set('login_alert_recipient', null);
        AppSetting::set('company_email', 'company@example.com');

        Event::dispatch(new Login('web', $user, false));

        Notification::assertSentOnDemand(LoginAlertNotification::class, function ($notification, array $channels, $notifiable): bool {
            $recipients = $notifiable->routeNotificationFor('mail', $notification);

            $this->assertSame(['company@example.com'], $recipients);

            return true;
        });
    }

    public function test_login_alert_failure_is_recorded_and_notified_internally(): void
    {
        Notification::fake();

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create([
            'role' => 'vendeur',
        ]);

        AppSetting::set('login_alert_enabled', '1');
        AppSetting::set('company_email', 'company@example.com');

        $listener = Mockery::mock(SendLoginAlert::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $listener->shouldReceive('sendMailAlert')
            ->once()
            ->andThrow(new \RuntimeException('SMTP refused connection.'));

        $listener->handle(new Login('web', $user, false));

        $this->assertSame('failed', AppSetting::get('login_alert_last_status'));
        $this->assertSame('SMTP refused connection.', AppSetting::get('login_alert_last_error'));
        $this->assertNotNull(AppSetting::get('login_alert_last_attempt_at'));

        Notification::assertSentTo($admin, LoginAlertFailedNotification::class);
    }

    public function test_system_health_page_displays_login_alert_health_status(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        AppSetting::set('login_alert_enabled', '1');
        AppSetting::set('login_alert_recipient', 'security@example.com');
        AppSetting::set('company_email', 'company@example.com');
        AppSetting::set('login_alert_last_status', 'failed');
        AppSetting::set('login_alert_last_error', 'SMTP refused connection.');
        AppSetting::set('login_alert_last_attempt_at', '2026-04-02 12:34:56');

        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp.host', 'mail.example.com');
        Config::set('mail.from.address', 'alert@example.com');

        $response = $this->actingAs($admin)->get(route('system.health'));

        $response->assertOk();
        $response->assertSee('Sante systeme email');
        $response->assertSee('mail.example.com');
        $response->assertSee('security@example.com');
        $response->assertSee('company@example.com');
        $response->assertSee('failed');
        $response->assertSee('SMTP refused connection.');
        $response->assertSee('2026-04-02 12:34:56');
    }

    public function test_admin_can_save_login_alert_settings_from_system_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        Livewire::actingAs($admin)
            ->test(SystemIndex::class)
            ->set('loginAlertEnabled', true)
            ->set('loginAlertRecipient', 'security@example.com')
            ->set('companyEmail', 'company@example.com')
            ->call('saveLoginAlertSettings')
            ->assertHasNoErrors();

        $this->assertSame('1', AppSetting::get('login_alert_enabled'));
        $this->assertSame('security@example.com', AppSetting::get('login_alert_recipient'));
        $this->assertSame('company@example.com', AppSetting::get('company_email'));
    }
}
