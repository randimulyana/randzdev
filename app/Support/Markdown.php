<?php

namespace App\Support;

use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class Markdown
{
    /**
     * Render the given Markdown string to safe HTML.
     */
    public static function render(?string $markdown): HtmlString
    {
        if ($markdown === null || trim($markdown) === '') {
            return new HtmlString('');
        }

        $html = Str::markdown($markdown, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 100,
        ]);

        return new HtmlString($html);
    }
}
