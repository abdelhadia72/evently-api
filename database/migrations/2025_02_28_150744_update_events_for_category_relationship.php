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
        Schema::table('events', function (Blueprint $table) {
            // If the events table has a 'category' column, remove it
            if (Schema::hasColumn('events', 'category')) {
                $table->dropColumn('category');
            }

            // Add the new foreign key to categories table
            if (! Schema::hasColumn('events', 'category_id')) {
                $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'category_id')) {
                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');
            }

            // Re-add the old column if needed
            if (! Schema::hasColumn('events', 'category')) {
                $table->string('category')->nullable();
            }
        });
    }
};
