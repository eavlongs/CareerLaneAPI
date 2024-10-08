<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->uuid("id")->primary()->default(DB::raw("(UUID())"));
            $table->string("name");
            $table->text("description")->nullable()->default(null);
            $table->string("logo_url")->nullable()->default(null);
            // links = {
            //     "title": sring;
            //     "url": string;
            // }[]
            $table->text("links")->nullable()->default(null);
            $table->foreignUuid("account_id")->constrained("accounts")->onDelete("cascade");
            $table->foreignUuid("province_id")->nullable()->default(null)->constrained("provinces");
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
