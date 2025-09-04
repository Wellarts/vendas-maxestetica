<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contas_recebers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->restrictOnDelete();
            $table->foreignId('venda_id')->constrained('vendas')->restrictOnDelete();
            $table->string('parcelas');
            $table->string('ordem_parcela');
            $table->date('data_vencimento');
            $table->date('data_pagamento');
            $table->boolean('status');
            $table->decimal('valor_total', 10, 2);
            $table->decimal('valor_parcela', 10, 2);
            $table->decimal('valor_recebido', 10, 2);
            $table->longText('obs');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contas_recebers');
    }
};
