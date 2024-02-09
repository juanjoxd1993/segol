<?xml version="1.0" encoding="UTF-8"?>
<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2">
    <ext:UBLExtensions>
        <ext:UBLExtension>
            <ext:ExtensionContent>
            </ext:ExtensionContent>
        </ext:UBLExtension>
    </ext:UBLExtensions>
    <cbc:UBLVersionID>2.1</cbc:UBLVersionID>
    <cbc:CustomizationID>2.0</cbc:CustomizationID>
    <cbc:ID><?php echo e($obj->serie_number); ?>-<?php echo e($obj->voucher_number); ?></cbc:ID>
    <cbc:IssueDate><?php echo e($obj->issue_date); ?></cbc:IssueDate>
    <cbc:IssueTime><?php echo e($obj->issue_hour); ?></cbc:IssueTime>
    <cbc:InvoiceTypeCode listID="0101"><?php echo e($obj->voucher_type_type); ?></cbc:InvoiceTypeCode>
    <cbc:Note languageLocaleID="1000"><![CDATA[SON <?php echo e($obj->total_text); ?>]]></cbc:Note>
    <cbc:DocumentCurrencyCode><?php echo e($obj->currency_short_name); ?></cbc:DocumentCurrencyCode>
    <cac:Signature>
        <cbc:ID><?php echo e($obj->company_document_number); ?></cbc:ID>
        <cbc:Note><?php echo e($obj->company_name); ?></cbc:Note>
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
                <cbc:ID schemeID="6"><?php echo e($obj->company_document_number); ?></cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyName>
                <cbc:Name><![CDATA[<?php echo e($obj->company_name); ?>]]></cbc:Name>
            </cac:PartyName>
            <cac:PartyLegalEntity>
                <cbc:RegistrationName><![CDATA[<?php echo e($obj->company_name); ?>]]></cbc:RegistrationName>
                <cac:RegistrationAddress>
                    <cbc:ID><?php echo e($obj->company_ubigeo); ?></cbc:ID>
                    <cbc:AddressTypeCode>0000</cbc:AddressTypeCode>
                    <cbc:CitySubdivisionName>-</cbc:CitySubdivisionName>
                    <cbc:CityName><?php echo e($obj->company_department); ?></cbc:CityName>
                    <cbc:CountrySubentity><?php echo e($obj->company_province); ?></cbc:CountrySubentity>
                    <cbc:District><?php echo e($obj->company_district); ?></cbc:District>
                    <cac:AddressLine>
                        <cbc:Line><![CDATA[<?php echo e($obj->company_address); ?>]]></cbc:Line>
                    </cac:AddressLine>
                    <cac:Country>
                        <cbc:IdentificationCode>PE</cbc:IdentificationCode>
                    </cac:Country>
                </cac:RegistrationAddress>
            </cac:PartyLegalEntity>
        </cac:Party>
    </cac:AccountingSupplierParty>
    <cac:AccountingCustomerParty>
        <cac:Party>
            <cac:PartyIdentification>
                <cbc:ID schemeID="1"><?php echo e($obj->client_document_number); ?></cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyLegalEntity>
                <cbc:RegistrationName><![CDATA[<?php echo e($obj->client_name); ?>]]></cbc:RegistrationName>
            </cac:PartyLegalEntity>
        </cac:Party>
    </cac:AccountingCustomerParty>
    <cac:TaxTotal>
        <cbc:TaxAmount currencyID="<?php echo e($obj->currency_short_name); ?>"><?php echo e(number_format($obj->igv, 2, '.', '')); ?></cbc:TaxAmount>
        <cac:TaxSubtotal>
            <cbc:TaxableAmount currencyID="<?php echo e($obj->currency_short_name); ?>"><?php echo e(number_format($obj->subtotal, 2, '.', '')); ?></cbc:TaxableAmount>
            <cbc:TaxAmount currencyID="<?php echo e($obj->currency_short_name); ?>"><?php echo e(number_format($obj->igv, 2, '.', '')); ?></cbc:TaxAmount>
            <cac:TaxCategory>
                <cac:TaxScheme>
                    <cbc:ID>1000</cbc:ID>
                    <cbc:Name>IGV</cbc:Name>
                    <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                </cac:TaxScheme>
            </cac:TaxCategory>
        </cac:TaxSubtotal>
    </cac:TaxTotal>
    <cac:LegalMonetaryTotal>
        <cbc:LineExtensionAmount currencyID="<?php echo e($obj->currency_short_name); ?>"><?php echo e($obj->taxed_operation); ?></cbc:LineExtensionAmount>
        <cbc:PayableAmount currencyID="<?php echo e($obj->currency_short_name); ?>"><?php echo e($obj->total); ?></cbc:PayableAmount>
    </cac:LegalMonetaryTotal>
    <?php $__currentLoopData = $obj->voucher_details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <cac:InvoiceLine>
            <cbc:ID><?php echo e(++$index); ?></cbc:ID>
            <cbc:InvoicedQuantity unitCode="<?php echo e($detail->unit_short_name); ?>"><?php echo e($detail->quantity); ?></cbc:InvoicedQuantity>
            <cbc:LineExtensionAmount currencyID="<?php echo e($obj->currency_short_name); ?>"><?php echo e($detail->subtotal); ?></cbc:LineExtensionAmount>
            <cac:PricingReference>
                <cac:AlternativeConditionPrice>
                    <cbc:PriceAmount currencyID="<?php echo e($obj->currency_short_name); ?>"><?php echo e(number_format($detail->sale_value, 2, '.', '')); ?></cbc:PriceAmount>
                    <cbc:PriceTypeCode>01</cbc:PriceTypeCode>
                </cac:AlternativeConditionPrice>
            </cac:PricingReference>
            <cac:TaxTotal>
                <cbc:TaxAmount currencyID="<?php echo e($obj->currency_short_name); ?>"><?php echo e($detail->igv); ?></cbc:TaxAmount>
                <cac:TaxSubtotal>
                    <cbc:TaxableAmount currencyID="<?php echo e($obj->currency_short_name); ?>"><?php echo e($detail->subtotal); ?></cbc:TaxableAmount>
                    <cbc:TaxAmount currencyID="<?php echo e($obj->currency_short_name); ?>"><?php echo e($detail->igv); ?></cbc:TaxAmount>
                    <cac:TaxCategory>
                        <cbc:Percent><?php echo e(number_format($detail->igv_percentage, 2, '.', '')); ?></cbc:Percent>
                        <cbc:TaxExemptionReasonCode>10</cbc:TaxExemptionReasonCode>
                        <cac:TaxScheme>
                            <cbc:ID>1000</cbc:ID>
                            <cbc:Name>IGV</cbc:Name>
                            <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                        </cac:TaxScheme>
                    </cac:TaxCategory>
                </cac:TaxSubtotal>
            </cac:TaxTotal>
            <cac:Item>
                <cbc:Description><![CDATA[<?php echo e($detail->name); ?>]]></cbc:Description>
            </cac:Item>
            <cac:Price>
                <cbc:PriceAmount currencyID="<?php echo e($obj->currency_short_name); ?>"><?php echo e($detail->unit_price); ?></cbc:PriceAmount>
            </cac:Price>
        </cac:InvoiceLine>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</Invoice><?php /**PATH /var/www/pdd/resources/views/backend/xml/boleta.blade.php ENDPATH**/ ?>