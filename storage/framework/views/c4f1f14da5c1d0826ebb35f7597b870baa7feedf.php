<!DOCTYPE html>
<html lang="es">
	<!-- begin::Head -->
	<head>
		<meta charset="utf-8" />
		<title><?php echo e(env('APP_NAME')); ?></title>
		<meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
		<meta name="description" content="">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">

		<!--begin::Web font -->
		<script src="https://ajax.googleapis.com/ajax/libs/webfont/1.6.16/webfont.js"></script>
		<script>
			WebFont.load({
            google: {"families":["Poppins:300,400,500,600,700","Roboto:300,400,500,600,700"]},
            active: function() {
                sessionStorage.fonts = true;
            }
          });
        </script>
		<!--end::Web font -->

		<!--begin::Global Theme Styles -->
		<link href="<?php echo e(asset('backend/css/main.css')); ?>" rel="stylesheet" type="text/css" />
		<!--end::Global Theme Styles -->
		<link rel="icon" href="<?php echo e(asset('backend/img/favicon.png')); ?>">
	</head>
	<!-- end::Head -->

	<!-- begin::Body -->
	<body class="kt-header--fixed kt-header-mobile--fixed kt-subheader--fixed kt-subheader--enabled kt-subheader--solid kt-aside--enabled kt-aside--fixed kt-page--loading">

		<!-- begin:: Page -->
		<div class="kt-grid kt-grid--ver kt-grid--root" id="app">
			<div class="kt-grid kt-grid--hor kt-grid--root  kt-login kt-login--v6 kt-login--signin" id="kt_login">
				<div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--desktop kt-grid--ver-desktop kt-grid--hor-tablet-and-mobile">
					<div class="kt-grid__item  kt-grid__item--order-tablet-and-mobile-2  kt-grid kt-grid--hor kt-login__aside">
						<div class="kt-login__wrapper">
							<div class="kt-login__container">
								<div class="kt-login__body">
									<div class="kt-login__logo">
										<a href="#">
											<img src="<?php echo e(asset('backend/img/logo-login-2.png')); ?>">
										</a>
									</div>
									<div class="kt-login__signin">
										<div class="kt-login__head">
											<h3 class="kt-login__title">Iniciar sesión</h3>
										</div>
										<div class="kt-login__form">
											<login
												:url="'<?php echo e(route('login.auth')); ?>'"
												>
											</login>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="kt-grid__item kt-grid__item--fluid kt-grid__item--center kt-grid kt-grid--ver kt-login__content" style="background-image: url(<?php echo e(asset('backend/img/bg-login.jpg')); ?>);">
						<div class="kt-login__section">
							<div class="kt-login__block">
								
								
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- end:: Page -->
	</body>
	<!-- end::Body -->

	<!--begin::Global Theme Bundle -->
	<script src="<?php echo e(asset('backend/js/main.js?'.time())); ?>" type="text/javascript"></script>
	<script src="<?php echo e(asset('backend/js/app.js?'.time())); ?>" type="text/javascript"></script>
	<!--end::Global Theme Bundle -->
	<!-- begin::Global Config(global config for global JS sciprts) -->
	<script>
		var KTAppOptions = {
			"colors": {
				"state": {
					"brand": "#5d78ff",
					"dark": "#282a3c",
					"light": "#ffffff",
					"primary": "#5867dd",
					"success": "#34bfa3",
					"info": "#36a3f7",
					"warning": "#ffb822",
					"danger": "#fd3995"
				},
				"base": {
					"label": ["#c5cbe3", "#a1a8c3", "#3d4465", "#3e4466"],
					"shape": ["#f0f3ff", "#d9dffa", "#afb4d4", "#646c9a"]
				}
			}
		};
	</script>
	<!-- end::Global Config -->
</html><?php /**PATH /var/www/pdd/resources/views/backend/login.blade.php ENDPATH**/ ?>