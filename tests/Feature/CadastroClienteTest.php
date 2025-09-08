<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Cliente;

class CadastroClienteTest extends TestCase
{
    use RefreshDatabase;

    public function test_cliente_pode_ser_cadastrado()
    {
        $dados = [
            'nome' => 'Cliente Teste',
            'cpf_cnpj' => '12345678901',
            'endereco' => 'Rua Teste, 123',
            'estado_id' => 1,
            'cidade_id' => 1,
            'telefone' => '11999999999',
            'email' => 'cliente@teste.com',
            'numero_conselho' => '12345',
        ];

        $response = $this->post('/clientes', $dados);

        $response->assertStatus(302); // Redirecionamento após cadastro
        $this->assertDatabaseHas('clientes', [
            'email' => 'cliente@teste.com',
        ]);
    }
}
