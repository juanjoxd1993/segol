<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Greenter\XMLSecLibs\Sunat\SignedXml;
use DB;
use PDF;
use QrCode;
use SimpleXMLElement;
use SoapClient;
use SoapFault;
use SoapHeader;
use SoapVar;
use App\Mail\VoucherMail;
use App\Company;

class UtilitiesController extends Controller
{
	public function send_ose($main_obj) {

		$response = [];

		foreach ($main_obj as $obj) {
			$nombre_xml = $obj->company_document_number . '-' . $obj->document_voucher_type . '-' . $obj->document_serie . '-' . $obj->document_voucher_number;
			$nombre_ruta = 'uploads/' . $obj->company_short_name . '/'. $obj->document_year;
			$nombre_ruta_xml = $nombre_ruta .'/xml/' . $nombre_xml . '.xml';
			$nombre_ruta_firma = $nombre_ruta .'/firma/' . $nombre_xml . '.xml';
			$nombre_ruta_zip = $nombre_ruta .'/firma/' . $nombre_xml . '.zip';
			$nombre_ruta_rspta = $nombre_ruta .'/rpta/R-' . $nombre_xml . '.zip';
			$nombre_ruta_pdf = $nombre_ruta .'/pdf/' . $nombre_xml . '.pdf';

			$obj->nombre_ruta_rspta = $nombre_ruta_rspta;
			$obj->nombre_ruta_pdf = $nombre_ruta_pdf;
			$obj->nombre_ruta_firma = $nombre_ruta_firma;

			if ( $obj->document_voucher_type == '01' ) {
				$textoXML = '<?xml version="1.0" encoding="UTF-8"?>';
				$textoXML .= "\n";
				$textoXML .= '<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2">';
					$textoXML .= "\n";
					$textoXML .= "\t";
					$textoXML .= '<ext:UBLExtensions>';
						$textoXML .= "\n";
						$textoXML .= "\t\t";
						$textoXML .= '<ext:UBLExtension>';
							$textoXML .= "\n";
							$textoXML .= "\t\t\t";
							$textoXML .= '<ext:ExtensionContent />';
								$textoXML .= "\n";
								$textoXML .= "\t\t\t\t";
						$textoXML .= "\n";
						$textoXML .= "\t\t";
						$textoXML .= '</ext:UBLExtension>';
					$textoXML .= "\n";
					$textoXML .= "\t";
					$textoXML .= '</ext:UBLExtensions>';
					$textoXML .= "\n";
					$textoXML .= "\t";
					$textoXML .= '<cbc:UBLVersionID>2.1</cbc:UBLVersionID>';
					$textoXML .= "\n";
					$textoXML .= "\t";
					$textoXML .= '<cbc:CustomizationID>2.0</cbc:CustomizationID>';
					$textoXML .= "\n";
					$textoXML .= "\t";
					$textoXML .= '<cbc:ID>'.$obj->document_serie.'-'.$obj->document_voucher_number.'</cbc:ID>';
					$textoXML .= "\n";
					$textoXML .= "\t";
					$textoXML .= '<cbc:IssueDate>'.$obj->document_date_of_issue.'</cbc:IssueDate>';
					$textoXML .= "\n";
					$textoXML .= "\t";
					$textoXML .= '<cbc:IssueTime>'.$obj->document_hour_of_issue.'</cbc:IssueTime>';
					if ( $obj->document_expiration_date ) {
						$textoXML .= "\n";
						$textoXML .= "\t";
						$textoXML .= '<cbc:DueDate>'.$obj->document_expiration_date.'</cbc:DueDate>';
					}
					$textoXML .= "\n";
					$textoXML .= "\t";
					$textoXML .= '<cbc:InvoiceTypeCode listID="'.( $obj->document_perception ? '2001' : '0101').'">'.$obj->document_voucher_type.'</cbc:InvoiceTypeCode>';
					$textoXML .= "\n";
					$textoXML .= "\t";
					$textoXML .= '<cbc:Note languageLocaleID="1000"><![CDATA[SON '.$obj->document_total_text.']]></cbc:Note>';
					if ( $obj->document_perception ) {
						$textoXML .= "\n";
						$textoXML .= "\t";
						$textoXML .= '<cbc:Note languageLocaleID="2000"><![CDATA[COMPROBANTE DE PERCEPCIÓN]]></cbc:Note>';
					}
					$textoXML .= "\n";
					$textoXML .= "\t";
					$textoXML .= '<cbc:DocumentCurrencyCode>'.$obj->document_currency_short_name.'</cbc:DocumentCurrencyCode>';
					$textoXML .= "\n";
					$textoXML .= "\t";
					$textoXML .= '<cac:Signature>';
						$textoXML .= "\n";
						$textoXML .= "\t\t";
						$textoXML .= '<cbc:ID>'.$obj->company_document_number.'</cbc:ID>';
						$textoXML .= "\n";
						$textoXML .= "\t\t";
						$textoXML .= '<cbc:Note>'.$obj->company_name.'</cbc:Note>';
						$textoXML .= "\n";
						$textoXML .= "\t\t";
						$textoXML .= '<cac:SignatoryParty>';
							$textoXML .= "\n";
							$textoXML .= "\t\t\t";
							$textoXML .= '<cac:PartyIdentification>';
								$textoXML .= "\n";
								$textoXML .= "\t\t\t\t";
								$textoXML .= '<cbc:ID>'.$obj->company_document_number.'</cbc:ID>';
							$textoXML .= "\n";
							$textoXML .= "\t\t\t";
							$textoXML .= '</cac:PartyIdentification>';
							$textoXML .= "\n";
							$textoXML .= "\t\t\t";
							$textoXML .= '<cac:PartyName>';
								$textoXML .= "\n";
								$textoXML .= "\t\t\t\t";
								$textoXML .= '<cbc:Name><![CDATA['.$obj->company_name.']]></cbc:Name>';
							$textoXML .= "\n";
							$textoXML .= "\t\t\t";
							$textoXML .= '</cac:PartyName>';
						$textoXML .= "\n";
						$textoXML .= "\t\t";
						$textoXML .= '</cac:SignatoryParty>';
						$textoXML .= "\n";
						$textoXML .= "\t\t";
						$textoXML .= '<cac:DigitalSignatureAttachment>';
							$textoXML .= "\n";
							$textoXML .= "\t\t\t";
							$textoXML .= '<cac:ExternalReference>';
								$textoXML .= "\n";
								$textoXML .= "\t\t\t\t";
								$textoXML .= '<cbc:URI>#SIGN-PDD</cbc:URI>';
							$textoXML .= "\n";
							$textoXML .= "\t\t\t";
							$textoXML .= '</cac:ExternalReference>';
						$textoXML .= "\n";
						$textoXML .= "\t\t";
						$textoXML .= '</cac:DigitalSignatureAttachment>';
					$textoXML .= "\n";
					$textoXML .= "\t";
					$textoXML .= '</cac:Signature>';
					$textoXML .= "\n";
					$textoXML .= "\t";
					$textoXML .= '<cac:AccountingSupplierParty>';
						$textoXML .= "\n";
						$textoXML .= "\t\t";
						$textoXML .= '<cac:Party>';
							$textoXML .= "\n";
							$textoXML .= "\t\t\t";
							$textoXML .= '<cac:PartyIdentification>';
								$textoXML .= "\n";
								$textoXML .= "\t\t\t\t";
								$textoXML .= '<cbc:ID schemeID="6">'.$obj->company_document_number.'</cbc:ID>';
							$textoXML .= "\n";
							$textoXML .= "\t\t\t";
							$textoXML .= '</cac:PartyIdentification>';
							$textoXML .= "\n";
							$textoXML .= "\t\t\t";
							$textoXML .= '<cac:PartyName>';
								$textoXML .= "\n";
								$textoXML .= "\t\t\t\t";
								$textoXML .= '<cbc:Name><![CDATA['.$obj->company_name.']]></cbc:Name>';
							$textoXML .= "\n";
							$textoXML .= "\t\t\t";
							$textoXML .= '</cac:PartyName>';
							$textoXML .= "\n";
							$textoXML .= "\t\t\t";
							$textoXML .= '<cac:PartyLegalEntity>';
								$textoXML .= "\n";
								$textoXML .= "\t\t\t\t";
								$textoXML .= '<cbc:RegistrationName><![CDATA['.$obj->company_name.']]></cbc:RegistrationName>';
								$textoXML .= "\n";
								$textoXML .= "\t\t\t\t";
								$textoXML .= '<cac:RegistrationAddress>';
									$textoXML .= "\n";
									$textoXML .= "\t\t\t\t\t";
									$textoXML .= '<cbc:ID>'.$obj->company_ubigeo.'</cbc:ID>';
									$textoXML .= "\n";
									$textoXML .= "\t\t\t\t\t";
									$textoXML .= '<cbc:AddressTypeCode>0000</cbc:AddressTypeCode>';
									$textoXML .= "\n";
									$textoXML .= "\t\t\t\t\t";
									$textoXML .= '<cbc:CitySubdivisionName>-</cbc:CitySubdivisionName>';
									$textoXML .= "\n";
									$textoXML .= "\t\t\t\t\t";
									$textoXML .= '<cbc:CityName>'.$obj->company_department.'</cbc:CityName>';
									$textoXML .= "\n";
									$textoXML .= "\t\t\t\t\t";
									$textoXML .= '<cbc:CountrySubentity>'.$obj->company_province.'</cbc:CountrySubentity>';
									$textoXML .= "\n";
									$textoXML .= "\t\t\t\t\t";
									$textoXML .= '<cbc:District>'.$obj->company_district.'</cbc:District>';
									$textoXML .= "\n";
									$textoXML .= "\t\t\t\t\t";
									$textoXML .= '<cac:AddressLine>';
										$textoXML .= "\n";
										$textoXML .= "\t\t\t\t\t\t";
										$textoXML .= '<cbc:Line><![CDATA['.$obj->company_address.']]></cbc:Line>';
									$textoXML .= "\n";
									$textoXML .= "\t\t\t\t\t";
									$textoXML .= '</cac:AddressLine>';
									$textoXML .= "\n";
									$textoXML .= "\t\t\t\t\t";
									$textoXML .= '<cac:Country>';
										$textoXML .= "\n";
										$textoXML .= "\t\t\t\t\t\t";
										$textoXML .= '<cbc:IdentificationCode>PE</cbc:IdentificationCode>';
									$textoXML .= "\n";
									$textoXML .= "\t\t\t\t\t";
									$textoXML .= '</cac:Country>';
								$textoXML .= "\n";
								$textoXML .= "\t\t\t\t";
								$textoXML .= '</cac:RegistrationAddress>';
							$textoXML .= "\n";
							$textoXML .= "\t\t\t";
							$textoXML .= '</cac:PartyLegalEntity>';
						$textoXML .= "\n";
						$textoXML .= "\t\t";
						$textoXML .= '</cac:Party>';
					$textoXML .= "\n";
					$textoXML .= "\t";
					$textoXML .= '</cac:AccountingSupplierParty>';
					$textoXML .= "\n";
					$textoXML .= "\t";
					$textoXML .= '<cac:AccountingCustomerParty>';
						$textoXML .= "\n";
						$textoXML .= "\t\t";
						$textoXML .= '<cac:Party>';
							$textoXML .= "\n";
							$textoXML .= "\t\t\t";
							$textoXML .= '<cac:PartyIdentification>';
								$textoXML .= "\n";
								$textoXML .= "\t\t\t\t";
								$textoXML .= '<cbc:ID schemeID="6">'.$obj->client_document_number.'</cbc:ID>';
							$textoXML .= "\n";
							$textoXML .= "\t\t\t";
							$textoXML .= '</cac:PartyIdentification>';
							$textoXML .= "\n";
							$textoXML .= "\t\t\t";
							$textoXML .= '<cac:PartyLegalEntity>';
								$textoXML .= "\n";
								$textoXML .= "\t\t\t\t";
								$textoXML .= '<cbc:RegistrationName><![CDATA['.$obj->client_name.']]></cbc:RegistrationName>';
								// $textoXML .= "\n";
								// $textoXML .= "\t\t\t\t";
								// $textoXML .= '<cac:RegistrationAddress>';
								// 	$textoXML .= "\n";
								// 	$textoXML .= "\t\t\t\t\t";
								// 	$textoXML .= '<cac:AddressLine>';
								// 		$textoXML .= "\n";
								// 		$textoXML .= "\t\t\t\t\t\t";
								// 		$textoXML .= '<cbc:Line><![CDATA[-]]></cbc:Line>';
								// 	$textoXML .= "\n";
								// 	$textoXML .= "\t\t\t\t\t";
								// 	$textoXML .= '</cac:AddressLine>';
								// 	$textoXML .= "\n";
								// 	$textoXML .= "\t\t\t\t\t";
								// 	$textoXML .= '<cac:Country>';
								// 		$textoXML .= "\n";
								// 		$textoXML .= "\t\t\t\t\t\t";
								// 		$textoXML .= '<cbc:IdentificationCode>PE</cbc:IdentificationCode>';
								// 	$textoXML .= "\n";
								// 	$textoXML .= "\t\t\t\t\t";
								// 	$textoXML .= '</cac:Country>';
								// $textoXML .= "\n";
								// $textoXML .= "\t\t\t\t";
								// $textoXML .= '</cac:RegistrationAddress>';
							$textoXML .= "\n";
							$textoXML .= "\t\t\t";
							$textoXML .= '</cac:PartyLegalEntity>';
						$textoXML .= "\n";
						$textoXML .= "\t\t";
						$textoXML .= '</cac:Party>';
					$textoXML .= "\n";
					$textoXML .= "\t";
					$textoXML .= '</cac:AccountingCustomerParty>';
					if ( $obj->document_perception ) {
						$textoXML .= "\n";
						$textoXML .= "\t";
						$textoXML .= '<cac:PaymentTerms>';
							$textoXML .= "\n";
							$textoXML .= "\t\t";
							$textoXML .= '<cbc:ID>Percepcion</cbc:ID>';
							$textoXML .= "\n";
							$textoXML .= "\t\t";
							$textoXML .= '<cbc:Amount currencyID="PEN">'.number_format((float)$obj->document_total + (float)$obj->document_perception, 2, '.','').'</cbc:Amount>';
						$textoXML .= "\n";
						$textoXML .= "\t";
						$textoXML .= '</cac:PaymentTerms>';
						$textoXML .= "\n";
						$textoXML .= "\t";
						$textoXML .= '<cac:AllowanceCharge>';
							$textoXML .= "\n";
							$textoXML .= "\t\t";
							$textoXML .= '<cbc:ChargeIndicator>true</cbc:ChargeIndicator>';
							$textoXML .= "\n";
							$textoXML .= "\t\t";
							$textoXML .= '<cbc:AllowanceChargeReasonCode>51</cbc:AllowanceChargeReasonCode>';
							$textoXML .= "\n";
							$textoXML .= "\t\t";
							$textoXML .= '<cbc:MultiplierFactorNumeric>'.$obj->document_perception_percentage.'</cbc:MultiplierFactorNumeric>';
							$textoXML .= "\n";
							$textoXML .= "\t\t";
							$textoXML .= '<cbc:Amount currencyID="PEN">'.$obj->document_perception.'</cbc:Amount>';
							$textoXML .= "\n";
							$textoXML .= "\t\t";
							$textoXML .= '<cbc:BaseAmount currencyID="PEN">'.number_format((float)$obj->document_total, 2, '.', '').'</cbc:BaseAmount>';
						$textoXML .= "\n";
						$textoXML .= "\t";
						$textoXML .= '</cac:AllowanceCharge>';
					}
					$textoXML .= "\n";
					$textoXML .= "\t";
					$textoXML .= '<cac:TaxTotal>';
						$textoXML .= "\n";
						$textoXML .= "\t\t";
						$textoXML .= '<cbc:TaxAmount currencyID="'.$obj->document_currency_short_name.'">'.number_format((float)$obj->document_igv, 2, '.', '').'</cbc:TaxAmount>';
						$textoXML .= "\n";
						$textoXML .= "\t\t";
						$textoXML .= '<cac:TaxSubtotal>';
							$textoXML .= "\n";
							$textoXML .= "\t\t\t";
							$textoXML .= '<cbc:TaxableAmount currencyID="'.$obj->document_currency_short_name.'">'.number_format((float)$obj->document_subtotal, 2, '.', '').'</cbc:TaxableAmount>';
							$textoXML .= "\n";
							$textoXML .= "\t\t\t";
							$textoXML .= '<cbc:TaxAmount currencyID="'.$obj->document_currency_short_name.'">'.number_format((float)$obj->document_igv, 2, '.', '').'</cbc:TaxAmount>';
							$textoXML .= "\n";
							$textoXML .= "\t\t\t";
							$textoXML .= '<cac:TaxCategory>';
								$textoXML .= "\n";
								$textoXML .= "\t\t\t\t";
								$textoXML .= '<cac:TaxScheme>';
									$textoXML .= "\n";
									$textoXML .= "\t\t\t\t\t";
									$textoXML .= '<cbc:ID>1000</cbc:ID>';
									$textoXML .= "\n";
									$textoXML .= "\t\t\t\t\t";
									$textoXML .= '<cbc:Name>IGV</cbc:Name>';
									$textoXML .= "\n";
									$textoXML .= "\t\t\t\t\t";
									$textoXML .= '<cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>';
								$textoXML .= "\n";
								$textoXML .= "\t\t\t\t";
								$textoXML .= '</cac:TaxScheme>';
							$textoXML .= "\n";
							$textoXML .= "\t\t\t";
							$textoXML .= '</cac:TaxCategory>';
						$textoXML .= "\n";
						$textoXML .= "\t\t";
						$textoXML .= '</cac:TaxSubtotal>';
					$textoXML .= "\n";
					$textoXML .= "\t";
					$textoXML .= '</cac:TaxTotal>';
					$textoXML .= "\n";
					$textoXML .= "\t";
					$textoXML .= '<cac:LegalMonetaryTotal>';
						$textoXML .= "\n";
						$textoXML .= "\t\t";
						$textoXML .= '<cbc:LineExtensionAmount currencyID="'.$obj->document_currency_short_name.'">'.number_format((float)$obj->document_subtotal, 2, '.', '').'</cbc:LineExtensionAmount>';
						$textoXML .= "\n";
						$textoXML .= "\t\t";
						$textoXML .= '<cbc:PayableAmount currencyID="'.$obj->document_currency_short_name.'">'.number_format((float)$obj->document_total, 2, '.', '').'</cbc:PayableAmount>';
					$textoXML .= "\n";
					$textoXML .= "\t";
					$textoXML .= '</cac:LegalMonetaryTotal>';
					foreach ($obj->details as $index => $detail) {
						$textoXML .= "\n";
						$textoXML .= "\t";
						$textoXML .= '<cac:InvoiceLine>';
							$textoXML .= "\n";
							$textoXML .= "\t\t";
							$textoXML .= '<cbc:ID>'.++$index.'</cbc:ID>';
							$textoXML .= "\n";
							$textoXML .= "\t\t";
							$textoXML .= '<cbc:InvoicedQuantity unitCode="'.$detail->detail_unit_short_name.'">'.$detail->detail_quantity.'</cbc:InvoicedQuantity>';
							$textoXML .= "\n";
							$textoXML .= "\t\t";
							$textoXML .= '<cbc:LineExtensionAmount currencyID="'.$obj->document_currency_short_name.'">'.$detail->detail_subtotal.'</cbc:LineExtensionAmount>';
							$textoXML .= "\n";
							$textoXML .= "\t\t";
							$textoXML .= '<cac:PricingReference>';
								$textoXML .= "\n";
								$textoXML .= "\t\t\t";
								$textoXML .= '<cac:AlternativeConditionPrice>';
									$textoXML .= "\n";
									$textoXML .= "\t\t\t\t";
									$textoXML .= '<cbc:PriceAmount currencyID="'.$obj->document_currency_short_name.'">'.$detail->detail_price_igv.'</cbc:PriceAmount>';
									$textoXML .= "\n";
									$textoXML .= "\t\t\t\t";
									$textoXML .= '<cbc:PriceTypeCode>01</cbc:PriceTypeCode>';
								$textoXML .= "\n";
								$textoXML .= "\t\t\t";
								$textoXML .= '</cac:AlternativeConditionPrice>';
							$textoXML .= "\n";
							$textoXML .= "\t\t";
							$textoXML .= '</cac:PricingReference>';
							$textoXML .= "\n";
							$textoXML .= "\t\t";
							$textoXML .= '<cac:TaxTotal>';
								$textoXML .= "\n";
								$textoXML .= "\t\t\t";
								$textoXML .= '<cbc:TaxAmount currencyID="'.$obj->document_currency_short_name.'">'.$detail->detail_igv.'</cbc:TaxAmount>';
								$textoXML .= "\n";
								$textoXML .= "\t\t\t";
								$textoXML .= '<cac:TaxSubtotal>';
									$textoXML .= "\n";
									$textoXML .= "\t\t\t\t";
									$textoXML .= '<cbc:TaxableAmount currencyID="'.$obj->document_currency_short_name.'">'.$detail->detail_subtotal.'</cbc:TaxableAmount>';
									$textoXML .= "\n";
									$textoXML .= "\t\t\t\t";
									$textoXML .= '<cbc:TaxAmount currencyID="'.$obj->document_currency_short_name.'">'.$detail->detail_igv.'</cbc:TaxAmount>';
									$textoXML .= "\n";
									$textoXML .= "\t\t\t\t";
									$textoXML .= '<cac:TaxCategory>';
										$textoXML .= "\n";
										$textoXML .= "\t\t\t\t\t";
										$textoXML .= '<cbc:Percent>'.$detail->detail_igv_percentage.'</cbc:Percent>';
										$textoXML .= "\n";
										$textoXML .= "\t\t\t\t\t";
										$textoXML .= '<cbc:TaxExemptionReasonCode>10</cbc:TaxExemptionReasonCode>';
										$textoXML .= "\n";
										$textoXML .= "\t\t\t\t\t";
										$textoXML .= '<cac:TaxScheme>';
											$textoXML .= "\n";
											$textoXML .= "\t\t\t\t\t\t";
											$textoXML .= '<cbc:ID>1000</cbc:ID>';
											$textoXML .= "\n";
											$textoXML .= "\t\t\t\t\t\t";
											$textoXML .= '<cbc:Name>IGV</cbc:Name>';
											$textoXML .= "\n";
											$textoXML .= "\t\t\t\t\t\t";
											$textoXML .= '<cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>';
										$textoXML .= "\n";
										$textoXML .= "\t\t\t\t\t";
										$textoXML .= '</cac:TaxScheme>';
									$textoXML .= "\n";
									$textoXML .= "\t\t\t\t";
									$textoXML .= '</cac:TaxCategory>';
								$textoXML .= "\n";
								$textoXML .= "\t\t\t";
								$textoXML .= '</cac:TaxSubtotal>';
							$textoXML .= "\n";
							$textoXML .= "\t\t";
							$textoXML .= '</cac:TaxTotal>';
							$textoXML .= "\n";
							$textoXML .= "\t\t";
							$textoXML .= '<cac:Item>';
								$textoXML .= "\n";
								$textoXML .= "\t\t\t";
								$textoXML .= '<cbc:Description><![CDATA['.$detail->detail_name.']]></cbc:Description>';
							$textoXML .= "\n";
							$textoXML .= "\t\t";
							$textoXML .= '</cac:Item>';
							$textoXML .= "\n";
							$textoXML .= "\t\t";
							$textoXML .= '<cac:Price>';
								$textoXML .= "\n";
								$textoXML .= "\t\t\t";
								$textoXML .= '<cbc:PriceAmount currencyID="'.$obj->document_currency_short_name.'">'.$detail->detail_price.'</cbc:PriceAmount>';
							$textoXML .= "\n";
							$textoXML .= "\t\t";
							$textoXML .= '</cac:Price>';
						$textoXML .= "\n";
						$textoXML .= "\t";
						$textoXML .= '</cac:InvoiceLine>';
					}
				$textoXML .= "\n";
				$textoXML .= '</Invoice>';
			} elseif ( $obj->document_voucher_type == '03' ) {
				$textoXML = '<?xml version="1.0" encoding="UTF-8"?>';
				$textoXML .= "\n";
				$textoXML .= '<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2">';
					$textoXML .= "\n";
					$textoXML .= "\t";
					$textoXML .= '<ext:UBLExtensions>';
						$textoXML .= "\n";
						$textoXML .= "\t\t";
						$textoXML .= '<ext:UBLExtension>';
							$textoXML .= "\n";
							$textoXML .= "\t\t\t";
							$textoXML .= '<ext:ExtensionContent>';
								$textoXML .= "\n";
								$textoXML .= "\t\t\t\t";
							$textoXML .= "\n";
							$textoXML .= "\t\t\t";
							$textoXML .= '</ext:ExtensionContent>';
						$textoXML .= "\n";
						$textoXML .= "\t\t";
						$textoXML .= '</ext:UBLExtension>';
					$textoXML .= "\n";
					$textoXML .= "\t";
					$textoXML .= '</ext:UBLExtensions>';
					$textoXML .= "\n";
					$textoXML .= "\t";
					$textoXML .= '<cbc:UBLVersionID>2.1</cbc:UBLVersionID>';
					$textoXML .= "\n";
					$textoXML .= "\t";
					$textoXML .= '<cbc:CustomizationID>2.0</cbc:CustomizationID>';
					$textoXML .= "\n";
					$textoXML .= "\t";
					$textoXML .= '<cbc:ID>'.$obj->document_serie.'-'.$obj->document_voucher_number.'</cbc:ID>';
					$textoXML .= "\n";
					$textoXML .= "\t";
					$textoXML .= '<cbc:IssueDate>'.$obj->document_date_of_issue.'</cbc:IssueDate>';
					$textoXML .= "\n";
					$textoXML .= "\t";
					$textoXML .= '<cbc:IssueTime>00:44:53</cbc:IssueTime>';
					$textoXML .= "\n";
					$textoXML .= "\t";
					$textoXML .= '<cbc:InvoiceTypeCode listID="0101">'.$obj->document_voucher_type.'</cbc:InvoiceTypeCode>';
					$textoXML .= "\n";
					$textoXML .= "\t";
					$textoXML .= '<cbc:Note languageLocaleID="1000"><![CDATA[SON '.$obj->document_total_text.']]></cbc:Note>';
					$textoXML .= "\n";
					$textoXML .= "\t";
					$textoXML .= '<cbc:DocumentCurrencyCode>'.$obj->document_currency_short_name.'</cbc:DocumentCurrencyCode>';
					$textoXML .= "\n";
					$textoXML .= "\t";
					$textoXML .= '<cac:Signature>';
						$textoXML .= "\n";
						$textoXML .= "\t\t";
						$textoXML .= '<cbc:ID>'.$obj->company_document_number.'</cbc:ID>';
						$textoXML .= "\n";
						$textoXML .= "\t\t";
						$textoXML .= '<cbc:Note>'.$obj->company_name.'</cbc:Note>';
						$textoXML .= "\n";
						$textoXML .= "\t\t";
						$textoXML .= '<cac:SignatoryParty>';
							$textoXML .= "\n";
							$textoXML .= "\t\t\t";
							$textoXML .= '<cac:PartyIdentification>';
								$textoXML .= "\n";
								$textoXML .= "\t\t\t\t";
								$textoXML .= '<cbc:ID>'.$obj->company_document_number.'</cbc:ID>';
							$textoXML .= "\n";
							$textoXML .= "\t\t\t";
							$textoXML .= '</cac:PartyIdentification>';
							$textoXML .= "\n";
							$textoXML .= "\t\t\t";
							$textoXML .= '<cac:PartyName>';
								$textoXML .= "\n";
								$textoXML .= "\t\t\t\t";
								$textoXML .= '<cbc:Name><![CDATA['.$obj->company_name.']]></cbc:Name>';
							$textoXML .= "\n";
							$textoXML .= "\t\t\t";
							$textoXML .= '</cac:PartyName>';
						$textoXML .= "\n";
						$textoXML .= "\t\t";
						$textoXML .= '</cac:SignatoryParty>';
						$textoXML .= "\n";
						$textoXML .= "\t\t";
						$textoXML .= '<cac:DigitalSignatureAttachment>';
							$textoXML .= "\n";
							$textoXML .= "\t\t\t";
							$textoXML .= '<cac:ExternalReference>';
								$textoXML .= "\n";
								$textoXML .= "\t\t\t\t";
								$textoXML .= '<cbc:URI>#SIGN-PDD</cbc:URI>';
							$textoXML .= "\n";
							$textoXML .= "\t\t\t";
							$textoXML .= '</cac:ExternalReference>';
						$textoXML .= "\n";
						$textoXML .= "\t\t";
						$textoXML .= '</cac:DigitalSignatureAttachment>';
					$textoXML .= "\n";
					$textoXML .= "\t";
					$textoXML .= '</cac:Signature>';
					$textoXML .= "\n";
					$textoXML .= "\t";
					$textoXML .= '<cac:AccountingSupplierParty>';
						$textoXML .= "\n";
						$textoXML .= "\t\t";
						$textoXML .= '<cac:Party>';
							$textoXML .= "\n";
							$textoXML .= "\t\t\t";
							$textoXML .= '<cac:PartyIdentification>';
								$textoXML .= "\n";
								$textoXML .= "\t\t\t\t";
								$textoXML .= '<cbc:ID schemeID="6">'.$obj->company_document_number.'</cbc:ID>';
							$textoXML .= "\n";
							$textoXML .= "\t\t\t";
							$textoXML .= '</cac:PartyIdentification>';
							$textoXML .= "\n";
							$textoXML .= "\t\t\t";
							$textoXML .= '<cac:PartyName>';
								$textoXML .= "\n";
								$textoXML .= "\t\t\t\t";
								$textoXML .= '<cbc:Name><![CDATA['.$obj->company_name.']]></cbc:Name>';
							$textoXML .= "\n";
							$textoXML .= "\t\t\t";
							$textoXML .= '</cac:PartyName>';
							$textoXML .= "\n";
							$textoXML .= "\t\t\t";
							$textoXML .= '<cac:PartyLegalEntity>';
								$textoXML .= "\n";
								$textoXML .= "\t\t\t\t";
								$textoXML .= '<cbc:RegistrationName><![CDATA['.$obj->company_name.']]></cbc:RegistrationName>';
								$textoXML .= "\n";
								$textoXML .= "\t\t\t\t";
								$textoXML .= '<cac:RegistrationAddress>';
									$textoXML .= "\n";
									$textoXML .= "\t\t\t\t\t";
									$textoXML .= '<cbc:ID>'.$obj->company_ubigeo.'</cbc:ID>';
									$textoXML .= "\n";
									$textoXML .= "\t\t\t\t\t";
									$textoXML .= '<cbc:AddressTypeCode>0000</cbc:AddressTypeCode>';
									$textoXML .= "\n";
									$textoXML .= "\t\t\t\t\t";
									$textoXML .= '<cbc:CitySubdivisionName>-</cbc:CitySubdivisionName>';
									$textoXML .= "\n";
									$textoXML .= "\t\t\t\t\t";
									$textoXML .= '<cbc:CityName>'.$obj->company_department.'</cbc:CityName>';
									$textoXML .= "\n";
									$textoXML .= "\t\t\t\t\t";
									$textoXML .= '<cbc:CountrySubentity>'.$obj->company_province.'</cbc:CountrySubentity>';
									$textoXML .= "\n";
									$textoXML .= "\t\t\t\t\t";
									$textoXML .= '<cbc:District>'.$obj->company_district.'</cbc:District>';
									$textoXML .= "\n";
									$textoXML .= "\t\t\t\t\t";
									$textoXML .= '<cac:AddressLine>';
										$textoXML .= "\n";
										$textoXML .= "\t\t\t\t\t\t";
										$textoXML .= '<cbc:Line><![CDATA['.$obj->company_address.']]></cbc:Line>';
									$textoXML .= "\n";
									$textoXML .= "\t\t\t\t\t";
									$textoXML .= '</cac:AddressLine>';
									$textoXML .= "\n";
									$textoXML .= "\t\t\t\t\t";
									$textoXML .= '<cac:Country>';
										$textoXML .= "\n";
										$textoXML .= "\t\t\t\t\t\t";
										$textoXML .= '<cbc:IdentificationCode>PE</cbc:IdentificationCode>';
									$textoXML .= "\n";
									$textoXML .= "\t\t\t\t\t";
									$textoXML .= '</cac:Country>';
								$textoXML .= "\n";
								$textoXML .= "\t\t\t\t";
								$textoXML .= '</cac:RegistrationAddress>';
							$textoXML .= "\n";
							$textoXML .= "\t\t\t";
							$textoXML .= '</cac:PartyLegalEntity>';
						$textoXML .= "\n";
						$textoXML .= "\t\t";
						$textoXML .= '</cac:Party>';
					$textoXML .= "\n";
					$textoXML .= "\t";
					$textoXML .= '</cac:AccountingSupplierParty>';
					$textoXML .= "\n";
					$textoXML .= "\t";
					$textoXML .= '<cac:AccountingCustomerParty>';
						$textoXML .= "\n";
						$textoXML .= "\t\t";
						$textoXML .= '<cac:Party>';
							$textoXML .= "\n";
							$textoXML .= "\t\t\t";
							$textoXML .= '<cac:PartyIdentification>';
								$textoXML .= "\n";
								$textoXML .= "\t\t\t\t";
								$textoXML .= '<cbc:ID schemeID="1">'.$obj->client_document_number.'</cbc:ID>';
							$textoXML .= "\n";
							$textoXML .= "\t\t\t";
							$textoXML .= '</cac:PartyIdentification>';
							$textoXML .= "\n";
							$textoXML .= "\t\t\t";
							$textoXML .= '<cac:PartyLegalEntity>';
								$textoXML .= "\n";
								$textoXML .= "\t\t\t\t";
								$textoXML .= '<cbc:RegistrationName><![CDATA['.$obj->client_name.']]></cbc:RegistrationName>';
							$textoXML .= "\n";
							$textoXML .= "\t\t\t";
							$textoXML .= '</cac:PartyLegalEntity>';
						$textoXML .= "\n";
						$textoXML .= "\t\t";
						$textoXML .= '</cac:Party>';
					$textoXML .= "\n";
					$textoXML .= "\t";
					$textoXML .= '</cac:AccountingCustomerParty>';
					$textoXML .= "\n";
					$textoXML .= "\t";
					$textoXML .= '<cac:TaxTotal>';
						$textoXML .= "\n";
						$textoXML .= "\t\t";
						$textoXML .= '<cbc:TaxAmount currencyID="'.$obj->document_currency_short_name.'">'.number_format($obj->document_igv, 2, '.', '').'</cbc:TaxAmount>';
						$textoXML .= "\n";
						$textoXML .= "\t\t";
						$textoXML .= '<cac:TaxSubtotal>';
							$textoXML .= "\n";
							$textoXML .= "\t\t\t";
							$textoXML .= '<cbc:TaxableAmount currencyID="'.$obj->document_currency_short_name.'">'.number_format($obj->document_subtotal, 2, '.', '').'</cbc:TaxableAmount>';
							$textoXML .= "\n";
							$textoXML .= "\t\t\t";
							$textoXML .= '<cbc:TaxAmount currencyID="'.$obj->document_currency_short_name.'">'.number_format($obj->document_igv, 2, '.', '').'</cbc:TaxAmount>';
							$textoXML .= "\n";
							$textoXML .= "\t\t\t";
							$textoXML .= '<cac:TaxCategory>';
								$textoXML .= "\n";
								$textoXML .= "\t\t\t\t";
								$textoXML .= '<cac:TaxScheme>';
									$textoXML .= "\n";
									$textoXML .= "\t\t\t\t\t";
									$textoXML .= '<cbc:ID>1000</cbc:ID>';
									$textoXML .= "\n";
									$textoXML .= "\t\t\t\t\t";
									$textoXML .= '<cbc:Name>IGV</cbc:Name>';
									$textoXML .= "\n";
									$textoXML .= "\t\t\t\t\t";
									$textoXML .= '<cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>';
								$textoXML .= "\n";
								$textoXML .= "\t\t\t\t";
								$textoXML .= '</cac:TaxScheme>';
							$textoXML .= "\n";
							$textoXML .= "\t\t\t";
							$textoXML .= '</cac:TaxCategory>';
						$textoXML .= "\n";
						$textoXML .= "\t\t";
						$textoXML .= '</cac:TaxSubtotal>';
					$textoXML .= "\n";
					$textoXML .= "\t";
					$textoXML .= '</cac:TaxTotal>';
					$textoXML .= "\n";
					$textoXML .= "\t";
					$textoXML .= '<cac:LegalMonetaryTotal>';
						$textoXML .= "\n";
						$textoXML .= "\t\t";
						$textoXML .= '<cbc:LineExtensionAmount currencyID="'.$obj->document_currency_short_name.'">'.number_format($obj->document_subtotal, 2, '.', '').'</cbc:LineExtensionAmount>';
						$textoXML .= "\n";
						$textoXML .= "\t\t";
						$textoXML .= '<cbc:PayableAmount currencyID="'.$obj->document_currency_short_name.'">'.number_format($obj->document_total, 2, '.', '').'</cbc:PayableAmount>';
					$textoXML .= "\n";
					$textoXML .= "\t";
					$textoXML .= '</cac:LegalMonetaryTotal>';
					foreach ($obj->details as $index => $detail) {
						$textoXML .= "\n";
						$textoXML .= "\t";
						$textoXML .= '<cac:InvoiceLine>';
							$textoXML .= "\n";
							$textoXML .= "\t\t";
							$textoXML .= '<cbc:ID>'.++$index.'</cbc:ID>';
							$textoXML .= "\n";
							$textoXML .= "\t\t";
							$textoXML .= '<cbc:InvoicedQuantity unitCode="'.$detail->detail_unit_short_name.'">'.$detail->detail_quantity.'</cbc:InvoicedQuantity>';
							$textoXML .= "\n";
							$textoXML .= "\t\t";
							$textoXML .= '<cbc:LineExtensionAmount currencyID="'.$obj->document_currency_short_name.'">'.$detail->detail_subtotal.'</cbc:LineExtensionAmount>';
							$textoXML .= "\n";
							$textoXML .= "\t\t";
							$textoXML .= '<cac:PricingReference>';
								$textoXML .= "\n";
								$textoXML .= "\t\t\t";
								$textoXML .= '<cac:AlternativeConditionPrice>';
									$textoXML .= "\n";
									$textoXML .= "\t\t\t\t";
									$textoXML .= '<cbc:PriceAmount currencyID="'.$obj->document_currency_short_name.'">'.$detail->detail_price_igv.'</cbc:PriceAmount>';
									$textoXML .= "\n";
									$textoXML .= "\t\t\t\t";
									$textoXML .= '<cbc:PriceTypeCode>01</cbc:PriceTypeCode>';
								$textoXML .= "\n";
								$textoXML .= "\t\t\t";
								$textoXML .= '</cac:AlternativeConditionPrice>';
							$textoXML .= "\n";
							$textoXML .= "\t\t";
							$textoXML .= '</cac:PricingReference>';
							$textoXML .= "\n";
							$textoXML .= "\t\t";
							$textoXML .= '<cac:TaxTotal>';
								$textoXML .= "\n";
								$textoXML .= "\t\t\t";
								$textoXML .= '<cbc:TaxAmount currencyID="'.$obj->document_currency_short_name.'">'.$detail->detail_igv.'</cbc:TaxAmount>';
								$textoXML .= "\n";
								$textoXML .= "\t\t\t";
								$textoXML .= '<cac:TaxSubtotal>';
									$textoXML .= "\n";
									$textoXML .= "\t\t\t\t";
									$textoXML .= '<cbc:TaxableAmount currencyID="'.$obj->document_currency_short_name.'">'.$detail->detail_subtotal.'</cbc:TaxableAmount>';
									$textoXML .= "\n";
									$textoXML .= "\t\t\t\t";
									$textoXML .= '<cbc:TaxAmount currencyID="'.$obj->document_currency_short_name.'">'.$detail->detail_igv.'</cbc:TaxAmount>';
									$textoXML .= "\n";
									$textoXML .= "\t\t\t\t";
									$textoXML .= '<cac:TaxCategory>';
										$textoXML .= "\n";
										$textoXML .= "\t\t\t\t\t";
										$textoXML .= '<cbc:Percent>'.$detail->detail_igv_percentage.'</cbc:Percent>';
										$textoXML .= "\n";
										$textoXML .= "\t\t\t\t\t";
										$textoXML .= '<cbc:TaxExemptionReasonCode>10</cbc:TaxExemptionReasonCode>';
										$textoXML .= "\n";
										$textoXML .= "\t\t\t\t\t";
										$textoXML .= '<cac:TaxScheme>';
											$textoXML .= "\n";
											$textoXML .= "\t\t\t\t\t\t";
											$textoXML .= '<cbc:ID>1000</cbc:ID>';
											$textoXML .= "\n";
											$textoXML .= "\t\t\t\t\t\t";
											$textoXML .= '<cbc:Name>IGV</cbc:Name>';
											$textoXML .= "\n";
											$textoXML .= "\t\t\t\t\t\t";
											$textoXML .= '<cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>';
										$textoXML .= "\n";
										$textoXML .= "\t\t\t\t\t";
										$textoXML .= '</cac:TaxScheme>';
									$textoXML .= "\n";
									$textoXML .= "\t\t\t\t";
									$textoXML .= '</cac:TaxCategory>';
								$textoXML .= "\n";
								$textoXML .= "\t\t\t";
								$textoXML .= '</cac:TaxSubtotal>';
							$textoXML .= "\n";
							$textoXML .= "\t\t";
							$textoXML .= '</cac:TaxTotal>';
							$textoXML .= "\n";
							$textoXML .= "\t\t";
							$textoXML .= '<cac:Item>';
								$textoXML .= "\n";
								$textoXML .= "\t\t\t";
								$textoXML .= '<cbc:Description><![CDATA['.$detail->detail_name.']]></cbc:Description>';
							$textoXML .= "\n";
							$textoXML .= "\t\t";
							$textoXML .= '</cac:Item>';
							$textoXML .= "\n";
							$textoXML .= "\t\t";
							$textoXML .= '<cac:Price>';
								$textoXML .= "\n";
								$textoXML .= "\t\t\t";
								$textoXML .= '<cbc:PriceAmount currencyID="'.$obj->document_currency_short_name.'">'.$detail->detail_price.'</cbc:PriceAmount>';
							$textoXML .= "\n";
							$textoXML .= "\t\t";
							$textoXML .= '</cac:Price>';
						$textoXML .= "\n";
						$textoXML .= "\t";
						$textoXML .= '</cac:InvoiceLine>';
					}
				$textoXML .= "\n";
				$textoXML .= '</Invoice>';
			}

			$textoXML = mb_convert_encoding($textoXML, "UTF-8");

			/*
			 * Crear el XML sin firma
			 */
			$generate_xml = Storage::disk('public')->put($nombre_ruta_xml, $textoXML);

			/*
			 * Firmar XML
			 */
			$xmlPath = $nombre_ruta . '/xml/' . $nombre_xml . '.xml';
			$certPath = base_path($obj->company_certificate_pem);

			$signer = new SignedXml();
			$signer->setCertificateFromFile($certPath);
			$textoXMLSigned = $signer->signFromFile($xmlPath);

			$sign_xml = Storage::disk('public')->put($nombre_ruta_firma, $textoXMLSigned);

			$xml_content = str_replace('ext:', '', $textoXMLSigned);
			$xml_content = str_replace('ds:', '', $xml_content);
			$xml_content = str_replace('cbc:', '', $xml_content);
			$xml_obj = simplexml_load_string($xml_content);

			$document_hash = (string)$xml_obj->UBLExtensions->UBLExtension->ExtensionContent->Signature->SignedInfo->Reference->DigestValue;
			$document_qrcode = base64_encode(QrCode::format('png')->size(100)->generate('| '.$obj->company_document_number.' | '.$obj->document_voucher_type.' | '.$obj->document_serie.'-'.$obj->document_voucher_number.' | '.$obj->document_igv.' | '.number_format($obj->document_total, 2).' | '.$obj->document_date_of_issue.' | '.$obj->client_document_type.' | '.$obj->client_document_number));

			$zip = $nombre_xml.'.zip';
			$xml = $nombre_xml.'.xml';

			/*
			 * Crear ZIP con XML insertado
			 */
			$zipArchive = new \ZipArchive;
			if ($zipArchive->open($nombre_ruta_zip, \ZipArchive::CREATE) === TRUE) {
				 $zipArchive->addFile($nombre_ruta_firma, $xml);
				 $zipArchive->close();
			}

			/*
			 * Convertir en Base64 el archivo .ZIP
			 */
			$fd = fopen($nombre_ruta_zip, 'rb');
			$size = filesize($nombre_ruta_zip);
			$cont = fread($fd, $size);
			fclose($fd);
			$enc = base64_encode($cont);

			/*
			 * Consumo de Webservice de Bizlinks
			 */
			$header = '<wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" mustUnderstand="1">';
				$header .= '<wsse:UsernameToken wsu:Id="UsernameToken-c175cdb9-9a32-4291-b8c7-85dff8107561">';
				$header .= '<wsse:Username>'.$obj->company_bizlinks_user.'</wsse:Username>';
				$header .= '<wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">'.$obj->company_bizlinks_password.'</wsse:Password>';
				$header .= '</wsse:UsernameToken>';
			$header .= '</wsse:Security>';

			$body = '<ns2:sendBill xmlns:ns2="http://service.sunat.gob.pe">';
			$body .= '<fileName>'.$zip.'</fileName>';
			$body .= '<contentFile>'.$enc.'</contentFile>';
			$body .= '</ns2:sendBill>';

			$header_block = new SoapVar( $header, XSD_ANYXML, NULL, NULL, NULL, NULL );
			$body_block = new SoapVar( $body, XSD_ANYXML, NULL, NULL, NULL, NULL );

			$header = new SoapHeader( 'http://schemas.xmlsoap.org/soap/envelope/', 'Header', $header_block );

								// https://osetesting.bizlinks.com.pe/ol-ti-itcpe/billService?wsdl
								// https://ose.bizlinks.com.pe/ol-ti-itcpe/billService?wsdl
			$client = new SoapClient(
							'https://ose.bizlinks.com.pe/ol-ti-itcpe/billService?wsdl',
							array(
								'trace' => 1,
								'use'	=> SOAP_LITERAL,
								'style'	=> SOAP_DOCUMENT,
								'cache_wsdl' => WSDL_CACHE_NONE,
								'user_agent' => 'PDD',
								'stream_context' => stream_context_create([
									  'ssl' => [
											'verify_peer' => false,
											'verify_peer_name' => false,
									  ],
								 ]),
							)
						);
			$client->__setSoapHeaders( $header );

			$data = [
				'company_address'					=> $obj->company_address,
				'company_department'				=> $obj->company_department,
				'company_district'					=> $obj->company_district,
				'company_document_number'			=> $obj->company_document_number,
				'company_name'						=> $obj->company_name,
				'company_province'					=> $obj->company_province,
				'client_address'					=> $obj->client_address,
				'client_document_name'				=> $obj->client_document_name,
				'client_document_number'			=> $obj->client_document_number,
				'client_email'						=> $obj->client_email,
				'client_name'						=> $obj->client_name,
				'document_currency_name'			=> $obj->document_currency_name,
				'document_currency_symbol'			=> $obj->document_currency_symbol,
				'document_date_of_issue'			=> $obj->document_date_of_issue,
				'document_expiration_date'			=> ($obj->document_expiration_date ? $obj->document_expiration_date : '-'),
				'document_hash'						=> $document_hash,
				'document_igv'						=> number_format($obj->document_igv, 2),
				'document_payment_name'				=> $obj->document_payment_name,
				'document_perception'				=> $obj->document_perception,
				'document_perception_percentage'	=> $obj->document_perception_percentage * 100,
				'document_qrcode'					=> $document_qrcode,
				'document_subtotal'					=> number_format($obj->document_subtotal, 2),
				'document_total'					=> number_format($obj->document_total, 2),
				'document_total_text'				=> $obj->document_total_text,
				'document_serie'					=> $obj->document_serie,
				'document_voucher_name'				=> $obj->document_voucher_name,
				'document_voucher_number'			=> $obj->document_voucher_number,
				'details'							=> $obj->details,
			];

			try {
				$client->sendBill($body_block);
				$client_response = $client->__getLastResponse();
				$client_response = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $client_response);
				$xml = new SimpleXMLElement($client_response);
				$body = $xml->xpath('//soapBody')[0];
				$array = json_decode(json_encode((array)$body), TRUE);
				$xmlCdr = base64_decode($array['ns2sendBillResponse']['applicationResponse']);

				/*
				 * Crear el Zip de Respuesta
				 */
				Storage::disk('public')->put($nombre_ruta_rspta, $xmlCdr);

				$pdf = PDF::loadView('backend.pdf_view', compact('data'));
				if ( !file_exists($nombre_ruta .'/pdf') ) {
					mkdir($nombre_ruta .'/pdf/', 0777, true);
				}
				$pdf->save($nombre_ruta_pdf);

				$company = Company::where('id', $obj->company_id)
	                ->select('database_name')
	                ->first();

				$query = DB::connection($company->database_name)
	                ->table('FacturacionMarket')
	                ->where('TipoDocumento', $obj->document_voucher_reference)
	                ->where('NumSerie', $obj->document_serie_number)
	                ->where('NumeroDocumento', $obj->document_voucher_number)
	                ->where('Estado', 1)
	                ->update(['flagOse' => 1]);

				$response_obj = new \stdClass();
				$response_obj->document_serie = $obj->document_serie;
				$response_obj->document_voucher_number = $obj->document_voucher_number;
				$response_obj->response_code = 0;
				$response_obj->response_text = $obj->document_voucher_name.' enviada correctamente';

				$response[] = $response_obj;

				if ( $obj->document_voucher_type != '03' ) {
					Mail::to(env('BILLING_ADDRESS_DESTINATION_EMAIL'))->queue(new VoucherMail($obj));

					if ( $obj->client_email ) {
						Mail::to($obj->client_email)->cc(env('BILLING_ADDRESS_DESTINATION_EMAIL'))->queue(new VoucherMail($obj));
					} else {
						Mail::to(env('BILLING_ADDRESS_DESTINATION_EMAIL'))->queue(new VoucherMail($obj));
					}
				}

			} catch (SoapFault $e) {
				if ( $e->faultstring >= 2000 && $e->faultstring <= 2099 ) {
					$query = DB::connection($company->database_name)
		                ->table('DetalleFacturacionMarket')
		                ->where('TipoDocumento', $obj->document_voucher_reference)
		                ->where('NumSerie', $obj->document_serie_number)
		                ->where('NumeroDocumento', $obj->document_voucher_number)
		                ->where('Estado', 1)
		                ->update(['flagOse' => $e->faultstring]);
				}
				$response_obj = new \stdClass();
				$response_obj->document_serie = $obj->document_serie;
				$response_obj->document_voucher_number = $obj->document_voucher_number;
				$response_obj->response_code = $e->faultstring;
				$response_obj->response_text = $e->detail->message;

				$response[] = $response_obj;
				// print_r($e->faultstring);
				// print_r($e->detail->message);
			}
		}

		return $response;
	}
}