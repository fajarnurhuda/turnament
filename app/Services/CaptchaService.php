<?php

namespace App\Services;

class CaptchaService
{
    /**
     * Characters used for generating CAPTCHA (excluding ambiguous characters: 0, O, 1, I, L).
     */
    private const CHARACTERS = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';

    /**
     * Generate CAPTCHA code, store in session, and return SVG image markup.
     */
    public function generate(int $length = 5): string
    {
        $code = '';
        $maxIndex = strlen(self::CHARACTERS) - 1;
        for ($i = 0; $i < $length; $i++) {
            $code .= self::CHARACTERS[random_int(0, $maxIndex)];
        }

        session(['login_captcha' => strtolower($code)]);

        return $this->renderSvg($code);
    }

    /**
     * Verify submitted CAPTCHA against session value.
     */
    public function verify(?string $input): bool
    {
        $expected = session('login_captcha');

        if (empty($expected) || empty($input)) {
            return false;
        }

        $isValid = hash_equals((string) $expected, strtolower(trim($input)));

        if ($isValid) {
            session()->forget('login_captcha');
        }

        return $isValid;
    }

    /**
     * Render cryptographic SVG visual with theme-aligned styling, noise lines, dots, and distortions.
     */
    public function renderSvg(string $code): string
    {
        $width = 150;
        $height = 44;
        $palette = ['#00FF87', '#00E5FF', '#FFD600', '#F0F6FC', '#38BDF8'];

        $elements = [];

        // Add subtle background grid lines
        for ($i = 0; $i < 5; $i++) {
            $x1 = random_int(0, $width);
            $y1 = random_int(0, $height);
            $x2 = random_int(0, $width);
            $y2 = random_int(0, $height);
            $stroke = $palette[array_rand($palette)];
            $opacity = random_int(20, 45) / 100;
            $elements[] = sprintf(
                '<line x1="%d" y1="%d" x2="%d" y2="%d" stroke="%s" stroke-width="1.2" stroke-opacity="%.2f" stroke-dasharray="3,3" />',
                $x1,
                $y1,
                $x2,
                $y2,
                $stroke,
                $opacity
            );
        }

        // Add noise dots
        for ($i = 0; $i < 35; $i++) {
            $cx = random_int(2, $width - 2);
            $cy = random_int(2, $height - 2);
            $r = random_int(1, 2);
            $fill = $palette[array_rand($palette)];
            $opacity = random_int(20, 60) / 100;
            $elements[] = sprintf(
                '<circle cx="%d" cy="%d" r="%d" fill="%s" fill-opacity="%.2f" />',
                $cx,
                $cy,
                $r,
                $fill,
                $opacity
            );
        }

        // Add an organic wavy path
        $waveStartX = 5;
        $waveStartY = random_int(15, 30);
        $cp1X = random_int(30, 60);
        $cp1Y = random_int(5, 40);
        $cp2X = random_int(80, 110);
        $cp2Y = random_int(5, 40);
        $waveEndX = $width - 5;
        $waveEndY = random_int(15, 30);
        $waveColor = $palette[array_rand($palette)];
        $elements[] = sprintf(
            '<path d="M%d,%d C%d,%d %d,%d %d,%d" stroke="%s" stroke-width="1.5" fill="none" stroke-opacity="0.4" />',
            $waveStartX,
            $waveStartY,
            $cp1X,
            $cp1Y,
            $cp2X,
            $cp2Y,
            $waveEndX,
            $waveEndY,
            $waveColor
        );

        // Render characters
        $len = strlen($code);
        $charSpacing = ($width - 24) / $len;

        for ($i = 0; $i < $len; $i++) {
            $char = $code[$i];
            $x = 16 + ($i * $charSpacing) + random_int(-3, 3);
            $y = 28 + random_int(-3, 3);
            $angle = random_int(-15, 15);
            $color = $palette[$i % count($palette)];
            $fontSize = random_int(22, 25);

            $elements[] = sprintf(
                '<text x="%d" y="%d" transform="rotate(%d %d %d)" fill="%s" font-size="%d" font-family="\'JetBrains Mono\', monospace" font-weight="800" letter-spacing="2">%s</text>',
                $x,
                $y,
                $angle,
                $x,
                $y,
                $color,
                $fontSize,
                htmlspecialchars($char, ENT_QUOTES | ENT_XML1)
            );
        }

        $innerSvg = implode("\n        ", $elements);

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="{$width}" height="{$height}" viewBox="0 0 {$width} {$height}">
    <defs>
        <linearGradient id="bg" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#0B0E17" />
            <stop offset="100%" stop-color="#121826" />
        </linearGradient>
    </defs>
    <rect width="100%" height="100%" fill="url(#bg)" rx="6" stroke="#22304C" stroke-width="1.5" />
    {$innerSvg}
</svg>
SVG;
    }
}
