@extends('theme.frontend.app')

@section('title', 'Anasayfa | KODBLOG')

@section('content')
<section id="slider" class="slider section dark-background">

    <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="swiper init-swiper">

            <script type="application/json" class="swiper-config">
                {
              "loop": true,
              "speed": 600,
              "autoplay": {
                "delay": 5000
              },
              "slidesPerView": "auto",
              "centeredSlides": true,
              "pagination": {
                "el": ".swiper-pagination",
                "type": "bullets",
                "clickable": true
              },
              "navigation": {
                "nextEl": ".swiper-button-next",
                "prevEl": ".swiper-button-prev"
              }
            }
            </script>

            <div class="swiper-wrapper">

                @foreach($posts->take(5) as $post)
                <div class="swiper-slide"
                    style="background-image: url('{{ ($post->image && file_exists(public_path('storage/' . $post->image))) ? asset('storage/' . $post->image) : 'https://placehold.co/1200x600/1a1a1a/ffffff?text=Gorsel+Yok' }}');">
                    <div class="content">
                        <h2>
                            <a href="{{ route('post.detail', $post->id ?? 1) }}">{{ $post->title }}</a>
                        </h2>
                        <p>{{ Str::limit($post->description, 200) }}</p>
                    </div>
                </div>
                @endforeach

            </div>

            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-pagination"></div>

        </div>
    </div>

</section>




<section id="trending-category" class="trending-category section">

    <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="container" data-aos="fade-up">
            <div class="row g-5">

                <div class="col-lg-4">
                    @if($largePost = $posts->first())
                    <div class="post-entry lg">
                        <a href="{{ route('post.detail', $largePost->id ?? 1) }}">
                            <img src="{{ ($largePost->image && file_exists(public_path('storage/' . $largePost->image))) ? asset('storage/' . $largePost->image) : 'https://placehold.co/800x500/f8f9fa/a1a1aa?text=Gorsel+Yok' }}"
                                alt="{{ $largePost->title }}" class="img-fluid"
                                style="height: 250px; width: 100%; object-fit: cover;">
                        </a>

                        <div class="post-meta">
                            <span class="date">{{ $largePost->category->name ?? 'Genel' }}</span>
                            <span class="mx-1">•</span>
                            <span>{{ $largePost->published_at ? $largePost->published_at->format('d M Y') :
                                $largePost->created_at->format('d M Y') }}</span>
                        </div>

                        <h2><a href="{{ route('post.detail', $largePost->id ?? 1) }}">{{ $largePost->title }}</a></h2>
                        <p class="mb-4 d-block">{{ Str::limit($largePost->description, 200) }}</p>

                        <div class="d-flex align-items-center author">
                            <div class="photo">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($largePost->user->full_name ?? 'Anonim') }}&background=random"
                                    alt="" class="img-fluid rounded-circle">
                            </div>
                            <div class="name">
                                <h3 class="m-0 p-0">{{ $largePost->user->full_name ?? 'Anonim Yazar' }}</h3>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="col-lg-8">
                    <div class="row g-5">

                        <div class="col-lg-4 border-start custom-border">
                            @foreach($posts->skip(1)->take(3) as $post)
                            <div class="post-entry">
                                <a href="{{ route('post.detail', $post->id ?? 1) }}">
                                    <img src="{{ ($post->image && file_exists(public_path('storage/' . $post->image))) ? asset('storage/' . $post->image) : 'https://placehold.co/800x500/f8f9fa/a1a1aa?text=Gorsel+Yok' }}"
                                        alt="{{ $post->title }}" class="img-fluid"
                                        style="height: 150px; width: 100%; object-fit: cover;">
                                </a>
                                <div class="post-meta">
                                    <span class="date">{{ $post->category->name ?? 'Genel' }}</span>
                                    <span class="mx-1">•</span>
                                    <span>{{ $post->published_at ? $post->published_at->format('d M Y') :
                                        $post->created_at->format('d M Y') }}</span>
                                    <span class="mx-1">•</span>
                                    <span><i class="bi bi-eye-fill"></i> {{ $post->view_count }}</span>
                                    <span class="mx-1"></span>
                                    <span><i class="bi bi-heart-fill"></i> {{ $post->like_count }}</span>
                                </div>
                                <h2><a href="{{ route('post.detail', $post->id ?? 1) }}">{{ $post->title }}</a></h2>
                            </div>
                            @endforeach
                        </div>

                        <div class="col-lg-4 border-start custom-border">
                            @foreach($posts->skip(4)->take(3) as $post)
                            <div class="post-entry">
                                <a href="{{ route('post.detail', $post->id ?? 1) }}">
                                    <img src="{{ ($post->image && file_exists(public_path('storage/' . $post->image))) ? asset('storage/' . $post->image) : 'https://placehold.co/800x500/f8f9fa/a1a1aa?text=Gorsel+Yok' }}"
                                        alt="{{ $post->title }}" class="img-fluid"
                                        style="height: 150px; width: 100%; object-fit: cover;">
                                </a>
                                <div class="post-meta">
                                    <span class="date">{{ $post->category->name ?? 'Genel' }}</span>
                                    <span class="mx-1">•</span>
                                    <span>{{ $post->published_at ? $post->published_at->format('d M Y') :
                                        $post->created_at->format('d M Y') }}</span>
                                </div>
                                <h2><a href="{{ route('post.detail', $post->id ?? 1) }}">{{ $post->title }}</a></h2>
                            </div>
                            @endforeach
                        </div>

                        <div class="col-lg-4">
                            <div class="trending">
                                <h3>Trend Olanlar</h3>
                                <ul class="trending-post">
                                    @foreach($trendingPosts as $index => $post)
                                    <li>
                                        <a href="{{ route('post.detail', $post->id ?? 1) }}">
                                            <span class="number">{{ $index + 1 }}</span>
                                            <h3>{{ $post->title }}</h3>
                                            <span class="author">{{ $post->user->full_name ?? 'Anonim Yazar' }}</span>
                                        </a>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>

    </div>

