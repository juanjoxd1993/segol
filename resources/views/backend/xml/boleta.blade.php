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
    <cbc:ID>{{ $obj->serie_number }}-{{ $obj->voucher_number }}</cbc:ID>
    <cbc:IssueDate>{{ $obj->issue_date }}</cbc:IssueDate>
    <cbc:IssueTime>{{ $obj->issue_hour }}</cbc:IssueTime>
    <cbc:InvoiceTypeCode listID="0101">{{ $obj->voucher_type_type }}</cbc:InvoiceTypeCode>
    <cbc:Note languageLocaleID="1000"><![CDATA[SON {{ $obj->total_text }}]]></cbc:Note>
    <cbc:DocumentCurrencyCode>{{ $obj->currency_short_name }}</cbc:DocumentCurrencyCode>
    <cac:Signature>
        <cbc:ID>{{ $obj->company_document_number }}</cbc:ID>
        <cbc:Note>{{ $obj->company_name }}</cbc:Note>
        <cac:SignatoryParty>
            <cac:PartyIdentification>
                <cbc:ID>{{ $obj->company_document_number }}</cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyName>
                <cbc:Name><![CDATA[{{ $obj->company_name }}]]></cbc:Name>
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
                <cbc:ID schemeID="6">{{ $obj->company_document_number }}</cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyName>
                <cbc:Name><![CDATA[{{ $obj->company_name }}]]></cbc:Name>
            </cac:PartyName>
            <cac:PartyLegalEntity>
                <cbc:RegistrationName><![CDATA[{{ $obj->company_name }}]]></cbc:RegistrationName>
                <cac:RegistrationAddress>
                    <cbc:ID>{{ $obj->company_ubigeo }}</cbc:ID>
                    <cbc:AddressTypeCode>0000</cbc:AddressTypeCode>
                    <cbc:CitySubdivisionName>-</cbc:CitySubdivisionName>
                    <cbc:CityName>{{ $obj->company_department }}</cbc:CityName>
                    <cbc:CountrySubentity>{{ $obj->company_province }}</cbc:CountrySubentity>
                    <cbc:District>{{ $obj->company_district }}</cbc:District>
                    <cac:AddressLine>
                        <cbc:Line><![CDATA[{{ $obj->company_address }}]]></cbc:Line>
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
                <cbc:ID schemeID="1">{{ $obj->client_document_number }}</cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyLegalEntity>
                <cbc:RegistrationName><![CDATA[{{ $obj->client_name }}]]></cbc:RegistrationName>
            </cac:PartyLegalEntity>
        </cac:Party>
    </cac:AccountingCustomerParty>
    <cac:TaxTotal>
        <cbc:TaxAmount currencyID="{{ $obj->currency_short_name }}">{{ number_format($obj->igv, 2, '.', '') }}</cbc:TaxAmount>
        <cac:TaxSubtotal>
            <cbc:TaxableAmount currencyID="{{ $obj->currency_short_name }}">{{ number_format($obj->subtotal, 2, '.', '') }}</cbc:TaxableAmount>
            <cbc:TaxAmount currencyID="{{ $obj->currency_short_name }}">{{ number_format($obj->igv, 2, '.', '') }}</cbc:TaxAmount>
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
        <cbc:LineExtensionAmount currencyID="{{ $obj->currency_short_name }}">{{ $obj->taxed_operation }}</cbc:LineExtensionAmount>
        <cbc:PayableAmount currencyID="{{ $obj->currency_short_name }}">{{ $obj->total }}</cbc:PayableAmount>
    </cac:LegalMonetaryTotal>
    @foreach ($obj->voucher_details as $index => $detail)
        <cac:InvoiceLine>
            <cbc:ID>{{ ++$index }}</cbc:ID>
            <cbc:InvoicedQuantity unitCode="{{ $detail->unit_short_name }}">{{ $detail->quantity }}</cbc:InvoicedQuantity>
            <cbc:LineExtensionAmount currencyID="{{ $obj->currency_short_name }}">{{ $detail->subtotal }}</cbc:LineExtensionAmount>
            <cac:PricingReference>
                <cac:AlternativeConditionPrice>
                    <cbc:PriceAmount currencyID="{{ $obj->currency_short_name }}">{{ number_format($detail->sale_value, 2, '.', '') }}</cbc:PriceAmount>
                    <cbc:PriceTypeCode>01</cbc:PriceTypeCode>
                </cac:AlternativeConditionPrice>
            </cac:PricingReference>
            <cac:TaxTotal>
                <cbc:TaxAmount currencyID="{{ $obj->currency_short_name }}">{{ $detail->igv }}</cbc:TaxAmount>
                <cac:TaxSubtotal>
                    <cbc:TaxableAmount currencyID="{{ $obj->currency_short_name }}">{{ $detail->subtotal }}</cbc:TaxableAmount>
                    <cbc:TaxAmount currencyID="{{ $obj->currency_short_name }}">{{ $detail->igv }}</cbc:TaxAmount>
                    <cac:TaxCategory>
                        <cbc:Percent>{{ number_format($detail->igv_percentage, 2, '.', '') }}</cbc:Percent>
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
                <cbc:Description><![CDATA[{{ $detail->name }}]]></cbc:Description>
            </cac:Item>
            <cac:Price>
                <cbc:PriceAmount currencyID="{{ $obj->currency_short_name }}">{{ $detail->unit_price }}</cbc:PriceAmount>
            </cac:Price>
        </cac:InvoiceLine>
    @endforeach
</Invoice>