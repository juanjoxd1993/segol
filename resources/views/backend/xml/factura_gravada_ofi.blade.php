<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2" 
xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" 
xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" 
xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2">
<cbc:UBLVersionID>2.1</cbc:UBLVersionID>
<cbc:CustomizationID>2.0</cbc:CustomizationID>
<cbc:ID>{{ $obj->serie_number }}-{{ $obj->voucher_number }}</cbc:ID>
<cbc:IssueDate>{{ $obj->issue_date }}</cbc:IssueDate>
@if ($obj->expiry_date)
        <cbc:DueDate>{{ $obj->expiry_date }}</cbc:DueDate>
@endif
<cbc:InvoiceTypeCode listID="{{ $obj->igv_perception > 0 ? '2001' : '0101' }}" listAgencyName="PE:SUNAT" listSchemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo51" name="Tipo de Operacion">01</cbc:InvoiceTypeCode>
<cbc:Note languageLocaleID="1000">{{ $obj->total_text }}</cbc:Note>

@if ($obj->payment_id == 1)
    <cbc:Note languageLocaleID="2000">COMPROBANTE DE PERCEPCIÓN</cbc:Note>
@endif

<cbc:DocumentCurrencyCode listAgencyName="United Nations Economic Commission for Europe" listID="ISO 4217 Alpha" listName="Currency">{{ $obj->currency_short_name }}</cbc:DocumentCurrencyCode>
<cbc:LineCountNumeric>1</cbc:LineCountNumeric>
<cac:Signature>
<cbc:ID>IDSignKG</cbc:ID>
<cac:SignatoryParty>
<cac:PartyIdentification>
<cbc:ID>{{ $obj->company_document_number }}</cbc:ID>
</cac:PartyIdentification>
<cac:PartyName>
<cbc:Name>{{ $obj->company_name }}</cbc:Name>
</cac:PartyName>
</cac:SignatoryParty>
<cac:DigitalSignatureAttachment>
<cac:ExternalReference>
<cbc:URI>#SignST</cbc:URI>
</cac:ExternalReference>
</cac:DigitalSignatureAttachment>
</cac:Signature>
<cac:AccountingSupplierParty>
<cac:Party>
<cac:PartyIdentification>
<cbc:ID schemeAgencyName="PE:SUNAT" schemeID="6" schemeName="Registro Unico de Contribuyentes" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">{{ $obj->company_document_number }}</cbc:ID>
</cac:PartyIdentification>
<cac:PartyName>
<cbc:Name>{{ $obj->company_name }}</cbc:Name>
</cac:PartyName>
<cac:PartyLegalEntity>
<cbc:RegistrationName>{{ $obj->company_name }}</cbc:RegistrationName>
<cac:RegistrationAddress>
<cbc:ID schemeAgencyName="PE:INEI" schemeName="Ubigeos">{{ $obj->company_ubigeo }}</cbc:ID>
<cbc:AddressTypeCode listAgencyName="PE:SUNAT" listName="Establecimientos anexos">0000</cbc:AddressTypeCode>
<cbc:CitySubdivisionName>URBANIZACION</cbc:CitySubdivisionName>
<cbc:CityName>{{ $obj->company_department }}</cbc:CityName>
<cbc:CountrySubentity>{{ $obj->company_province }}</cbc:CountrySubentity>
<cbc:District>{{ $obj->company_district }}</cbc:District>
<cac:AddressLine>
<cbc:Line>{{ $obj->company_address }}</cbc:Line>
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
<cac:PartyName>
<cbc:Name>{{ $obj->client_name }}</cbc:Name>
</cac:PartyName>
<cac:PartyLegalEntity>
<cbc:RegistrationName>{{ $obj->client_name }}</cbc:RegistrationName>
<cac:RegistrationAddress>
<cbc:ID schemeAgencyName="PE:INEI" schemeName="Ubigeos">{{ $obj->client_ubigeo }}</cbc:ID>
<cbc:CitySubdivisionName>URBANIZACION</cbc:CitySubdivisionName>
<cbc:CityName>{{ $obj->client_department }}</cbc:CityName>
<cbc:CountrySubentity>{{ $obj->client_province }}</cbc:CountrySubentity>
<cbc:District>{{ $obj->client_district }}</cbc:District>
<cac:AddressLine>
<cbc:Line>{{ $obj->client_address }}</cbc:Line>
</cac:AddressLine>
<cac:Country>
<cbc:IdentificationCode listAgencyName="United Nations Economic Commission for Europe" listID="ISO 3166-1" listName="Country">PE</cbc:IdentificationCode>
</cac:Country>
</cac:RegistrationAddress>
</cac:PartyLegalEntity>
<cac:Contact>
<cbc:ElectronicMail>enviofacturacion@puntodedistribucion.com</cbc:ElectronicMail>
</cac:Contact>
</cac:Party>
</cac:AccountingCustomerParty>

@if ($obj->payment_id == 1 && $obj->igv_perception > 0 && $obj->company_id == 1)
    <cac:PaymentTerms>
        <cbc:ID>Percepcion</cbc:ID>
        <cbc:Amount currencyID="{{ $obj->currency_short_name }}">{{ $obj->total_perception }}</cbc:Amount>
    </cac:PaymentTerms>

    <cac:AllowanceCharge>
            <cbc:ChargeIndicator>true</cbc:ChargeIndicator>
            <cbc:AllowanceChargeReasonCode listAgencyName="PE:SUNAT" listName="Cargo/descuento"
                listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo53">51</cbc:AllowanceChargeReasonCode>
            <cbc:MultiplierFactorNumeric>{{ $obj->igv_perception_percentage }}</cbc:MultiplierFactorNumeric>
            <cbc:Amount currencyID="{{ $obj->currency_short_name }}">{{ $obj->igv_perception }}</cbc:Amount>
            <cbc:BaseAmount currencyID="{{ $obj->currency_short_name }}">{{ $obj->total }}</cbc:BaseAmount>
    </cac:AllowanceCharge>
