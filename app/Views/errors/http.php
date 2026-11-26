<?php use App\Core\View; ?>
<section class="empty-state error-page"><p class="eyebrow">Viet Cinema / Có sự cố</p>
    <p class="error-code"><?= View::escape((string) $status) ?></p><h1><?= View::escape($title) ?></h1>
    <p>Yêu cầu chưa thể thực hiện. Bạn có thể quay lại trang chủ hoặc thử lại sau.</p>
    <a class="button" href="/">Về trang chủ ↗</a></section>
