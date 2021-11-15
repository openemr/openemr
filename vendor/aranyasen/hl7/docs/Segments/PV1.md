# Aranyasen\HL7\Segments\PV1  

PV1 segment class
Ref: https://corepointhealth.com/resource-center/hl7-resources/hl7-pv1-patient-visit-information-segment



## Extend:

Aranyasen\HL7\Segment

## Methods

| Name | Description |
|------|-------------|
|[getAccountStatus](#pv1getaccountstatus)||
|[getAdmissionType](#pv1getadmissiontype)||
|[getAdmitDateTime](#pv1getadmitdatetime)||
|[getAdmitSource](#pv1getadmitsource)||
|[getAdmittingDoctor](#pv1getadmittingdoctor)||
|[getAlternateVisitID](#pv1getalternatevisitid)||
|[getAmbulatoryStatus](#pv1getambulatorystatus)||
|[getAssignedPatientLocation](#pv1getassignedpatientlocation)||
|[getAttendingDoctor](#pv1getattendingdoctor)||
|[getBadDebtAgencyCode](#pv1getbaddebtagencycode)||
|[getBadDebtRecoveryAmount](#pv1getbaddebtrecoveryamount)||
|[getBadDebtTransferAmount](#pv1getbaddebttransferamount)||
|[getBedStatus](#pv1getbedstatus)||
|[getChargePriceIndicator](#pv1getchargepriceindicator)||
|[getConsultingDoctor](#pv1getconsultingdoctor)||
|[getContractAmount](#pv1getcontractamount)||
|[getContractCode](#pv1getcontractcode)||
|[getContractEffectiveDate](#pv1getcontracteffectivedate)||
|[getContractPeriod](#pv1getcontractperiod)||
|[getCourtesyCode](#pv1getcourtesycode)||
|[getCreditRating](#pv1getcreditrating)||
|[getCurrentPatientBalance](#pv1getcurrentpatientbalance)||
|[getDeleteAccountDate](#pv1getdeleteaccountdate)||
|[getDeleteAccountIndicator](#pv1getdeleteaccountindicator)||
|[getDietType](#pv1getdiettype)||
|[getDischargeDateTime](#pv1getdischargedatetime)||
|[getDischargeDisposition](#pv1getdischargedisposition)||
|[getDischargedToLocation](#pv1getdischargedtolocation)||
|[getFinancialClass](#pv1getfinancialclass)||
|[getHospitalService](#pv1gethospitalservice)||
|[getID](#pv1getid)||
|[getInterestCode](#pv1getinterestcode)||
|[getOtherHealthcareProvider](#pv1getotherhealthcareprovider)||
|[getPatientClass](#pv1getpatientclass)||
|[getPatientType](#pv1getpatienttype)||
|[getPendingLocation](#pv1getpendinglocation)||
|[getPreAdmitNumber](#pv1getpreadmitnumber)||
|[getPreAdmitTestIndicator](#pv1getpreadmittestindicator)||
|[getPriorPatientLocation](#pv1getpriorpatientlocation)||
|[getPriorTemporaryLocation](#pv1getpriortemporarylocation)||
|[getReAdmissionIndicator](#pv1getreadmissionindicator)||
|[getReferringDoctor](#pv1getreferringdoctor)||
|[getServicingFacility](#pv1getservicingfacility)||
|[getTemporaryLocation](#pv1gettemporarylocation)||
|[getTotalAdjustments](#pv1gettotaladjustments)||
|[getTotalCharges](#pv1gettotalcharges)||
|[getTotalPayments](#pv1gettotalpayments)||
|[getTransferToBadDebtCode](#pv1gettransfertobaddebtcode)||
|[getTransferToBadDebtDate](#pv1gettransfertobaddebtdate)||
|[getVipIndicator](#pv1getvipindicator)||
|[getVisitIndicator](#pv1getvisitindicator)||
|[getVisitNumber](#pv1getvisitnumber)||
|[setAccountStatus](#pv1setaccountstatus)||
|[setAdmissionType](#pv1setadmissiontype)||
|[setAdmitDateTime](#pv1setadmitdatetime)||
|[setAdmitSource](#pv1setadmitsource)||
|[setAdmittingDoctor](#pv1setadmittingdoctor)||
|[setAlternateVisitID](#pv1setalternatevisitid)||
|[setAmbulatoryStatus](#pv1setambulatorystatus)||
|[setAssignedPatientLocation](#pv1setassignedpatientlocation)||
|[setAttendingDoctor](#pv1setattendingdoctor)||
|[setBadDebtAgencyCode](#pv1setbaddebtagencycode)||
|[setBadDebtRecoveryAmount](#pv1setbaddebtrecoveryamount)||
|[setBadDebtTransferAmount](#pv1setbaddebttransferamount)||
|[setBedStatus](#pv1setbedstatus)||
|[setChargePriceIndicator](#pv1setchargepriceindicator)||
|[setConsultingDoctor](#pv1setconsultingdoctor)||
|[setContractAmount](#pv1setcontractamount)||
|[setContractCode](#pv1setcontractcode)||
|[setContractEffectiveDate](#pv1setcontracteffectivedate)||
|[setContractPeriod](#pv1setcontractperiod)||
|[setCourtesyCode](#pv1setcourtesycode)||
|[setCreditRating](#pv1setcreditrating)||
|[setCurrentPatientBalance](#pv1setcurrentpatientbalance)||
|[setDeleteAccountDate](#pv1setdeleteaccountdate)||
|[setDeleteAccountIndicator](#pv1setdeleteaccountindicator)||
|[setDietType](#pv1setdiettype)||
|[setDischargeDateTime](#pv1setdischargedatetime)||
|[setDischargeDisposition](#pv1setdischargedisposition)||
|[setDischargedToLocation](#pv1setdischargedtolocation)||
|[setFinancialClass](#pv1setfinancialclass)||
|[setHospitalService](#pv1sethospitalservice)||
|[setID](#pv1setid)||
|[setInterestCode](#pv1setinterestcode)||
|[setOtherHealthcareProvider](#pv1setotherhealthcareprovider)||
|[setPatientClass](#pv1setpatientclass)||
|[setPatientType](#pv1setpatienttype)||
|[setPendingLocation](#pv1setpendinglocation)||
|[setPreAdmitNumber](#pv1setpreadmitnumber)||
|[setPreAdmitTestIndicator](#pv1setpreadmittestindicator)||
|[setPriorPatientLocation](#pv1setpriorpatientlocation)||
|[setPriorTemporaryLocation](#pv1setpriortemporarylocation)||
|[setReAdmissionIndicator](#pv1setreadmissionindicator)||
|[setReferringDoctor](#pv1setreferringdoctor)||
|[setServicingFacility](#pv1setservicingfacility)||
|[setTemporaryLocation](#pv1settemporarylocation)||
|[setTotalAdjustments](#pv1settotaladjustments)||
|[setTotalCharges](#pv1settotalcharges)||
|[setTotalPayments](#pv1settotalpayments)||
|[setTransferToBadDebtCode](#pv1settransfertobaddebtcode)||
|[setTransferToBadDebtDate](#pv1settransfertobaddebtdate)||
|[setVipIndicator](#pv1setvipindicator)||
|[setVisitIndicator](#pv1setvisitindicator)||
|[setVisitNumber](#pv1setvisitnumber)||

## Inherited methods

| Name | Description |
|------|-------------|
|__construct|Create a segment.|
|getField|Get the field at index.|
|getFields|Get fields from a segment|
|getName|Get the name of the segment. This is basically the value at index 0|
|setField|Set the field specified by index to value.|
|size|Get the number of fields for this segment, not including the name|



### PV1::getAccountStatus  

**Description**

```php
public getAccountStatus (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getAdmissionType  

**Description**

```php
public getAdmissionType (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getAdmitDateTime  

**Description**

```php
public getAdmitDateTime (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getAdmitSource  

**Description**

```php
public getAdmitSource (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getAdmittingDoctor  

**Description**

```php
public getAdmittingDoctor (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getAlternateVisitID  

**Description**

```php
public getAlternateVisitID (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getAmbulatoryStatus  

**Description**

```php
public getAmbulatoryStatus (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getAssignedPatientLocation  

**Description**

```php
public getAssignedPatientLocation (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getAttendingDoctor  

**Description**

```php
public getAttendingDoctor (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getBadDebtAgencyCode  

**Description**

```php
public getBadDebtAgencyCode (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getBadDebtRecoveryAmount  

**Description**

```php
public getBadDebtRecoveryAmount (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getBadDebtTransferAmount  

**Description**

```php
public getBadDebtTransferAmount (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getBedStatus  

**Description**

```php
public getBedStatus (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getChargePriceIndicator  

**Description**

```php
public getChargePriceIndicator (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getConsultingDoctor  

**Description**

```php
public getConsultingDoctor (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getContractAmount  

**Description**

```php
public getContractAmount (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getContractCode  

**Description**

```php
public getContractCode (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getContractEffectiveDate  

**Description**

```php
public getContractEffectiveDate (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getContractPeriod  

**Description**

```php
public getContractPeriod (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getCourtesyCode  

**Description**

```php
public getCourtesyCode (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getCreditRating  

**Description**

```php
public getCreditRating (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getCurrentPatientBalance  

**Description**

```php
public getCurrentPatientBalance (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getDeleteAccountDate  

**Description**

```php
public getDeleteAccountDate (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getDeleteAccountIndicator  

**Description**

```php
public getDeleteAccountIndicator (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getDietType  

**Description**

```php
public getDietType (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getDischargeDateTime  

**Description**

```php
public getDischargeDateTime (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getDischargeDisposition  

**Description**

```php
public getDischargeDisposition (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getDischargedToLocation  

**Description**

```php
public getDischargedToLocation (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getFinancialClass  

**Description**

```php
public getFinancialClass (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getHospitalService  

**Description**

```php
public getHospitalService (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getID  

**Description**

```php
public getID (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getInterestCode  

**Description**

```php
public getInterestCode (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getOtherHealthcareProvider  

**Description**

```php
public getOtherHealthcareProvider (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getPatientClass  

**Description**

```php
public getPatientClass (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getPatientType  

**Description**

```php
public getPatientType (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getPendingLocation  

**Description**

```php
public getPendingLocation (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getPreAdmitNumber  

**Description**

```php
public getPreAdmitNumber (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getPreAdmitTestIndicator  

**Description**

```php
public getPreAdmitTestIndicator (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getPriorPatientLocation  

**Description**

```php
public getPriorPatientLocation (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getPriorTemporaryLocation  

**Description**

```php
public getPriorTemporaryLocation (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getReAdmissionIndicator  

**Description**

```php
public getReAdmissionIndicator (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getReferringDoctor  

**Description**

```php
public getReferringDoctor (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getServicingFacility  

**Description**

```php
public getServicingFacility (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getTemporaryLocation  

**Description**

```php
public getTemporaryLocation (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getTotalAdjustments  

**Description**

```php
public getTotalAdjustments (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getTotalCharges  

**Description**

```php
public getTotalCharges (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getTotalPayments  

**Description**

```php
public getTotalPayments (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getTransferToBadDebtCode  

**Description**

```php
public getTransferToBadDebtCode (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getTransferToBadDebtDate  

**Description**

```php
public getTransferToBadDebtDate (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getVipIndicator  

**Description**

```php
public getVipIndicator (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getVisitIndicator  

**Description**

```php
public getVisitIndicator (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::getVisitNumber  

**Description**

```php
public getVisitNumber (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::setAccountStatus  

**Description**

```php
public setAccountStatus ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setAdmissionType  

**Description**

```php
public setAdmissionType ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setAdmitDateTime  

**Description**

```php
public setAdmitDateTime ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setAdmitSource  

**Description**

```php
public setAdmitSource ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setAdmittingDoctor  

**Description**

```php
public setAdmittingDoctor ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setAlternateVisitID  

**Description**

```php
public setAlternateVisitID ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setAmbulatoryStatus  

**Description**

```php
public setAmbulatoryStatus ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setAssignedPatientLocation  

**Description**

```php
public setAssignedPatientLocation (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::setAttendingDoctor  

**Description**

```php
public setAttendingDoctor ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setBadDebtAgencyCode  

**Description**

```php
public setBadDebtAgencyCode ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setBadDebtRecoveryAmount  

**Description**

```php
public setBadDebtRecoveryAmount ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setBadDebtTransferAmount  

**Description**

```php
public setBadDebtTransferAmount ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setBedStatus  

**Description**

```php
public setBedStatus ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setChargePriceIndicator  

**Description**

```php
public setChargePriceIndicator ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setConsultingDoctor  

**Description**

```php
public setConsultingDoctor ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setContractAmount  

**Description**

```php
public setContractAmount ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setContractCode  

**Description**

```php
public setContractCode ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setContractEffectiveDate  

**Description**

```php
public setContractEffectiveDate ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setContractPeriod  

**Description**

```php
public setContractPeriod ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setCourtesyCode  

**Description**

```php
public setCourtesyCode ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setCreditRating  

**Description**

```php
public setCreditRating ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setCurrentPatientBalance  

**Description**

```php
public setCurrentPatientBalance ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setDeleteAccountDate  

**Description**

```php
public setDeleteAccountDate ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setDeleteAccountIndicator  

**Description**

```php
public setDeleteAccountIndicator ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setDietType  

**Description**

```php
public setDietType ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setDischargeDateTime  

**Description**

```php
public setDischargeDateTime ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setDischargeDisposition  

**Description**

```php
public setDischargeDisposition ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setDischargedToLocation  

**Description**

```php
public setDischargedToLocation ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setFinancialClass  

**Description**

```php
public setFinancialClass ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setHospitalService  

**Description**

```php
public setHospitalService ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setID  

**Description**

```php
public setID (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::setInterestCode  

**Description**

```php
public setInterestCode ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setOtherHealthcareProvider  

**Description**

```php
public setOtherHealthcareProvider ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setPatientClass  

**Description**

```php
public setPatientClass (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />


### PV1::setPatientType  

**Description**

```php
public setPatientType ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setPendingLocation  

**Description**

```php
public setPendingLocation ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setPreAdmitNumber  

**Description**

```php
public setPreAdmitNumber ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setPreAdmitTestIndicator  

**Description**

```php
public setPreAdmitTestIndicator ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setPriorPatientLocation  

**Description**

```php
public setPriorPatientLocation ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setPriorTemporaryLocation  

**Description**

```php
public setPriorTemporaryLocation ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setReAdmissionIndicator  

**Description**

```php
public setReAdmissionIndicator ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setReferringDoctor  

**Description**

```php
public setReferringDoctor ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setServicingFacility  

**Description**

```php
public setServicingFacility ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setTemporaryLocation  

**Description**

```php
public setTemporaryLocation ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setTotalAdjustments  

**Description**

```php
public setTotalAdjustments ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setTotalCharges  

**Description**

```php
public setTotalCharges ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setTotalPayments  

**Description**

```php
public setTotalPayments ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setTransferToBadDebtCode  

**Description**

```php
public setTransferToBadDebtCode ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setTransferToBadDebtDate  

**Description**

```php
public setTransferToBadDebtDate ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setVipIndicator  

**Description**

```php
public setVipIndicator ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setVisitIndicator  

**Description**

```php
public setVisitIndicator ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />


### PV1::setVisitNumber  

**Description**

```php
public setVisitNumber ( $value, int $position)
```

 

 

**Parameters**

* `() $value`
* `(int) $position`

**Return Values**

`bool`



<hr />

