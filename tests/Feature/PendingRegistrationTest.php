<?php

namespace Tests\Feature;

use Database\Seeders\DemoRegistrationRequestSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\OwnerTestCase;

class PendingRegistrationTest extends OwnerTestCase
{
    use RefreshDatabase;

    public function test_only_pending_requests_are_listed(): void
    {
        $this->withoutVite();
        $this->seed(DemoRegistrationRequestSeeder::class);
        $this->get('/owner/customers/pending')->assertOk()->assertSee('Ella Flores')
            ->assertViewHas('requests', fn ($requests) => $requests->count() === 15);
        DB::table('registration_requests')->where('firstName', 'Ella')->update(['status' => 'Approved']);
        DB::table('registration_requests')->where('firstName', 'Luis')->update(['status' => 'Rejected']);
        $this->get('/owner/customers/pending')->assertOk()->assertDontSee('Ella Flores')->assertDontSee('Luis Navarro')
            ->assertViewHas('requests', fn ($requests) => $requests->count() === 13);
    }

    public function test_empty_queue_and_production_guard(): void
    {
        $this->withoutVite();
        $this->get('/owner/customers/pending')->assertOk()->assertSee('No pending registration requests.');
        $this->app->detectEnvironment(fn () => 'production');
        Auth::logout();
        $this->get('/owner/customers/pending')->assertRedirect('/login');
    }

    public function test_rejection_deletes_only_the_pending_request_without_creating_a_user(): void
    {
        $this->seed(DemoRegistrationRequestSeeder::class);
        $request = DB::table('registration_requests')->first();
        $users = DB::table('users')->count();
        $url = '/owner/customers/pending/'.$request->requestId;
        $this->delete($url)->assertRedirect('/owner/customers/pending');
        $this->assertDatabaseMissing('registration_requests', ['requestId' => $request->requestId]);
        $this->assertDatabaseCount('registration_requests', 14);
        $this->assertDatabaseCount('users', $users);
        $this->delete($url)->assertNotFound();
        $other = DB::table('registration_requests')->first();
        DB::table('registration_requests')->where('requestId', $other->requestId)->update(['status' => 'Approved']);
        $this->delete('/owner/customers/pending/'.$other->requestId)->assertNotFound();
        $this->assertDatabaseHas('registration_requests', ['requestId' => $other->requestId]);
        $this->app->detectEnvironment(fn () => 'production');
        $this->withoutMiddleware();
        $this->delete('/owner/customers/pending/'.$other->requestId)->assertNotFound();
    }
}
