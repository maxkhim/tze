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
        Schema::create('order_exports', function (Blueprint $table) {
            $table->id();
            $table->integer('order_id');
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->json('request_data')->nullable()->comment('Данные, отправленные во внешнюю систему');
            $table->json('response_data')->nullable()->comment('Ответ от внешней системы');
            $table->text('error_message')->nullable();
            $table->timestamp('attempted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_exports');
    }
};
