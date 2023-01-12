<?xml version="1.0" encoding="UTF-8"?>
<SummaryDocuments xmlns="urn:sunat:names:specification:ubl:peru:schema:xsd:SummaryDocuments-1" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2" xmlns:sac="urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
	<ext:UBLExtensions>
        <ext:UBLExtension>
            <ext:ExtensionContent>
            </ext:ExtensionContent>
        </ext:UBLExtension>
    </ext:UBLExtensions>
	<cbc:UBLVersionID>2.0</cbc:UBLVersionID>
	<cbc:CustomizationID>1.1</cbc:CustomizationID>
	<cbc:ID>RC-{{ $obj->summary_date }}-{{ $obj->summary_number }}</cbc:ID>
	<cbc:ReferenceDate>{{ date('Y-m-d') }}</cbc:ReferenceDate>
	<cbc:IssueDate>{{ date('Y-m-d') }}</cbc:IssueDate>
	<cac:Signature>
		<cbc:ID>{{ $obj->company->document_number }}</cbc:ID>
		<cac:SignatoryParty>
		<cac:PartyIdentification>
			<cbc:ID>{{ $obj->company->document_number }}</cbc:ID>
		</cac:PartyIdentification>
		<cac:PartyName>
			<cbc:Name>{{ $obj->company->name }}</cbc:Name>
		</cac:PartyName>
		</cac:SignatoryParty>
		<cac:DigitalSignatureAttachment>
		<cac:ExternalReference>
			<cbc:URI>{{ $obj->company->document_number }}</cbc:URI>
		</cac:ExternalReference>
		</cac:DigitalSignatureAttachment>
	</cac:Signature>
	<cac:AccountingSupplierParty>
		<cbc:CustomerAssignedAccountID>{{ $obj->company->document_number }}</cbc:CustomerAssignedAccountID>
		<cbc:AdditionalAccountID>6</cbc:AdditionalAccountID>
		<cac:Party>
			<cac:PartyLegalEntity>
				<cbc:RegistrationName>{{ $obj->company->name }}</cbc:RegistrationName>
			</cac:PartyLegalEntity>
		</cac:Party>
	</cac:AccountingSupplierParty>
	@foreach ($obj->vouchers as $index => $item)	
	<sac:SummaryDocumentsLine>
		<cbc:LineID>{{ ++$index }}</cbc:LineID>
		<cbc:DocumentTypeCode>{{ $item->voucher_type_type }}</cbc:DocumentTypeCode>
		<cbc:ID>{{ $item->serie_number }}-{{ $item->voucher_number }}</cbc:ID>
		<cac:AccountingCustomerParty>
            <cbc:CustomerAssignedAccountID>00000000</cbc:CustomerAssignedAccountID>
            <cbc:AdditionalAccountID>0</cbc:AdditionalAccountID>
        </cac:AccountingCustomerParty>
		<cac:Status>
            <cbc:ConditionCode>1</cbc:ConditionCode>
        </cac:Status>
		<sac:TotalAmount currencyID="{{ $item->currency_short_name }}">{{ $item->total }}</sac:TotalAmount>
		<sac:BillingPayment>
			<cbc:PaidAmount currencyID="{{ $item->currency_short_name }}">{{ $item->taxed_operation }}</cbc:PaidAmount>
			<cbc:InstructionID>01</cbc:InstructionID>
		</sac:BillingPayment>
		<sac:BillingPayment>
			<cbc:PaidAmount currencyID="{{ $item->currency_short_name }}">{{ $item->exonerated_operation }}</cbc:PaidAmount>
			<cbc:InstructionID>02</cbc:InstructionID>
		</sac:BillingPayment>
		<sac:BillingPayment>
			<cbc:PaidAmount currencyID="{{ $item->currency_short_name }}">{{ $item->unaffected_operation }}</cbc:PaidAmount>
			<cbc:InstructionID>03</cbc:InstructionID>
		</sac:BillingPayment>
		<cac:TaxTotal>
			<cbc:TaxAmount currencyID="{{ $item->currency_short_name }}">0.00</cbc:TaxAmount>
			<cac:TaxSubtotal>
				<cbc:TaxAmount currencyID="{{ $item->currency_short_name }}">0.00</cbc:TaxAmount>
				<cac:TaxCategory>
					<cac:TaxScheme>
						<cbc:ID>2000</cbc:ID>
						<cbc:Name>ISC</cbc:Name>
						<cbc:TaxTypeCode>EXC</cbc:TaxTypeCode>
					</cac:TaxScheme>
				</cac:TaxCategory>
			</cac:TaxSubtotal>
		</cac:TaxTotal>
		<cac:TaxTotal>
			<cbc:TaxAmount currencyID="{{ $item->currency_short_name }}">{{ $item->igv }}</cbc:TaxAmount>
			<cac:TaxSubtotal>
				<cbc:TaxAmount currencyID="{{ $item->currency_short_name }}">{{ $item->igv }}</cbc:TaxAmount>
				<cac:TaxCategory>
					<cac:TaxScheme>
						<cbc:ID>1000</cbc:ID>
						<cbc:Name>IGV</cbc:Name>
						<cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
					</cac:TaxScheme>
				</cac:TaxCategory>
			</cac:TaxSubtotal>
		</cac:TaxTotal>
		<cac:TaxTotal>
			<cbc:TaxAmount currencyID="{{ $item->currency_short_name }}">0.00</cbc:TaxAmount>
			<cac:TaxSubtotal>
				<cbc:TaxAmount currencyID="{{ $item->currency_short_name }}">0.00</cbc:TaxAmount>
				<cac:TaxCategory>
					<cac:TaxScheme>
						<cbc:ID>9999</cbc:ID>
						<cbc:Name>OTROS</cbc:Name>
						<cbc:TaxTypeCode>OTH</cbc:TaxTypeCode>
					</cac:TaxScheme>
				</cac:TaxCategory>
			</cac:TaxSubtotal>
		</cac:TaxTotal>
	</sac:SummaryDocumentsLine>
	@endforeach
</SummaryDocuments>