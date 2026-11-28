<?php use App\Core\View; ?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= View::escape($title ?? 'Quản trị') ?> · Viet Cinema</title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    <a class="skip-link" href="#main">Đến nội dung chính</a>
    <?= View::render('partials/header') ?>
    <div class="container admin-shell">
        <aside class="panel"><p class="eyebrow">Viet Cinema Studio</p><h2>Quản trị</h2>
            <p>Giao diện minh họa</p><a href="/">Về trang chủ ↗</a></aside>
        <main id="main"><?= $content ?></main>
    </div>
    <?= View::render('partials/footer') ?>
</body>
</html>
