<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('service');
            $table->string('method', 10);
            $table->string('path');
            $table->json('request_body')->nullable();
            $table->unsignedSmallInteger('status_code');
            $table->longText('response_body')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->nullable()->index();

            $table->index('user_id');
            $table->index('service');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_logs');
    }
};
