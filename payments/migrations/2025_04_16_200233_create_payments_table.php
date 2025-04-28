<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('payer_id');
            $table->unsignedInteger('payee_id');
            $table->integer('amount');

            $table->foreign('payer_id')->references('id')->on('accounts');
            $table->foreign('payee_id')->references('id')->on('accounts');

            $table->datetimes(3);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
