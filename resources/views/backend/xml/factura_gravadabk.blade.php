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
    <cbc:ID>{{ $obj->serie_number }}-{{ $obj->voucher_number }}</cbc:ID>
    <cbc:IssueDate>{{ $obj->issue_date }}</cbc:IssueDate>
    <cbc:IssueTime>{{ $obj->issue_hour }}</cbc:IssueTime>
    @if ( $obj->expiry_date )
        <cbc:DueDate>{{ $obj->expiry_date }}</cbc:DueDate>
    @endif
    <cbc:InvoiceTypeCode listAgencyName="PE:SUNAT" listID="{{ ( $obj->igv_perception > 0 ? '2001' : '0101') }}" listName="Tipo de Documento" listSchemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo51" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo01" name="Tipo de Operacion">{{ $obj->voucher_type_type }}</cbc:InvoiceTypeCode>
    <cbc:Note languageLocaleID="1000">SON : {{ $obj->total_text }}</cbc:Note>
    @if ( $obj->igv_perception > 0 )
        <cbc:Note languageLocaleID="2000">COMPROBANTE DE PERCEPCIÓN</cbc:Note>
    @endif
    <cbc:DocumentCurrencyCode listAgencyName="United Nations Economic Commission for Europe" listID="ISO 4217 Alpha" listName="Currency">{{ $obj->currency_short_name }}</cbc:DocumentCurrencyCode>
    <cac:Signature>
        <cbc:ID>{{ $obj->company_document_number }}</cbc:ID>
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
                <cbc:ID schemeAgencyName="PE:SUNAT" schemeID="6" schemeName="Documento de Identidad" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">{{ $obj->company_document_number }}</cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyName>
                <cbc:Name><![CDATA[{{ $obj->company_name }}]]></cbc:Name>
            </cac:PartyName>
            <cac:PartyLegalEntity>
                <cbc:RegistrationName><![CDATA[{{ $obj->company_name }}]]></cbc:RegistrationName>
                <cac:RegistrationAddress>
                    <cbc:ID schemeAgencyName="PE:INEI" schemeName="Ubigeos">{{ $obj->company_ubigeo }}</cbc:ID>
                    <cbc:AddressTypeCode listAgencyName="PE:SUNAT" listName="Establecimientos anexos">0000</cbc:AddressTypeCode>
                    <cbc:CitySubdivisionName><![CDATA[-]]></cbc:CitySubdivisionName>
                    <cbc:CityName><![CDATA[{{ $obj->company_department }}]]></cbc:CityName>
                    <cbc:CountrySubentity><![CDATA[{{ $obj->company_province }}]]></cbc:CountrySubentity>
                    <cbc:District><![CDATA[{{ $obj->company_district }}]]></cbc:District>
                    <cac:AddressLine>
                        <cbc:Line><![CDATA[{{ $obj->company_address }}]]></cbc:Line>
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
                <cbc:ID schemeAgencyName="PE:SUNAT" schemeID="6" schemeName="Documento de Identidad" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">{{ $obj->client_document_number }}</cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyLegalEntity>
                <cbc:RegistrationName><![CDATA[{{ $obj->client_name }}]]></cbc:RegistrationName>
                <cac:RegistrationAddress>
                	<cac:AddressLine>
                		<cbc:Line><![CDATA[{{ $obj->client_address }}]]></cbc:Line>
                	</cac:AddressLine>
                	<cac:Country>
                		<cbc:IdentificationCode listAgencyName="United Nations Economic Commission for Europe" listID="ISO 3166-1" listName="Country">PE</cbc:IdentificationCode>
                	</cac:Country>
                </cac:RegistrationAddress>
            </cac:PartyLegalEntity>
        </cac:Party>
    </cac:AccountingCustomerParty>
    @if ( $obj->igv_perception > 0 )
    <cac:PaymentTerms>
        <cbc:ID>Percepcion</cbc:ID>
        <cbc:Amount currencyID="PEN">{{ $obj->total_perception }}</cbc:Amount>
    </cac:PaymentTerms>
    @endif
    <cac:PaymentTerms>
        <cbc:ID>FormaPago</cbc:ID>
        @if ( $obj->payment_id == 1 )
        <cbc:PaymentMeansID>Contado</cbc:PaymentMeansID>
        @else
        <cbc:PaymentMeansID>Credito</cbc:PaymentMeansID>
        <cbc:Amount currencyID="{{ $obj->currency_short_name }}">{{ $obj->total }}</cbc:Amount>
        @endif
    </cac:PaymentTerms>
    @if ( $obj->payment_id == 2 )
    <cac:PaymentTerms>
        <cbc:ID>FormaPago</cbc:ID>
        <cbc:PaymentMeansID>Cuota001</cbc:PaymentMeansID>
        <cbc:Amount currencyID="{{ $obj->currency_short_name }}">{{ $obj->total }}</cbc:Amount>
        <cbc:PaymentDueDate>{{ $obj->payment_due_date }}</cbc:PaymentDueDate>
    </cac:PaymentTerms>
    @endif
    @if ( $obj->igv_perception > 0 )
    <cac:AllowanceCharge>
        <cbc:ChargeIndicator>true</cbc:ChargeIndicator>
        <cbc:AllowanceChargeReasonCode>51</cbc:AllowanceChargeReasonCode>
        <cbc:MultiplierFactorNumeric>{{ number_format($obj->igv_perception_percentage, 2, '.', '') }}</cbc:MultiplierFactorNumeric>
        <cbc:Amount currencyID="PEN">{{ $obj->igv_perception }}</cbc:Amount>
        <cbc:BaseAmount currencyID="PEN">{{ $obj->total }}</cbc:BaseAmount>
    </cac:AllowanceCharge>
    @endif
    <cac:TaxTotal>
        <cbc:TaxAmount currencyID="{{ $obj->currency_short_name }}">{{ $obj->igv }}</cbc:TaxAmount>
        <cac:TaxSubtotal>
            <cbc:TaxableAmount currencyID="{{ $obj->currency_short_name }}">{{ $obj->taxed_operation }}</cbc:TaxableAmount>
            <cbc:TaxAmount currencyID="{{ $obj->currency_short_name }}">{{ $obj->igv }}</cbc:TaxAmount>
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
        <cbc:LineExtensionAmount currencyID="{{ $obj->currency_short_name }}">{{ $obj->taxed_operation }}</cbc:LineExtensionAmount>
        <cbc:TaxInclusiveAmount currencyID="{{ $obj->currency_short_name }}">{{ $obj->total }}</cbc:TaxInclusiveAmount>
        <cbc:PayableAmount currencyID="{{ $obj->currency_short_name }}">{{ $obj->total }}</cbc:PayableAmount>
    </cac:LegalMonetaryTotal>
    @foreach ($obj->voucher_details as $index => $detail)
        <cac:InvoiceLine>
            <cbc:ID>{{ ++$index }}</cbc:ID>
            <cbc:InvoicedQuantity unitCode="{{ $detail->unit_short_name }}" unitCodeListAgencyName="United Nations Economic Commission for Europe" unitCodeListID="UN/ECE rec 20">{{ number_format($detail->quantity, 2, '.', '') }}</cbc:InvoicedQuantity>
            <cbc:LineExtensionAmount currencyID="{{ $obj->currency_short_name }}">{{ $detail->subtotal }}</cbc:LineExtensionAmount>
            <cac:PricingReference>
                <cac:AlternativeConditionPrice>
                    <cbc:PriceAmount currencyID="{{ $obj->currency_short_name }}">{{ number_format($detail->sale_value, 2, '.', '') }}</cbc:PriceAmount>
                    <cbc:PriceTypeCode listAgencyName="PE:SUNAT" listName="Tipo de Precio" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo16">01</cbc:PriceTypeCode>
                </cac:AlternativeConditionPrice>
            </cac:PricingReference>
            <cac:TaxTotal>
                <cbc:TaxAmount currencyID="{{ $obj->currency_short_name }}">{{ $detail->igv }}</cbc:TaxAmount>
                <cac:TaxSubtotal>
                    <cbc:TaxableAmount currencyID="{{ $obj->currency_short_name }}">{{ $detail->subtotal }}</cbc:TaxableAmount>
                    <cbc:TaxAmount currencyID="{{ $obj->currency_short_name }}">{{ $detail->igv }}</cbc:TaxAmount>
                    <cac:TaxCategory>
                        <cbc:Percent>{{ number_format($detail->igv_percentage, 2, '.', '') }}</cbc:Percent>
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
                <cbc:Description><![CDATA[{{ $detail->name }}]]></cbc:Description>
                <cac:SellersItemIdentification>
                    <cbc:ID>P{{ sprintf('%03d', $index) }}</cbc:ID>
                </cac:SellersItemIdentification>
            </cac:Item>
            <cac:Price>
                <cbc:PriceAmount currencyID="{{ $obj->currency_short_name }}">{{ number_format($detail->unit_price, 2, '.', '') }}</cbc:PriceAmount>
            </cac:Price>
        </cac:InvoiceLine>
    @endforeach
</Invoice>
