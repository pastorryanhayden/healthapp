<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;
use Stringable;

#[Provider(Lab::xAI)]
#[Model('grok-4.6')]
class CalorieEstimator implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return <<<'PROMPT'
            Estimate calories for one food description a person typed.
            Return a short display name and a positive integer calorie count for the amount described.
            Count only what they wrote. Do not add drinks or sides they did not mention.
            PROMPT;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->required(),
            'calories' => $schema->integer()->min(1)->required(),
        ];
    }
}
