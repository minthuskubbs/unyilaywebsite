<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('brass_showroom_frontend_releases', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('version', 40);
            $table->string('javascript_path', 255);
            $table->string('stylesheet_path', 255);
            $table->char('javascript_sha256', 64);
            $table->char('stylesheet_sha256', 64);
            $table->unsignedInteger('javascript_bytes');
            $table->unsignedInteger('stylesheet_bytes');
            $table->string('note', 240)->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('activated_by')->nullable()->index();
            $table->timestamp('activated_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::table('brass_showrooms', function (Blueprint $table): void {
            $table->uuid('frontend_release_id')->nullable()->index()->after('updated_by');
        });

        Schema::create('brass_showroom_frontend_activations', function (Blueprint $table): void {
            $table->id();
            $table->string('from_release_id', 40);
            $table->string('to_release_id', 40);
            $table->string('note', 240)->nullable();
            $table->unsignedBigInteger('activated_by')->nullable()->index();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brass_showroom_frontend_activations');
        Schema::table('brass_showrooms', function (Blueprint $table): void {
            $table->dropColumn('frontend_release_id');
        });
        Schema::dropIfExists('brass_showroom_frontend_releases');
    }
};
