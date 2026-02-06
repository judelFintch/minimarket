<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        $defaultUserId = DB::table('users')->orderBy('id')->value('id');
        if ($defaultUserId) {
            DB::table('sales')->whereNull('user_id')->update(['user_id' => $defaultUserId]);
            DB::table('purchases')->whereNull('user_id')->update(['user_id' => $defaultUserId]);
        }
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
