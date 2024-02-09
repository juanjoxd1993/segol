<VoidedDocuments xmlns="urn:sunat:names:specification:ubl:peru:schema:xsd:VoidedDocuments-1" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2" xmlns:sac="urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
	<ext:UBLExtensions>
		<ext:UBLExtension>
			<ext:ExtensionContent></ext:ExtensionContent>
		</ext:UBLExtension>
	</ext:UBLExtensions>
	<cbc:UBLVersionID>2.0</cbc:UBLVersionID>
	<cbc:CustomizationID>1.0</cbc:CustomizationID>
	<cbc:ID>RA-<?php echo e($obj->low_date); ?>-<?php echo e($obj->low_number); ?></cbc:ID>
	<cbc:ReferenceDate><?php echo e($obj->issue_date); ?></cbc:ReferenceDate>
	<cbc:IssueDate><?php echo e(date('Y-m-d')); ?></cbc:IssueDate>
	<cac:Signature>
		<cbc:ID><?php echo e($obj->company_document_number); ?></cbc:ID>
		<cac:SignatoryParty>
		<cac:PartyIdentification>
			<cbc:ID><?php echo e($obj->company_document_number); ?></cbc:ID>
		</cac:PartyIdentification>
		<cac:PartyName>
			<cbc:Name><?php echo e($obj->company_name); ?></cbc:Name>
		</cac:PartyName>
		</cac:SignatoryParty>
		<cac:DigitalSignatureAttachment>
		<cac:ExternalReference>
			<cbc:URI><?php echo e($obj->company_document_number); ?></cbc:URI>
		</cac:ExternalReference>
		</cac:DigitalSignatureAttachment>
	</cac:Signature>
	<cac:AccountingSupplierParty>
		<cbc:CustomerAssignedAccountID><?php echo e($obj->company_document_number); ?></cbc:CustomerAssignedAccountID>
		<cbc:AdditionalAccountID>6</cbc:AdditionalAccountID>
		<cac:Party>
		<cac:PartyLegalEntity>
			<cbc:RegistrationName><?php echo e($obj->company_name); ?></cbc:RegistrationName>
		</cac:PartyLegalEntity>
		</cac:Party>
	</cac:AccountingSupplierParty>
	<sac:VoidedDocumentsLine>
		<cbc:LineID>1</cbc:LineID>
		<cbc:DocumentTypeCode><?php echo e($obj->voucher_type_type); ?></cbc:DocumentTypeCode>
		<sac:DocumentSerialID><?php echo e($obj->serie_number); ?></sac:DocumentSerialID>
		<sac:DocumentNumberID><?php echo e($obj->voucher_number); ?></sac:DocumentNumberID>
		<sac:VoidReasonDescription>ERROR EN CAPTURA</sac:VoidReasonDescription>
	</sac:VoidedDocumentsLine>
</VoidedDocuments><?php /**PATH /var/www/pdd/resources/views/backend/xml/comunicacion_baja.blade.php ENDPATH**/ ?>