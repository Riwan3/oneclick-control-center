<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// "Lupa Password" requests submitted from the login page. A super admin
// verifies the requester and then resets the password from User Management.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('password_reset_requests', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->string('full_name');
            $table->string('position');
            $table->string('phone');
            // Matched by username at submit time; null when no such user exists.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            // pending -> verified -> reset, or pending/verified -> rejected.
            $table->string('status')->default('pending');
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_requests');
    }
};
