<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'phone' => '+25884' . fake()->numerify('#######'),
            'university' => fake()->randomElement([
                'Universidade Eduardo Mondlane (UEM)',
                'Universidade Pedagógica (UP)',
                'Universidade Lúrio (UniLúrio)',
                'Instituto Superior de Ciências e Tecnologia de Moçambique (ISCTEM)',
            ]),
            'course' => fake()->randomElement([
                'Engenharia Informática',
                'Medicina',
                'Direito',
                'Economia',
                'Administração e Gestão',
            ]),
            'role' => fake()->randomElement(['student', 'participant']),
            'balance' => fake()->randomFloat(2, 0, 1000),
            'profile_info' => null,
            'verification_status' => 'approved',
            'email_notifications' => true,
            'whatsapp_notifications' => fake()->boolean(),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Configure the model to be a student.
     */
    public function student(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => 'student',
            'bi_number' => 'BI' . fake()->unique()->numerify('########'),
            'birth_date' => fake()->dateTimeBetween('-30 years', '-18 years')->format('Y-m-d'),
            'gender' => fake()->randomElement(['masculino', 'feminino']),
            'institution_type' => fake()->randomElement(['Universidade Pública', 'Universidade Privada']),
            'admission_year' => fake()->numberBetween(2018, 2023),
            'expected_graduation' => fake()->numberBetween(2024, 2028),
            'academic_level' => fake()->randomElement([
                'Licenciatura - 1º ano',
                'Licenciatura - 2º ano',
                'Licenciatura - 3º ano',
                'Licenciatura - 4º ano',
            ]),
            'student_card_number' => 'STU' . fake()->numerify('########'),
            'research_interests' => json_encode(fake()->randomElements(
                ['ciencias_sociais', 'saude', 'tecnologia', 'educacao', 'economia'],
                fake()->numberBetween(1, 3)
            )),
            'documents_submitted' => fake()->boolean(80),
        ]);
    }

    /**
     * Configure the model to be a participant.
     */
    public function participant(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => 'participant',
            'verification_status' => 'approved',
        ]);
    }

    /**
     * Configure the model to be an admin.
     */
    public function admin(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => 'admin',
            'email' => 'admin@mozpesquisa.com',
            'verification_status' => 'approved',
        ]);
    }

    /**
     * Configure the model to have pending verification.
     */
    public function pendingVerification(): static
    {
        return $this->state(fn(array $attributes) => [
            'verification_status' => 'pending',
            'documents_submitted' => fake()->boolean(),
        ]);
    }
}