@endif

<cac:PaymentTerms>
    <cbc:ID>FormaPago</cbc:ID>
    @if ($obj->payment_id == 1)
        <cbc:PaymentMeansID>Contado</cbc:PaymentMeansID>
    @else
        <cbc:PaymentMeansID>Credito</cbc:PaymentMeansID>
        <cbc:Amount currencyID="{{ $obj->currency_short_name }}">{{ $obj->total }}</cbc:Amount>
    @endif
</cac:PaymentTerms>


@if ($obj->payment_id == 2)
    <cac:PaymentTerms>
        <cbc:ID>FormaPago</cbc:ID>
        <cbc:PaymentMeansID>Cuota001</cbc:PaymentMeansID>
        <cbc:Amount currencyID="{{ $obj->currency_short_name }}">{{ $obj->total }}</cbc:Amount>
        <cbc:PaymentDueDate>{{ $obj->expiry_date }}</cbc:PaymentDueDate>
    </cac:PaymentTerms>
@endif

<cac:TaxTotal>
<cbc:TaxAmount currencyID="{{ $obj->currency_short_name }}">{{ $obj->igv }}</cbc:TaxAmount>
<cac:TaxSubtotal>
<cbc:TaxableAmount currencyID="{{ $obj->currency_short_name }}">{{ number_format($obj->taxed_operation, 2, '.', '') }}</cbc:TaxableAmount>
<cbc:TaxAmount currencyID="{{ $obj->currency_short_name }}">{{ $obj->igv }}</cbc:TaxAmount>
<cac:TaxCategory>
<cac:TaxScheme>
<cbc:ID schemeAgencyID="6" schemeID="UN/ECE 5153">1000</cbc:ID>
<cbc:Name>IGV</cbc:Name>
<cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
</cac:TaxScheme>
</cac:TaxCategory>
</cac:TaxSubtotal>
</cac:TaxTotal>
<cac:LegalMonetaryTotal>
<cbc:LineExtensionAmount currencyID="{{ $obj->currency_short_name }}">{{ number_format($obj->taxed_operation, 2, '.', '') }}</cbc:LineExtensionAmount>
<cbc:TaxInclusiveAmount currencyID="{{ $obj->currency_short_name }}">{{ $obj->total }}</cbc:TaxInclusiveAmount>
<cbc:PayableAmount currencyID="{{ $obj->currency_short_name }}">{{ $obj->total }}</cbc:PayableAmount>
</cac:LegalMonetaryTotal>

@foreach ($obj->voucher_details as $index => $detail)
    <cac:InvoiceLine>
    <cbc:ID>{{ ++$index }}</cbc:ID>
    <cbc:Note>UNIDAD</cbc:Note>
    <cbc:InvoicedQuantity unitCode="ZZ" unitCodeListAgencyName="United Nations Economic Commission for Europe" unitCodeListID="UN/ECE rec 20">{{ $detail->quantity }}</cbc:InvoicedQuantity>
    <cbc:LineExtensionAmount currencyID="{{ $obj->currency_short_name }}">{{ $detail->subtotal }}</cbc:LineExtensionAmount>
    <cac:PricingReference>
    <cac:AlternativeConditionPrice>
    <cbc:PriceAmount currencyID="{{ $obj->currency_short_name }}">{{ $detail->sale_value }}</cbc:PriceAmount>
    <cbc:PriceTypeCode listAgencyName="PE:SUNAT" listName="Tipo de Precio" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo16">01</cbc:PriceTypeCode>
    </cac:AlternativeConditionPrice>
    </cac:PricingReference>
    <cac:TaxTotal>
    <cbc:TaxAmount currencyID="{{ $obj->currency_short_name }}">{{ $detail->igv }}</cbc:TaxAmount>
    <cac:TaxSubtotal>
    <cbc:TaxableAmount currencyID="{{ $obj->currency_short_name }}">{{ $detail->subtotal }}</cbc:TaxableAmount>
    <cbc:TaxAmount currencyID="{{ $obj->currency_short_name }}">{{ $detail->igv }}</cbc:TaxAmount>
    <cac:TaxCategory>
    <cbc:Percent>{{ $detail->igv_percentage }}</cbc:Percent>
    <cbc:TaxExemptionReasonCode listAgencyName="PE:SUNAT" listName="Afectacion del IGV" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo07">10</cbc:TaxExemptionReasonCode>
    <cac:TaxScheme>
    <cbc:ID schemeAgencyName="PE:SUNAT" schemeName="Codigo de tributos" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo05">1000</cbc:ID>
    <cbc:Name>IGV</cbc:Name>
    <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
    </cac:TaxScheme>
    </cac:TaxCategory>
    </cac:TaxSubtotal>
    </cac:TaxTotal>
    <cac:Item>
    <cbc:Description>{{ $detail->name }}</cbc:Description>
    <cac:SellersItemIdentification>
    <cbc:ID>P{{ sprintf('%03d', $index) }}</cbc:ID>
    </cac:SellersItemIdentification>
    </cac:Item>
    <cac:Price>
    <cbc:PriceAmount currencyID="{{ $obj->currency_short_name }}">{{ $detail->unit_price }}</cbc:PriceAmount>
    </cac:Price>
    </cac:InvoiceLine>
@endforeach
</Invoice>