</section>




<section id="culture-category" class="culture-category section">

    <div class="container section-title" data-aos="fade-up">
        <div class="section-title-container d-flex align-items-center justify-content-between">
            <h2>Culture</h2>
            <p><a href="categories.html">See All Culture</a></p>
        </div>
    </div>
    <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row">
            <div class="col-md-9">

                <div class="d-lg-flex post-entry">
                    <a href="blog-details.html" class="me-4 thumbnail mb-4 mb-lg-0 d-inline-block">
                        <img src="{{ asset('frontend/assets/img/post-landscape-6.jpg') }}" alt="" class="img-fluid">
                    </a>
                    <div>
                        <div class="post-meta"><span class="date">Culture</span> <span class="mx-1">•</span>
                            <span>Jul 5th '22</span>
                        </div>
                        <h3><a href="blog-details.html">What is the son of Football Coach John Gruden, Deuce
                                Gruden doing Now?</a></h3>
                        <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Distinctio placeat
                            exercitationem magni voluptates dolore. Tenetur fugiat voluptates quas, nobis error
                            deserunt aliquam temporibus sapiente, laudantium dolorum itaque libero eos deleniti?
                        </p>
                        <div class="d-flex align-items-center author">
                            <div class="photo"><img src="{{ asset('frontend/assets/img/person-2.jpg') }}" alt=""
                                    class="img-fluid">
                            </div>
                            <div class="name">
                                <h3 class="m-0 p-0">Wade Warren</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-4">
                        <div class="post-list border-bottom">
                            <a href="blog-details.html"><img
                                    src="{{ asset('frontend/assets/img/post-landscape-1.jpg') }}" alt=""
                                    class="img-fluid"></a>
                            <div class="post-meta"><span class="date">Culture</span> <span class="mx-1">•</span>
                                <span>Jul 5th '22</span>
                            </div>
                            <h2 class="mb-2"><a href="blog-details.html">11 Work From Home Part-Time Jobs You
                                    Can Do Now</a></h2>
                            <span class="author mb-3 d-block">Jenny Wilson</span>
                            <p class="mb-4 d-block">Lorem ipsum dolor sit, amet consectetur adipisicing elit.
                                Vero temporibus repudiandae, inventore pariatur numquam cumque possimus</p>
                        </div>

                        <div class="post-list">
                            <div class="post-meta"><span class="date">Culture</span> <span class="mx-1">•</span>
                                <span>Jul 5th '22</span>
                            </div>
                            <h2 class="mb-2"><a href="blog-details.html">5 Great Startup Tips for Female
                                    Founders</a></h2>
                            <span class="author mb-3 d-block">Jenny Wilson</span>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div class="post-list">
                            <a href="blog-details.html"><img
                                    src="{{ asset('frontend/assets/img/post-landscape-2.jpg') }}" alt=""
                                    class="img-fluid"></a>
                            <div class="post-meta"><span class="date">Culture</span> <span class="mx-1">•</span>
                                <span>Jul 5th '22</span>
                            </div>
                            <h2 class="mb-2"><a href="blog-details.html">How to Avoid Distraction and Stay
                                    Focused During Video Calls?</a></h2>
                            <span class="author mb-3 d-block">Jenny Wilson</span>
                            <p class="mb-4 d-block">Lorem ipsum dolor sit, amet consectetur adipisicing elit.
                                Vero temporibus repudiandae, inventore pariatur numquam cumque possimus</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="post-list border-bottom">
                    <div class="post-meta"><span class="date">Culture</span> <span class="mx-1">•</span>
                        <span>Jul 5th '22</span>
                    </div>
                    <h2 class="mb-2"><a href="blog-details.html">How to Avoid Distraction and Stay Focused
                            During Video Calls?</a></h2>
                    <span class="author mb-3 d-block">Jenny Wilson</span>
                </div>

                <div class="post-list border-bottom">
                    <div class="post-meta"><span class="date">Culture</span> <span class="mx-1">•</span>
                        <span>Jul 5th '22</span>
                    </div>
                    <h2 class="mb-2"><a href="blog-details.html">17 Pictures of Medium Length Hair in Layers
                            That Will Inspire Your New Haircut</a></h2>
                    <span class="author mb-3 d-block">Jenny Wilson</span>
                </div>

                <div class="post-list border-bottom">
                    <div class="post-meta"><span class="date">Culture</span> <span class="mx-1">•</span>
                        <span>Jul 5th '22</span>
                    </div>
                    <h2 class="mb-2"><a href="blog-details.html">9 Half-up/half-down Hairstyles for Long and
                            Medium Hair</a></h2>
                    <span class="author mb-3 d-block">Jenny Wilson</span>
                </div>

                <div class="post-list border-bottom">
                    <div class="post-meta"><span class="date">Culture</span> <span class="mx-1">•</span>
                        <span>Jul 5th '22</span>
                    </div>
                    <h2 class="mb-2"><a href="blog-details.html">Life Insurance And Pregnancy: A Working Mom’s
                            Guide</a></h2>
                    <span class="author mb-3 d-block">Jenny Wilson</span>
                </div>

                <div class="post-list border-bottom">
                    <div class="post-meta"><span class="date">Culture</span> <span class="mx-1">•</span>
                        <span>Jul 5th '22</span>
                    </div>
                    <h2 class="mb-2"><a href="blog-details.html">The Best Homemade Masks for Face (keep the
                            Pimples Away)</a></h2>
                    <span class="author mb-3 d-block">Jenny Wilson</span>
                </div>

                <div class="post-list border-bottom">
                    <div class="post-meta"><span class="date">Culture</span> <span class="mx-1">•</span>
                        <span>Jul 5th '22</span>
                    </div>
                    <h2 class="mb-2"><a href="blog-details.html">10 Life-Changing Hacks Every Working Mom Should
                            Know</a></h2>
                    <span class="author mb-3 d-block">Jenny Wilson</span>
                </div>
            </div>
        </div>

    </div>

