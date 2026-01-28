<?php

namespace Tests\Unit\Services;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AuditServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_log_creates_entry()
    {
        $service = new AuditService();
        $user = User::factory()->create();
        Auth::login($user);

        $log = $service->log(
            action: 'test_action',
            entityType: 'App\Models\Test',
            entityId: 1,
            newValues: ['foo' => 'bar']
        );

        $this->assertInstanceOf(AuditLog::class, $log);
        $this->assertEquals('test_action', $log->action);
        $this->assertEquals($user->id, $log->user_id);
    }
}
