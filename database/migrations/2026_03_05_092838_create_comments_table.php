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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('comments')->cascadeOnDelete();

            $table->string('ancestry')->nullable()->index();
            $table->text('body');
            $table->tinyInteger('status')->default(0)->index();
            $table->text('rejection_note')->nullable();

            $table->foreignId('moderated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('moderated_at')->nullable();

            $table->unsignedInteger('like_count')->default(0);
            $table->unsignedInteger('dislike_count')->default(0);
            $table->unsignedInteger('report_count')->default(0);
            $table->unsignedInteger('reply_count')->default(0);

            $table->boolean('is_edited')->default(false);
            $table->timestamp('edited_at')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['post_id', 'status']);
            $table->index(['post_id', 'parent_id', 'status']);
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('comment_likes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('comment_id')->constrained('comments')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->tinyInteger('type')->default(1); // 1: like | -1: dislike
            $table->timestamps();
            $table->unique(['comment_id', 'user_id']);
        });

        Schema::create('comment_reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('comment_id')->constrained('comments')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('reason', [
                'spam',           // Reklam / istenmeyen içerik
                'hate_speech',    // Nefret söylemi
                'harassment',     // Taciz / hakaret
                'misinformation', // Yanlış bilgi
                'off_topic',      // Konu dışı
                'other',          // Diğer
            ])->default('spam');
            $table->text('note')->nullable();
            $table->tinyInteger('status')->default(0); // 0: Bekliyor | 1: İşleme Alındı | 2: Reddedildi
            $table->timestamps();
            $table->unique(['comment_id', 'user_id']);

            $table->index(['comment_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comment_reports');
        Schema::dropIfExists('comment_likes');
        Schema::dropIfExists('comments');
    }
};