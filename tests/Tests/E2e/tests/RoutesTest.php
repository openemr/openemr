<?php

declare(strict_types=1);

namespace OpenEMR\Tests\E2e\tests;

use Symfony\Component\Panther\PantherTestCase;
use OpenEMR\Tests\E2e\pages\{LoginPage, UsersTab};
use OpenEMR\Tests\E2e\ui\
    {
        TableTestElement, 
        TabTestElement, 
        ButtonTestElement, 
        FormTestElement,
        AlertTestElement
    };

class RoutesTest extends PantherTestCase {
    private $e2eBaseUrl;

    protected function setUp(): void
    {
        $this->e2eBaseUrl = getenv('OPENEMR_BASE_URL_E2E', true) ?: 'http://localhost';
    }

    public function start()
    {      
        $driver = static::createPantherClient(['external_base_uri' => $this->e2eBaseUrl]);

        return (new LoginPage)->login($driver, $this);
    }     
    /** @test */
    public function testCalendarNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Calendar");
        $tab->isActive($session, 'Calendar');
        $session[1]->quit();
    }
     
    /** @test */
    public function testFlowBoardNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Flow Board");
        $tab->isActive($session, 'Flow Board');
        $session[1]->quit();
    }
     
    /** @test */
    public function testRecallBoardNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Recall Board");
        $tab->isActive($session, 'Recall Board');
        $session[1]->quit();
    }
     
    /** @test */
    public function testMessagesNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Messages");
        $tab->isActive($session, 'Message Center');
        $session[1]->quit();
    }
     
    /** @test */
    public function testPatientPatientsNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Patient/Client", "Patients");
        $tab->isActive($session, 'Patient Finder');
        $session[1]->quit();
    }
     
    /** @test */
    public function testAdministrationUsersNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Administration", "Users");
        $tab->isActive($session, 'User / Groups');
        $session[1]->quit();
    }
     
    /** @test */
    public function testPatientNewNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Patient/Client", "New/Search");
        $tab->isActive($session, 'Search or Add Patient');
        $session[1]->quit();
    }
     
    /** @test */
    public function testPatientImportUploadNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Patient/Client", "Import", "Upload");
        $tab->isActive($session, 'Import');
        $session[1]->quit();
    }
     
    /** @test */
    public function testPatientImportPendingApprovalNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Patient/Client", "Import", "Pending Approval");
        $tab->isActive($session, 'Pending Approval');
        $session[1]->quit();
    }
     
    /** @test */
    public function testFeesBillingManagerNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Fees", "Billing Manager");
        $tab->isActive($session, 'Billing Manager');
        $session[1]->quit();
    }
     
    /** @test */
    public function testFeesBatchPaymentsNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Fees", "Batch Payments");
        $tab->isActive($session, 'New Payment');
        $session[1]->quit();
    }
     
    /** @test */
    public function testFeesPostingPaymentsNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Fees", "Posting Payments");
        $tab->isActive($session, 'EOB Posting - Search');
        $session[1]->quit();
    }
     
    /** @test */
    public function testFeesEDIHistoryNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Fees", "EDI History");
        $tab->isActive($session, 'EDI History');
        $session[1]->quit();
    }
     
    /** @test */
    public function testProceduresProvidersNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Procedures", "Providers");
        $tab->isActive($session, 'Procedure Providers');
        $session[1]->quit();
    }
     
    /** @test */
    public function testProceduresConfigurationNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Procedures", "Configuration");
        $tab->isActive($session, 'Configure Orders and Results');
        $session[1]->quit();
    }
     
    /** @test */
    public function testProceduresLoadCompendiumNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Procedures", "Load Compendium");
        $tab->isActive($session, 'Load Compendium');
        $session[1]->quit();
    }
     
    /** @test */
    public function testProceduresBatchResultsNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Procedures", "Batch Results");
        $tab->isActive($session, 'Procedure Results');
        $session[1]->quit();
    }
     
    /** @test */
    public function testProceduresElectronicReportsNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Procedures", "Electronic Reports");
        $tab->isActive($session, 'Procedure Orders and Reports');
        $session[1]->quit();
    }
     
    /** @test */
    public function testProceduresLabDocumentsNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Procedures", "Lab Documents");
        $tab->isActive($session, 'Lab Documents');
        $session[1]->quit();
    }
     
    /** @test */
    public function testAdministrationGlobalsNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Administration", "Globals");
        $tab->isActive($session, 'Global Settings');
        $session[1]->quit();
    }
     
    /** @test */
    public function testAdministrationClinicFacilitiesNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Administration", "Clinic", "Facilities");
        $tab->isActive($session, 'Facilities');
        $session[1]->quit();
    }
     
    /** @test */
    public function testAdministrationClinicCalenderNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Administration", "Clinic", "Calendar");
        $tab->isActive($session, 'Calendar');
        $session[1]->quit();
    }
     
    /** @test */
    public function testAdministrationClinicImportHolidaysNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Administration", "Clinic", "Import Holidays");
        $tab->isActive($session, 'Holidays management');
        $session[1]->quit();
    }
     
    /** @test */
    public function testAdministrationPracticePracticeSettingsNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Administration", "Practice", "Practice Settings");
        $tab->isActive($session, 'Practice Settings');
        $session[1]->quit();
    }
     
    /** @test */
    public function testAdministrationPracticeRulesNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Administration", "Practice", "Rules");
        $tab->isActive($session, 'Plans Configuration Go');
        $session[1]->quit();
    }
     
    /** @test */
    public function testAdministrationPracticeAlertsNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Administration", "Practice", "Alerts");
        $tab->isActive($session, 'Clinical Decision Rules Alert Manager');
        $session[1]->quit();
    }
     
    /** @test */
    public function testAdministrationPatientsPatientRemindersNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Administration", "Patients", "Patient Reminders");
        $tab->isActive($session, 'Patient Reminders');
        $session[1]->quit();
    }
     
    /** @test */
    public function testAdministrationPatientsMergePatientsNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Administration", "Patients", "Merge Patients");
        $tab->isActive($session, 'Merge Patients');
        $session[1]->quit();
    }
     
    /** @test */
    public function testAdministrationCodingCodesNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Administration", "Coding", "Codes");
        $tab->isActive($session, 'Codes');
        $session[1]->quit();
    }
     
    /** @test */
    public function testAdministrationCodingNativeDataLoadsNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Administration", "Coding", "Native Data Loads");
        $tab->isActive($session, 'Install Code Set');
        $session[1]->quit();
    }
     
    /** @test */
    public function testAdministrationCodingExternalDataLoadsNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Administration", "Coding", "External Data Loads");
        $tab->isActive($session, 'External Data Loads');
        $session[1]->quit();
    }
     
    /** @test */
    public function testAdministrationFormsFormsAdministrationNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Administration", "Forms", "Forms Administration");
        $tab->isActive($session, 'Forms Administration');
        $session[1]->quit();
    }
     
    /** @test */
    public function testAdministrationFormsLayoutsNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Administration", "Forms", "Layouts");
        $tab->isActive($session, 'Layout Editor');
        $session[1]->quit();
    }
     
    /** @test */
    public function testAdministrationFormsListsNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Administration", "Forms", "Lists");
        $tab->isActive($session, 'List Editor');
        $session[1]->quit();
    }
     
    /** @test */
    public function testAdministrationDocumentsDocumentTemplatesNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Administration", "Documents", "Document Templates");
        $tab->isActive($session, 'Document Template Management');
        $session[1]->quit();
    }
     
    /** @test */
    public function testAdministrationSystemBackupNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Administration", "System", "Backup");
        $tab->isActive($session, 'Backup');
        $session[1]->quit();
    }
     
    /** @test */
    public function testAdministrationSystemFilesItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Administration", "System", "Files");
        $tab->isActive($session, 'File management');
        $session[1]->quit();
    }
     
    /** @test */
    public function testAdministrationSystemLanguageNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Administration", "System", "Language");
        $tab->isActive($session, 'Multi Language Tool');
        $session[1]->quit();
    }
     
    /** @test */
    public function testAdministrationSystemCertificatesNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Administration", "System", "Certificates");
        $tab->isActive($session, 'SSL Certificate Administration');
        $session[1]->quit();
    }
     
    /** @test */
    public function testAdministrationSystemLogsNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Administration", "System", "Logs");
        $tab->isActive($session, 'Logs Viewer');
        $session[1]->quit();
    }
     
    /** @test */
    public function testAdministrationSystemAuditLogTamperNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Administration", "System", "Audit Log Tamper");
        $tab->isActive($session, 'Audit Log Tamper Report');
        $session[1]->quit();
    }
     
    /** @test */
    public function testAdministrationSystemDiagnosticsNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Administration", "System", "Diagnostics");
        $tab->isActive($session, 'Diagnostics');
        $session[1]->quit();
    }
     
    /** @test */
    public function testAdministrationAddressBookNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Administration", "Address Book");
        $tab->isActive($session, 'Address Book');
        $session[1]->quit();
    }
     
    /** @test */
    public function testAdministrationACLNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Administration", "ACL");
        $tab->isActive($session, 'Access Control List Administration');
        $session[1]->quit();
    }
     
    /** @test */
    public function testReportsClientsListNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Reports", "Clients", "List");
        $tab->isActive($session, 'Patient List');
        $session[1]->quit();
    }
     
    /** @test */
    public function testReportsClientsRxNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Reports", "Clients", "Rx");
        $tab->isActive($session, 'Prescriptions and Dispensations');
        $session[1]->quit();
    }
     
    /** @test */
    public function testReportsClientsPatientListCreationNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Reports", "Clients", "Patient List Creation");
        $tab->isActive($session, 'Patient List Creation');
        $session[1]->quit();
    }
     
    /** @test */
    public function testReportsClientsClinicalNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Reports", "Clients", "Clinical");
        $tab->isActive($session, 'Clinical Reports');
        $session[1]->quit();
    }
     
    /** @test */
    public function testReportsClientsReferralsNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Reports", "Clients", "Referrals");
        $tab->isActive($session, 'Referrals');
        $session[1]->quit();
    }
     
    /** @test */
    public function testReportsClientsImmunizationRegistryNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Reports", "Clients", "Immunization Registry");
        $tab->isActive($session, 'Immunization Registry');
        $session[1]->quit();
    }
     
    /** @test */
    public function testReportsClinicReportResultsNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Reports", "Clinic", "Report Results");
        $tab->isActive($session, 'Report Results/History');
        $session[1]->quit();
    }
     
    /** @test */
    public function testReportsClinicStandardMeasuresNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Reports", "Clinic", "Standard Measures");
        $tab->isActive($session, 'Standard Measures');
        $session[1]->quit();
    }
     
    /** @test */
    public function testReportsClinicQualityMeasuresCQMNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Reports", "Clinic", "Quality Measures (CQM)");
        $tab->isActive($session, 'Clinical Quality Measures (CQM)');
        $session[1]->quit();
    }
     
    /** @test */
    public function testReportsClinicAutomatedMeasuresAMCNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Reports", "Clinic", "Automated Measures (AMC)");
        $tab->isActive($session, 'Automated Measure Calculations (AMC)');
        $session[1]->quit();
    }
     
    /** @test */
    public function testReportsClinicAMCTrackingNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Reports", "Clinic", "AMC Tracking");
        $tab->isActive($session, 'Automated Measure Calculations (AMC) Tracking');
        $session[1]->quit();
    }
     
    /** @test */
    public function testReportsClinicAlertsLogNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Reports", "Clinic", "Alerts Log");
        $tab->isActive($session, 'Alerts Log');
        $session[1]->quit();
    }
     
    /** @test */
    public function testReportsVisitsDailyReportNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Reports", "Visits", "Daily Report");
        $tab->isActive($session, 'Daily Summary Report');
        $session[1]->quit();
    }
     
    /** @test */
    public function testReportsVisitsAppointmentsNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Reports", "Visits", "Appointments");
        $tab->isActive($session, 'Appointments Report');
        $session[1]->quit();
    }
     
    /** @test */
    public function testReportsVisitsPatientFlowBoardNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Reports", "Visits", "Patient Flow Board");
        $tab->isActive($session, 'Patient Flow Board Report');
        $session[1]->quit();
    }
     
    /** @test */
    public function testReportsVisitsEncountersNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Reports", "Visits", "Encounters");
        $tab->isActive($session, 'Encounters Report');
        $session[1]->quit();
    }
     
    /** @test */
    public function testReportsVisitsApptEncNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Reports", "Visits", "Appt-Enc");
        $tab->isActive($session, 'Appointments and Encounters');
        $session[1]->quit();
    }
     
    /** @test */
    public function testReportsVisitsSuperbillNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Reports", "Visits", "Superbill");
        $tab->isActive($session, 'Reports - Superbill');
        $session[1]->quit();
    }
     
    /** @test */
    public function testReportsVisitsEligibilityNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Reports", "Visits", "Eligibility");
        $tab->isActive($session, 'Eligibility 270 Inquiry Batch');
        $session[1]->quit();
    }
     
    /** @test */
    public function testReportsVisitsEligibilityResponseNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Reports", "Visits", "Eligibility Response");
        $tab->isActive($session, 'EDI-271 Response File Upload');
        $session[1]->quit();
    }
     
    /** @test */
    public function testReportsVisitsChartAcivityNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Reports", "Visits", "Chart Activity");
        $tab->isActive($session, 'Chart Location Activity');
        $session[1]->quit();
    }
     
    /** @test */
    public function testReportsVisitsChartsOutNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Reports", "Visits", "Charts Out");
        $tab->isActive($session, 'Charts Checked Out');
        $session[1]->quit();
    }
     
    /** @test */
    public function testReportsVisitsSyndromicSurveillanceNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Reports", "Visits", "Syndromic Surveillance");
        $tab->isActive($session, 'Syndromic Surveillance - Non Reported Issues');
        $session[1]->quit();
    }
     
    /** @test */
    public function testReportsFinancialSalesNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Reports", "Financial", "Sales");
        $tab->isActive($session, 'Sales by Item');
        $session[1]->quit();
    }
     
    /** @test */
    public function testReportsFinancialCashRecNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Reports", "Financial", "Cash Rec");
        $tab->isActive($session, 'Cash Receipts by Provider');
        $session[1]->quit();
    }
     
    /** @test */
    public function testReportsFinancialFrontRecNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Reports", "Financial", "Front Rec");
        $tab->isActive($session, 'Front Office Receipts');
        $session[1]->quit();
    }
     
    /** @test */
    public function testReportsFinancialPmtMethodNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Reports", "Financial", "Pmt Method");
        $tab->isActive($session, 'Receipts Summary');
        $session[1]->quit();
    }
     
    /** @test */
    public function testReportsFinancialCollectionsReportNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Reports", "Financial", "Collections and Aging");
        $tab->isActive($session, 'Collections Report');
        $session[1]->quit();
    }
     
    /** @test */
    public function testReportsFinancialPatLedgerNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Reports", "Financial", "Pat Ledger");
        $tab->isActive($session, 'Patient Ledger by Date');
        $session[1]->quit();
    }
     
    /** @test */
    public function testReportsProceduresPendingResNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Reports", "Procedures", "Pending Res");
        $tab->isActive($session, 'Pending Orders');
        $session[1]->quit();
    }
     
    /** @test */
    public function testReportsProceduresStatisticsNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Reports", "Procedures", "Statistics");
        $tab->isActive($session, 'Procedure Statistics Report');
        $session[1]->quit();
    }
     
    /** @test */
    public function testReportsProceduresDistributionNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Reports", "Insurance", "Distribution");
        $tab->isActive($session, 'Patient Insurance Distribution');
        $session[1]->quit();
    }
     
    /** @test */
    public function testReportsProceduresIndigentsNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Reports", "Insurance", "Indigents");
        $tab->isActive($session, 'Indigent Patients Report');
        $session[1]->quit();
    }
     
    /** @test */
    public function testReportsProceduresUniqueSPNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Reports", "Insurance", "Unique SP");
        $tab->isActive($session, 'Front Office Receipts');
        $session[1]->quit();
    }
     
    /** @test */
    public function testMiscellaneousDicomViewerNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Miscellaneous", "Dicom Viewer");
        $tab->isActive($session, 'Dicom Viewer');
        $session[1]->quit();
    }
     
    /** @test */
    public function testMiscellaneousPatientEducationNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Miscellaneous", "Patient Education");
        $tab->isActive($session, 'Web Search - Patient Education Materials');
        $session[1]->quit();
    }
     
    /** @test */
    public function testMiscellaneousAuthorizationsNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Miscellaneous", "Authorizations");
        $tab->isActive($session, 'Authorizations (More)');
        $session[1]->quit();
    }
     
    /** @test */
    public function testMiscellaneousChartTrackerNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Miscellaneous", "Chart Tracker");
        $tab->isActive($session, 'Chart Tracker');
        $session[1]->quit();
    }
     
    /** @test */
    public function testMiscellaneousOfficeNotesNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Miscellaneous", "Office Notes");
        $tab->isActive($session, 'Office Notes');
        $session[1]->quit();
    }
     
    /** @test */
    public function testMiscellaneousBatchCommunicationToolNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Miscellaneous", "Batch Communication Tool");
        $tab->isActive($session, 'BatchCom');
        $session[1]->quit();
    }
     
    /** @test */
    public function testMiscellaneousNewDocumentsNavbarItemOpensCorrectTab()
    {
        $session = $this->start();
        $tab = new TabTestElement;
        $tab->open($session, "Miscellaneous", "New Documents");
        $tab->isActive($session, 'Documents');
        $session[1]->quit();
    }
}