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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('description');
            $table->string('startDate');
            $table->string('endDate');
            $table->string('startClock');
            $table->string('endClock');
            $table->string('image');
            $table->string('type');
            $table->string('address');
            $table->string('link')->nullable();
            $table->enum('approvalLevel', ['admin', 'superadmin'])->default('admin');
            $table->enum('status',['pending','accept','reject'])->default('pending');
            $table->enum('eventState',['active','cancel','delay'])->default('active');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