</section>
<section id="business-category" class="business-category section">

    <div class="container section-title" data-aos="fade-up">
        <div class="section-title-container d-flex align-items-center justify-content-between">
            <h2>Business</h2>
            <p><a href="categories.html">See All Business</a></p>
        </div>
    </div>
    <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row">
            <div class="col-md-9 order-md-2">

                <div class="d-lg-flex post-entry">
                    <a href="blog-details.html" class="me-4 thumbnail d-inline-block mb-4 mb-lg-0">
                        <img src="{{ asset('frontend/assets/img/post-landscape-3.jpg') }}" alt="" class="img-fluid">
                    </a>
                    <div>
                        <div class="post-meta"><span class="date">Business</span> <span class="mx-1">•</span>
                            <span>Jul 5th '22</span>
                        </div>
                        <h3><a href="blog-details.html">What is the son of Football Coach John Gruden, Deuce
                                Gruden doing Now?</a></h3>
                        <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Distinctio placeat
                            exercitationem magni voluptates dolore. Tenetur fugiat voluptates quas, nobis error
                            deserunt aliquam temporibus sapiente, laudantium dolorum itaque libero eos deleniti?
                        </p>
                        <div class="d-flex align-items-center author">
                            <div class="photo"><img src="{{ asset('frontend/assets/img/person-4.jpg') }}" alt=""
                                    class="img-fluid">
                            </div>
                            <div class="name">
                                <h3 class="m-0 p-0">Wade Warren</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-4">
                        <div class="post-list border-bottom">
                            <a href="blog-details.html"><img
                                    src="{{ asset('frontend/assets/img/post-landscape-5.jpg') }}" alt=""
                                    class="img-fluid"></a>
                            <div class="post-meta"><span class="date">Business</span> <span class="mx-1">•</span>
                                <span>Jul 5th '22</span>
                            </div>
                            <h2 class="mb-2"><a href="blog-details.html">11 Work From Home Part-Time Jobs You
                                    Can Do Now</a></h2>
                            <span class="author mb-3 d-block">Jenny Wilson</span>
                            <p class="mb-4 d-block">Lorem ipsum dolor sit, amet consectetur adipisicing elit.
                                Vero temporibus repudiandae, inventore pariatur numquam cumque possimus</p>
                        </div>

                        <div class="post-list">
                            <div class="post-meta"><span class="date">Business</span> <span class="mx-1">•</span>
                                <span>Jul 5th '22</span>
                            </div>
                            <h2 class="mb-2"><a href="blog-details.html">5 Great Startup Tips for Female
                                    Founders</a></h2>
                            <span class="author mb-3 d-block">Jenny Wilson</span>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div class="post-list">
                            <a href="blog-details.html"><img
                                    src="{{ asset('frontend/assets/img/post-landscape-7.jpg') }}" alt=""
                                    class="img-fluid"></a>
                            <div class="post-meta"><span class="date">Business</span> <span class="mx-1">•</span>
                                <span>Jul 5th '22</span>
                            </div>
                            <h2 class="mb-2"><a href="blog-details.html">How to Avoid Distraction and Stay
                                    Focused During Video Calls?</a></h2>
                            <span class="author mb-3 d-block">Jenny Wilson</span>
                            <p class="mb-4 d-block">Lorem ipsum dolor sit, amet consectetur adipisicing elit.
                                Vero temporibus repudiandae, inventore pariatur numquam cumque possimus</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="post-list border-bottom">
                    <div class="post-meta"><span class="date">Business</span> <span class="mx-1">•</span>
                        <span>Jul 5th '22</span>
                    </div>
                    <h2 class="mb-2"><a href="blog-details.html">How to Avoid Distraction and Stay Focused
                            During Video Calls?</a></h2>
                    <span class="author mb-3 d-block">Jenny Wilson</span>
                </div>

                <div class="post-list border-bottom">
                    <div class="post-meta"><span class="date">Business</span> <span class="mx-1">•</span>
                        <span>Jul 5th '22</span>
                    </div>
                    <h2 class="mb-2"><a href="blog-details.html">17 Pictures of Medium Length Hair in Layers
                            That Will Inspire Your New Haircut</a></h2>
                    <span class="author mb-3 d-block">Jenny Wilson</span>
                </div>

                <div class="post-list border-bottom">
                    <div class="post-meta"><span class="date">Business</span> <span class="mx-1">•</span>
                        <span>Jul 5th '22</span>
                    </div>
                    <h2 class="mb-2"><a href="blog-details.html">9 Half-up/half-down Hairstyles for Long and
                            Medium Hair</a></h2>
                    <span class="author mb-3 d-block">Jenny Wilson</span>
                </div>

                <div class="post-list border-bottom">
                    <div class="post-meta"><span class="date">Business</span> <span class="mx-1">•</span>
                        <span>Jul 5th '22</span>
                    </div>
                    <h2 class="mb-2"><a href="blog-details.html">Life Insurance And Pregnancy: A Working Mom’s
                            Guide</a></h2>
                    <span class="author mb-3 d-block">Jenny Wilson</span>
                </div>

                <div class="post-list border-bottom">
                    <div class="post-meta"><span class="date">Business</span> <span class="mx-1">•</span>
                        <span>Jul 5th '22</span>
                    </div>
                    <h2 class="mb-2"><a href="blog-details.html">The Best Homemade Masks for Face (keep the
                            Pimples Away)</a></h2>
                    <span class="author mb-3 d-block">Jenny Wilson</span>
                </div>

                <div class="post-list border-bottom">
                    <div class="post-meta"><span class="date">Business</span> <span class="mx-1">•</span>
                        <span>Jul 5th '22</span>
                    </div>
                    <h2 class="mb-2"><a href="blog-details.html">10 Life-Changing Hacks Every Working Mom Should
                            Know</a></h2>
                    <span class="author mb-3 d-block">Jenny Wilson</span>
                </div>
            </div>
        </div>

    </div>

