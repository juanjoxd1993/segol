<!-- begin:: Header -->
<div id="kt_header" class="kt-header kt-grid__item  kt-header--fixed ">

	<!-- begin:: Header Menu -->
	<button class="kt-header-menu-wrapper-close" id="kt_header_menu_mobile_close_btn"><i class="la la-close"></i></button>
	<div class="kt-header-menu-wrapper" id="kt_header_menu_wrapper">

	</div>
	<!-- end:: Header Menu -->

	<!-- begin:: Header Topbar -->
	<div class="kt-header__topbar">
		<!--begin: User Bar -->
		<div class="kt-header__topbar-item kt-header__topbar-item--user">
			<div class="kt-header__topbar-wrapper" data-toggle="dropdown" data-offset="0px,0px">
				<div class="kt-header__topbar-user">
					<span class="kt-header__topbar-welcome">Hola,</span>
					<span class="kt-header__topbar-username"><?php echo e(Auth::user()->name); ?></span>
					<img class="kt-hidden" alt="Pic" src="<?php echo e(asset('backend/media/users/300_25.jpg')); ?>" />

					<!--use below badge element instead the user avatar to display username's first letter(remove kt-hidden class to display it) -->
					<span class="kt-badge kt-badge--username kt-badge--unified-success kt-badge--lg kt-badge--rounded kt-badge--bold">
						<?php
							echo substr(Auth::user()->name, 0, 1)
						?>
					</span>
				</div>
			</div>
			<div class="dropdown-menu dropdown-menu-fit dropdown-menu-right dropdown-menu-anim dropdown-menu-top-unround dropdown-menu-xl">
				<!--begin: Head -->
				<div class="kt-user-card kt-user-card--skin-dark kt-notification-item-padding-x" style="background-image: url(<?php echo e(asset('backend/media/misc/bg-1.jpg')); ?>)">
					<div class="kt-user-card__avatar">
						<img class="kt-hidden" alt="Pic" src="<?php echo e(asset('backend/media/users/300_25.jpg')); ?>" />

						<!--use below badge element instead the user avatar to display username's first letter(remove kt-hidden class to display it) -->
						<span class="kt-badge kt-badge--lg kt-badge--rounded kt-badge--bold kt-font-success">
							<?php
								echo substr(Auth::user()->name, 0, 1)
							?>
						</span>
					</div>
					<div class="kt-user-card__name">
						<?php echo e(Auth::user()->name); ?>

					</div>
				</div>
				<!--end: Head -->

				<!--begin: Navigation -->
				<div class="kt-notification">
					<div class="kt-notification__custom">
						<a href="<?php echo e(route('logout')); ?>" class="btn btn-label-brand btn-sm btn-bold">Cerrar sesión</a>
					</div>
				</div>
				<!--end: Navigation -->
			</div>
		</div>
		<!--end: User Bar -->
	</div>
	<!-- end:: Header Topbar -->
</div>
<!-- end:: Header --><?php /**PATH /var/www/pdd/resources/views/backend/includes/header.blade.php ENDPATH**/ ?>