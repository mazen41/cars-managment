<?php

namespace Database\Factories;

use App\Models\FaqTranslation;
use App\Models\Faq;
use Illuminate\Database\Eloquent\Factories\Factory;

class FaqTranslationFactory extends Factory
{
    protected $model = FaqTranslation::class;

    public function definition(): array
    {
        $faq = Faq::factory()->create();

        return [
            'faq_id' => $faq->id,
            'locale' => 'ar',
            'question' => $this->faker->sentence() . '?',
            'answer' => $this->faker->paragraphs(2, true),
        ];
    }

    public function locale(string $locale): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => $locale,
        ]);
    }
}
