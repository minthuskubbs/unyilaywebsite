<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Reads artwork prices from the Notion databases (read-only).
 * Needs an internal integration token that has been shared with each database.
 */
final class BrassShowroomNotionClient
{
    /** @return array<string, int> Notion page id => price in MMK, for every row with a usable price. */
    public function prices(): array
    {
        $token = (string) config('brass-showroom.notion.token');
        $databases = (array) config('brass-showroom.notion.databases', []);
        if ($token === '' || $databases === []) {
            throw new RuntimeException('Notion sync is not configured: set BRASS_SHOWROOM_NOTION_TOKEN and the database ids.');
        }
        $property = (string) config('brass-showroom.notion.price_property', 'Sale Price');

        $prices = [];
        foreach ($databases as $database) {
            $cursor = null;
            $pages = 0;
            do {
                $body = ['page_size' => 100] + ($cursor ? ['start_cursor' => $cursor] : []);
                $response = Http::withToken($token)
                    ->withHeaders(['Notion-Version' => config('brass-showroom.notion.version', '2022-06-28')])
                    ->acceptJson()->timeout(30)->retry(3, 2000, throw: false)
                    ->post('https://api.notion.com/v1/databases/'.$database.'/query', $body);
                if (! $response->successful()) {
                    throw new RuntimeException("Notion database {$database} could not be read (HTTP {$response->status()}).");
                }
                foreach ($response->json('results', []) as $page) {
                    if (($page['archived'] ?? false) || ($page['in_trash'] ?? false)) {
                        continue;
                    }
                    $price = self::price($page['properties'][$property] ?? null);
                    if ($price !== null) {
                        $prices[BrassShowroomManager::normalizeNotionId($page['id'])] = $price;
                    }
                }
                $cursor = $response->json('has_more') ? $response->json('next_cursor') : null;
            } while ($cursor && ++$pages < 50);
        }

        return $prices;
    }

    /** A positive whole-kyat price from a number, formula, text or rollup property; null when empty or unreadable. */
    public static function price(?array $property): ?int
    {
        if ($property === null) {
            return null;
        }
        $type = $property['type'] ?? '';
        $value = $property[$type] ?? null;
        if ($type === 'formula' || $type === 'rollup') {
            $value = $value[$value['type'] ?? ''] ?? null;
        } elseif ($type === 'rich_text' || $type === 'title') {
            $value = implode('', array_map(fn (array $part): string => $part['plain_text'] ?? '', $value ?? []));
        }
        if (is_string($value)) {
            // "250,000", "250000 Ks" or Myanmar digits "၂၅၀,၀၀၀".
            $value = strtr($value, ['၀' => '0', '၁' => '1', '၂' => '2', '၃' => '3', '၄' => '4', '၅' => '5', '၆' => '6', '၇' => '7', '၈' => '8', '၉' => '9']);
            $digits = preg_replace('/[^0-9.]/', '', $value);
            $value = ($digits === '' || substr_count($digits, '.') > 1) ? null : (float) $digits;
        }
        if (! is_int($value) && ! is_float($value)) {
            return null;
        }
        $price = (int) round($value);

        return $price > 0 && $price <= 1_000_000_000 ? $price : null;
    }
}
