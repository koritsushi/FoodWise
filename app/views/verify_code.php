<div class="container mt-5 col-md-6">
    <h3>Verify your email</h3>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label>Email</label>
            <input name="email" type="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Verification Code</label>
            <input name="code" type="text" class="form-control" maxlength="6" required>
        </div>
        <button class="btn btn-primary">Verify</button>
    </form>
</div>