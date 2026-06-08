<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class TrelloService
{
    public function configured(): bool
    {
        return !empty($this->setting('api_key')) && !empty($this->setting('api_token'));
    }

    public function getBoards(): array
    {
        $boards = array_filter(config('trello.boards'));

        $result = [];
        foreach ($boards as $key => $boardId) {
            $board = $this->fetchBoard($boardId);
            if ($board !== null) {
                $result[$key] = $board;
            }
        }

        return $result;
    }

    public function fetchBoard(string $boardId): ?array
    {
        try {
            $response = Http::get("https://api.trello.com/1/boards/{$boardId}", [
                'key' => $this->setting('api_key'),
                'token' => $this->setting('api_token'),
            ]);

            if ($response->successful()) {
                return $response->json();
            }
        } catch (ConnectionException) {
        }

        return null;
    }

    public function getCards(string $boardId): array
    {
        try {
            $response = Http::get("https://api.trello.com/1/boards/{$boardId}/cards", [
                'key' => $this->setting('api_key'),
                'token' => $this->setting('api_token'),
                'fields' => 'id,name,desc,due,url,idList,closed,dateLastActivity',
            ]);

            if ($response->successful()) {
                return $response->json();
            }
        } catch (ConnectionException) {
        }

        return [];
    }

    private function setting(string $key): mixed
    {
        $user = User::first();
        $dbValue = $user?->getSetting("trello_{$key}");

        return $dbValue ?? config("trello.{$key}");
    }
}
