<?php

namespace Tests\Feature;

use Database\Seeders\DemoCustomerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\OwnerTestCase;

class OwnerNotificationsTest extends OwnerTestCase
{
    use RefreshDatabase;

    public function test_bell_links_to_notifications_and_lists_only_the_owners_records(): void
    {
        $this->withoutVite();
        $this->seed(DemoCustomerSeeder::class);
        DB::table('notifications')->insert([
            ['userId' => auth()->id(), 'title' => 'GCash payment pending', 'message' => 'Marco submitted a payment.', 'createdAt' => now(), 'readAt' => null],
            ['userId' => DB::table('users')->where('role', 'customer')->value('userId'), 'title' => 'Private customer notification', 'message' => 'Private message', 'createdAt' => now(), 'readAt' => null],
        ]);
        foreach (['dashboard', 'customers', 'customers/pending', 'transactions', 'utang', 'payments'] as $page) {
            $this->get('/owner/'.$page)->assertOk()->assertSee('href="'.route('owner.notifications').'"', false);
        }
        $this->get('/owner/notifications')->assertOk()
            ->assertSee('Marco submitted a payment.')->assertSee('GCash payment pending')
            ->assertDontSee('Private customer notification')
            ->assertViewHas('notifications', fn ($rows) => $rows->count() === 1)
            ->assertSee('aria-label="Notifications"', false);
    }

    public function test_marking_as_read_removes_the_new_marker(): void
    {
        $this->withoutVite();
        $this->seed(DemoCustomerSeeder::class);
        $id = DB::table('notifications')->insertGetId(['userId' => auth()->id(), 'title' => 'Payment pending', 'message' => 'Awaiting review', 'createdAt' => now()], 'notificationId');
        $this->get('/owner/notifications')->assertOk()->assertSee('notification-card-new');
        $this->postJson('/owner/notifications/'.$id.'/read')->assertOk();
        $this->get('/owner/notifications')->assertOk()->assertDontSee('notification-card-new');
    }

    public function test_empty_state_and_production_guard(): void
    {
        $this->withoutVite();
        $this->get('/owner/notifications')->assertOk()->assertSee('No notifications yet.');
        $this->app->detectEnvironment(fn () => 'production');
        Auth::logout();
        $this->get('/owner/notifications')->assertRedirect('/login');
    }
}
