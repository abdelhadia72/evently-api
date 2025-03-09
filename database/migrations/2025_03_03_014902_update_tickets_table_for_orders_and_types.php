<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Change this line from Schema::create to Schema::table
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('ticket_type_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('price_paid', 10, 2)->nullable();
        });
    }

    public function down()
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropForeign(['ticket_type_id']);
            $table->dropColumn(['order_id', 'ticket_type_id', 'price_paid']);
        });
    }
};
