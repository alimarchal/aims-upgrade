<?php

namespace App\Support;

class DashboardFormat
{
    /** Percentage change as [tone, text] for the little up / down badges. */
    public static function change(float $now, float $before, string $suffix = 'vs yesterday'): array
    {
        if ($before == 0) {
            return $now == 0 ? ['flat', 'no change'] : ['up', 'new'];
        }
        $pct = (int) round(($now - $before) / $before * 100);

        return [$pct > 0 ? 'up' : ($pct < 0 ? 'down' : 'flat'), ($pct > 0 ? '▲ ' : ($pct < 0 ? '▼ ' : '')).abs($pct).'% '.$suffix];
    }

    /** CSS custom properties for a card accent: --tone, --soft (tinted background), --glow (hover shadow), --edge (hover border). */
    public static function tone(string $hex): string
    {
        $hex = ltrim($hex, '#');
        [$r, $g, $b] = [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];

        return "--tone:#{$hex};--soft:rgba({$r},{$g},{$b},.12);--glow:rgba({$r},{$g},{$b},.40);--edge:rgba({$r},{$g},{$b},.38)";
    }
}
