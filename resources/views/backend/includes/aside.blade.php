<!-- begin:: Aside -->
<button class="kt-aside-close " id="kt_aside_close_btn"><i class="la la-close"></i></button>
<div class="kt-aside  kt-aside--fixed  kt-grid__item kt-grid kt-grid--desktop kt-grid--hor-desktop" id="kt_aside">
	<!-- begin:: Aside -->
	<div class="kt-aside__brand kt-grid__item " id="kt_aside_brand">
		<div class="kt-aside__brand-logo">
			<a href="{{ route('dashboard.voucher.send_ose') }}">
				<img alt="Logo" src="{{ asset('backend/img/logo-dashboard-2.png') }}" style="width: 15em;" />
			</a>
		</div>
		<div class="kt-aside__brand-tools">
			<button class="kt-aside__brand-aside-toggler" id="kt_aside_toggler">
				<span><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1" class="kt-svg-icon">
						<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
							<polygon id="Shape" points="0 0 24 0 24 24 0 24" />
							<path d="M5.29288961,6.70710318 C4.90236532,6.31657888 4.90236532,5.68341391 5.29288961,5.29288961 C5.68341391,4.90236532 6.31657888,4.90236532 6.70710318,5.29288961 L12.7071032,11.2928896 C13.0856821,11.6714686 13.0989277,12.281055 12.7371505,12.675721 L7.23715054,18.675721 C6.86395813,19.08284 6.23139076,19.1103429 5.82427177,18.7371505 C5.41715278,18.3639581 5.38964985,17.7313908 5.76284226,17.3242718 L10.6158586,12.0300721 L5.29288961,6.70710318 Z" id="Path-94" fill="#000000" fill-rule="nonzero" transform="translate(8.999997, 11.999999) scale(-1, 1) translate(-8.999997, -11.999999) " />
							<path d="M10.7071009,15.7071068 C10.3165766,16.0976311 9.68341162,16.0976311 9.29288733,15.7071068 C8.90236304,15.3165825 8.90236304,14.6834175 9.29288733,14.2928932 L15.2928873,8.29289322 C15.6714663,7.91431428 16.2810527,7.90106866 16.6757187,8.26284586 L22.6757187,13.7628459 C23.0828377,14.1360383 23.1103407,14.7686056 22.7371482,15.1757246 C22.3639558,15.5828436 21.7313885,15.6103465 21.3242695,15.2371541 L16.0300699,10.3841378 L10.7071009,15.7071068 Z" id="Path-94" fill="#000000" fill-rule="nonzero" opacity="0.3" transform="translate(15.999997, 11.999999) scale(-1, 1) rotate(-270.000000) translate(-15.999997, -11.999999) " />
						</g>
					</svg></span>
				<span><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1" class="kt-svg-icon">
						<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
							<polygon id="Shape" points="0 0 24 0 24 24 0 24" />
							<path d="M12.2928955,6.70710318 C11.9023712,6.31657888 11.9023712,5.68341391 12.2928955,5.29288961 C12.6834198,4.90236532 13.3165848,4.90236532 13.7071091,5.29288961 L19.7071091,11.2928896 C20.085688,11.6714686 20.0989336,12.281055 19.7371564,12.675721 L14.2371564,18.675721 C13.863964,19.08284 13.2313966,19.1103429 12.8242777,18.7371505 C12.4171587,18.3639581 12.3896557,17.7313908 12.7628481,17.3242718 L17.6158645,12.0300721 L12.2928955,6.70710318 Z" id="Path-94" fill="#000000" fill-rule="nonzero" />
							<path d="M3.70710678,15.7071068 C3.31658249,16.0976311 2.68341751,16.0976311 2.29289322,15.7071068 C1.90236893,15.3165825 1.90236893,14.6834175 2.29289322,14.2928932 L8.29289322,8.29289322 C8.67147216,7.91431428 9.28105859,7.90106866 9.67572463,8.26284586 L15.6757246,13.7628459 C16.0828436,14.1360383 16.1103465,14.7686056 15.7371541,15.1757246 C15.3639617,15.5828436 14.7313944,15.6103465 14.3242754,15.2371541 L9.03007575,10.3841378 L3.70710678,15.7071068 Z" id="Path-94" fill="#000000" fill-rule="nonzero" opacity="0.3" transform="translate(9.000003, 11.999999) rotate(-270.000000) translate(-9.000003, -11.999999) " />
						</g>
					</svg></span>
			</button>

			<!--
			<button class="kt-aside__brand-aside-toggler kt-aside__brand-aside-toggler--left" id="kt_aside_toggler"><span></span></button>
			-->
		</div>
	</div>
	<!-- end:: Aside -->

	<!-- begin:: Aside Menu -->
	<div class="kt-aside-menu-wrapper kt-grid__item kt-grid__item--fluid" id="kt_aside_menu_wrapper">
		<div id="kt_aside_menu" class="kt-aside-menu " data-ktmenu-vertical="1" data-ktmenu-scroll="1" data-ktmenu-dropdown-timeout="500">
			<ul class="kt-menu__nav ">
				<li class="kt-menu__item" aria-haspopup="true">
					<a href="#" class="kt-menu__link ">
						<span class="kt-menu__link-icon">
							<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1" class="kt-svg-icon">
								<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
									<polygon id="Bound" points="0 0 24 0 24 24 0 24" />
									<path d="M12.9336061,16.072447 L19.36,10.9564761 L19.5181585,10.8312381 C20.1676248,10.3169571 20.2772143,9.3735535 19.7629333,8.72408713 C19.6917232,8.63415859 19.6104327,8.55269514 19.5206557,8.48129411 L12.9336854,3.24257445 C12.3871201,2.80788259 11.6128799,2.80788259 11.0663146,3.24257445 L4.47482784,8.48488609 C3.82645598,9.00054628 3.71887192,9.94418071 4.23453211,10.5925526 C4.30500305,10.6811601 4.38527899,10.7615046 4.47382636,10.8320511 L4.63,10.9564761 L11.0659024,16.0730648 C11.6126744,16.5077525 12.3871218,16.5074963 12.9336061,16.072447 Z" id="Shape" fill="#000000" fill-rule="nonzero" />
									<path d="M11.0563554,18.6706981 L5.33593024,14.122919 C4.94553994,13.8125559 4.37746707,13.8774308 4.06710397,14.2678211 C4.06471678,14.2708238 4.06234874,14.2738418 4.06,14.2768747 L4.06,14.2768747 C3.75257288,14.6738539 3.82516916,15.244888 4.22214834,15.5523151 C4.22358765,15.5534297 4.2250303,15.55454 4.22647627,15.555646 L11.0872776,20.8031356 C11.6250734,21.2144692 12.371757,21.2145375 12.909628,20.8033023 L19.7677785,15.559828 C20.1693192,15.2528257 20.2459576,14.6784381 19.9389553,14.2768974 C19.9376429,14.2751809 19.9363245,14.2734691 19.935,14.2717619 L19.935,14.2717619 C19.6266937,13.8743807 19.0546209,13.8021712 18.6572397,14.1104775 C18.654352,14.112718 18.6514778,14.1149757 18.6486172,14.1172508 L12.9235044,18.6705218 C12.377022,19.1051477 11.6029199,19.1052208 11.0563554,18.6706981 Z" id="Path" fill="#000000" opacity="0.3" />
								</g>
							</svg>
						</span>
						<span class="kt-menu__link-text">Dashboard</span>
					</a>
				</li>
				<li class="kt-menu__item kt-menu__item--submenu {{ ( strpos(url()->current(), '/facturacion/') !== false ? 'kt-menu__item--open kt-menu__item--here' : '' ) }}" aria-haspopup="true" data-ktmenu-submenu-toggle="hover">
					<a href="javascript:;" class="kt-menu__link kt-menu__toggle">
						<span class="kt-menu__link-icon">
							<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1" class="kt-svg-icon">
							    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
							        <rect id="bound" x="0" y="0" width="24" height="24"/>
							        <rect id="Rectangle" fill="#000000" opacity="0.3" x="11.5" y="2" width="2" height="4" rx="1"/>
							        <rect id="Rectangle-Copy-3" fill="#000000" opacity="0.3" x="11.5" y="16" width="2" height="5" rx="1"/>
							        <path d="M15.493,8.044 C15.2143319,7.68933156 14.8501689,7.40750104 14.4005,7.1985 C13.9508311,6.98949895 13.5170021,6.885 13.099,6.885 C12.8836656,6.885 12.6651678,6.90399981 12.4435,6.942 C12.2218322,6.98000019 12.0223342,7.05283279 11.845,7.1605 C11.6676658,7.2681672 11.5188339,7.40749914 11.3985,7.5785 C11.2781661,7.74950085 11.218,7.96799867 11.218,8.234 C11.218,8.46200114 11.2654995,8.65199924 11.3605,8.804 C11.4555005,8.95600076 11.5948324,9.08899943 11.7785,9.203 C11.9621676,9.31700057 12.1806654,9.42149952 12.434,9.5165 C12.6873346,9.61150047 12.9723317,9.70966616 13.289,9.811 C13.7450023,9.96300076 14.2199975,10.1308324 14.714,10.3145 C15.2080025,10.4981676 15.6576646,10.7419985 16.063,11.046 C16.4683354,11.3500015 16.8039987,11.7268311 17.07,12.1765 C17.3360013,12.6261689 17.469,13.1866633 17.469,13.858 C17.469,14.6306705 17.3265014,15.2988305 17.0415,15.8625 C16.7564986,16.4261695 16.3733357,16.8916648 15.892,17.259 C15.4106643,17.6263352 14.8596698,17.8986658 14.239,18.076 C13.6183302,18.2533342 12.97867,18.342 12.32,18.342 C11.3573285,18.342 10.4263378,18.1741683 9.527,17.8385 C8.62766217,17.5028317 7.88033631,17.0246698 7.285,16.404 L9.413,14.238 C9.74233498,14.6433354 10.176164,14.9821653 10.7145,15.2545 C11.252836,15.5268347 11.7879973,15.663 12.32,15.663 C12.5606679,15.663 12.7949989,15.6376669 13.023,15.587 C13.2510011,15.5363331 13.4504991,15.4540006 13.6215,15.34 C13.7925009,15.2259994 13.9286662,15.0740009 14.03,14.884 C14.1313338,14.693999 14.182,14.4660013 14.182,14.2 C14.182,13.9466654 14.1186673,13.7313342 13.992,13.554 C13.8653327,13.3766658 13.6848345,13.2151674 13.4505,13.0695 C13.2161655,12.9238326 12.9248351,12.7908339 12.5765,12.6705 C12.2281649,12.5501661 11.8323355,12.420334 11.389,12.281 C10.9583312,12.141666 10.5371687,11.9770009 10.1255,11.787 C9.71383127,11.596999 9.34650161,11.3531682 9.0235,11.0555 C8.70049838,10.7578318 8.44083431,10.3968355 8.2445,9.9725 C8.04816568,9.54816454 7.95,9.03200304 7.95,8.424 C7.95,7.67666293 8.10199848,7.03700266 8.406,6.505 C8.71000152,5.97299734 9.10899753,5.53600171 9.603,5.194 C10.0970025,4.85199829 10.6543302,4.60183412 11.275,4.4435 C11.8956698,4.28516587 12.5226635,4.206 13.156,4.206 C13.9160038,4.206 14.6918294,4.34533194 15.4835,4.624 C16.2751706,4.90266806 16.9686637,5.31433061 17.564,5.859 L15.493,8.044 Z" id="Combined-Shape" fill="#000000"/>
							    </g>
							</svg>
						</span>
						<span class="kt-menu__link-text">Facturación</span>
						<i class="kt-menu__ver-arrow la la-angle-right"></i>
					</a>
					<div class="kt-menu__submenu ">
						<span class="kt-menu__arrow"></span>
						<ul class="kt-menu__subnav">
							<li class="kt-menu__item kt-menu__item--parent" aria-haspopup="true">
								<span class="kt-menu__link">
									<span class="kt-menu__link-text">Facturación</span>
								</span>
							</li>
						
							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.voucher.send_ose' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.voucher.send_ose') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Envío OSE</span>
								</a>
							</li>
							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.voucher.reportOse' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.voucher.reportOse') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Consulta Envío OSE</span>
								</a>
							</li>
							
							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.voucher.register_document_charge' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.voucher.register_document_charge') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Generación de Documentos </span>
								</a>
							</li>
                            <li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.voucher.register_document_fact' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.voucher.register_document_fact') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Facturación Manual</span>
								</a>
							</li>

							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.voucher.collection_register' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.voucher.collection_register') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Registro de Cobranzas</span>
								</a>
							</li>
							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.voucher.liquidations_final' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.voucher.liquidations_final') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Liquidaciones Final</span>
								</a>
							</li>
							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.voucher.liquidations' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.voucher.liquidations') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Facturación entre Empresas</span>
								</a>
							</li>
					 </ul>
				 </div>
			 </li>
							
							

				<li class="kt-menu__item  kt-menu__item--submenu {{ ( strpos(url()->current(), '/reporte/') !== false ? 'kt-menu__item--open kt-menu__item--here' : '' ) }}" aria-haspopup="true" data-ktmenu-submenu-toggle="hover">
					<a href="javascript:;" class="kt-menu__link kt-menu__toggle">
						<span class="kt-menu__link-icon">
							<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1" class="kt-svg-icon">
								<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
									<rect x="0" y="0" width="24" height="24"></rect>
									<rect fill="#000000" opacity="0.3" x="2" y="3" width="20" height="18" rx="2"></rect>
									<path d="M9.9486833,13.3162278 C9.81256925,13.7245699 9.43043041,14 9,14 L5,14 C4.44771525,14 4,13.5522847 4,13 C4,12.4477153 4.44771525,12 5,12 L8.27924078,12 L10.0513167,6.68377223 C10.367686,5.73466443 11.7274983,5.78688777 11.9701425,6.75746437 L13.8145063,14.1349195 L14.6055728,12.5527864 C14.7749648,12.2140024 15.1212279,12 15.5,12 L19,12 C19.5522847,12 20,12.4477153 20,13 C20,13.5522847 19.5522847,14 19,14 L16.118034,14 L14.3944272,17.4472136 C13.9792313,18.2776054 12.7550291,18.143222 12.5298575,17.2425356 L10.8627389,10.5740611 L9.9486833,13.3162278 Z" fill="#000000" fill-rule="nonzero"></path>
									<circle fill="#000000" opacity="0.3" cx="19" cy="6" r="1"></circle>
								</g>
							</svg>
						</span>
						<span class="kt-menu__link-text">Reportes</span>
						<i class="kt-menu__ver-arrow la la-angle-right"></i>
					</a>
					<div class="kt-menu__submenu ">
						<span class="kt-menu__arrow"></span>
						<ul class="kt-menu__subnav">
							<li class="kt-menu__item kt-menu__item--parent" aria-haspopup="true">
								<span class="kt-menu__link">
									<span class="kt-menu__link-text">Reportes</span>
								</span>
							</li>
							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.report.checking_account_report' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.report.checking_account_report') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Cuentas Corrientes Clientes</span>
								</a>
							</li>
							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.report.kardex' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.report.kardex') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Kardex</span>
								</a>
							</li>
							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.report.liquidations' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.report.liquidations') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Liquidaciones</span>
								</a>
							</li>
							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.report.liquidations_total' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.report.liquidations_total') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Liquidaciones Resumido</span>
								</a>
							</li>
							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.report.stock_seek_register' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.report.stock_seek_register') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Reporte de Guías Prueba</span>
								</a>
							</li>

							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.report.stock_final' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.report.stock_final') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Reporte de Guías</span>
								</a>
							</li>
							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.report.guides_scop' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.report.guides_scop') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Estado de Guías</span>
								</a>
							</li>
							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.report.guides_seek' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.report.guides_seek') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Buscador de Guías</span>
								</a>
							</li>
							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.report.facturations_sales' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.report.facturations_sales') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Facturas Emitidas</span>
								</a>
							</li>

							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.report.facturation_boletas' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.report.facturation_boletas') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Boletas Emitidas</span>
								</a>
							</li>

							
							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.report.liquidations_sales' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.report.liquidations_sales') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Venta Comercial</span>
								</a>
							</li>

							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.report.liquidations_channel' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.report.liquidations_channel') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Venta Canales</span>
								</a>
							</li>
							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.report.liquidations_channel_total' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.report.liquidations_channel_total') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Venta Canales Resumido</span>
								</a>
							</li>





							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.report.collection_sales_report' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.report.collection_sales_report') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Liquidación Detallado</span>
								</a>
							</li>
							
							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.report.stock_register' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.report.stock_register') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Movimiento de Existencias</span>
								</a>
							</li>

							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.report.warehouse_part' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.report.warehouse_part') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Parte de Almacén</span>
								</a>
							</li>
							
							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.report.collection_report' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.report.collection_report') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Relación de Cobranzas</span>
								</a>
							</li>
							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.report.pending_document_report' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.report.pending_document_report') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Relación de Documentos Pendientes</span>
								</a>
							</li>
							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.report.uncollected_document_report' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.report.uncollected_document_report') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Relación de Documentos Emitidos</span>
								</a>
							</li>
							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.report.sales-register' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.report.sales-register') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Registro de Ventas</span>
								</a>
							</li>
							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.report.sales-report' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.report.sales-report') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Reporte de Ventas</span>
								</a>
							</li>
							
						</ul>
					</div>
				</li>

				<li class="kt-menu__item  kt-menu__item--submenu {{ ( strpos(url()->current(), '/logistica/') !== false ? 'kt-menu__item--open kt-menu__item--here' : '' ) }}" aria-haspopup="true" data-ktmenu-submenu-toggle="hover">
					<a href="javascript:;" class="kt-menu__link kt-menu__toggle">
						<span class="kt-menu__link-icon">
							<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1" class="kt-svg-icon">
								<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
									<rect x="0" y="0" width="24" height="24"/>
									<path d="M10,4 L21,4 C21.5522847,4 22,4.44771525 22,5 L22,7 C22,7.55228475 21.5522847,8 21,8 L10,8 C9.44771525,8 9,7.55228475 9,7 L9,5 C9,4.44771525 9.44771525,4 10,4 Z M10,10 L21,10 C21.5522847,10 22,10.4477153 22,11 L22,13 C22,13.5522847 21.5522847,14 21,14 L10,14 C9.44771525,14 9,13.5522847 9,13 L9,11 C9,10.4477153 9.44771525,10 10,10 Z M10,16 L21,16 C21.5522847,16 22,16.4477153 22,17 L22,19 C22,19.5522847 21.5522847,20 21,20 L10,20 C9.44771525,20 9,19.5522847 9,19 L9,17 C9,16.4477153 9.44771525,16 10,16 Z" fill="#000000"/>
									<rect fill="#000000" opacity="0.3" x="2" y="4" width="5" height="16" rx="1"/>
								</g>
							</svg>
						</span>
						<span class="kt-menu__link-text">Logística</span>
						<i class="kt-menu__ver-arrow la la-angle-right"></i>
					</a>
					<div class="kt-menu__submenu ">
						<span class="kt-menu__arrow"></span>
						<ul class="kt-menu__subnav">
							<li class="kt-menu__item kt-menu__item--parent" aria-haspopup="true">
								<span class="kt-menu__link">
									<span class="kt-menu__link-text">Logística</span>
								</span>
							</li>
							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.logistics.stock_register' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.logistics.stock_register') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Registro Movimiento de Existencias</span>
								</a>
							</li>
							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.logistics.stock_register_beta' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.logistics.stock_register_beta') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Registro de Guías Prueba</span>
								</a>
							</li>
							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.logistics.inventories' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.logistics.inventories') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Inventario</span>
								</a>
							</li>
							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.articles' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.articles') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Artículos</span>
								</a>
							</li>
							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.classifications' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.classifications') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Clasificaciones</span>
								</a>
							</li>
							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.providers' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.providers') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Proveedores</span>
								</a>
							</li>
						</ul>
					</div>
				</li>
		
	            <li class="kt-menu__item  kt-menu__item--submenu {{ ( strpos(url()->current(), '/operaciones/') !== false ? 'kt-menu__item--open kt-menu__item--here' : '' ) }}" aria-haspopup="true" data-ktmenu-submenu-toggle="hover">
					<a href="javascript:;" class="kt-menu__link kt-menu__toggle">
						<span class="kt-menu__link-icon">
							<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1" class="kt-svg-icon">
								<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
									<rect x="0" y="0" width="24" height="24"></rect>
									<rect fill="#000000" opacity="0.3" x="2" y="3" width="20" height="18" rx="2"></rect>
									<path d="M9.9486833,13.3162278 C9.81256925,13.7245699 9.43043041,14 9,14 L5,14 C4.44771525,14 4,13.5522847 4,13 C4,12.4477153 4.44771525,12 5,12 L8.27924078,12 L10.0513167,6.68377223 C10.367686,5.73466443 11.7274983,5.78688777 11.9701425,6.75746437 L13.8145063,14.1349195 L14.6055728,12.5527864 C14.7749648,12.2140024 15.1212279,12 15.5,12 L19,12 C19.5522847,12 20,12.4477153 20,13 C20,13.5522847 19.5522847,14 19,14 L16.118034,14 L14.3944272,17.4472136 C13.9792313,18.2776054 12.7550291,18.143222 12.5298575,17.2425356 L10.8627389,10.5740611 L9.9486833,13.3162278 Z" fill="#000000" fill-rule="nonzero"></path>
									<circle fill="#000000" opacity="0.3" cx="19" cy="6" r="1"></circle>
								</g>
							</svg>
						</span>
						<span class="kt-menu__link-text">Operaciones</span>
						<i class="kt-menu__ver-arrow la la-angle-right"></i>
					</a>
					<div class="kt-menu__submenu ">
						<span class="kt-menu__arrow"></span>
						<ul class="kt-menu__subnav">
							<li class="kt-menu__item kt-menu__item--parent" aria-haspopup="true">
								<span class="kt-menu__link">
									<span class="kt-menu__link-text">Operaciones</span>
								</span>
							</li>
                            <li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.operations.guides_register' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.operations.guides_register') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Registro de Guías de Remisión</span>
								</a>
							</li>
							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.operations.operations_part' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.operations.operations_part') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Impresión de Guías</span>
								</a>
							</li>
							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.operations.guides_return' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.operations.guides_return') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Retorno de Guías de Remisión</span>
								</a>
							</li>
			            </ul>
					</div>
				</li>

				<li class="kt-menu__item  kt-menu__item--submenu {{ ( strpos(url()->current(), '/comercial/') !== false ? 'kt-menu__item--open kt-menu__item--here' : '' ) }}" aria-haspopup="true" data-ktmenu-submenu-toggle="hover">
					<a href="javascript:;" class="kt-menu__link kt-menu__toggle">
						<span class="kt-menu__link-icon">
							<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1" class="kt-svg-icon">
								<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
									<rect x="0" y="0" width="24" height="24"></rect>
									<rect fill="#000000" opacity="0.3" x="2" y="3" width="20" height="18" rx="2"></rect>
									<path d="M9.9486833,13.3162278 C9.81256925,13.7245699 9.43043041,14 9,14 L5,14 C4.44771525,14 4,13.5522847 4,13 C4,12.4477153 4.44771525,12 5,12 L8.27924078,12 L10.0513167,6.68377223 C10.367686,5.73466443 11.7274983,5.78688777 11.9701425,6.75746437 L13.8145063,14.1349195 L14.6055728,12.5527864 C14.7749648,12.2140024 15.1212279,12 15.5,12 L19,12 C19.5522847,12 20,12.4477153 20,13 C20,13.5522847 19.5522847,14 19,14 L16.118034,14 L14.3944272,17.4472136 C13.9792313,18.2776054 12.7550291,18.143222 12.5298575,17.2425356 L10.8627389,10.5740611 L9.9486833,13.3162278 Z" fill="#000000" fill-rule="nonzero"></path>
									<circle fill="#000000" opacity="0.3" cx="19" cy="6" r="1"></circle>
								</g>
							</svg>
						</span>
						<span class="kt-menu__link-text">Comercial</span>
						<i class="kt-menu__ver-arrow la la-angle-right"></i>
					</a>
					<div class="kt-menu__submenu ">
						<span class="kt-menu__arrow"></span>
						<ul class="kt-menu__subnav">
							<li class="kt-menu__item kt-menu__item--parent" aria-haspopup="true">
								<span class="kt-menu__link">
									<span class="kt-menu__link-text">Comercial</span>
								</span>
							</li>


							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.voucher.clients' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.voucher.clients') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Registro de Clientes</span>
								</a>
							</li>

							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.report.list_clients' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.report.list_clients') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Lista de Clientes</span>
								</a>
							</li>

							
							
						    	
							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.voucher.price_list' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.voucher.price_list') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Modificación de Precios</span>
								</a>
							</li>

							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.report.price_list' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.report.price_list') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Lista de Precios</span>
								</a>
							</li>
						
							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.commercial.commercial_channel' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								  <a href="{{ route('dashboard.commercial.commercial_channel') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Consulta de Ventas Detallado</span>
								</a>
							</li> 
							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.report.liquidations_channel_total' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.report.liquidations_channel_total') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Consulta de Ventas Resumido</span>
								</a>
							</li>


							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.commercial.guides_commercial' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								  <a href="{{ route('dashboard.commercial.guides_commercial') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Listado de Guías</span>
								</a>
							</li> 

							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.report.days' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								  <a href="{{ route('dashboard.report.days') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Reporte de Avance de Venta Diaria</span>
								</a>
							</li>

							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.report.gerencia' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								  <a href="{{ route('dashboard.report.gerencia') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Reporte de Gerencia Diaria</span>
								</a>
							</li>

							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.report.proyection' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								  <a href="{{ route('dashboard.report.proyection') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Reporte de Proyección Diaria</span>
								</a>
							</li>

							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.report.cobert' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								  <a href="{{ route('dashboard.report.cobert') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Reporte de Cobertura Diaria</span>
								</a>
							</li>


							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.report.rutas' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								  <a href="{{ route('dashboard.report.rutas') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Reporte de Rutas Diarias</span>
								</a>
							</li>


							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.report.days_glp' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								  <a href="{{ route('dashboard.report.days_glp') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Reporte de Compras GLP</span>
								</a>
							</li>


 
					  </ul>
					</div>
				</li>
				<li class="kt-menu__item  kt-menu__item--submenu {{ ( strpos(url()->current(), '/creditos/') !== false ? 'kt-menu__item--open kt-menu__item--here' : '' ) }}" aria-haspopup="true" data-ktmenu-submenu-toggle="hover">
					<a href="javascript:;" class="kt-menu__link kt-menu__toggle">
						<span class="kt-menu__link-icon">
							<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1" class="kt-svg-icon">
								<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
									<rect x="0" y="0" width="24" height="24"></rect>
									<rect fill="#000000" opacity="0.3" x="2" y="3" width="20" height="18" rx="2"></rect>
									<path d="M9.9486833,13.3162278 C9.81256925,13.7245699 9.43043041,14 9,14 L5,14 C4.44771525,14 4,13.5522847 4,13 C4,12.4477153 4.44771525,12 5,12 L8.27924078,12 L10.0513167,6.68377223 C10.367686,5.73466443 11.7274983,5.78688777 11.9701425,6.75746437 L13.8145063,14.1349195 L14.6055728,12.5527864 C14.7749648,12.2140024 15.1212279,12 15.5,12 L19,12 C19.5522847,12 20,12.4477153 20,13 C20,13.5522847 19.5522847,14 19,14 L16.118034,14 L14.3944272,17.4472136 C13.9792313,18.2776054 12.7550291,18.143222 12.5298575,17.2425356 L10.8627389,10.5740611 L9.9486833,13.3162278 Z" fill="#000000" fill-rule="nonzero"></path>
									<circle fill="#000000" opacity="0.3" cx="19" cy="6" r="1"></circle>
								</g>
							</svg>
						</span>
						<span class="kt-menu__link-text">Creditos y Cobranzas</span>
						<i class="kt-menu__ver-arrow la la-angle-right"></i>
					</a>
					<div class="kt-menu__submenu ">
						<span class="kt-menu__arrow"></span>
						<ul class="kt-menu__subnav">
							<li class="kt-menu__item kt-menu__item--parent" aria-haspopup="true">
								<span class="kt-menu__link">
									<span class="kt-menu__link-text">Creditos y Cobranzas</span>
								</span>
							</li>

							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.voucher.clients' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.voucher.clients') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Clientes</span>
								</a>
							</li>
							
						    	<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.credits.report.liquidations_credits' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								  <a href="{{ route('dashboard.credits.report.liquidations_credits') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Registro de liquidaciones detallado </span>
								</a>
							</li> 

							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.report.pending_document_int_report' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.report.pending_document_int_report') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Documentos Pendientes Internos</span>
								</a>
							</li>







					  </ul>
					</div>
				</li>

				

				<li class="kt-menu__item  kt-menu__item--submenu {{ ( strpos(url()->current(), '/contabilidad/') !== false ? 'kt-menu__item--open kt-menu__item--here' : '' ) }}" aria-haspopup="true" data-ktmenu-submenu-toggle="hover">
					<a href="javascript:;" class="kt-menu__link kt-menu__toggle">
						<span class="kt-menu__link-icon">
							<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1" class="kt-svg-icon">
								<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
									<rect x="0" y="0" width="24" height="24"></rect>
									<rect fill="#000000" opacity="0.3" x="2" y="3" width="20" height="18" rx="2"></rect>
									<path d="M9.9486833,13.3162278 C9.81256925,13.7245699 9.43043041,14 9,14 L5,14 C4.44771525,14 4,13.5522847 4,13 C4,12.4477153 4.44771525,12 5,12 L8.27924078,12 L10.0513167,6.68377223 C10.367686,5.73466443 11.7274983,5.78688777 11.9701425,6.75746437 L13.8145063,14.1349195 L14.6055728,12.5527864 C14.7749648,12.2140024 15.1212279,12 15.5,12 L19,12 C19.5522847,12 20,12.4477153 20,13 C20,13.5522847 19.5522847,14 19,14 L16.118034,14 L14.3944272,17.4472136 C13.9792313,18.2776054 12.7550291,18.143222 12.5298575,17.2425356 L10.8627389,10.5740611 L9.9486833,13.3162278 Z" fill="#000000" fill-rule="nonzero"></path>
									<circle fill="#000000" opacity="0.3" cx="19" cy="6" r="1"></circle>
								</g>
							</svg>
						</span>
						<span class="kt-menu__link-text">Contabilidad</span>
						<i class="kt-menu__ver-arrow la la-angle-right"></i>
					</a>
					<div class="kt-menu__submenu ">
						<span class="kt-menu__arrow"></span>
						<ul class="kt-menu__subnav">
							<li class="kt-menu__item kt-menu__item--parent" aria-haspopup="true">
								<span class="kt-menu__link">
									<span class="kt-menu__link-text">Contabilidad</span>
								</span>
							</li>

							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.voucher.massive_generation' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.voucher.massive_generation') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Generación masiva de comprobantes</span>
								</a>
							</li>


							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.report.transportist_register' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.report.transportist_register') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Reporte de Guias de Transportistas</span>
								</a>
							</li>

							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.report.sales_volume' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.report.sales_volume') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Volumen de Boletas</span>
								</a>
							</li>

							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.report.cordia_volume' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.report.cordia_volume') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Volumen de Boletas Cordia</span>
								</a>
							</li>


							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.report.facturations_sales_volume' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.report.facturations_sales_volume') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Facturación Volumen</span>
								</a>
							</li>

							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.report.facturations_total_sales_volume' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.report.facturations_total_sales_volume') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Facturación Volumen Resumido</span>
								</a>
							</li>


							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'facturations.import' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('facturations.import') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Importar Facturación</span>
								</a>
							</li>
					


			
						    	

					  </ul>
					</div>
				</li>


                <li class="kt-menu__item  kt-menu__item--submenu {{ ( strpos(url()->current(), '/comprasglp/') !== false ? 'kt-menu__item--open kt-menu__item--here' : '' ) }}" aria-haspopup="true" data-ktmenu-submenu-toggle="hover">
					<a href="javascript:;" class="kt-menu__link kt-menu__toggle">
						<span class="kt-menu__link-icon">
							<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1" class="kt-svg-icon">
								<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
									<rect x="0" y="0" width="24" height="24"></rect>
									<rect fill="#000000" opacity="0.3" x="2" y="3" width="20" height="18" rx="2"></rect>
									<path d="M9.9486833,13.3162278 C9.81256925,13.7245699 9.43043041,14 9,14 L5,14 C4.44771525,14 4,13.5522847 4,13 C4,12.4477153 4.44771525,12 5,12 L8.27924078,12 L10.0513167,6.68377223 C10.367686,5.73466443 11.7274983,5.78688777 11.9701425,6.75746437 L13.8145063,14.1349195 L14.6055728,12.5527864 C14.7749648,12.2140024 15.1212279,12 15.5,12 L19,12 C19.5522847,12 20,12.4477153 20,13 C20,13.5522847 19.5522847,14 19,14 L16.118034,14 L14.3944272,17.4472136 C13.9792313,18.2776054 12.7550291,18.143222 12.5298575,17.2425356 L10.8627389,10.5740611 L9.9486833,13.3162278 Z" fill="#000000" fill-rule="nonzero"></path>
									<circle fill="#000000" opacity="0.3" cx="19" cy="6" r="1"></circle>
								</g>
							</svg>
						</span>
						<span class="kt-menu__link-text">Control GLP</span>
						<i class="kt-menu__ver-arrow la la-angle-right"></i>
					</a>
					<div class="kt-menu__submenu ">
						<span class="kt-menu__arrow"></span>
						<ul class="kt-menu__subnav">
							<li class="kt-menu__item kt-menu__item--parent" aria-haspopup="true">
								<span class="kt-menu__link">
									<span class="kt-menu__link-text">Control GLP</span>
								</span>
							</li>

							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.operations.guides_glp_register' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.operations.guides_glp_register') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Compras GLP</span>
								</a>
							</li>

							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.report.stock_sales_register' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.report.stock_sales_register') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Reporte de Compras GLP</span>
								</a>
							</li>

							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.report.glp_fact' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.report.glp_fact') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Editor de Compras GLP</span>
								</a>
							</li>


							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.operations.stock_glp_register' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.operations.stock_glp_register') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Abastecimiento de GLP</span>
								</a>
							</li>


							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.report.terminals' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.report.terminals') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Reporte de Abastecimientos </span>
								</a>
							</li>


							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.report.cost_glp_register' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.report.cost_glp_register') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Registro de Costo GLP</span>
								</a>
							</li>



							
						    	

					  </ul>
					</div>
				</li>




            <!--Menú de Finanzas-->

				<li class="kt-menu__item  kt-menu__item--submenu {{ ( strpos(url()->current(), '/administracion/') !== false ? 'kt-menu__item--open kt-menu__item--here' : '' ) }}" aria-haspopup="true" data-ktmenu-submenu-toggle="hover">
					<a href="javascript:;" class="kt-menu__link kt-menu__toggle">
						<span class="kt-menu__link-icon">
							<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1" class="kt-svg-icon">
								<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
									<rect x="0" y="0" width="24" height="24"></rect>
									<rect fill="#000000" opacity="0.3" x="2" y="3" width="20" height="18" rx="2"></rect>
									<path d="M9.9486833,13.3162278 C9.81256925,13.7245699 9.43043041,14 9,14 L5,14 C4.44771525,14 4,13.5522847 4,13 C4,12.4477153 4.44771525,12 5,12 L8.27924078,12 L10.0513167,6.68377223 C10.367686,5.73466443 11.7274983,5.78688777 11.9701425,6.75746437 L13.8145063,14.1349195 L14.6055728,12.5527864 C14.7749648,12.2140024 15.1212279,12 15.5,12 L19,12 C19.5522847,12 20,12.4477153 20,13 C20,13.5522847 19.5522847,14 19,14 L16.118034,14 L14.3944272,17.4472136 C13.9792313,18.2776054 12.7550291,18.143222 12.5298575,17.2425356 L10.8627389,10.5740611 L9.9486833,13.3162278 Z" fill="#000000" fill-rule="nonzero"></path>
									<circle fill="#000000" opacity="0.3" cx="19" cy="6" r="1"></circle>
								</g>
							</svg>
						</span>
						<span class="kt-menu__link-text">Finanzas</span>
						<i class="kt-menu__ver-arrow la la-angle-right"></i>
					</a>
					<div class="kt-menu__submenu ">
						<span class="kt-menu__arrow"></span>
						<ul class="kt-menu__subnav">
							<li class="kt-menu__item kt-menu__item--parent" aria-haspopup="true">
								<span class="kt-menu__link">
									<span class="kt-menu__link-text">Finanzas</span>
								</span>
							</li>








							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.administration.cobranzas_detail_total' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.administration.cobranzas_detail_total') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Cobranzas Detallado Resumido</span>
								</a>
							</li>



							
							<li class="kt-menu__item {{ ( ( Route::currentRouteName() === 'dashboard.report.liquidations_detail_total' ) ? 'kt-menu__item--active' : '' ) }}" aria-haspopup="true">
								<a href="{{ route('dashboard.report.liquidations_detail_total') }}" class="kt-menu__link ">
									<i class="kt-menu__link-bullet kt-menu__link-bullet--dot">
										<span></span>
									</i>
									<span class="kt-menu__link-text">Liquidación Detallado Resumido</span>
								</a>
							</li>





							
						    	

					  </ul>
					</div>
				</li>

             <!-- cierre Menú de Finanzas-->
















































			</ul>
		</div>
	</div>				
	<!-- end:: Aside Menu -->
</div>
<!-- end:: Aside -->
