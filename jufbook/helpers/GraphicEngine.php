<?php
declare(strict_types=1);

if (!class_exists('GraphicEngine')) {
    class GraphicEngine {
        /**
         * বাংলা প্রথম অক্ষর বের করার নিরাপদ মেথড
         */
        public static function getFirstBengaliChar(string $str): string {
            $str = trim(strip_tags($str));
            if (empty($str)) return 'ব';
            return mb_substr($str, 0, 1, 'UTF-8');
        }

        /**
         * বাংলা ফন্ট কালেকশন তালিকা (অ্যাডমিনের ড্রপডাউনের জন্য)
         */
        public static function getFontList(): array {
            return [
                'font-tiro'     => 'টিরো বাংলা (Tiro Bangla — ক্লাসিক ক্যালিগ্রাফি)',
                'font-serif'    => 'নোটো সেরিফ (Noto Serif Bengali — ঐতিহ্যবাহী সাহিত্য)',
                'font-siliguri' => 'হিন্দ শিলিগুড়ি (Hind Siliguri — মডার্ন বোল্ড)',
                'font-sans'     => 'নোটো সান্স (Noto Sans Bengali — ক্লিন মিনিমালিস্ট)'
            ];
        }

        /**
         * রেফারেন্স ইমেজ অনুযায়ী ২D মিনিমালিস্ট ও ভিনটেজ কভার আর্টওয়ার্ক জেনারেটর
         */
        public static function render2DCover(string $title, string $genre = 'উপন্যাস', string $themeColor = '#243447', string $fontClass = 'font-tiro'): string {
            $titleEsc = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
            $genreEsc = htmlspecialchars($genre, ENT_QUOTES, 'UTF-8');
            $colorEsc = htmlspecialchars($themeColor, ENT_QUOTES, 'UTF-8');

            $svgOverlay = '
            <svg style="position:absolute; inset:0; width:100%; height:100%; pointer-events:none; opacity:0.22;" viewBox="0 0 200 280">
                <circle cx="160" cy="55" r="65" fill="#FFFFFF" opacity="0.4" />
                <rect x="10" y="10" width="180" height="260" rx="4" fill="none" stroke="#FFFFFF" stroke-width="1.2" stroke-dasharray="4 3" />
                <line x1="25" y1="120" x2="175" y2="120" stroke="#FFFFFF" stroke-width="1.5" />
                <line x1="25" y1="124" x2="175" y2="124" stroke="#FFFFFF" stroke-width="0.8" />
                <circle cx="100" cy="122" r="3" fill="#FFFFFF" />
            </svg>';

            return '
            <div class="juf-book-jacket" style="background-color: ' . $colorEsc . ';">
                ' . $svgOverlay . '
                <span class="juf-jacket-genre">' . $genreEsc . '</span>
                <div class="juf-jacket-title ' . $fontClass . '">' . $titleEsc . '</div>
            </div>';
        }

        /**
         * বুক ডিটেইলস ও ক্যাটালগের জন্য বুক কভার মেথড
         */
        public static function renderBookCover(string $title, string $author = '', string $genre = 'উপন্যাস', string $themeColor = '#243447'): string {
            return self::render2DCover($title, $genre, $themeColor, 'font-tiro');
        }
    }
}