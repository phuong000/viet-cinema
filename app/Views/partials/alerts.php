<?php
use App\Core\Session;
use App\Core\View;

$flash = session_status() === PHP_SESSION_ACTIVE ? Session::pullFlash() : null;
?>
<?php if ($errors !== []) : ?>
    <div class="alert alert-error" role="alert"><strong>Vui lòng kiểm tra thông tin.</strong>
        <ul><?php foreach ($errors as $field => $error) : ?>
            <li><?= View::escape($field . ': ' . $error) ?></li>
        <?php endforeach; ?></ul>
    </div>
<?php endif; ?>
<?php if ($message || $flash) : ?>
    <div class="alert <?= ($flash['type'] ?? '') === 'error' ? 'alert-error' : 'alert-success' ?>" role="status">
        <?= View::escape($message ?? $flash['message']) ?>
    </div>
<?php endif; ?>
