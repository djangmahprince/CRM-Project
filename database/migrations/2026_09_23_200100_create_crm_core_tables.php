<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->foreignId('parent_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->string('phone', 40)->nullable();
            $table->string('fax', 40)->nullable();
            $table->string('website', 255)->nullable();
            $table->string('type', 50)->nullable();
            $table->string('industry', 80)->nullable();
            $table->unsignedInteger('employees')->nullable();
            $table->decimal('annual_revenue', 15, 2)->nullable();
            $table->string('billing_street', 255)->nullable();
            $table->string('billing_city', 80)->nullable();
            $table->string('billing_state', 80)->nullable();
            $table->string('billing_postal_code', 20)->nullable();
            $table->string('billing_country', 80)->nullable();
            $table->string('shipping_street', 255)->nullable();
            $table->string('shipping_city', 80)->nullable();
            $table->string('shipping_state', 80)->nullable();
            $table->string('shipping_postal_code', 20)->nullable();
            $table->string('shipping_country', 80)->nullable();
            $table->text('description')->nullable();
            $table->foreignId('owner_id')->constrained('users');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['owner_id', 'name']);
        });

        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->restrictOnDelete();
            $table->string('salutation', 20)->nullable();
            $table->string('first_name', 40)->nullable();
            $table->string('middle_name', 40)->nullable();
            $table->string('last_name', 80);
            $table->string('title', 128)->nullable();
            $table->string('department', 80)->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('mobile', 40)->nullable();
            $table->string('home_phone', 40)->nullable();
            $table->string('other_phone', 40)->nullable();
            $table->string('email', 80)->nullable();
            $table->string('fax', 40)->nullable();
            $table->foreignId('reports_to_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->string('assistant', 80)->nullable();
            $table->string('asst_phone', 40)->nullable();
            $table->string('mailing_street', 255)->nullable();
            $table->string('mailing_city', 80)->nullable();
            $table->string('mailing_state', 80)->nullable();
            $table->string('mailing_postal_code', 20)->nullable();
            $table->string('mailing_country', 80)->nullable();
            $table->string('other_street', 255)->nullable();
            $table->string('other_city', 80)->nullable();
            $table->string('other_state', 80)->nullable();
            $table->string('other_postal_code', 20)->nullable();
            $table->string('other_country', 80)->nullable();
            $table->string('lead_source', 80)->nullable();
            $table->date('birthdate')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('owner_id')->constrained('users');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['owner_id', 'last_name']);
            $table->index('email');
        });

        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('salutation', 20)->nullable();
            $table->string('first_name', 40)->nullable();
            $table->string('last_name', 80);
            $table->string('company', 255);
            $table->string('title', 128)->nullable();
            $table->string('email', 80)->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('mobile', 40)->nullable();
            $table->string('lead_status', 40)->default('New');
            $table->string('lead_source', 80)->nullable();
            $table->string('rating', 20)->nullable();
            $table->string('industry', 80)->nullable();
            $table->decimal('annual_revenue', 15, 2)->nullable();
            $table->unsignedInteger('number_of_employees')->nullable();
            $table->string('website', 255)->nullable();
            $table->string('street', 255)->nullable();
            $table->string('city', 80)->nullable();
            $table->string('state', 80)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 80)->nullable();
            $table->text('description')->nullable();
            $table->boolean('converted')->default(false);
            $table->foreignId('converted_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('converted_contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->unsignedBigInteger('converted_opportunity_id')->nullable();
            $table->foreignId('owner_id')->constrained('users');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['owner_id', 'lead_status']);
            $table->index('email');
            $table->index('company');
        });

        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->foreignId('account_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 15, 2)->nullable();
            $table->date('close_date');
            $table->string('stage', 80);
            $table->unsignedTinyInteger('probability')->default(10);
            $table->string('type', 50)->nullable();
            $table->string('lead_source', 80)->nullable();
            $table->string('next_step', 255)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_closed')->default(false);
            $table->boolean('is_won')->default(false);
            $table->foreignId('owner_id')->constrained('users');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['owner_id', 'stage']);
            $table->index('close_date');
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->foreign('converted_opportunity_id')->references('id')->on('opportunities')->nullOnDelete();
        });

        Schema::create('opportunity_stage_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opportunity_id')->constrained()->cascadeOnDelete();
            $table->string('from_stage', 80)->nullable();
            $table->string('to_stage', 80);
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('cases', function (Blueprint $table) {
            $table->id();
            $table->string('case_number', 40)->unique();
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('subject', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('status', 40)->default('New');
            $table->string('priority', 20)->nullable();
            $table->string('type', 50)->nullable();
            $table->string('origin', 40);
            $table->string('reason', 80)->nullable();
            $table->text('internal_comments')->nullable();
            $table->string('web_email', 80)->nullable();
            $table->string('web_name', 80)->nullable();
            $table->string('web_company', 80)->nullable();
            $table->string('web_phone', 40)->nullable();
            $table->boolean('is_closed')->default(false);
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('owner_id')->constrained('users');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['owner_id', 'status']);
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('subject', 255);
            $table->foreignId('assigned_to_id')->constrained('users');
            $table->nullableMorphs('related');
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->date('due_date')->nullable();
            $table->string('status', 40)->default('Not Started');
            $table->string('priority', 20)->default('Normal');
            $table->text('comments')->nullable();
            $table->boolean('reminder_set')->default(false);
            $table->dateTime('reminder_at')->nullable();
            $table->timestamp('reminder_sent_at')->nullable();
            $table->foreignId('owner_id')->constrained('users');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['assigned_to_id', 'due_date', 'status']);
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('subject', 255);
            $table->foreignId('assigned_to_id')->constrained('users');
            $table->nullableMorphs('related');
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->boolean('all_day')->default(false);
            $table->string('location', 255)->nullable();
            $table->string('show_as', 40)->default('Busy');
            $table->boolean('is_private')->default(false);
            $table->string('recurrence_rule', 255)->nullable();
            $table->text('description')->nullable();
            $table->foreignId('owner_id')->constrained('users');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['assigned_to_id', 'starts_at']);
        });

        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255)->nullable();
            $table->text('body');
            $table->morphs('notable');
            $table->foreignId('owner_id')->constrained('users');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->string('original_name');
            $table->string('path');
            $table->string('disk')->default('local');
            $table->string('mime', 120)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->morphs('attachable');
            $table->foreignId('owner_id')->constrained('users');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('record_shares', function (Blueprint $table) {
            $table->id();
            $table->morphs('shareable');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('access', 20)->default('read');
            $table->timestamps();
            $table->unique(['shareable_type', 'shareable_id', 'user_id']);
        });

        Schema::create('recently_viewed', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('viewable');
            $table->timestamp('viewed_at');
            $table->index(['user_id', 'viewed_at']);
        });

        Schema::create('search_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('query', 255);
            $table->timestamps();
        });

        Schema::create('saved_searches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('object_type')->nullable();
            $table->json('criteria');
            $table->timestamps();
        });

        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description', 500)->nullable();
            $table->string('folder')->default('Private Reports');
            $table->string('report_type');
            $table->json('definition');
            $table->boolean('is_system')->default(false);
            $table->foreignId('owner_id')->constrained('users');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('report_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('frequency');
            $table->unsignedTinyInteger('send_day')->nullable();
            $table->time('send_time')->default('08:00:00');
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamps();
        });

        Schema::create('dashboards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description', 500)->nullable();
            $table->string('folder')->default('Private Dashboards');
            $table->unsignedTinyInteger('auto_refresh_minutes')->nullable();
            $table->foreignId('owner_id')->constrained('users');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('dashboard_widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dashboard_id')->constrained()->cascadeOnDelete();
            $table->foreignId('report_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('type', 40);
            $table->unsignedTinyInteger('x')->default(0);
            $table->unsignedTinyInteger('y')->default(0);
            $table->unsignedTinyInteger('w')->default(6);
            $table->unsignedTinyInteger('h')->default(4);
            $table->json('options')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 80);
            $table->nullableMorphs('auditable');
            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });

        Schema::create('assistant_dismissals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->timestamps();
            $table->unique(['user_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assistant_dismissals');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('dashboard_widgets');
        Schema::dropIfExists('dashboards');
        Schema::dropIfExists('report_subscriptions');
        Schema::dropIfExists('reports');
        Schema::dropIfExists('saved_searches');
        Schema::dropIfExists('search_histories');
        Schema::dropIfExists('recently_viewed');
        Schema::dropIfExists('record_shares');
        Schema::dropIfExists('attachments');
        Schema::dropIfExists('notes');
        Schema::dropIfExists('events');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('cases');
        Schema::dropIfExists('opportunity_stage_histories');
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['converted_opportunity_id']);
        });
        Schema::dropIfExists('opportunities');
        Schema::dropIfExists('leads');
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('accounts');
    }
};
