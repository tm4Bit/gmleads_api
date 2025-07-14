<?php

declare(strict_types=1);

function base_path(string $path): string
{
    return BASE_PATH.$path;
}

function render(string $template, array $attributes = []): string
{
    extract($attributes); // Extrai as variáveis do array para o escopo local
    ob_start();
    require $template;
    $html = ob_get_clean();

    return $html;
}

function renderPartial(string $template, array $attributes = [])
{
    extract($attributes);

    return require base_path('templates/partials/'.$template.'.php');
}

function get_translation(string $key, array $translations)
{
    return $translations[$key];
}

function normalize_string(string $value): string
{
    if (function_exists('iconv')) {
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
    } else {
        $value = Normalizer::normalize($value, Normalizer::FORM_D);
        $value = preg_replace('/[\pM]/u', '', $value);
    }
    $value = preg_replace('/[^\p{L}\p{N}\s_]/u', '', $value);
    $value = strtolower($value);
    $value = preg_replace('/\s+/', '_', $value);
    $value = preg_replace('/_+/', '_', $value);
    $value = trim($value, '_');

    return $value;
}
