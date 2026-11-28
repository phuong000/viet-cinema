<?php use App\Core\View; ?>
<section class="page-heading"><p class="eyebrow">Phim minh họa / Phiêu lưu</p><h1><?= View::escape($title) ?></h1></section>
<div class="detail-grid"><?= View::render('partials/movie-card') ?>
    <section class="panel"><span class="tag">120 PHÚT · PHIM MẪU</span><h2>Đi về phía ánh sáng.</h2>
        <p>Một hành trình giả tưởng về tình bạn, lòng can đảm và những điều chưa biết phía chân trời.</p>
        <p class="muted">Nội dung hư cấu để minh họa giao diện. Không phải lịch phát hành thật.</p>
        <a class="button" href="/showtimes">Xem lịch chiếu ↗</a></section></div>
