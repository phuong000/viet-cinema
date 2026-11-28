<?php use App\Core\View; ?>
<section class="hero">
    <div class="hero-copy"><p class="eyebrow">Viet Cinema / Trải nghiệm điện ảnh</p>
        <h1>Màn ảnh lớn.<br><em>Cảm xúc thật.</em></h1>
        <p class="lead">Chào mừng đến với <?= View::escape($title) ?>. Khám phá những câu chuyện mới và dành thời gian cho điều bạn yêu thích.</p>
        <div class="actions"><a class="button" href="/movies">Khám phá phim <span>↗</span></a>
            <a class="button button-outline" href="/showtimes">Xem lịch chiếu</a></div>
        <p class="muted">Giao diện trải nghiệm mẫu · Chưa mở bán vé</p>
    </div>
    <div class="hero-art" aria-hidden="true"><div class="light-beam"></div>
        <div class="cinema-screen"><span>VIET CINEMA</span><b>THE NEXT<br>STORY IS YOURS.</b><small>ĐIỆN ẢNH KẾT NỐI CHÚNG TA</small></div>
        <div class="seat-row"></div><div class="seat-row second-row"></div>
    </div>
</section>
<section class="section"><div class="section-heading"><div><p class="eyebrow">Đón chờ câu chuyện tiếp theo</p><h2>Tiêu điểm màn ảnh</h2></div>
    <a class="text-link" href="/movies">Tất cả phim ↗</a></div>
    <div class="movie-grid"><?= View::render('partials/movie-card') ?>
        <div class="panel editorial"><span class="tag">SẮP CÓ TẠI VIET CINEMA</span><h2>Một chỗ ngồi.<br>Vô vàn thế giới.</h2>
            <p>Lịch chiếu, lựa chọn ghế và đặt vé sẽ có mặt trong phiên bản tiếp theo.</p><a class="text-link" href="/showtimes">Khám phá lịch chiếu ↗</a></div>
    </div>
</section>
