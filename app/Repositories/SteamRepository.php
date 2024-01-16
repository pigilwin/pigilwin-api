<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DataObjects\Achievement;
use App\DataObjects\Game;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

readonly class SteamRepository
{
    private Client $client;

    public function __construct(private string $apiKey, private int $steamId)
    {
        $this->client = new Client([
            'base_uri' => 'https://api.steampowered.com/',
            'verify' => false,
        ]);
    }

    public function games(): Collection
    {
        $url = $this->createUrl('IPlayerService/GetOwnedGames/v0001', [
            'include_appinfo' => 'true'
        ]);
        $response = $this->client->get($url);

        /**
         * Ensure the status code is acceptable
         */
        if ($response->getStatusCode() !== Response::HTTP_OK) {
            return collect();
        }

        $output = $this->responseToArray($response);

        /**
         * Check for the games entry within the response
         */
        if (!isset($output['response']['games'])) {
            return collect();
        }

        $games = [];
        foreach ($output['response']['games'] as $game) {
            $games[] = new Game($game['appid'], $game['name']);
        }
        return collect($games);
    }

    public function achievements(int $gameId): Collection
    {
        $url = $this->createUrl('ISteamUserStats/GetPlayerAchievements/v0001', [
            'l' => 'en',
            'appid' => $gameId
        ]);

        $response = $this->client->get($url);

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            return collect();
        }

        $output = $this->responseToArray($response);

        if (!isset($output['playerstats']['achievements'])) {
            return collect();
        }

        $achievementsForGame = $this->getGameAchievements($gameId);
        $achievements = [];
        foreach ($output['playerstats']['achievements'] as $playerAchievementData) {
            $apiName = $playerAchievementData['apiname'];

            if (!key_exists($apiName, $achievementsForGame)) {
                continue;
            }

            $achievementForGame = $achievementsForGame[$apiName];

            $achievements[] = new Achievement(
                $apiName,
                $achievementForGame['displayName'],
                $achievementForGame['description'] ?? '',
                $playerAchievementData['achieved'] === 1,
                $playerAchievementData['unlocktime'],
                $achievementForGame['icon'],
                $achievementForGame['icongray'],
                $achievementForGame['hidden'] === 1
            );
        }
        return collect($achievements);
    }

    private function getGameAchievements(int $gameId): array
    {
        $url = $this->createUrl('ISteamUserStats/GetSchemaForGame/v2', [
            'l' => 'en',
            'appid' => $gameId
        ]);

        $response = $this->client->get($url);

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            return [];
        }

        $output = $this->responseToArray($response);

        if (!isset($output['game']['availableGameStats']['achievements'])) {
            return [];
        }

        $achievements = [];
        foreach ($output['game']['availableGameStats']['achievements'] as $achievement) {
            $achievements[$achievement['name']] = $achievement;
        }
        return $achievements;
    }

    /**
     * Create a url with required parameters
     * @param string $endpoint
     * @param array $additionalOptions
     * @return string
     */
    private function createUrl(string $endpoint, array $additionalOptions): string
    {
        $options = [
            'key' => $this->apiKey,
            'steamid' => $this->steamId,
            'format' => 'json'
        ] + $additionalOptions;

        return $endpoint . '?' . http_build_query($options);
    }

    private function responseToArray(ResponseInterface $response): array
    {
        return json_decode($response->getBody()->getContents(), true);
    }
}
