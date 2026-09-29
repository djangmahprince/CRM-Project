<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table): void {
            $table->index('updated_at');
        });

        Schema::table('opportunities', function (Blueprint $table): void {
            $table->index(['is_closed', 'close_date']);
            $table->index('updated_at');
        });

        Schema::table('leads', function (Blueprint $table): void {
            $table->index(['converted', 'owner_id']);
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table): void {
            $table->dropIndex(['updated_at']);
        });

        Schema::table('opportunities', function (Blueprint $table): void {
            $table->dropIndex(['is_closed', 'close_date']);
            $table->dropIndex(['updated_at']);
        });

        Schema::table('leads', function (Blueprint $table): void {
            $table->dropIndex(['converted', 'owner_id']);
        });
    }
};
