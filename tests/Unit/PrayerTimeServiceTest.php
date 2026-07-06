<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\PrayerTimeService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class PrayerTimeServiceTest extends TestCase
{
    protected PrayerTimeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PrayerTimeService();
        Cache::flush();
    }

    public function test_can_get_supported_zones(): void
    {
        Http::fake([
            'https://api.waktusolat.app/zones' => Http::response([
                [
                    'jakimCode' => 'WLY01',
                    'negeri' => 'Wilayah Persekutuan',
                    'daerah' => 'Kuala Lumpur, Putrajaya'
                ]
            ], 200)
        ]);

        $zones = $this->service->getSupportedZones();
        
        $this->assertIsArray($zones);
        $this->assertArrayHasKey('WLY01', $zones);
        $this->assertEquals('Wilayah Persekutuan (Kuala Lumpur, Putrajaya)', $zones['WLY01']['name']);
    }

    public function test_fetches_and_formats_prayer_times_from_api(): void
    {
        Http::fake([
            'https://api.waktusolat.app/zones' => Http::response([
                [
                    'jakimCode' => 'WLY01',
                    'negeri' => 'Wilayah Persekutuan',
                    'daerah' => 'Kuala Lumpur, Putrajaya'
                ]
            ], 200),
            'https://www.e-solat.gov.my/*' => Http::response([
                'status' => 'OK',
                'zone' => 'WLY01',
                'prayerTime' => [
                    [
                        'hijri' => '1447-01-20',
                        'date' => '06-Jul-2026',
                        'day' => 'Monday',
                        'imsak' => '05:43:00',
                        'fajr' => '05:53:00',
                        'syuruk' => '07:11:00',
                        'dhuhr' => '13:14:00',
                        'asr' => '16:41:00',
                        'maghrib' => '19:24:00',
                        'isha' => '20:39:00',
                    ]
                ]
            ], 200)
        ]);

        $times = $this->service->getTodayTimes('WLY01');

        $this->assertEquals('WLY01', $times['zone_code']);
        $this->assertEquals('Wilayah Persekutuan (Kuala Lumpur, Putrajaya)', $times['zone_name']);
        $this->assertEquals('Official JAKIM (e-Solat)', $times['source']);
        $this->assertEquals('05:43', $times['times']['Imsak']);
        $this->assertEquals('05:53', $times['times']['Subuh']);
        $this->assertEquals('13:14', $times['times']['Zohor']);
        $this->assertEquals('19:24', $times['times']['Maghrib']);
        $this->assertEquals('20:39', $times['times']['Isyak']);
    }

    public function test_uses_fallback_times_when_api_fails(): void
    {
        Http::fake([
            'https://api.waktusolat.app/zones' => Http::response([
                [
                    'jakimCode' => 'WLY01',
                    'negeri' => 'Wilayah Persekutuan',
                    'daerah' => 'Kuala Lumpur, Putrajaya'
                ]
            ], 200),
            'https://www.e-solat.gov.my/*' => Http::response(null, 500),
            'https://api.aladhan.com/*' => Http::response([
                'code' => 200,
                'status' => 'OK',
                'data' => [
                    'timings' => [
                        'Imsak' => '05:43',
                        'Fajr' => '05:53',
                        'Sunrise' => '07:11',
                        'Dhuhr' => '13:14',
                        'Asr' => '16:41',
                        'Maghrib' => '19:24',
                        'Isha' => '20:39',
                    ]
                ]
            ], 200)
        ]);

        $times = $this->service->getTodayTimes('WLY01');

        $this->assertEquals('WLY01', $times['zone_code']);
        $this->assertEquals('AlAdhan (JAKIM Fallback)', $times['source']);
        $this->assertEquals('05:43', $times['times']['Imsak']);
        $this->assertEquals('13:14', $times['times']['Zohor']);
    }

    public function test_caches_prayer_times(): void
    {
        Http::fake([
            'https://api.waktusolat.app/zones' => Http::response([
                [
                    'jakimCode' => 'WLY01',
                    'negeri' => 'Wilayah Persekutuan',
                    'daerah' => 'Kuala Lumpur, Putrajaya'
                ]
            ], 200),
            'https://www.e-solat.gov.my/*' => Http::response([
                'status' => 'OK',
                'zone' => 'WLY01',
                'prayerTime' => [
                    [
                        'hijri' => '1447-01-20',
                        'date' => '06-Jul-2026',
                        'day' => 'Monday',
                        'imsak' => '05:43:00',
                        'fajr' => '05:53:00',
                        'syuruk' => '07:11:00',
                        'dhuhr' => '13:14:00',
                        'asr' => '16:41:00',
                        'maghrib' => '19:24:00',
                        'isha' => '20:39:00',
                    ]
                ]
            ], 200)
        ]);

        // First call: triggers Http request
        $this->service->getTodayTimes('WLY01');

        // Reset fake or make it fail: if cache works, it shouldn't hit HTTP request again
        Http::fake([
            'https://api.waktusolat.app/zones' => Http::response([
                [
                    'jakimCode' => 'WLY01',
                    'negeri' => 'Wilayah Persekutuan',
                    'daerah' => 'Kuala Lumpur, Putrajaya'
                ]
            ], 200),
            'https://www.e-solat.gov.my/*' => Http::response(null, 500)
        ]);

        $times = $this->service->getTodayTimes('WLY01');

        // Still uses the API cached result, not the failing fallback result
        $this->assertEquals('Official JAKIM (e-Solat)', $times['source']);
    }
}
