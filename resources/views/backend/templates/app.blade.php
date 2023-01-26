<!DOCTYPE html>
<html lang="es">
	<!-- begin::Head -->
	<head>
		<meta charset="utf-8" />
		<title>{{ env('APP_NAME') }}</title>
		<meta name="csrf-token" content="{{ csrf_token() }}">
		<meta name="description" content="">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0, shrink-to-fit=no">

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
		<link href="{{ asset('backend/css/main.css') }}" rel="stylesheet" type="text/css" />
		<link href="{{ asset('backend/css/custom.css') }}" rel="stylesheet" type="text/css" />
		<link rel="stylesheet" href="//cdn.materialdesignicons.com/3.4.93/css/materialdesignicons.min.css">
		<!--end::Global Theme Styles -->

		<link rel="icon" href="{{ asset('backend/img/favicon.png') }}">

		<link href="{{ asset('backend/css/app.css') }}" rel="stylesheet" type="text/css" />
	</head>
	<!-- end::Head -->

	<!-- begin::Body -->
	<body class="kt-header--fixed kt-header-mobile--fixed kt-subheader--fixed kt-subheader--enabled kt-subheader--solid kt-aside--enabled kt-aside--fixed kt-page--loading @yield('body-class')">
		<main id="app">
			<!-- begin:: Page -->
			<!-- begin:: Header Mobile -->
			<div id="kt_header_mobile" class="kt-header-mobile  kt-header-mobile--fixed ">
				<div class="kt-header-mobile__logo">
					<a href="{{ route('dashboard.voucher.send_ose') }}">
						<img alt="Logo" src="{{ asset('backend/img/logo-dashboard.png') }}" style="width: 13em;" />
					</a>
				</div>
				<div class="kt-header-mobile__toolbar">
					<button class="kt-header-mobile__toggler kt-header-mobile__toggler--left" id="kt_aside_mobile_toggler"><span></span></button>
					<button class="kt-header-mobile__topbar-toggler" id="kt_header_mobile_topbar_toggler"><i class="flaticon-more"></i></button>
				</div>
			</div>

			<!-- end:: Header Mobile -->
			<div class="kt-grid kt-grid--hor kt-grid--root">
				<div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--ver kt-page">

					@include('backend.includes.aside')

					<div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-wrapper" id="kt_wrapper">
						@include('backend.includes.header')

						<div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor">
							@include('backend.includes.title')

							<!-- begin:: Content -->
							<div class="kt-content  kt-grid__item kt-grid__item--fluid" id="kt_content">
								@yield('content')
							</div>
							<!-- end:: Content -->
						</div>

						@include('backend.includes.footer')
					</div>
				</div>
			</div>
			<!-- end:: Page -->

			<!-- begin::Scrolltop -->
			<div id="kt_scrolltop" class="kt-scrolltop">
				<i class="fa fa-arrow-up"></i>
			</div>
			<!-- end::Scrolltop -->
		</main>

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

		<!--begin::Global Theme Bundle -->
		<script src="{{ asset('backend/js/main.js?'.time()) }}" type="text/javascript"></script>
		<script src="{{ asset('backend/js/app.js?'.time()) }}" type="text/javascript"></script>
		<!--end::Global Theme Bundle -->
	</body>
	<!-- end::Body -->
</html>