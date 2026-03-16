<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>@yield('title', 'Dashboard') | SIMTI RSUDZM</title>

	<link rel="shortcut icon" href="{{ asset('adminkit/img/icons/favicon.ico') }}" type="image/x-icon">
	<link rel="icon" href="{{ asset('adminkit/img/icons/favicon.ico') }}" type="image/x-icon">
	
	<link href="{{ asset('adminkit/css/app.css') }}" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
	@stack('styles')
</head>
    
<body>
	<div class="wrapper">
		@include('layouts.partials.sidebar')

		<div class="main">
			@include('layouts.partials.navbar')

			<main class="content">
				<div class="container-fluid p-0">
					@yield('content')
				</div>
			</main>

			@include('layouts.partials.footer')
		</div>
	</div>

	<script src="{{ asset('adminkit/js/app.js') }}"></script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	@stack('scripts')
	<script>
		document.addEventListener('click', function (event) {
			const btn = event.target.closest('[data-toggle="password"]');
			if (!btn) return;
			event.preventDefault();
			const selector = btn.getAttribute('data-target');
			const input = selector
				? document.querySelector(selector)
				: btn.closest('.input-group')?.querySelector('input');
			if (!input) return;
			const isPassword = input.type === 'password';
			input.type = isPassword ? 'text' : 'password';
			btn.setAttribute('aria-pressed', isPassword ? 'true' : 'false');
			btn.innerHTML = `<i data-feather="${isPassword ? 'eye' : 'eye-off'}"></i>`;
			if (typeof feather !== 'undefined') {
				feather.replace();
			}
		});
	</script>
    
</body>
</html>
