<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2" xmlns:biz="urn:bizlinks:names:specification:ubl:peru:schema:xsd:BizlinksAggregateComponents-1" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2" xmlns:qdt="urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2" xmlns:sac="urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1">
    <ext:UBLExtensions>
        <ext:UBLExtension>
            <ext:ExtensionContent />
        </ext:UBLExtension>
        <ext:UBLExtension>
            <cbc:ID>EBIZ</cbc:ID>
            <ext:ExtensionContent>
                <biz:AdditionalInformation/>
            </ext:ExtensionContent>
        </ext:UBLExtension>
    </ext:UBLExtensions>
    <cbc:UBLVersionID>2.1</cbc:UBLVersionID>
    <cbc:CustomizationID schemeAgencyName="PE:SUNAT">2.0</cbc:CustomizationID>
    <cbc:ID><?php echo e($obj->serie_number); ?>-<?php echo e($obj->voucher_number); ?></cbc:ID>
    <cbc:IssueDate><?php echo e($obj->issue_date); ?></cbc:IssueDate>
    <cbc:IssueTime><?php echo e($obj->issue_hour); ?></cbc:IssueTime>
    <?php if( $obj->expiry_date ): ?>
        <cbc:DueDate><?php echo e($obj->expiry_date); ?></cbc:DueDate>
    <?php endif; ?>
    <cbc:InvoiceTypeCode listAgencyName="PE:SUNAT" listID="<?php echo e(( $obj->igv_perception > 0 ? '2001' : '0101')); ?>" listName="Tipo de Documento" listSchemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo51" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo01" name="Tipo de Operacion"><?php echo e($obj->voucher_type_type); ?></cbc:InvoiceTypeCode>
    <cbc:Note languageLocaleID="1000">SON : <?php echo e($obj->total_text); ?></cbc:Note>
    <?php if( $obj->igv_perception > 0 ): ?>
        <cbc:Note languageLocaleID="2000">COMPROBANTE DE PERCEPCIÓN</cbc:Note>
    <?php endif; ?>
    <cbc:DocumentCurrencyCode listAgencyName="United Nations Economic Commission for Europe" listID="ISO 4217 Alpha" listName="Currency"><?php echo e($obj->currency_short_name); ?></cbc:DocumentCurrencyCode>
    <cac:Signature>
        <cbc:ID><?php echo e($obj->company_document_number); ?></cbc:ID>
        <cac:SignatoryParty>
            <cac:PartyIdentification>
                <cbc:ID><?php echo e($obj->company_document_number); ?></cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyName>
                <cbc:Name><![CDATA[<?php echo e($obj->company_name); ?>]]></cbc:Name>
            </cac:PartyName>
        </cac:SignatoryParty>
        <cac:DigitalSignatureAttachment>
            <cac:ExternalReference>
                <cbc:URI>#SIGN-PDD</cbc:URI>
            </cac:ExternalReference>
        </cac:DigitalSignatureAttachment>
    </cac:Signature>
    <cac:AccountingSupplierParty>
        <cac:Party>
            <cac:PartyIdentification>
                <cbc:ID schemeAgencyName="PE:SUNAT" schemeID="6" schemeName="Documento de Identidad" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06"><?php echo e($obj->company_document_number); ?></cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyName>
                <cbc:Name><![CDATA[<?php echo e($obj->company_name); ?>]]></cbc:Name>
            </cac:PartyName>
            <cac:PartyLegalEntity>
                <cbc:RegistrationName><![CDATA[<?php echo e($obj->company_name); ?>]]></cbc:RegistrationName>
                <cac:RegistrationAddress>
                    <cbc:ID schemeAgencyName="PE:INEI" schemeName="Ubigeos"><?php echo e($obj->company_ubigeo); ?></cbc:ID>
                    <cbc:AddressTypeCode listAgencyName="PE:SUNAT" listName="Establecimientos anexos">0000</cbc:AddressTypeCode>
                    <cbc:CitySubdivisionName><![CDATA[-]]></cbc:CitySubdivisionName>
                    <cbc:CityName><![CDATA[<?php echo e($obj->company_department); ?>]]></cbc:CityName>
                    <cbc:CountrySubentity><![CDATA[<?php echo e($obj->company_province); ?>]]></cbc:CountrySubentity>
                    <cbc:District><![CDATA[<?php echo e($obj->company_district); ?>]]></cbc:District>
                    <cac:AddressLine>
                        <cbc:Line><![CDATA[<?php echo e($obj->company_address); ?>]]></cbc:Line>
                    </cac:AddressLine>
                    <cac:Country>
                        <cbc:IdentificationCode listAgencyName="United Nations Economic Commission for Europe" listID="ISO 3166-1" listName="Country">PE</cbc:IdentificationCode>
                    </cac:Country>
                </cac:RegistrationAddress>
            </cac:PartyLegalEntity>
        </cac:Party>
    </cac:AccountingSupplierParty>
    <cac:AccountingCustomerParty>
        <cac:Party>
            <cac:PartyIdentification>
                <cbc:ID schemeAgencyName="PE:SUNAT" schemeID="6" schemeName="Documento de Identidad" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06"><?php echo e($obj->client_document_number); ?></cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyLegalEntity>
                <cbc:RegistrationName><![CDATA[<?php echo e($obj->client_name); ?>]]></cbc:RegistrationName>
                <cac:RegistrationAddress>
                	<cac:AddressLine>
                		<cbc:Line><![CDATA[<?php echo e($obj->client_address); ?>]]></cbc:Line>
                	</cac:AddressLine>
                	<cac:Country>
                		<cbc:IdentificationCode listAgencyName="United Nations Economic Commission for Europe" listID="ISO 3166-1" listName="Country">PE</cbc:IdentificationCode>
                	</cac:Country>
                </cac:RegistrationAddress>
            </cac:PartyLegalEntity>
        </cac:Party>
    </cac:AccountingCustomerParty>
    <?php if( $obj->igv_perception > 0 ): ?>
    <cac:PaymentTerms>
        <cbc:ID>Percepcion</cbc:ID>
        <cbc:Amount currencyID="PEN"><?php echo e($obj->total_perception); ?></cbc:Amount>
    </cac:PaymentTerms>
    <?php endif; ?>
    <cac:PaymentTerms>
        <cbc:ID>FormaPago</cbc:ID>
        <?php if( $obj->payment_id == 1 ): ?>
        <cbc:PaymentMeansID>Contado</cbc:PaymentMeansID>
        <?php else: ?>
        <cbc:PaymentMeansID>Credito</cbc:PaymentMeansID>
        <cbc:Amount currencyID="<?php echo e($obj->currency_short_name); ?>"><?php echo e($obj->total); ?></cbc:Amount>
        <?php endif; ?>
    </cac:PaymentTerms>
    <?php if( $obj->payment_id == 2 ): ?>
    <cac:PaymentTerms>
        <cbc:ID>FormaPago</cbc:ID>
        <cbc:PaymentMeansID>Cuota001</cbc:PaymentMeansID>
        <cbc:Amount currencyID="<?php echo e($obj->currency_short_name); ?>"><?php echo e($obj->total); ?></cbc:Amount>
        <cbc:PaymentDueDate><?php echo e($obj->payment_due_date); ?></cbc:PaymentDueDate>
    </cac:PaymentTerms>
    <?php endif; ?>
    <?php if( $obj->igv_perception > 0 ): ?>
    <cac:AllowanceCharge>
        <cbc:ChargeIndicator>true</cbc:ChargeIndicator>
        <cbc:AllowanceChargeReasonCode>51</cbc:AllowanceChargeReasonCode>
        <cbc:MultiplierFactorNumeric><?php echo e(number_format($obj->igv_perception_percentage, 4, '.', '')); ?></cbc:MultiplierFactorNumeric>
        <cbc:Amount currencyID="PEN"><?php echo e($obj->igv_perception); ?></cbc:Amount>
        <cbc:BaseAmount currencyID="PEN"><?php echo e($obj->total); ?></cbc:BaseAmount>
    </cac:AllowanceCharge>
    <?php endif; ?>
    <cac:TaxTotal>
        <cbc:TaxAmount currencyID="<?php echo e($obj->currency_short_name); ?>"><?php echo e($obj->igv); ?></cbc:TaxAmount>
        <cac:TaxSubtotal>
            <cbc:TaxableAmount currencyID="<?php echo e($obj->currency_short_name); ?>"><?php echo e($obj->taxed_operation); ?></cbc:TaxableAmount>
            <cbc:TaxAmount currencyID="<?php echo e($obj->currency_short_name); ?>"><?php echo e($obj->igv); ?></cbc:TaxAmount>
            <cac:TaxCategory>
                <cac:TaxScheme>
                    <cbc:ID schemeAgencyName="PE:SUNAT" schemeName="Codigo de tributos" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo05">1000</cbc:ID>
                    <cbc:Name><![CDATA[IGV]]></cbc:Name>
                    <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                </cac:TaxScheme>
            </cac:TaxCategory>
        </cac:TaxSubtotal>
    </cac:TaxTotal>
    <cac:LegalMonetaryTotal>
        <cbc:LineExtensionAmount currencyID="<?php echo e($obj->currency_short_name); ?>"><?php echo e($obj->taxed_operation); ?></cbc:LineExtensionAmount>
        <cbc:TaxInclusiveAmount currencyID="<?php echo e($obj->currency_short_name); ?>"><?php echo e($obj->total); ?></cbc:TaxInclusiveAmount>
        <cbc:PayableAmount currencyID="<?php echo e($obj->currency_short_name); ?>"><?php echo e($obj->total); ?></cbc:PayableAmount>
    </cac:LegalMonetaryTotal>
    <?php $__currentLoopData = $obj->voucher_details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <cac:InvoiceLine>
            <cbc:ID><?php echo e(++$index); ?></cbc:ID>
            <cbc:InvoicedQuantity unitCode="<?php echo e($detail->unit_short_name); ?>" unitCodeListAgencyName="United Nations Economic Commission for Europe" unitCodeListID="UN/ECE rec 20"><?php echo e(number_format($detail->quantity, 2, '.', '')); ?></cbc:InvoicedQuantity>
            <cbc:LineExtensionAmount currencyID="<?php echo e($obj->currency_short_name); ?>"><?php echo e($detail->subtotal); ?></cbc:LineExtensionAmount>
            <cac:PricingReference>
                <cac:AlternativeConditionPrice>
                    <cbc:PriceAmount currencyID="<?php echo e($obj->currency_short_name); ?>"><?php echo e(number_format($detail->sale_value, 2, '.', '')); ?></cbc:PriceAmount>
                    <cbc:PriceTypeCode listAgencyName="PE:SUNAT" listName="Tipo de Precio" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo16">01</cbc:PriceTypeCode>
                </cac:AlternativeConditionPrice>
            </cac:PricingReference>
            <cac:TaxTotal>
                <cbc:TaxAmount currencyID="<?php echo e($obj->currency_short_name); ?>"><?php echo e($detail->igv); ?></cbc:TaxAmount>
                <cac:TaxSubtotal>
                    <cbc:TaxableAmount currencyID="<?php echo e($obj->currency_short_name); ?>"><?php echo e($detail->subtotal); ?></cbc:TaxableAmount>
                    <cbc:TaxAmount currencyID="<?php echo e($obj->currency_short_name); ?>"><?php echo e($detail->igv); ?></cbc:TaxAmount>
                    <cac:TaxCategory>
                        <cbc:Percent><?php echo e(number_format($detail->igv_percentage, 2, '.', '')); ?></cbc:Percent>
                        <cbc:TaxExemptionReasonCode listAgencyName="PE:SUNAT" listName="Afectacion del IGV" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo07">10</cbc:TaxExemptionReasonCode>
                        <cac:TaxScheme>
                            <cbc:ID schemeAgencyName="PE:SUNAT" schemeName="Codigo de tributos" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo05">1000</cbc:ID>
                            <cbc:Name><![CDATA[IGV]]></cbc:Name>
                            <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                        </cac:TaxScheme>
                    </cac:TaxCategory>
                </cac:TaxSubtotal>
            </cac:TaxTotal>
            <cac:Item>
                <cbc:Description><![CDATA[<?php echo e($detail->name); ?>]]></cbc:Description>
                <cac:SellersItemIdentification>
                    <cbc:ID>P<?php echo e(sprintf('%03d', $index)); ?></cbc:ID>
                </cac:SellersItemIdentification>
            </cac:Item>
            <cac:Price>
                <cbc:PriceAmount currencyID="<?php echo e($obj->currency_short_name); ?>"><?php echo e(number_format($detail->unit_price, 10, '.', '')); ?></cbc:PriceAmount>
            </cac:Price>
        </cac:InvoiceLine>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</Invoice>
<?php /**PATH /var/www/pdd/resources/views/backend/xml/factura_gravada.blade.php ENDPATH**/ ?>