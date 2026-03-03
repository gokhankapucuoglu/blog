<header id="header" class="header d-flex align-items-center sticky-top">
    <div class="container position-relative d-flex align-items-center justify-content-between">

        <a href="index.html" class="logo d-flex align-items-center me-auto me-xl-0">
            <!-- Uncomment the line below if you also wish to use an image logo -->
            <!-- <img src="assets/img/logo.png" alt=""> -->
            <h1 class="sitename">Blog</h1>
        </a>

        <nav id="navmenu" class="navmenu">
            <ul>
                <li><a href="index.html" class="active">Anasayfa</a></li>
                <li><a href="about.html">Hakkımda</a></li>
                <li><a href="single-post.html">Single Post</a></li>
                <li class="dropdown">
                    <a href="#"><span>Kategoriler</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
                    <ul>
                        @foreach($globalCategories as $category)

                        @if($category->children->count() > 0)
                        <li class="dropdown">
                            <a href="#"><span>{{ $category->name }}</span> <i
                                    class="bi bi-chevron-down toggle-dropdown"></i></a>
                            <ul>
                                @foreach($category->children as $child)
                                <li><a href="#">{{ $child->name }}</a></li>
                                @endforeach
                            </ul>
                        </li>

                        @else
                        <li><a href="#">{{ $category->name }}</a></li>
                        @endif

                        @endforeach
                    </ul>
                </li>
                <li><a href="contact.html">İletişim</a></li>
            </ul>
            <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
        </nav>

        <div class="header-social-links">
            <a href="#" class="twitter"><i class="bi bi-twitter-x"></i></a>
            <a href="#" class="facebook"><i class="bi bi-facebook"></i></a>
            <a href="#" class="instagram"><i class="bi bi-instagram"></i></a>
            <a href="#" class="linkedin"><i class="bi bi-linkedin"></i></a>
        </div>

    </div>
</header>
