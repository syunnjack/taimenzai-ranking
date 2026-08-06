<?php

final class ContentSafetyFilter
{
    private const BLOCKED_KEYWORDS = [
        'ロリ', 'ロ●ータ', 'ロリータ', '少女', '幼女', '幼い', '子供', '子ども',
        'JS', 'JC', 'JK', '女子小学生', '女子中学生', '初潮', 'いたいけ',
        'あどけない', '子役', 'キッズ',
    ];

    public static function isSafe(string ...$fields): bool
    {
        $haystack = implode(' ', $fields);
        foreach (self::BLOCKED_KEYWORDS as $keyword) {
            if (mb_stripos($haystack, $keyword) !== false) {
                return false;
            }
        }
        return true;
    }
}
