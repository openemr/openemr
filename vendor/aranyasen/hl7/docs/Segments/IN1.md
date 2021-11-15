# Aranyasen\HL7\Segments\IN1  

IN1 segment class
Ref: https://corepointhealth.com/resource-center/hl7-resources/hl7-in1-insurance-segment



## Extend:

Aranyasen\HL7\Segment

## Methods

| Name | Description |
|------|-------------|
|[getAssignmentOfBenefits](#in1getassignmentofbenefits)||
|[getAuthorizationInformation](#in1getauthorizationinformation)||
|[getBillingStatus](#in1getbillingstatus)||
|[getCompanyPlanCode](#in1getcompanyplancode)||
|[getCoordOfBenPriority](#in1getcoordofbenpriority)||
|[getCoordinationOfBenefits](#in1getcoordinationofbenefits)||
|[getCoverageType](#in1getcoveragetype)||
|[getDelayBeforeLRDay](#in1getdelaybeforelrday)||
|[getGroupName](#in1getgroupname)||
|[getGroupNumber](#in1getgroupnumber)||
|[getHandicap](#in1gethandicap)||
|[getID](#in1getid)||
|[getInsuranceCoContactPerson](#in1getinsurancecocontactperson)||
|[getInsuranceCoPhoneNumber](#in1getinsurancecophonenumber)||
|[getInsuranceCompanyAddress](#in1getinsurancecompanyaddress)||
|[getInsuranceCompanyID](#in1getinsurancecompanyid)||
|[getInsuranceCompanyName](#in1getinsurancecompanyname)||
|[getInsurancePlanID](#in1getinsuranceplanid)||
|[getInsuredsAddress](#in1getinsuredsaddress)||
|[getInsuredsDateOfBirth](#in1getinsuredsdateofbirth)||
|[getInsuredsEmployersAddress](#in1getinsuredsemployersaddress)||
|[getInsuredsEmploymentStatus](#in1getinsuredsemploymentstatus)||
|[getInsuredsGroupEmpID](#in1getinsuredsgroupempid)||
|[getInsuredsGroupEmpName](#in1getinsuredsgroupempname)||
|[getInsuredsIDNumber](#in1getinsuredsidnumber)||
|[getInsuredsRelationshipToPatient](#in1getinsuredsrelationshiptopatient)||
|[getInsuredsSex](#in1getinsuredssex)||
|[getLifetimeReserveDays](#in1getlifetimereservedays)||
|[getNameOfInsured](#in1getnameofinsured)||
|[getNoticeOfAdmissionDate](#in1getnoticeofadmissiondate)||
|[getNoticeOfAdmissionFlag](#in1getnoticeofadmissionflag)||
|[getPlanEffectiveDate](#in1getplaneffectivedate)||
|[getPlanExpirationDate](#in1getplanexpirationdate)||
|[getPlanType](#in1getplantype)||
|[getPolicyDeductible](#in1getpolicydeductible)||
|[getPolicyLimitAmount](#in1getpolicylimitamount)||
|[getPolicyLimitDays](#in1getpolicylimitdays)||
|[getPolicyNumber](#in1getpolicynumber)||
|[getPreAdmitCertPAC](#in1getpreadmitcertpac)||
|[getPriorInsurancePlanID](#in1getpriorinsuranceplanid)||
|[getReleaseInformationCode](#in1getreleaseinformationcode)||
|[getReportOfEligibilityDate](#in1getreportofeligibilitydate)||
|[getReportOfEligibilityFlag](#in1getreportofeligibilityflag)||
|[getRoomRatePrivate](#in1getroomrateprivate)||
|[getRoomRateSemiPrivate](#in1getroomratesemiprivate)||
|[getTypeOfAgreementCode](#in1gettypeofagreementcode)||
|[getVerificationBy](#in1getverificationby)||
|[getVerificationDateTime](#in1getverificationdatetime)||
|[getVerificationStatus](#in1getverificationstatus)||
|[setAssignmentOfBenefits](#in1setassignmentofbenefits)||
|[setAuthorizationInformation](#in1setauthorizationinformation)||
|[setBillingStatus](#in1setbillingstatus)||
|[setCompanyPlanCode](#in1setcompanyplancode)||
|[setCoordOfBenPriority](#in1setcoordofbenpriority)||
|[setCoordinationOfBenefits](#in1setcoordinationofbenefits)||
|[setCoverageType](#in1setcoveragetype)||
|[setDelayBeforeLRDay](#in1setdelaybeforelrday)||
|[setGroupName](#in1setgroupname)||
|[setGroupNumber](#in1setgroupnumber)||
|[setHandicap](#in1sethandicap)||
|[setID](#in1setid)||
|[setInsuranceCoContactPerson](#in1setinsurancecocontactperson)||
|[setInsuranceCoPhoneNumber](#in1setinsurancecophonenumber)||
|[setInsuranceCompanyAddress](#in1setinsurancecompanyaddress)||
|[setInsuranceCompanyID](#in1setinsurancecompanyid)||
|[setInsuranceCompanyName](#in1setinsurancecompanyname)||
|[setInsurancePlanID](#in1setinsuranceplanid)||
|[setInsuredsAddress](#in1setinsuredsaddress)||
|[setInsuredsDateOfBirth](#in1setinsuredsdateofbirth)||
|[setInsuredsEmployersAddress](#in1setinsuredsemployersaddress)||
|[setInsuredsEmploymentStatus](#in1setinsuredsemploymentstatus)||
|[setInsuredsGroupEmpID](#in1setinsuredsgroupempid)||
|[setInsuredsGroupEmpName](#in1setinsuredsgroupempname)||
|[setInsuredsIDNumber](#in1setinsuredsidnumber)||
|[setInsuredsRelationshipToPatient](#in1setinsuredsrelationshiptopatient)||
|[setInsuredsSex](#in1setinsuredssex)||
|[setLifetimeReserveDays](#in1setlifetimereservedays)||
|[setNameOfInsured](#in1setnameofinsured)||
|[setNoticeOfAdmissionDate](#in1setnoticeofadmissiondate)||
|[setNoticeOfAdmissionFlag](#in1setnoticeofadmissionflag)||
|[setPlanEffectiveDate](#in1setplaneffectivedate)||
|[setPlanExpirationDate](#in1setplanexpirationdate)||
|[setPlanType](#in1setplantype)||
|[setPolicyDeductible](#in1setpolicydeductible)||
|[setPolicyLimitAmount](#in1setpolicylimitamount)||
|[setPolicyLimitDays](#in1setpolicylimitdays)||
|[setPolicyNumber](#in1setpolicynumber)||
|[setPreAdmitCertPAC](#in1setpreadmitcertpac)||
|[setPriorInsurancePlanID](#in1setpriorinsuranceplanid)||
|[setReleaseInformationCode](#in1setreleaseinformationcode)||
|[setReportOfEligibilityDate](#in1setreportofeligibilitydate)||
|[setReportOfEligibilityFlag](#in1setreportofeligibilityflag)||
|[setRoomRatePrivate](#in1setroomrateprivate)||
|[setRoomRateSemiPrivate](#in1setroomratesemiprivate)||
|[setTypeOfAgreementCode](#in1settypeofagreementcode)||
|[setVerificationBy](#in1setverificationby)||
|[setVerificationDateTime](#in1setverificationdatetime)||
|[setVerificationStatus](#in1setverificationstatus)||

## Inherited methods

| Name | Description |
|------|-------------|
|__construct|Create a segment.|
|getField|Get the field at index.|
|getFields|Get fields from a segment|
|getName|Get the name of the segment. This is basically the value at index 0|
|setField|Set the field specified by index to value.|
|size|Get the number of fields for this segment, not including the name|



### IN1::getAssignmentOfBenefits  

**Description**

```php
public getAssignmentOfBenefits (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getAuthorizationInformation  

**Description**

```php
public getAuthorizationInformation (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getBillingStatus  

**Description**

```php
public getBillingStatus (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getCompanyPlanCode  

**Description**

```php
public getCompanyPlanCode (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getCoordOfBenPriority  

**Description**

```php
public getCoordOfBenPriority (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getCoordinationOfBenefits  

**Description**

```php
public getCoordinationOfBenefits (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getCoverageType  

**Description**

```php
public getCoverageType (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getDelayBeforeLRDay  

**Description**

```php
public getDelayBeforeLRDay (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getGroupName  

**Description**

```php
public getGroupName (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getGroupNumber  

**Description**

```php
public getGroupNumber (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getHandicap  

**Description**

```php
public getHandicap (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getID  

**Description**

```php
public getID (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getInsuranceCoContactPerson  

**Description**

```php
public getInsuranceCoContactPerson (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getInsuranceCoPhoneNumber  

**Description**

```php
public getInsuranceCoPhoneNumber (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getInsuranceCompanyAddress  

**Description**

```php
public getInsuranceCompanyAddress (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getInsuranceCompanyID  

**Description**

```php
public getInsuranceCompanyID (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getInsuranceCompanyName  

**Description**

```php
public getInsuranceCompanyName (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getInsurancePlanID  

**Description**

```php
public getInsurancePlanID (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getInsuredsAddress  

**Description**

```php
public getInsuredsAddress (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getInsuredsDateOfBirth  

**Description**

```php
public getInsuredsDateOfBirth (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getInsuredsEmployersAddress  

**Description**

```php
public getInsuredsEmployersAddress (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getInsuredsEmploymentStatus  

**Description**

```php
public getInsuredsEmploymentStatus (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getInsuredsGroupEmpID  

**Description**

```php
public getInsuredsGroupEmpID (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getInsuredsGroupEmpName  

**Description**

```php
public getInsuredsGroupEmpName (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getInsuredsIDNumber  

**Description**

```php
public getInsuredsIDNumber (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getInsuredsRelationshipToPatient  

**Description**

```php
public getInsuredsRelationshipToPatient (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getInsuredsSex  

**Description**

```php
public getInsuredsSex (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getLifetimeReserveDays  

**Description**

```php
public getLifetimeReserveDays (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getNameOfInsured  

**Description**

```php
public getNameOfInsured (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getNoticeOfAdmissionDate  

**Description**

```php
public getNoticeOfAdmissionDate (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getNoticeOfAdmissionFlag  

**Description**

```php
public getNoticeOfAdmissionFlag (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getPlanEffectiveDate  

**Description**

```php
public getPlanEffectiveDate (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getPlanExpirationDate  

**Description**

```php
public getPlanExpirationDate (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getPlanType  

**Description**

```php
public getPlanType (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getPolicyDeductible  

**Description**

```php
public getPolicyDeductible (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getPolicyLimitAmount  

**Description**

```php
public getPolicyLimitAmount (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getPolicyLimitDays  

**Description**

```php
public getPolicyLimitDays (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getPolicyNumber  

**Description**

```php
public getPolicyNumber (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getPreAdmitCertPAC  

**Description**

```php
public getPreAdmitCertPAC (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getPriorInsurancePlanID  

**Description**

```php
public getPriorInsurancePlanID (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getReleaseInformationCode  

**Description**

```php
public getReleaseInformationCode (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getReportOfEligibilityDate  

**Description**

```php
public getReportOfEligibilityDate (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getReportOfEligibilityFlag  

**Description**

```php
public getReportOfEligibilityFlag (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getRoomRatePrivate  

**Description**

```php
public getRoomRatePrivate (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getRoomRateSemiPrivate  

**Description**

```php
public getRoomRateSemiPrivate (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getTypeOfAgreementCode  

**Description**

```php
public getTypeOfAgreementCode (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getVerificationBy  

**Description**

```php
public getVerificationBy (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getVerificationDateTime  

**Description**

```php
public getVerificationDateTime (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::getVerificationStatus  

**Description**

```php
public getVerificationStatus (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setAssignmentOfBenefits  

**Description**

```php
public setAssignmentOfBenefits (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setAuthorizationInformation  

**Description**

```php
public setAuthorizationInformation (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setBillingStatus  

**Description**

```php
public setBillingStatus (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setCompanyPlanCode  

**Description**

```php
public setCompanyPlanCode (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setCoordOfBenPriority  

**Description**

```php
public setCoordOfBenPriority (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setCoordinationOfBenefits  

**Description**

```php
public setCoordinationOfBenefits (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setCoverageType  

**Description**

```php
public setCoverageType (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setDelayBeforeLRDay  

**Description**

```php
public setDelayBeforeLRDay (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setGroupName  

**Description**

```php
public setGroupName (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setGroupNumber  

**Description**

```php
public setGroupNumber (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setHandicap  

**Description**

```php
public setHandicap (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setID  

**Description**

```php
public setID (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setInsuranceCoContactPerson  

**Description**

```php
public setInsuranceCoContactPerson (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setInsuranceCoPhoneNumber  

**Description**

```php
public setInsuranceCoPhoneNumber (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setInsuranceCompanyAddress  

**Description**

```php
public setInsuranceCompanyAddress (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setInsuranceCompanyID  

**Description**

```php
public setInsuranceCompanyID (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setInsuranceCompanyName  

**Description**

```php
public setInsuranceCompanyName (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setInsurancePlanID  

**Description**

```php
public setInsurancePlanID (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setInsuredsAddress  

**Description**

```php
public setInsuredsAddress (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setInsuredsDateOfBirth  

**Description**

```php
public setInsuredsDateOfBirth (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setInsuredsEmployersAddress  

**Description**

```php
public setInsuredsEmployersAddress (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setInsuredsEmploymentStatus  

**Description**

```php
public setInsuredsEmploymentStatus (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setInsuredsGroupEmpID  

**Description**

```php
public setInsuredsGroupEmpID (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setInsuredsGroupEmpName  

**Description**

```php
public setInsuredsGroupEmpName (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setInsuredsIDNumber  

**Description**

```php
public setInsuredsIDNumber (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setInsuredsRelationshipToPatient  

**Description**

```php
public setInsuredsRelationshipToPatient (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setInsuredsSex  

**Description**

```php
public setInsuredsSex (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setLifetimeReserveDays  

**Description**

```php
public setLifetimeReserveDays (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setNameOfInsured  

**Description**

```php
public setNameOfInsured (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setNoticeOfAdmissionDate  

**Description**

```php
public setNoticeOfAdmissionDate (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setNoticeOfAdmissionFlag  

**Description**

```php
public setNoticeOfAdmissionFlag (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setPlanEffectiveDate  

**Description**

```php
public setPlanEffectiveDate (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setPlanExpirationDate  

**Description**

```php
public setPlanExpirationDate (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setPlanType  

**Description**

```php
public setPlanType (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setPolicyDeductible  

**Description**

```php
public setPolicyDeductible (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setPolicyLimitAmount  

**Description**

```php
public setPolicyLimitAmount (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setPolicyLimitDays  

**Description**

```php
public setPolicyLimitDays (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setPolicyNumber  

**Description**

```php
public setPolicyNumber (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setPreAdmitCertPAC  

**Description**

```php
public setPreAdmitCertPAC (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setPriorInsurancePlanID  

**Description**

```php
public setPriorInsurancePlanID (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setReleaseInformationCode  

**Description**

```php
public setReleaseInformationCode (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setReportOfEligibilityDate  

**Description**

```php
public setReportOfEligibilityDate (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setReportOfEligibilityFlag  

**Description**

```php
public setReportOfEligibilityFlag (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setRoomRatePrivate  

**Description**

```php
public setRoomRatePrivate (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setRoomRateSemiPrivate  

**Description**

```php
public setRoomRateSemiPrivate (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setTypeOfAgreementCode  

**Description**

```php
public setTypeOfAgreementCode (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setVerificationBy  

**Description**

```php
public setVerificationBy (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setVerificationDateTime  

**Description**

```php
public setVerificationDateTime (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### IN1::setVerificationStatus  

**Description**

```php
public setVerificationStatus (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />

