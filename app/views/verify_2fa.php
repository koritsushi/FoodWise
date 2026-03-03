<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-body p-4">
                    <h3 class="text-center text-success mb-3">Two-Factor Authentication</h3>
                    <p class="text-center text-muted">We sent a 6-digit code to your email. It expires in 10 minutes.</p>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="POST" action="/verify-2fa">
                        <div class="mb-3">
                            <label class="form-label">Verification Code</label>
                            <input 
                                type="text" 
                                name="code" 
                                class="form-control form-control-lg text-center" 
                                maxlength="6" 
                                placeholder="000000"
                                autocomplete="one-time-code"
                                autofocus
                                required
                            >
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg">Verify</button>
                        </div>
                    </form>

					<!-- Resend button with cooldown -->
                    <div class="d-grid mb-3">
                        <button id="resendBtn" class="btn btn-outline-secondary" onclick="resendCode()">
                            Resend Code (<span id="countdown">30</span>s)
                        </button>
                    </div>

                    <div class="text-center mt-3">
                        <a href="/login" class="text-muted small">← Back to login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // 30-second cooldown to prevent spam
    let seconds = 30;
    const btn = document.getElementById('resendBtn');
    const countdown = document.getElementById('countdown');

    btn.disabled = true;

    const timer = setInterval(() => {
        seconds--;
        countdown.textContent = seconds;
        if (seconds <= 0) {
            clearInterval(timer);
            btn.disabled = false;
            btn.textContent = 'Resend Code';
        }
    }, 1000);

    function resendCode() {
        window.location.href = '/resend-2fa';
    }
</script>