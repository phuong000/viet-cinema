<?php
use App\Core\Csrf;
use App\Core\View;
?>
<section class="form-shell"><div><p class="eyebrow">Viet Cinema / Thành viên</p><h1><?= View::escape($title) ?></h1>
    <p class="lead">Một tài khoản. Nhiều trải nghiệm điện ảnh.</p>
    <p class="muted">Biểu mẫu minh họa. Chưa đăng nhập hoặc tạo tài khoản. Vui lòng dùng thông tin thử nghiệm.</p></div>
    <form class="panel" method="post" action="/<?= View::escape($mode) ?>">
        <input type="hidden" name="_token" value="<?= View::escape(Csrf::token()) ?>">
        <label for="email">Email</label><input id="email" name="email" type="email" required maxlength="254" autocomplete="email" placeholder="ban@example.com">
        <label for="password">Mật khẩu</label><div class="password-field">
            <input id="password" name="password" type="password" required minlength="8" maxlength="128" autocomplete="<?= $mode === 'register' ? 'new-password' : 'current-password' ?>">
            <button class="password-toggle" type="button" data-password-toggle aria-controls="password" aria-pressed="false">Hiện</button></div>
        <button class="button" type="submit">Kiểm tra biểu mẫu</button>
        <p><?= $mode === 'login' ? 'Chưa có tài khoản?' : 'Đã có tài khoản?' ?>
            <a class="text-link" href="/<?= $mode === 'login' ? 'register' : 'login' ?>"><?= $mode === 'login' ? 'Đăng ký' : 'Đăng nhập' ?></a></p>
    </form>
</section>
