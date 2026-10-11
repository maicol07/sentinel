<?php

declare(strict_types=1);

use Ninja\Sentinel\Analyzers\PrismAI;
use Ninja\Sentinel\Services\Adapters\PrismAdapter;
use Ninja\Sentinel\Services\Pipeline\TransformationPipeline;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism as PrismFacade;
use Prism\Prism\Prism;
use Prism\Prism\Testing\StructuredResponseFake;
use Prism\Prism\Testing\TextResponseFake;

test('Prism provider analyzes responses using the current SDK', function (Provider $provider): void {
    $data = [
        'detected_language' => 'en',
        'is_offensive' => false,
        'offensive_words' => [],
        'categories' => [],
        'confidence' => 1.0,
        'severity' => 0.0,
        'sentiment' => ['type' => 'neutral', 'score' => 0.0],
        'matches' => [],
    ];

    config(['sentinel.services.prism_ai.provider' => $provider, 'sentinel.services.prism_ai.model' => 'test-model']);
    PrismFacade::fake([
        $provider === Provider::Gemini
            ? TextResponseFake::make()->withText(json_encode($data, JSON_THROW_ON_ERROR))
            : StructuredResponseFake::make()->withStructured($data),
    ]);

    $analyzer = new PrismAI(
        new Prism,
        new PrismAdapter,
        app(TransformationPipeline::class),
    );

    expect($analyzer->analyze('A useful game review')->offensive())->toBeFalse();
})->with([
    'Anthropic' => Provider::Anthropic,
    'OpenAI' => Provider::OpenAI,
    'Gemini' => Provider::Gemini,
]);
