<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PrayerTimeService
{
    /**
     * Cache duration in seconds (12 hours)
     */
    protected const CACHE_TTL = 43200;

    /**
     * Get prayer times for today for the specified zone.
     *
     * @param string $zone
     * @return array
     */
    public function getTodayTimes(string $zone = 'WLY01'): array
    {
        // Force uppercase and clean up user input string
        $zone = strtoupper(trim($zone));
        $zonesList = $this->getSupportedZones();
        if (!array_key_exists($zone, $zonesList)) {
            $zone = 'WLY01';
        }

        $todayDate = now()->format('Y-m-d');
        $cacheKey = "jakim_solat_{$zone}_{$todayDate}";

        // Cache parameters expire automatically at midnight
        return Cache::remember($cacheKey, now()->endOfDay(), function () use ($zone, $zonesList) {
            try {
                // Official JAKIM e-Solat API Core Endpoint
                $response = Http::timeout(10)
                    ->withoutVerifying()
                    ->get("https://www.e-solat.gov.my/index.php", [
                        'r' => 'esolatApi/TakwimSolat',
                        'period' => 'today',
                        'zone' => $zone,
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    
                    // Validate if JAKIM returned valid arrays
                    if (isset($data['prayerTime'][0])) {
                        $timings = $data['prayerTime'][0];

                        return [
                            'zone_code' => $data['zone'] ?? $zone,
                            'zone_name' => $zonesList[$zone]['name'] ?? '',
                            'date' => $timings['date'] ?? date('d-M-Y'),
                            'hijri' => $timings['hijri'] ?? '',
                            'day' => $timings['day'] ?? date('l'),
                            'times' => [
                                'Imsak'   => date('H:i', strtotime($timings['imsak'])),
                                'Subuh'   => date('H:i', strtotime($timings['fajr'])),
                                'Syuruk'  => date('H:i', strtotime($timings['syuruk'])),
                                'Zohor'   => date('H:i', strtotime($timings['dhuhr'])),
                                'Asar'    => date('H:i', strtotime($timings['asr'])),
                                'Maghrib' => date('H:i', strtotime($timings['maghrib'])),
                                'Isyak'   => date('H:i', strtotime($timings['isha'])),
                            ],
                            'source'  => 'Official JAKIM (e-Solat)'
                        ];
                    }
                }
                
                Log::warning("JAKIM e-Solat API returned unsuccessful response or invalid format for zone {$zone}");
            } catch (\Exception $e) {
                Log::error("Failed to fetch prayer times from JAKIM e-Solat API for zone {$zone}: " . $e->getMessage());
            }

            // Secondary fallback: AlAdhan API using JAKIM calculation method
            $alAdhanTimes = $this->getAlAdhanFallback($zone, $zonesList[$zone] ?? null);
            if ($alAdhanTimes) {
                return $alAdhanTimes;
            }

            return $this->getFallbackTimes($zone);
        });
    }

    /**
     * Get list of supported zones, dynamically fetched from the community API.
     *
     * @return array
     */
    public function getSupportedZones(): array
    {
        return Cache::remember('prayer_zones_list_v5', now()->addDays(30), function () {
            try {
                $response = Http::timeout(5)
                    ->withoutVerifying()
                    ->get('https://api.waktusolat.app/zones');

                if ($response->successful()) {
                    $data = $response->json();
                    $formatted = [];
                    foreach ($data as $item) {
                        $daerahList = explode(',', $item['daerah']);
                        // Clean up daerah to use as city name for AlAdhan fallback (e.g. remove "Daerah Kecil" or parentheses)
                        $firstCity = preg_replace('/\s*\(.*\)\s*/', '', trim($daerahList[0]));
                        
                        $formatted[$item['jakimCode']] = [
                            'name' => $item['negeri'] . ' (' . $item['daerah'] . ')',
                            'city' => $firstCity,
                            'state' => $item['negeri']
                        ];
                    }
                    ksort($formatted);
                    return $formatted;
                }
            } catch (\Exception $e) {
                Log::error("Failed to fetch zones list from community API: " . $e->getMessage());
            }

            // Return a minimal fallback on failure
            return [
                'WLY01' => [
                    'name' => 'Wilayah Persekutuan (Kuala Lumpur, Putrajaya)',
                    'city' => 'Kuala Lumpur',
                    'state' => 'Wilayah Persekutuan'
                ]
            ];
        });
    }

    /**
     * Fetch prayer times from AlAdhan API as fallback.
     *
     * @param string $zone
     * @param array|null $zoneInfo
     * @return array|null
     */
    protected function getAlAdhanFallback(string $zone, ?array $zoneInfo): ?array
    {
        if (!$zoneInfo) {
            return null;
        }

        $city = $zoneInfo['city'] ?? 'Kuala Lumpur';

        try {
            $response = Http::timeout(5)
                ->withoutVerifying()
                ->get("https://api.aladhan.com/v1/timingsByCity", [
                    'city' => $city,
                    'country' => 'Malaysia',
                    'method' => 11, // JAKIM method
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['data']['timings'])) {
                    return $this->formatAlAdhanData($data['data']['timings'], $zone, $zoneInfo);
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to fetch fallback timings from AlAdhan for city {$city} (zone {$zone}): " . $e->getMessage());
        }

        return null;
    }

    /**
     * Format AlAdhan response timings to standard format.
     *
     * @param array $timings
     * @param string $zone
     * @param array $zoneInfo
     * @return array
     */
    protected function formatAlAdhanData(array $timings, string $zone, array $zoneInfo): array
    {
        return [
            'zone_code' => $zone,
            'zone_name' => $zoneInfo['name'] ?? '',
            'date' => date('d-M-Y'),
            'hijri' => '',
            'day' => date('l'),
            'times' => [
                'Imsak'   => isset($timings['Imsak']) ? date('H:i', strtotime($timings['Imsak'])) : '',
                'Subuh'   => isset($timings['Fajr']) ? date('H:i', strtotime($timings['Fajr'])) : '',
                'Syuruk'  => isset($timings['Sunrise']) ? date('H:i', strtotime($timings['Sunrise'])) : '',
                'Zohor'   => isset($timings['Dhuhr']) ? date('H:i', strtotime($timings['Dhuhr'])) : '',
                'Asar'    => isset($timings['Asr']) ? date('H:i', strtotime($timings['Asr'])) : '',
                'Maghrib' => isset($timings['Maghrib']) ? date('H:i', strtotime($timings['Maghrib'])) : '',
                'Isyak'   => isset($timings['Isha']) ? date('H:i', strtotime($timings['Isha'])) : '',
            ],
            'source' => 'AlAdhan (JAKIM Fallback)'
        ];
    }

    /**
     * Hardcoded fallback times in case JAKIM API and AlAdhan are both offline.
     *
     * @param string $zone
     * @return array
     */
    protected function getFallbackTimes(string $zone): array
    {
        $zonesList = $this->getSupportedZones();
        return [
            'zone_code' => $zone,
            'zone_name' => $zonesList[$zone]['name'] ?? '',
            'date' => date('d-M-Y'),
            'hijri' => '',
            'day' => date('l'),
            'times' => [
                'Imsak'   => '05:43',
                'Subuh'   => '05:53',
                'Syuruk'  => '07:11',
                'Zohor'   => '13:14',
                'Asar'    => '16:41',
                'Maghrib' => '19:24',
                'Isyak'   => '20:39',
            ],
            'source' => 'Fallback Estimation'
        ];
    }
}
