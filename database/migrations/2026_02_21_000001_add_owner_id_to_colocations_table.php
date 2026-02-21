<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('colocations', 'owner_id')) {
            Schema::table('colocations', function (Blueprint $table) {
                $table->foreignId('owner_id')->nullable()->constrained('users')->cascadeOnDelete();
            });

            // Renseigner owner_id pour les colocations existantes (premier membre)
            $colocations = DB::table('colocations')->get();
            foreach ($colocations as $coloc) {
                $first = DB::table('colocation_user')
                    ->where('colocation_id', $coloc->id)
                    ->orderBy('id')
                    ->first();
                if ($first) {
                    DB::table('colocations')->where('id', $coloc->id)->update(['owner_id' => $first->user_id]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('colocations', 'owner_id')) {
            Schema::table('colocations', function (Blueprint $table) {
                $table->dropForeign(['owner_id']);
            });
        }
    }
};
