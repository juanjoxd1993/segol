<?xml version="1.0" encoding="UTF-8"?>
<Perception xmlns="urn:sunat:names:specification:ubl:peru:schema:xsd:Perception-1" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2" xmlns:sac="urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1">
<ext:UBLExtensions>
<ext:UBLExtension>
<ext:ExtensionContent>
<sac:AdditionalInformation>
<sac:AdditionalProperty>
<cbc:ID>1000</cbc:ID>
<cbc:Value>{{ $obj->total_text }}</cbc:Value>
</sac:AdditionalProperty>
</sac:AdditionalInformation>
</ext:ExtensionContent>
</ext:UBLExtension>
</ext:UBLExtensions>
<cbc:UBLVersionID>2.0</cbc:UBLVersionID>
<cbc:CustomizationID>1.0</cbc:CustomizationID>
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
<cbc:ID>{{ $obj->serie_number }}-{{ $obj->voucher_number }}</cbc:ID>
<cbc:IssueDate>2024-08-13</cbc:IssueDate>
<cac:AgentParty>
<cac:PartyIdentification>
<cbc:ID schemeID="6">{{ $obj->company_document_number }}</cbc:ID>
</cac:PartyIdentification>
<cac:PartyName>
<cbc:Name>{{ $obj->company_name }}</cbc:Name>
</cac:PartyName>
<cac:PostalAddress>
<cbc:ID>{{ $obj->company_ubigeo }}</cbc:ID>
<cbc:StreetName>{{ $obj->company_addresses }}</cbc:StreetName>
<cbc:CitySubdivisionName>URBANIZACION</cbc:CitySubdivisionName>
<cbc:CityName>{{ $obj->company_province }}</cbc:CityName>
<cbc:CountrySubentity>{{ $obj->company_department }}</cbc:CountrySubentity>
<cbc:District>{{ $obj->company_district }}</cbc:District>
<cac:Country>
<cbc:IdentificationCode>PE</cbc:IdentificationCode>
</cac:Country>
</cac:PostalAddress>
<cac:PartyLegalEntity>
<cbc:RegistrationName>{{ $obj->company_name }}</cbc:RegistrationName>
</cac:PartyLegalEntity>
</cac:AgentParty>
<cac:ReceiverParty>
<cac:PartyIdentification>
<cbc:ID schemeID="6">{{ $obj->client_document_number }}</cbc:ID>
</cac:PartyIdentification>
<cac:PartyName>
<cbc:Name>{{ $obj->client_name }}</cbc:Name>
</cac:PartyName>
<cac:PostalAddress>
<cbc:ID>{{ $obj->company_ubigeo }}</cbc:ID>
<cbc:StreetName>{{ $obj->client_address }}</cbc:StreetName>
<cbc:CitySubdivisionName>URBANIZACION</cbc:CitySubdivisionName>
<cbc:CityName>{{ $obj->client_province }}</cbc:CityName>
<cbc:CountrySubentity>{{ $obj->client_department }}</cbc:CountrySubentity>
<cbc:District>{{ $obj->client_district }}</cbc:District>
<cac:Country>
<cbc:IdentificationCode>PE</cbc:IdentificationCode>
</cac:Country>
</cac:PostalAddress>
<cac:PartyLegalEntity>
<cbc:RegistrationName>{{ $obj->client_name }}</cbc:RegistrationName>
</cac:PartyLegalEntity>
<cac:Contact>
<cbc:ElectronicMail>enviofacturacion@puntodedistribucion.com</cbc:ElectronicMail>
</cac:Contact>
</cac:ReceiverParty>
<sac:SUNATPerceptionSystemCode>{{ $obj->rate_type }}</sac:SUNATPerceptionSystemCode>
<sac:SUNATPerceptionPercent>{{ $obj->rate_value }}</sac:SUNATPerceptionPercent>
<cbc:Note/>
<cbc:TotalInvoiceAmount currencyID="{{ $obj->currency_short_name }}">{{ number_format($obj->igv_perception * $obj->total,2,'.','') }}</cbc:TotalInvoiceAmount>
<sac:SUNATTotalCashed currencyID="{{ $obj->currency_short_name }}">{{ $obj->total_perception }}</sac:SUNATTotalCashed>
<sac:SUNATPerceptionDocumentReference>
<cbc:ID schemeID="01">{{ $obj->credit_note_reference_serie }}-{{ $obj->credit_note_reference_number }}</cbc:ID>
<cbc:IssueDate>{{ $obj->issue_date }}</cbc:IssueDate>
<cbc:TotalInvoiceAmount currencyID="{{ $obj->currency_short_name }}">{{ $obj->total }}</cbc:TotalInvoiceAmount>
<cac:Payment>
<cbc:ID>1</cbc:ID>
<cbc:PaidAmount currencyID="{{ $obj->currency_short_name }}">{{ $obj->total }}</cbc:PaidAmount>
<cbc:PaidDate>2024-08-13</cbc:PaidDate>
</cac:Payment>
<sac:SUNATPerceptionInformation>
<sac:SUNATPerceptionAmount currencyID="{{ $obj->currency_short_name }}">{{ number_format($obj->igv_perception * $obj->total,2,'.','') }}</sac:SUNATPerceptionAmount>
<sac:SUNATPerceptionDate>{{ $obj->issue_date }}</sac:SUNATPerceptionDate>
<sac:SUNATNetTotalCashed currencyID="{{ $obj->currency_short_name }}">{{ $obj->total_perception }}</sac:SUNATNetTotalCashed>
<cac:ExchangeRate>
<cbc:SourceCurrencyCode>{{ $obj->currency_short_name }}</cbc:SourceCurrencyCode>
<cbc:TargetCurrencyCode>{{ $obj->currency_short_name }}</cbc:TargetCurrencyCode>
<cbc:CalculationRate>1.0</cbc:CalculationRate>
<cbc:Date>{{ $obj->issue_date }}</cbc:Date>
</cac:ExchangeRate>
</sac:SUNATPerceptionInformation>
</sac:SUNATPerceptionDocumentReference>
</Perception>