<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_balance_changes', function (Blueprint $table) {
            $table->id();
            $table->integer('walletId');
            $table->char('before_currency', 3);
            $table->char('after_currency', 3);
            $table->float('before_balance');
            $table->float('after_balance');
            $table->string('reason');
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
        Schema::dropIfExists('log_balance_changes');
    }
};
