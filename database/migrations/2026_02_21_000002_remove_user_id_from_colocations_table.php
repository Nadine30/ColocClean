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
        if (Schema::hasColumn('colocations', 'user_id')) {
            Schema::withoutForeignKeyConstraints(function () {
                Schema::table('colocations', function (Blueprint $table) {
                    $table->dropColumn('user_id');
                });
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('colocations', 'user_id')) {
            Schema::table('colocations', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            });
        }
    }
};
