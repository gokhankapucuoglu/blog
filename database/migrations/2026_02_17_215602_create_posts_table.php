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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();

            // İlişkiler
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();

            // İçerik Alanları
            $table->string('image');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->longText('content');

            // SEO Alanları
            $table->json('tags');
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();

            // Durum ve İstatistikler
            $table->unsignedBigInteger('view_count')->default(0);
            $table->unsignedBigInteger('like_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->tinyInteger('status')->default(0); // 0: Taslak, 1: Onay Bekliyor, 2: Yayında, 3: Reddedildi

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('post_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action'); // İşlem tipi (Oluşturuldu, Onaya Gönderildi vs.)
            $table->text('description')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_histories');
        Schema::dropIfExists('posts');
    }
};
