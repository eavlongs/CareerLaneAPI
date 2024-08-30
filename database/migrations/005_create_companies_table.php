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
        Schema::create('companies', function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->string("name");
            $table->text("description");
            $table->string("logo_url")->nullable();
            // links = {
            //     "title": sring;
            //     "url": string;
            // }[]
            $table->text("links");
            $table->foreignUuid("account_id")->constrained("accounts")->onDelete("cascade");
            $table->foreignUuid("province_id")->constrained("provinces");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
