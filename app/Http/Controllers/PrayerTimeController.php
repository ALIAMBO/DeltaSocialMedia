<?php

namespace App\Http\Controllers;

use App\Services\PrayerTimeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrayerTimeController extends Controller
{
    protected PrayerTimeService $prayerTimeService;

    public function __construct(PrayerTimeService $prayerTimeService)
    {
        $this->prayerTimeService = $prayerTimeService;
    }

    /**
     * Get prayer times for a given zone.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getTimes(Request $request): JsonResponse
    {
        $zone = $request->query('zone', 'WLY01');
        $times = $this->prayerTimeService->getTodayTimes($zone);
        
        return response()->json($times);
    }

    /**
     * Get all supported zones.
     *
     * @return JsonResponse
     */
    public function getZones(): JsonResponse
    {
        return response()->json($this->prayerTimeService->getSupportedZones());
    }
}
