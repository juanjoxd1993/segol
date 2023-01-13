<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Storage;

class VoucherMail extends Mailable
{
    use Queueable, SerializesModels;

    public $item, $routes, $mail_info;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($item, $routes, $mail_info)
    {
        $this->item = $item;
        $this->routes = $routes;
        $this->mail_info = $mail_info;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
		if ( isset($this->mail_info->summary_number) ) {
			return $this->from(env('BILLING_ADDRESS_DESTINATION_EMAIL'), $this->mail_info->company_name)
                ->subject($this->mail_info->company_short_name.' - Resumen de envío RC-'.$this->mail_info->summary_date.' ticket: '.$this->mail_info->summary_ticket)
                ->attach($this->routes->nombre_ruta_rspta)
                ->attach($this->routes->nombre_ruta_firma)
                ->view('backend.mail.voucher_mail')
				->with(['mail_info' => $this->mail_info]);
		} elseif ( isset($this->mail_info->low_number) && $this->mail_info->low_number ) {
            return $this->from(env('BILLING_ADDRESS_DESTINATION_EMAIL'), $this->mail_info->company_name)
                ->subject('Comunicación de Baja '.date('Ymd', strtotime($this->mail_info->issue_date)).'-'.$this->mail_info->low_number)
                ->attach($this->routes->nombre_ruta_rspta)
                ->attach($this->routes->nombre_ruta_firma)
                ->view('backend.mail.voucher_mail')
				->with(['mail_info' => $this->mail_info]);
        } else {
            return $this->from(env('BILLING_ADDRESS_DESTINATION_EMAIL'), $this->mail_info->company_name)
                ->subject($this->mail_info->voucher_type_name.' '.$this->mail_info->serie_number.'-'.$this->mail_info->voucher_number)
                ->attach($this->routes->nombre_ruta_rspta)
                ->attach($this->routes->nombre_ruta_pdf)
                ->attach($this->routes->nombre_ruta_firma)
                ->view('backend.mail.voucher_mail')
				->with(['mail_info' => $this->mail_info]);
        }
    }
}
