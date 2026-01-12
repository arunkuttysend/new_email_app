<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ====================
        // MAILING LISTS
        // ====================
        Schema::create('mailing_lists', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->string('visibility')->default('public');
            $table->string('opt_in')->default('double');
            $table->string('opt_out')->default('single');
            $table->boolean('welcome_email')->default(false);
            $table->boolean('require_approval')->default(false);
            $table->jsonb('defaults')->nullable();
            $table->jsonb('company_info')->nullable();
            $table->jsonb('notifications')->nullable();
            $table->unsignedInteger('subscribers_count')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
        });

        // ====================
        // LIST FIELDS (Custom Fields)
        // ====================
        Schema::create('list_fields', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('mailing_list_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('tag');
            $table->string('type')->default('text');
            $table->jsonb('options')->nullable();
            $table->string('default_value')->nullable();
            $table->string('help_text')->nullable();
            $table->boolean('required')->default(false);
            $table->string('visibility')->default('visible');
            $table->smallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['mailing_list_id', 'tag']);
        });

        // ====================
        // SUBSCRIBERS
        // ====================
        Schema::create('subscribers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('mailing_list_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('ip_address', 45)->nullable();
            $table->string('source')->default('web');
            $table->string('status')->default('unconfirmed');
            $table->timestamp('subscribed_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->timestamps();

            $table->unique(['mailing_list_id', 'email']);
            $table->index(['status', 'created_at']);
            $table->index('email');
        });

        // ====================
        // SUBSCRIBER FIELD VALUES
        // ====================
        Schema::create('subscriber_field_values', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('subscriber_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('list_field_id')->constrained()->cascadeOnDelete();
            $table->text('value');
            $table->timestamps();

            $table->unique(['subscriber_id', 'list_field_id']);
        });

        // ====================
        // SEGMENTS
        // ====================
        Schema::create('segments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('mailing_list_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('match_type')->default('all');
            $table->jsonb('conditions');
            $table->unsignedInteger('subscribers_count')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        // ====================
        // DELIVERY SERVERS
        // ====================
        Schema::create('delivery_servers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('type');
            $table->text('credentials'); // Changed to text to support encrypted cast
            $table->string('from_email');
            $table->string('from_name')->nullable();
            $table->string('reply_to')->nullable();
            $table->jsonb('settings')->nullable();
            $table->jsonb('quotas')->nullable();
            $table->jsonb('current_usage')->nullable();
            $table->uuid('warmup_plan_id')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'status']);
        });

        // ====================
        // CAMPAIGNS
        // ====================
        Schema::create('campaigns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('mailing_list_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('segment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('type')->default('regular');
            $table->string('from_name');
            $table->string('from_email');
            $table->string('reply_to')->nullable();
            $table->string('subject');
            $table->string('preheader')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->jsonb('options')->nullable();
            $table->jsonb('stats')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'scheduled_at']);
        });

        // ====================
        // CAMPAIGN CONTENTS
        // ====================
        Schema::create('campaign_contents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('campaign_id')->constrained()->cascadeOnDelete();
            $table->text('html_content');
            $table->text('plain_text')->nullable();
            $table->jsonb('template_data')->nullable();
            $table->string('template_type')->default('html');
            $table->timestamps();
        });

        // ====================
        // EMAIL SEQUENCES
        // ====================
        Schema::create('email_sequences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('campaign_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->boolean('enable_threading')->default(true);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        // ====================
        // SEQUENCE STEPS
        // ====================
        Schema::create('sequence_steps', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sequence_id')->constrained('email_sequences')->cascadeOnDelete();
            $table->unsignedSmallInteger('step_order');
            $table->string('name');
            $table->unsignedInteger('wait_value')->default(1);
            $table->string('wait_unit')->default('days');
            $table->string('condition_type')->nullable();
            $table->string('condition_operator')->default('not');
            $table->string('condition_link_id')->nullable();
            $table->string('subject');
            $table->text('html_content');
            $table->text('plain_text')->nullable();
            $table->jsonb('template_data')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['sequence_id', 'step_order']);
        });

        // ====================
        // SEQUENCE SUBSCRIBER PROGRESS
        // ====================
        Schema::create('sequence_subscriber_progress', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sequence_id')->constrained('email_sequences')->cascadeOnDelete();
            $table->foreignUuid('subscriber_id')->constrained()->cascadeOnDelete();
            $table->uuid('current_step_id')->nullable();
            $table->unsignedSmallInteger('current_step_order')->default(0);
            $table->timestamp('next_send_at')->nullable();
            $table->string('status')->default('active');
            $table->string('stop_reason')->nullable();
            $table->timestamps();

            $table->unique(['sequence_id', 'subscriber_id']);
            $table->index(['status', 'next_send_at']);
        });

        // ====================
        // SEQUENCE STEP LOGS
        // ====================
        Schema::create('sequence_step_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sequence_id')->constrained('email_sequences')->cascadeOnDelete();
            $table->foreignUuid('step_id')->constrained('sequence_steps')->cascadeOnDelete();
            $table->foreignUuid('subscriber_id')->constrained()->cascadeOnDelete();
            $table->string('message_id')->nullable();
            $table->string('thread_id')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->timestamps();

            $table->index(['sequence_id', 'subscriber_id']);
            $table->index('message_id');
        });

        // ====================
        // CAMPAIGN SENDS
        // ====================
        Schema::create('campaign_sends', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('subscriber_id')->constrained()->cascadeOnDelete();
            $table->uuid('delivery_server_id')->nullable();
            $table->string('message_id')->nullable();
            $table->string('status')->default('pending');
            $table->text('error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->unique(['campaign_id', 'subscriber_id']);
            $table->index('message_id');
            $table->index(['campaign_id', 'status']);
        });

        // ====================
        // CAMPAIGN LINKS
        // ====================
        Schema::create('campaign_links', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('campaign_id')->constrained()->cascadeOnDelete();
            $table->string('hash', 32)->unique();
            $table->text('url');
            $table->unsignedInteger('clicks_count')->default(0);
            $table->timestamps();
        });

        // ====================
        // CAMPAIGN OPENS
        // ====================
        Schema::create('campaign_opens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('subscriber_id')->constrained()->cascadeOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->jsonb('geo_data')->nullable();
            $table->timestamp('opened_at');

            $table->index(['campaign_id', 'opened_at']);
            $table->index('subscriber_id');
        });

        // ====================
        // CAMPAIGN CLICKS
        // ====================
        Schema::create('campaign_clicks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('subscriber_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('campaign_link_id')->constrained()->cascadeOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->jsonb('geo_data')->nullable();
            $table->timestamp('clicked_at');

            $table->index(['campaign_id', 'clicked_at']);
        });

        // ====================
        // CAMPAIGN BOUNCES
        // ====================
        Schema::create('campaign_bounces', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('subscriber_id')->constrained()->cascadeOnDelete();
            $table->string('message_id')->nullable();
            $table->string('bounce_type'); // hard, soft
            $table->string('bounce_category')->nullable();
            $table->text('bounce_message')->nullable();
            $table->timestamp('bounced_at');
            $table->timestamps();

            $table->index(['campaign_id', 'bounced_at']);
        });

        // ====================
        // EMAIL TEMPLATES
        // ====================
        Schema::create('email_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('category')->nullable();
            $table->text('html_content');
            $table->jsonb('template_data')->nullable();
            $table->string('thumbnail')->nullable();
            $table->boolean('is_global')->default(false);
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        // ====================
        // SETTINGS
        // ====================
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('group')->default('general');
            $table->string('key');
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['group', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('campaign_bounces');
        Schema::dropIfExists('campaign_clicks');
        Schema::dropIfExists('campaign_opens');
        Schema::dropIfExists('campaign_links');
        Schema::dropIfExists('campaign_sends');
        Schema::dropIfExists('sequence_step_logs');
        Schema::dropIfExists('sequence_subscriber_progress');
        Schema::dropIfExists('sequence_steps');
        Schema::dropIfExists('email_sequences');
        Schema::dropIfExists('campaign_contents');
        Schema::dropIfExists('campaigns');
        Schema::dropIfExists('delivery_servers');
        Schema::dropIfExists('segments');
        Schema::dropIfExists('subscriber_field_values');
        Schema::dropIfExists('subscribers');
        Schema::dropIfExists('list_fields');
        Schema::dropIfExists('mailing_lists');
    }
};
