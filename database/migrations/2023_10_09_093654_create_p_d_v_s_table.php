<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('p_d_v_s', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produto_id');
            $table->string('venda_id');
            $table->decimal('valor_venda', 10, 2);
            $table->string('qtd');
            $table->decimal('acres_desc', 10, 2);
            $table->decimal('sub_total', 10, 2);
            $table->decimal('valor_custo_atual', 10, 2);
            $table->decimal('total_custo_atual', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('p_d_v_s');
    }
};
