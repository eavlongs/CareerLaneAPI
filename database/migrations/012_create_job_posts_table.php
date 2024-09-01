<?php

use App\Enums\LocationEnum;
use App\Enums\JobTypeEnum;
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
        Schema::create('job_posts', function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->string("title");
            $table->text("description");
            $table->integer("location");
            $table->integer("type");
            $table->double("salary")->nullable();
            $table->double("salary_start_range")->nullable();
            $table->double("salary_end_range")->nullable();
            $table->boolean("is_salary_negotiable");
            $table->timestamp("original_deadline");
            $table->timestamp("extended_deadline")->nullable();
            $table->unsignedInteger("applicants")->default(0);
            $table->foreignUuid("company_id")->constrained("companies");
            $table->foreignUuid("category_id")->constrained("categories");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_posts');
    }
};
