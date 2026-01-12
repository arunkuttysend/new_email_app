<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bounce_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('subscriber_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email');
            $table->string('bounce_type'); // hard, soft, block
            $table->text('bounce_reason')->nullable();
            $table->text('diagnostic_code')->nullable();
            $table->string('smtp_code')->nullable();
            $table->text('raw_message')->nullable();
            $table->timestamp('bounced_at');
            $table->timestamps();
            
            $table->index(['email', 'bounce_type']);
            $table->index(['subscriber_id', 'bounced_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bounce_logs');
    }
};
