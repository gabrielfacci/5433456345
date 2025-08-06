<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('distribution_process_control', function (Blueprint $table) {
            $table->id();

            $table->timestamp('last_execution')->nullable()->comment('Última execução do processo');
            $table->timestamp('next_execution')->nullable()->comment('Próxima execução programada');

            $table->boolean('is_processing')->default(false)->comment('Indica se o processo está em execução no momento');

            $table->enum('current_mode', ['arrecadacao', 'distribuicao'])
                ->default('arrecadacao')
                ->comment('Modo atual do sistema');

            $table->decimal('current_total', 15, 2)
                ->default(0)
                ->comment('Valor acumulado no ciclo atual (arrecadado ou distribuído)');

            $table->decimal('current_target', 15, 2)
                ->default(0)
                ->comment('Meta para o ciclo atual');

            $table->decimal('current_rtp', 5, 2)
                ->default(30.00)
                ->comment('RTP atual aplicado');

            $table->string('status', 50)
                ->default('idle')
                ->comment('Status textual do processo (ex: idle, running, error)');

            $table->text('last_message')->nullable()->comment('Última mensagem ou log breve do processo');
            $table->text('last_error')->nullable()->comment('Último erro ocorrido, se houver');

            $table->timestamps();

            // Índices para consultas frequentes
            $table->index('current_mode');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distribution_process_control');
    }
};
