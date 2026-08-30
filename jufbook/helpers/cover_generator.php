<?php
declare(strict_types=1);

class GraphicEngine {

    public static function getFirstBengaliChar(string $name): string {
        $name = trim($name);
        if (empty($name)) return 'অ';
        return mb_substr($name, 0, 1, 'UTF-8');
    }

    public static function renderBookCover(string $title, string $author, string $genre, ?string $color = '#1A2930'): string {
        $safeColor = htmlspecialchars($color ?? '#1A2930', ENT_QUOTES, 'UTF-8');
        $firstChar = self::getFirstBengaliChar($title);
        
        $html = '<div class="juf-book-jacket" style="--book-bg: ' . $safeColor . ';">';
        $html .= '<div class="juf-jacket-spine"></div>';
        $html .= '<div class="juf-jacket-shine"></div>';
        $html .= '<div class="juf-jacket-symbol">' . htmlspecialchars($firstChar, ENT_QUOTES, 'UTF-8') . '</div>';
        $html .= '<div class="juf-jacket-content">';
        $html .= '<span class="juf-jacket-genre">' . htmlspecialchars($genre, ENT_QUOTES, 'UTF-8') . '</span>';
        $html .= '<h3 class="juf-jacket-title">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h3>';
        $html .= '<p class="juf-jacket-author">' . htmlspecialchars($author, ENT_QUOTES, 'UTF-8') . '</p>';
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }

    public static function renderAuthorAvatar(string $name, ?string $photo = null): string {
        if (!empty($photo) && file_exists(__DIR__ . '/../uploads/authors/' . $photo)) {
            return '<img src="uploads/authors/' . htmlspecialchars($photo, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" class="juf-author-circle-img">';
        }

        $char = self::getFirstBengaliChar($name);
        $gradients = [
            'linear-gradient(135deg, #BA4E2C, #3F1407)',
            'linear-gradient(135deg, #1F3F35, #081511)',
            'linear-gradient(135deg, #223843, #0B151A)',
            'linear-gradient(135deg, #5C3D2E, #1F120B)',
            'linear-gradient(135deg, #4A2840, #180B15)'
        ];
        $hash = crc32($name);
        $bg = $gradients[abs($hash) % count($gradients)];

        $svgVectors = [
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>',
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>',
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 21a9 9 0 100-18 9 9 0 000 18z"/><path d="M12 7v5l3 3"/></svg>'
        ];
        $icon = $svgVectors[abs($hash) % count($svgVectors)];

        return '
        <div class="juf-author-art-avatar" style="background: ' . $bg . ';">
            <div class="author-vector-bg">' . $icon . '</div>
            <div class="author-art-char">' . htmlspecialchars($char, ENT_QUOTES, 'UTF-8') . '</div>
            <div class="author-art-ring"></div>
        </div>';
    }
}