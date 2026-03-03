<?php

use App\Models\Category;
use App\Models\Post;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // 1. Vitrin için en yeni yazılar (Performans için sadece 10 tane çekiyoruz)
    $posts = Post::where('status', 2)
        ->where('published_at', '<=', now())
        ->latest('published_at')
        ->take(10)
        ->get();

    // 2. "Trend Olanlar" listesi için en çok okunan (view_count) 5 yazı
    $trendingPosts = Post::where('status', 2)
        ->where('published_at', '<=', now())
        ->orderBy('view_count', 'desc') // Okunma sayısına göre çoktan aza sırala
        ->take(5)
        ->get();

    $globalCategories = Category::whereNull('parent_id') // Sadece ana kategoriler
        ->where('status', true) // Aktif olanları getir
        ->orderBy('order', 'asc') // Admin panelindeki sıraya göre diz
        ->with(['children' => function ($query) {
            // Alt kategorileri çekerken de sadece aktif olanları ve sıraya göre getir
            $query->where('status', true)->orderBy('order', 'asc');
        }])
        ->get();

    return view('pages.frontend.index', compact('posts', 'trendingPosts', 'globalCategories'));
});

Route::get('/yazi/{post}', function (Post $post) {
    // Sadece yayınlanmış yazıları göster
    if ($post->status !== 2) {
        abort(404);
    }

    // Okunma sayısını 1 artır (Eğer migration'da view_count varsa)
    // Eğer view_count sütunu yoksa bu satırı silebilir veya yorum satırına alabilirsin
    $post->increment('view_count');

    return view('pages.frontend.blog-detail', compact('post'));
})->name('post.detail'); // <- İşte Laravel'in aradığı isim bu!