</section>
<section id="lifestyle-category" class="lifestyle-category section">

    <div class="container section-title" data-aos="fade-up">
        <div class="section-title-container d-flex align-items-center justify-content-between">
            <h2>Lifestyle</h2>
            <p><a href="categories.html">See All Lifestyle</a></p>
        </div>
    </div>
    <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row g-5">
            <div class="col-lg-4">
                <div class="post-list lg">
                    <a href="blog-details.html"><img src="{{ asset('frontend/assets/img/post-landscape-8.jpg') }}"
                            alt="" class="img-fluid"></a>
                    <div class="post-meta"><span class="date">Lifestyle</span> <span class="mx-1">•</span>
                        <span>Jul 5th '22</span>
                    </div>
                    <h2><a href="blog-details.html">11 Work From Home Part-Time Jobs You Can Do Now</a></h2>
                    <p class="mb-4 d-block">Lorem ipsum dolor sit, amet consectetur adipisicing elit. Vero
                        temporibus repudiandae, inventore pariatur numquam cumque possimus exercitationem? Nihil
                        tempore odit ab minus eveniet praesentium, similique blanditiis molestiae ut saepe
                        perspiciatis officia nemo, eos quae cumque. Accusamus fugiat architecto rerum animi
                        atque eveniet, quo, praesentium dignissimos</p>

                    <div class="d-flex align-items-center author">
                        <div class="photo"><img src="{{ asset('frontend/assets/img/person-7.jpg') }}" alt=""
                                class="img-fluid"></div>
                        <div class="name">
                            <h3 class="m-0 p-0">Esther Howard</h3>
                        </div>
                    </div>
                </div>

                <div class="post-list border-bottom">
                    <div class="post-meta"><span class="date">Lifestyle</span> <span class="mx-1">•</span>
                        <span>Jul 5th '22</span>
                    </div>
                    <h2 class="mb-2"><a href="blog-details.html">The Best Homemade Masks for Face (keep the
                            Pimples Away)</a></h2>
                    <span class="author mb-3 d-block">Jenny Wilson</span>
                </div>

                <div class="post-list">
                    <div class="post-meta"><span class="date">Lifestyle</span> <span class="mx-1">•</span>
                        <span>Jul 5th '22</span>
                    </div>
                    <h2 class="mb-2"><a href="blog-details.html">10 Life-Changing Hacks Every Working Mom Should
                            Know</a></h2>
                    <span class="author mb-3 d-block">Jenny Wilson</span>
                </div>

            </div>

            <div class="col-lg-8">
                <div class="row g-5">
                    <div class="col-lg-4 border-start custom-border">
                        <div class="post-list">
                            <a href="blog-details.html"><img
                                    src="{{ asset('frontend/assets/img/post-landscape-6.jpg') }}" alt=""
                                    class="img-fluid"></a>
                            <div class="post-meta"><span class="date">Lifestyle</span> <span class="mx-1">•</span>
                                <span>Jul 5th '22</span>
                            </div>
                            <h2><a href="blog-details.html">Let’s Get Back to Work, New York</a></h2>
                        </div>
                        <div class="post-list">
                            <a href="blog-details.html"><img
                                    src="{{ asset('frontend/assets/img/post-landscape-5.jpg') }}" alt=""
                                    class="img-fluid"></a>
                            <div class="post-meta"><span class="date">Lifestyle</span> <span class="mx-1">•</span>
                                <span>Jul 17th '22</span>
                            </div>
                            <h2><a href="blog-details.html">How to Avoid Distraction and Stay Focused During
                                    Video Calls?</a></h2>
                        </div>
                        <div class="post-list">
                            <a href="blog-details.html"><img
                                    src="{{ asset('frontend/assets/img/post-landscape-4.jpg') }}" alt=""
                                    class="img-fluid"></a>
                            <div class="post-meta"><span class="date">Lifestyle</span> <span class="mx-1">•</span>
                                <span>Mar 15th '22</span>
                            </div>
                            <h2><a href="blog-details.html">Why Craigslist Tampa Is One of The Most Interesting
                                    Places On the Web?</a></h2>
                        </div>
                    </div>
                    <div class="col-lg-4 border-start custom-border">
                        <div class="post-list">
                            <a href="blog-details.html"><img
                                    src="{{ asset('frontend/assets/img/post-landscape-3.jpg') }}" alt=""
                                    class="img-fluid"></a>
                            <div class="post-meta"><span class="date">Lifestyle</span> <span class="mx-1">•</span>
                                <span>Jul 5th '22</span>
                            </div>
                            <h2><a href="blog-details.html">6 Easy Steps To Create Your Own Cute Merch For
                                    Instagram</a></h2>
                        </div>
                        <div class="post-list">
                            <a href="blog-details.html"><img
                                    src="{{ asset('frontend/assets/img/post-landscape-2.jpg') }}" alt=""
                                    class="img-fluid"></a>
                            <div class="post-meta"><span class="date">Lifestyle</span> <span class="mx-1">•</span>
                                <span>Mar 1st '22</span>
                            </div>
                            <h2><a href="blog-details.html">10 Life-Changing Hacks Every Working Mom Should
                                    Know</a></h2>
                        </div>
                        <div class="post-list">
                            <a href="blog-details.html"><img
                                    src="{{ asset('frontend/assets/img/post-landscape-1.jpg') }}" alt=""
                                    class="img-fluid"></a>
                            <div class="post-meta"><span class="date">Lifestyle</span> <span class="mx-1">•</span>
                                <span>Jul 5th '22</span>
                            </div>
                            <h2><a href="blog-details.html">5 Great Startup Tips for Female Founders</a></h2>
                        </div>
                    </div>
                    <div class="col-lg-4">

                        <div class="post-list border-bottom">
                            <div class="post-meta"><span class="date">Lifestyle</span> <span class="mx-1">•</span>
                                <span>Jul 5th '22</span>
                            </div>
                            <h2 class="mb-2"><a href="blog-details.html">How to Avoid Distraction and Stay
                                    Focused During Video Calls?</a></h2>
                            <span class="author mb-3 d-block">Jenny Wilson</span>
                        </div>

                        <div class="post-list border-bottom">
                            <div class="post-meta"><span class="date">Lifestyle</span> <span class="mx-1">•</span>
                                <span>Jul 5th '22</span>
                            </div>
                            <h2 class="mb-2"><a href="blog-details.html">17 Pictures of Medium Length Hair in
                                    Layers That Will Inspire Your New Haircut</a></h2>
                            <span class="author mb-3 d-block">Jenny Wilson</span>
                        </div>

                        <div class="post-list border-bottom">
                            <div class="post-meta"><span class="date">Lifestyle</span> <span class="mx-1">•</span>
                                <span>Jul 5th '22</span>
                            </div>
                            <h2 class="mb-2"><a href="blog-details.html">9 Half-up/half-down Hairstyles for Long
                                    and Medium Hair</a></h2>
                            <span class="author mb-3 d-block">Jenny Wilson</span>
                        </div>

                        <div class="post-list border-bottom">
                            <div class="post-meta"><span class="date">Lifestyle</span> <span class="mx-1">•</span>
                                <span>Jul 5th '22</span>
                            </div>
                            <h2 class="mb-2"><a href="blog-details.html">Life Insurance And Pregnancy: A Working
                                    Mom’s Guide</a></h2>
                            <span class="author mb-3 d-block">Jenny Wilson</span>
                        </div>

                        <div class="post-list border-bottom">
                            <div class="post-meta"><span class="date">Lifestyle</span> <span class="mx-1">•</span>
                                <span>Jul 5th '22</span>
                            </div>
                            <h2 class="mb-2"><a href="blog-details.html">The Best Homemade Masks for Face (keep
                                    the Pimples Away)</a></h2>
                            <span class="author mb-3 d-block">Jenny Wilson</span>
                        </div>

                        <div class="post-list border-bottom">
                            <div class="post-meta"><span class="date">Lifestyle</span> <span class="mx-1">•</span>
                                <span>Jul 5th '22</span>
                            </div>
                            <h2 class="mb-2"><a href="blog-details.html">10 Life-Changing Hacks Every Working
                                    Mom Should Know</a></h2>
                            <span class="author mb-3 d-block">Jenny Wilson</span>
                        </div>

                    </div>
                </div>
            </div>

        </div>

    </div>

</section>@endsection
