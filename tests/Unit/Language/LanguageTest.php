<?php

use Ninja\Sentinel\Enums\LanguageCode;
use Ninja\Sentinel\Language\Language;

test('language detection deduplicates evidence and preserves numeric counters', function (): void {
    $language = new Language([
        'words' => [
            'offensive' => [],
            'intensifiers' => ['signal'],
            'modifiers' => ['negative' => ['signal'], 'positive' => []],
            'quote' => ['signal'],
            'excuse' => ['signal'],
        ],
        'pronouns' => ['signal'],
        'prefixes' => [],
        'suffixes' => [],
        'markers' => ['signal'],
        'contexts' => [],
        'patterns' => ['word_specific' => []],
        'rules' => [],
    ], LanguageCode::English);

    $result = $language->detect('signal signal unknown');

    foreach (['pronouns', 'markers', 'intensifiers', 'modifiers', 'quotes', 'excuses', 'common_words'] as $key) {
        expect($result->details[$key])->toBe(['signal']);
    }

    expect($result->details['word_count'])->toBe(3);
    expect($result->details['unique_word_count'])->toBe(2);
    expect($result->details['total_language_elements'])->toBe(7);
    expect($result->details['percentage_of_language_elements'])->toBe(7 / 3 * 100);
    expect($result->details['raw_score'])->toBe(50);
    expect($result->score->value())->toBe(0.7);
    expect($result->confidence->value())->toEqualWithDelta(0.2933333333, 0.0000000001);
});
