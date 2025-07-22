<?xml version="1.0" encoding="UTF-8"?>
<CreditNote xmlns="urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2">
<cbc:UBLVersionID>2.1</cbc:UBLVersionID>
<cbc:CustomizationID>2.0</cbc:CustomizationID>
<cbc:ID>{{ $obj->serie_number }}-{{ $obj->voucher_number }}</cbc:ID>
<cbc:IssueDate>{{ $obj->issue_date }}</cbc:IssueDate>
<cbc:Note languageLocaleID="1000">{{ $obj->total_text }}</cbc:Note>
<cbc:DocumentCurrencyCode listAgencyName="United Nations Economic Commission for Europe" listID="ISO 4217 Alpha" listName="Currency">PEN</cbc:DocumentCurrencyCode>
<cbc:LineCountNumeric>1</cbc:LineCountNumeric>
<cac:DiscrepancyResponse>
<cbc:ReferenceID>{{ $obj->credit_note_reference_serie }}-{{ $obj->credit_note_reference_number }}</cbc:ReferenceID>
<cbc:ResponseCode listAgencyName="PE:SUNAT" listName="Tipo de nota de credito" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo09">{{ $obj->credit_note_reason_type }}</cbc:ResponseCode>
<cbc:Description>{{ $obj->credit_note_reason_name }}</cbc:Description>
</cac:DiscrepancyResponse>
<cac:BillingReference>
<cac:InvoiceDocumentReference>
<cbc:ID>{{ $obj->credit_note_reference_serie }}-{{ $obj->credit_note_reference_number }}</cbc:ID>
<cbc:IssueDate>{{ $obj->issue_date }}</cbc:IssueDate>
<cbc:DocumentTypeCode listAgencyName="PE:SUNAT" listName="{{ $obj->warehouse_document_types_name }}" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo01">{{ $obj->efact_serie }}</cbc:DocumentTypeCode>
</cac:InvoiceDocumentReference>
</cac:BillingReference>
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
<cbc:CityName>{{ $obj->company_province }}</cbc:CityName>
<cbc:CountrySubentity>{{ $obj->company_department }}</cbc:CountrySubentity>
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
<cbc:ID schemeAgencyName="PE:SUNAT" schemeID="{{ $obj->client_document_type }}" schemeName="{{ $obj->client_document_name }}" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">{{ $obj->client_document_number }}</cbc:ID>
</cac:PartyIdentification>
<cac:PartyName>
<cbc:Name>{{ $obj->client_name }}</cbc:Name>
</cac:PartyName>
<cac:PartyLegalEntity>
<cbc:RegistrationName>{{ $obj->client_name }}</cbc:RegistrationName>
<cac:RegistrationAddress>
<cbc:ID schemeAgencyName="PE:INEI" schemeName="Ubigeos">{{ $obj->client_ubigeo }}</cbc:ID>
<cbc:CitySubdivisionName>URBANIZACION</cbc:CitySubdivisionName>
<cbc:CityName>{{ $obj->client_province }}</cbc:CityName>
<cbc:CountrySubentity>{{ $obj->client_department }}</cbc:CountrySubentity>
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
<cbc:ElectronicMail>J.olivasc@gmail.com</cbc:ElectronicMail>
</cac:Contact>
</cac:Party>
</cac:AccountingCustomerParty>
<cac:TaxTotal>
<cbc:TaxAmount currencyID="{{ $obj->currency_short_name }}">{{ $obj->igv }}</cbc:TaxAmount>
<cac:TaxSubtotal>
<cbc:TaxableAmount currencyID="{{ $obj->currency_short_name }}">{{ number_format($obj->taxed_operation,2,'.','') }}</cbc:TaxableAmount>
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
<cbc:LineExtensionAmount currencyID="{{ $obj->currency_short_name }}">{{ number_format($obj->taxed_operation,2,'.','') }}</cbc:LineExtensionAmount>
<cbc:PayableAmount currencyID="{{ $obj->currency_short_name }}">{{ number_format($obj->total,2,'.','') }}</cbc:PayableAmount>
</cac:LegalMonetaryTotal>
    @foreach ($obj->voucher_details as $index => $detail)
        <cac:CreditNoteLine>
        <cbc:ID>{{ ++$index }}</cbc:ID>
        <cbc:Note>UNIDAD</cbc:Note>
        <cbc:CreditedQuantity unitCode="ZZ" unitCodeListAgencyName="United Nations Economic Commission for Europe" unitCodeListID="UN/ECE rec 20">{{ $detail->quantity }}</cbc:CreditedQuantity>
        <cbc:LineExtensionAmount currencyID="{{ $obj->currency_short_name }}">{{ number_format($detail->subtotal,2,'.','') }}</cbc:LineExtensionAmount>
        <cac:PricingReference>
        <cac:AlternativeConditionPrice>
        <cbc:PriceAmount currencyID="{{ $obj->currency_short_name }}">{{ number_format($detail->sale_value, 2, '.', '') }}</cbc:PriceAmount>
        <cbc:PriceTypeCode listAgencyName="PE:SUNAT" listName="Tipo de Precio" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo16">01</cbc:PriceTypeCode>
        </cac:AlternativeConditionPrice>
        </cac:PricingReference>
        <cac:TaxTotal>
        <cbc:TaxAmount currencyID="{{ $obj->currency_short_name }}">{{ $detail->igv }}</cbc:TaxAmount>
        <cac:TaxSubtotal>
        <cbc:TaxableAmount currencyID="{{ $obj->currency_short_name }}">{{ number_format($detail->subtotal,2,'.','') }}</cbc:TaxableAmount>
        <cbc:TaxAmount currencyID="{{ $obj->currency_short_name }}">{{ number_format($detail->igv,2,'.','') }}</cbc:TaxAmount>
        <cac:TaxCategory>
        <cbc:Percent>{{ number_format($obj->igv_percentage, 2, '.', '') }}</cbc:Percent>
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
        <cbc:PriceAmount currencyID="{{ $obj->currency_short_name }}">{{ number_format($detail->unit_price,2,'.','') }}</cbc:PriceAmount>
        </cac:Price>
        </cac:CreditNoteLine>
    @endforeach
</CreditNote>