<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->decimal('loan_amount', 10, 2);
            $table->decimal('disbursement_amount', 10, 2);
            $table->decimal('total_amount_to_pay', 10, 2);
            $table->tinyInteger('tenure');
            $table->enum('frequency',['week', 'month', 'year'])->default('week');
            $table->decimal('interest_rate', 10, 2);
            $table->decimal('processing_fee', 10, 2);
            $table->tinyInteger('status')->default(0)->comment('0-pending, 1-approved, 2-completed');
            $table->foreign('user_id')->references('id')->on('users');
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
        Schema::dropIfExists('loans');
    }
}
