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
        Schema::table('campaign_replies', function (Blueprint $table) {
            $table->string('status')->default('unread'); // unread, read, archived
            $table->string('sentiment')->nullable(); // interested, not_interested, ooo
            $table->boolean('is_lead')->default(false); // Quick filter for hot leads
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaign_replies', function (Blueprint $table) {
            //
        });
    }
};
