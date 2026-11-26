<?php use App\Core\View; ?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="dark">
    <title><?= View::escape($title ?? 'Viet Cinema') ?> · Viet Cinema</title>
    <link rel="stylesheet" href="/assets/css/app.css">
    <script src="/assets/js/app.js" defer></script>
</head>
<body>
    <a class="skip-link" href="#main">Đến nội dung chính</a>
    <?= View::render('partials/header') ?>
    <main id="main" class="container">
        <?= View::render('partials/alerts', ['errors' => $errors ?? [], 'message' => $message ?? null]) ?>
        <?= $content ?>
    </main>
    <?= View::render('partials/footer') ?>
</body>
</html>
