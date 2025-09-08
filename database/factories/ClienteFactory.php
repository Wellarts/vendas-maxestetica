<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClienteFactory extends Factory
{
    protected $model = Cliente::class;

    public function definition()
    {
        return [
            'nome' => $this->faker->name(),
            'cpf_cnpj' => $this->faker->cpf(false),
            'endereco' => $this->faker->address(),
            'estado_id' => 1, // Ajuste conforme necessário
            'cidade_id' => 1, // Ajuste conforme necessário
            'telefone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'numero_conselho' => $this->faker->optional()->numerify('#####'),
        ];
    }
}
