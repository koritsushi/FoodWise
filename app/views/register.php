<div class="container content-wrap container-fluid p-5 d-flex flex-column align-items-center">
	<!-- Toast Error Message (only shows when register fails) -->
    <?php if (!empty($errors)): ?>
        <div class="toast align-items-center text-white bg-danger border-0 position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999;" 
             role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
				<?php foreach ($errors as $error): ?>
					<div class="toast-body">
						<?= htmlspecialchars($error) ?>
					</div>
					<button type="button" class="btn-close btn-close-white me-2 m-auto" 
							data-bs-dismiss="toast" aria-label="Close"></button>
				<?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
	<form method="POST" class="form-control mt-5 p-4"
		style="height:auto; width:380px;
		box-shadow: rgba(60, 64, 67, 0.3) 0px 1px 2px 0px,
		rgba(60, 64, 67, 0.15) 0px 2px 6px 2px;">
		<div class="row text-center">
			<i class="fa fa-user-circle-o fa-3x mt-1 mb-2" style="color: light blue;"></i>
			<h5 class="p-4" style="font-weight: 700;">Create Your Account</h5>
		</div>
		<div class="mb-2">
			<label for="name"><i class="fa fa-user"></i> Name</label>
			<input type="text" name="name" id="name" class="form-control" required>
		</div>
		<div class="mb-2 mt-2">
			<label for="email"><i class="fa fa-envelope"></i> Email</label>
			<input type="text" name="email" id="email" class="form-control" required>
		</div>
		<div class="mb-2 mt-2">
			<label for="phoneNumber"><i class="fa fa-phone"></i> Phone number</label>
			<input type="tel" name="phoneNumber" id="phoneNumber" pattern="[0-9]{10}" class="form-control" required>
		</div>
		<div class="mb-2 mt-2">
			<label for="address"><i class="fa fa-address-book"></i> Address</label>
			<input type="text" name="address" id="address" class="form-control" required>
		</div>
		<div class="mb-2 mt-2">
			<label for="password"><i class="fa fa-lock"></i> Password</label>
			<input type="password" name="password" id="password" class="form-control" required>
		</div>
		<div class="mb-2 mt-3">
			<button type="submit" class="btn btn-success bg-success" style="font-weight: 600;">
				Create Account</button>
		</div>
		<div class="mb-2 mt-4">
			<p class="text-center" style="font-weight: 600; color: navy;">I have an Account 
				<a href="/login" style="text-decoration: none;">
					Login
				</a>
			</p>
		</div>
	</form>
</div>
<script>
	let toastElList = [].slice.call(document.querySelectorAll('.toast'))
	let toastList = toastElList.map(function (toastEl) {
		return new bootstrap.Toast(toastEl, { delay: 3000 });
	});
	toastList.forEach(toast => toast.show());
</script>