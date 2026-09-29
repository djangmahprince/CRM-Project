<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('object_type', 40);
            $table->string('field', 80);
            $table->string('operator', 40)->default('equals');
            $table->string('value', 255);
            $table->string('action_type', 40)->default('create_task');
            $table->json('action_config');
            $table->boolean('enabled')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['object_type', 'enabled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_rules');
    }
};
