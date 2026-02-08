<?php

namespace Tests\Unit;

use App\Services\SmsService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class SmsServiceTest extends TestCase
{
    public function test_safe_mode_prevents_api_call_and_logs()
    {
        Config::set('services.sms.enabled', false);
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message) {
                return str_contains($message, 'SMS Safe Mode');
            });
            
        Http::fake(); // Should not be called, but good practice

        $service = new SmsService();
        $response = $service->send('1234567890', 'Test Message');

        $this->assertEquals(0, $response['handshake']['id']);
        $this->assertEquals('simulated', $response['data']['status']);
        
        Http::assertNothingSent();
    }

    public function test_enabled_mode_sends_api_request()
    {
        Config::set('services.sms.key', 'test_key');
        Config::set('services.sms.enabled', true);
        
        Http::fake([
            'api.smsonlinegh.com/*' => Http::response(['handshake' => ['id' => 0], 'data' => ['status' => 'sent']], 200),
        ]);

        $service = new SmsService();
        $response = $service->send('1234567890', 'Test Message');

        $this->assertEquals(0, $response['handshake']['id']);
        
        Http::assertSent(function ($request) {
            return $request->url() == 'https://api.smsonlinegh.com/v5/message/sms/send' &&
                   $request['text'] == 'Test Message';
        });
    }
}
