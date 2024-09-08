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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid("id")->primary()->default(DB::raw("(UUID())"));
            $table->foreignUuid('account_id')->constrained("accounts")->onDelete('cascade');
            $table->string("first_name");
            $table->string("last_name")->nullable();
            $table->string("about_me")->default("");
            $table->string("avatar_url")->nullable();
            $table->string("job_title")->nullable();
            $table->string("job_level")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
