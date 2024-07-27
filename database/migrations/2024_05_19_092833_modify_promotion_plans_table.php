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
        Schema::table('promotion_plans', function (Blueprint $table) {
            $table->dropColumn(['one_day_price', 'three_day_price', 'seven_day_price']);
            $table->decimal('price')->after('name');
            $table->integer('number_of_days')->after('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promotion_plans', function (Blueprint $table) {
            $table->decimal('one_day_price')->after('name');
            $table->decimal('three_day_price')->after('one_day_price');
            $table->decimal('seven_day_price')->after('three_day_price');
            $table->dropColumn(['price', 'number_of_days']);
        });
    }
};
