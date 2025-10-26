<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->enum('role', ['admin', 'parent', 'child']);
            $table->foreignId('parent_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->decimal('allowance_balance', 10, 2)->default(0.00);
            $table->decimal('weekly_allowance_rate', 10, 2)->default(0.00);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};

