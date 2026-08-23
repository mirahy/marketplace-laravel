<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->string('address_type')->nullable()->after('state');
            $table->string('address_street')->nullable()->after('address_type');
            $table->string('address_number')->nullable()->after('address_street');
            $table->string('address_neighborhood')->nullable()->after('address_number');
            $table->string('address_complement')->nullable()->after('address_neighborhood');
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn([
                'address_type',
                'address_street',
                'address_number',
                'address_neighborhood',
                'address_complement',
            ]);
        });
    }
};
