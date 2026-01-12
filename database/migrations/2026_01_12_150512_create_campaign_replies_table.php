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
        Schema::create('campaign_replies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('subscriber_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('campaign_send_id')->nullable()->constrained('campaign_sends')->nullOnDelete();
            $table->string('message_id')->nullable();
            $table->text('subject')->nullable();
            $table->text('from');
            $table->longText('body_text')->nullable();
            $table->longText('body_html')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
            
            $table->index(['campaign_id', 'subscriber_id']);
            $table->index('message_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_replies');
    }
};
