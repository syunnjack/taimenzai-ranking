<?php

final class DmmClient
{
    private const ENDPOINT = 'https://api.dmm.com/affiliate/v3/ItemList';
    private const CACHE_TTL = 3600;

    public function __construct(
        private readonly string $apiId,
        private readonly string $affiliateId,
        private readonly string $cacheDir,
    ) {
    }

    /** @return array<int, array{content_id:string,title:string,affiliateURL:string,imageURL:?string,maker:string,price:?string,reviewAverage:float,reviewCount:int}> */
    public function fetchItems(string $keyword, int $hits = 60): array
    {
        $cacheFile = $this->cacheDir . '/' . md5($keyword) . '.json';

        if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < self::CACHE_TTL) {
            $cached = json_decode((string) file_get_contents($cacheFile), true);
            if (is_array($cached)) {
                return $cached;
            }
        }

        $params = [
            'api_id' => $this->apiId,
            'affiliate_id' => $this->affiliateId,
            'site' => 'FANZA',
            'service' => 'digital',
            'floor' => 'videoa',
            'keyword' => $keyword,
            'hits' => $hits,
            'sort' => 'review',
            'output' => 'json',
        ];

        $url = self::ENDPOINT . '?' . http_build_query($params);
        $context = stream_context_create(['http' => ['timeout' => 10]]);
        $raw = @file_get_contents($url, false, $context);
        if ($raw === false) {
            return is_file($cacheFile) ? (json_decode((string) file_get_contents($cacheFile), true) ?: []) : [];
        }

        $data = json_decode($raw, true);
        $rawItems = $data['result']['items'] ?? [];

        $items = array_values(array_filter(array_map(static function (array $item): ?array {
            $reviewCount = (int) ($item['review']['count'] ?? 0);
            if ($reviewCount < 3) {
                return null;
            }
            return [
                'content_id' => $item['content_id'] ?? '',
                'title' => $item['title'] ?? '',
                'affiliateURL' => $item['affiliateURL'] ?? '',
                'imageURL' => $item['imageURL']['large'] ?? $item['imageURL']['small'] ?? null,
                'maker' => $item['iteminfo']['maker'][0]['name'] ?? '',
                'price' => $item['prices']['price'] ?? null,
                'reviewAverage' => (float) ($item['review']['average'] ?? 0),
                'reviewCount' => $reviewCount,
            ];
        }, $rawItems)));

        if (is_dir($this->cacheDir) || mkdir($this->cacheDir, 0775, true)) {
            file_put_contents($cacheFile, json_encode($items, JSON_UNESCAPED_UNICODE));
        }

        return $items;
    }
}
