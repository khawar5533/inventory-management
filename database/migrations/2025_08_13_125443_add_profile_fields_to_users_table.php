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
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('user_image');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('address')->nullable()->after('last_name');
            $table->string('address2')->nullable()->after('address');
            $table->string('city')->nullable()->after('address2');
            $table->string('state')->nullable()->after('city');
            $table->string('zip', 20)->nullable()->after('state');
            $table->string('country')->nullable()->after('zip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
              $table->dropColumn([
                'first_name',
                'last_name',
                'address',
                'address2',
                'city',
                'state',
                'zip',
                'country',
            ]);
        });
    }
};
