<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('brass_showrooms', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 64)->unique();
            $table->json('published_config');
            $table->json('draft_config');
            $table->unsignedInteger('published_revision')->default(1);
            $table->unsignedInteger('draft_revision')->default(1);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('draft_updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('brass_showroom_revisions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('brass_showroom_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('revision');
            $table->json('config');
            $table->string('note', 240)->nullable();
            $table->unsignedBigInteger('published_by')->nullable()->index();
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['brass_showroom_id', 'revision']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brass_showroom_revisions');
        Schema::dropIfExists('brass_showrooms');
    }
};
