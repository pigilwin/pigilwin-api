<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Repositories\SteamRepository;
use Illuminate\Http\JsonResponse;

class SteamController extends Controller
{
    public function games(int $steamId): JsonResponse
    {
        $repository = new SteamRepository(env('STEAM_API_KEY'), $steamId);
        return response()->json($repository->games());
    }

    public function achievements(int $steamId, int $gameId): JsonResponse
    {
        $repository = new SteamRepository(env('STEAM_API_KEY'), $steamId);
        return response()->json($repository->achievements($gameId));
    }
}
