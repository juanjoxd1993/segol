<!doctype html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0, shrink-to-fit=no">
		<title>PetroAmérica</title>
	</head>
	<body class="mail">
		<div class="body" style="background-color:#f2f3f8;padding:30px 15px;">
			<div class="content" style="max-width:600px;padding:30px;background-color:#FFF;margin:0 auto;font-family:Arial,sans-serif;">
				<img src="http://puntodedistribucion.com/backend/img/logo-pdf.png" alt="" style="display:block;width:150px;height:auto;margin:0 0 0 auto;">
				<h3>Estimado Cliente Sr(es). <b><?php echo e($mail_info->client_name); ?></b></h3>
				<?php if( $mail_info->voucher_type_type == 'RC' ): ?>
					<p style="margin:0 0 20px;font-size:15px;">Se realizó envío de Resumen de la empresa <?php echo e($mail_info->company_name); ?> RC-<?php echo e($mail_info->summary_date); ?> ticket: <?php echo e($mail_info->summary_ticket); ?> por <?php echo e($mail_info->ids_count); ?> documentos</p>
				<?php else: ?>
					<?php if( $mail_info->low_number ): ?>
						<p style="margin:0 0 20px;font-size:15px;">Informamos a usted que el comprobante <b>Comunicación de Baja N. <?php echo e($mail_info->issue_date); ?>-<?php echo e($mail_info->low_number); ?></b>, ya se encuentra disponible.</p>
					<?php else: ?>
						<p style="margin:0 0 20px;font-size:15px;">Informamos a usted que el comprobante <b><?php echo e($mail_info->voucher_type_name); ?> N. <?php echo e($mail_info->serie_number); ?>-<?php echo e($mail_info->voucher_number); ?></b>, ya se encuentra disponible.</p>
					<?php endif; ?>
					<p style="margin:0 0 5px;font-size:11px;text-transform:uppercase;color:#787874;">RUC Emisor:</p>
					<p style="margin:0 0 20px;font-size:15px;"><?php echo e($mail_info->company_document_number); ?></p>
					<p style="margin:0 0 5px;font-size:11px;text-transform:uppercase;color:#787874;">Tipo:</p>
					<?php if( $mail_info->low_number ): ?>
						<p style="margin:0 0 20px;font-size:15px;">Comunicación de Baja</p>
					<?php else: ?>
						<p style="margin:0 0 20px;font-size:15px;"><?php echo e($mail_info->voucher_type_name); ?></p>
					<?php endif; ?>
					<p style="margin:0 0 5px;font-size:11px;text-transform:uppercase;color:#787874;">Número:</p>
					<p style="margin:0 0 20px;font-size:15px;"><?php echo e($mail_info->serie_number); ?>-<?php echo e($mail_info->voucher_number); ?></p>
					<p style="margin:0 0 5px;font-size:11px;text-transform:uppercase;color:#787874;">Monto:</p>
					<p style="margin:0 0 20px;font-size:15px;"><?php echo e($mail_info->currency_symbol); ?> <?php echo e($mail_info->total); ?></p>
					<p style="margin:0 0 5px;font-size:11px;text-transform:uppercase;color:#787874;">Fecha de Emisión:</p>
					<p style="margin:0 0 20px;font-size:15px;"><?php echo e($mail_info->issue_date); ?></p>
					<?php if( $mail_info->expiry_date ): ?>
						<p style="margin:0 0 5px;font-size:11px;text-transform:uppercase;color:#787874;">Fecha de Vencimiento:</p>
						<p style="margin:0 0 20px;font-size:15px;"><?php echo e($mail_info->expiry_date); ?></p>
					<?php endif; ?>
				<?php endif; ?>
				<p style="margin:0;font-size:15px;">Atentamente,</p>
				<p style="margin:0 0 20px;font-size:15px;"><?php echo e($mail_info->company_name); ?></p>
			</div>
		</div>
	</body>
</html><?php /**PATH /var/www/pdd/resources/views/backend/mail/voucher_mail.blade.php ENDPATH**/ ?>