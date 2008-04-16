-- phpMyAdmin SQL Dump
-- version 2.11.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 03, 2008 at 04:11 PM
-- Server version: 4.1.20
-- PHP Version: 4.3.11

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `openemr`
--

-- --------------------------------------------------------

--
-- Table structure for table `cl_vektis_retcodes`
--

CREATE TABLE IF NOT EXISTS `cl_vektis_retcodes` (
  `cvr_code` varchar(4) collate utf8_bin NOT NULL default '' COMMENT 'four digits return code',
  `cvr_expl` varchar(150) collate utf8_bin NOT NULL default '',
  `cvr_content` char(1) collate utf8_bin NOT NULL default '' COMMENT 'D/X/T or space',
  PRIMARY KEY  (`cvr_code`),
  KEY `cvr_content` (`cvr_content`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='returning codes for vektis';

--
-- Dumping data for table `cl_vektis_retcodes`
--

INSERT INTO `cl_vektis_retcodes` (`cvr_code`, `cvr_expl`, `cvr_content`) VALUES
('0001', 'Bericht is afgekeurd om technische redenen.', 'X'),
('0002', 'Kenmerk record ontbreekt of is onjuist.', 'X'),
('0010', 'Code externe-integratiebericht ontbreekt of is onjuist.', 'X'),
('0011', 'Versienummer berichtstandaard ontbreekt of is onjuist.', 'X'),
('0013', 'Code servicebureau ontbreekt, is onbekend of onjuist.', 'T'),
('0014', 'Zorgverlenerscode ontbreekt, is onbekend of onjuist (voorlooprecord).', 'T'),
('0016', 'Praktijkcode ontbreekt, is onbekend of onjuist (voorlooprecord).', 'T'),
('0017', 'Instellingscode, ontbreekt, is onbekend of onjuist (voorlooprecord).', 'T'),
('0018', 'UZOVI-nummer ontbreekt of is onjuist (voorlooprecord).', 'T'),
('0020', 'Begindatum declaratieperiode ontbreekt of is onjuist.', 'X'),
('0021', 'Einddatum declaratieperiode ontbreekt of is onjuist.', 'X'),
('0025', 'Dagtekening factuur ontbreekt of is niet bestaand.', 'T'),
('0030', 'Factuurnummer declarant ontbreekt of is niet uniek (nummer is reeds gebruikt in een voorgaande factuur).', 'T'),
('0031', 'Valutacode ontbreekt of is onjuist.', 'T'),
('0150', 'Totaal declaratiebedrag ontbreekt of is onjuist.', 'X'),
('0151', 'Aantal verzekerdenrecords ontbreekt of is onjuist.', 'X'),
('0153', 'Aantal commentaarrecords ontbreekt of is onjuist.', 'X'),
('0156', 'Totaal aantal detailrecords ontbreekt of is onjuist.', 'X'),
('0157', 'Aantal debiteurrecords ontbreekt of is onjuist.', 'X'),
('0158', 'Aantal prestatierecords ontbreekt of is onjuist.', 'X'),
('0200', 'Geen opmerking bij dit recordtype.', 'O'),
('0300', 'Verzekerdennummer (inschrijvingsnummer, relatienummer) is onbekend (bij deze UZOVI-code).', 'D'),
('0302', 'Combinatie verzekerdennummer en achternaam verzekerde is onjuist.', 'D'),
('0307', 'Combinatie verzekerdennummer en geboortedatum verzekerde is onjuist.', 'D'),
('0310', 'Voorletters ontbreken of zijn onjuist (verzekerde).', 'D'),
('0312', 'Naam verzekerde (01) ontbreekt of is onjuist.', 'D'),
('0316', 'Code land verzekerde ontbreekt of is onjuist.', 'D'),
('0317', 'Naamcode/naamgebruik (01) ontbreekt of is onjuist.', 'D'),
('0320', 'Cliënt is niet verzekerd.', 'D'),
('0328', 'Cliënt is niet verzekerd wegens overlijden.', 'D'),
('0350', 'Geboortedatum ontbreekt of is onjuist (debiteur).', 'D'),
('0351', 'Code geslacht ontbreekt of is onjuist.', 'D'),
('0397', 'Naamcode/naamgebruik (03) ontbreekt of is onjuist.', 'D'),
('0406', 'Huisnummer (huisadres) ontbreekt of is onjuist (verzekerde).', 'D'),
('0409', 'Straatnaam ontbreekt.', 'D'),
('0410', 'Woonplaats ontbreekt of is onjuist.', 'D'),
('0423', 'UZOVI-nummer ontbreekt of is onjuist (detailrecord).', 'D'),
('0435', 'Burgerservicenummer (BSN) verzekerde ontbreekt of is onjuist.', 'D'),
('0506', 'Cliënt is slechts tijdens een gedeelte van de behandelperiode verzekerd.', 'D'),
('0550', 'Machtigingsnummer / meldingsnummer zorgverzekeraar ontbreekt of is onjuist.', 'D'),
('0551', 'Er is geen machtiging afgegeven.', 'D'),
('0552', 'Machtiging is vervallen.', 'D'),
('0553', 'Gedeclareerde prestatie valt buiten de gemachtigde periode.', 'D'),
('0554', 'Gedeclareerde prestatie is anders dan afgegeven machtiging.', 'D'),
('0559', 'Zorgverlenerscode behandelaar/uitvoerder heeft geen overeenkomst met declarerende praktijk of instelling.', 'D'),
('0562', 'Zorgverlener is niet erkend of bevoegd tot uitvoering van opgegeven prestatie.', 'D'),
('0581', 'Aanduiding prestatiecodelijst ontbreekt of is onjuist.', 'D'),
('0582', 'Prestatiecode (of artikel [AP] GPH-/DBC-declaratiecode) ontbreekt of is onjuist (niet bestaande code).', 'D'),
('0584', 'Maximum aantal prestaties / maximum tijd is overschreden.', 'D'),
('0586', 'Declaratie is te laat ingediend.', 'D'),
('0587', 'Prestatie / declaratie is reeds gedeclareerd en/of vergoed of is al eerder vergoed aan een ander.', 'D'),
('0589', 'Maximum te claimen bedrag is reeds gedeclareerd.', 'D'),
('0611', 'Tarief prestatie ontbreekt of is niet in overeenstemming met landelijke of contractafspraken.', 'D'),
('0613', 'Prestatie is niet (volledig) declarabel volgens de verzekeringsvoorwaarden.', 'D'),
('0630', 'Debiteurnummer ontbreekt of is onjuist.', 'D'),
('0631', 'Indicatie debet/credit ontbreekt of is onjuist.', 'D'),
('0633', 'Datum prestatie voldoet niet aan termijn eerdere prestatie.', 'D'),
('0634', 'Prestatie voldoet niet in combinatie met eerdere prestatie.', 'D'),
('0638', 'Aantal uitgevoerde prestaties of hoeveelheid afgeleverd ontbreekt of is onjuist.', 'D'),
('0651', 'Referentienummer voorgaande gerelateerde prestatie ontbreekt of is onjuist.', 'D'),
('0698', 'Prestatie mag niet gedeclareerd worden volgens wettelijke declaratiebepalingen (NZa).', 'D'),
('0704', 'Voorletters ontbreken of zijn onjuist (debiteur).', 'D'),
('0706', 'Naam debiteur (01) ontbreekt of is onjuist.', 'D'),
('0708', 'Huisnummer (huisadres) ontbreekt of is onjuist (debiteur).', 'D'),
('0711', 'Bankrekeningnummer debiteur ontbreekt of is onjuist.', 'D'),
('0754', 'Regelnummer vrije tekst ontbreekt of is onjuist.', 'D'),
('0755', 'Vrije tekst ontbreekt.', 'D'),
('0801', 'Identificatie detailrecord ontbreekt of is onjuist.', 'X'),
('0804', 'Lengte record is onjuist.', 'X'),
('5603', 'Nota niet in overeenstemming met GMSB.', 'D'),
('5655', 'Volgnummer DBC-declaratie is niet uniek.', 'D'),
('5703', 'DBC-prestatiecode ontbreekt of is onjuist.', 'D'),
('5755', 'DBC-poortspecialisme is onbekend of onjuist.', 'D'),
('5760', 'Zorgverlenerscode behandelaar/uitvoerder is onbekend of onjuist.', 'D'),
('5803', 'Verrekenpercentage/factor ontbreekt of is onjuist.', 'D'),
('8001', 'Declaratie is volledig toegewezen.', 'O'),
('8002', 'Record is niet beoordeeld (wegens afkeuring boven- of ondergeschikt[e] record[s]).', 'O'),
('8003', 'Declaratieperiode is niet aansluitend of niet volgens afspraak.', 'X'),
('8004', 'Combinatie BSN en geboortedatum verzekerde is onjuist.', 'D'),
('8005', 'Combinatie BSN en naam verzekerde is onjuist.', 'D'),
('8006', 'Er is geen toestemming verleend aan servicebureau om prestatie / declaratie door te sturen.', 'D'),
('8007', '(Begin-/eind)datum prestatie ontbreekt of is onjuist.', 'D'),
('8008', 'Opgegeven prestatie mag niet uitgevoerd worden bij verzekerde.', 'D'),
('8010', 'Prestatie mag volgens de contractvoorwaarden met de zorgverzekeraar niet verricht worden door behandelaar/uitvoerder.', 'D'),
('8011', 'Zorgverlenerscode behandelaar/uitvoerder of specialisme behandelaar/uitvoerder ontbreekt of is onjuist.', 'D'),
('8013', 'Opgegeven prestatie op verzoek van deze voorschrijver/verwijzer is niet declarabel.', 'D'),
('8016', 'BTW-percentage declaratiebedrag is onjuist.', 'D'),
('8017', 'Van deze creditering is geen debitering bekend.', 'D'),
('8019', 'Overschrijding/overlapping DBC-periode.', 'D'),
('8020', 'Prestatie valt binnen de looptijd van een andere gerelateerde prestatie.', 'D'),
('8021', 'Referentienummer dit prestatierecord ontbreekt of is niet uniek (reeds aangeleverd).', 'D'),
('8022', 'Er is geen contract met de aangeduide individuele declarant, declarende praktijk of declarerende instelling binnen de aangegeven declaratieperiode.', 'T'),
('8023', 'Er is bij de zorgverzekeraar geen relatie bekend tussen het servicebureau en de zorgaanbieder.', 'T'),
('8024', 'Factuurdatum ligt te ver in het verleden (niet conform gemaakte afspraken).', 'T'),
('8026', 'Volgorde recordtypen is niet correct.', 'X'),
('8027', 'Subversienummer ontbreekt of is onjuist.', 'X'),
('8028', 'Soort bericht ontbreekt of is onjuist.', 'X'),
('8029', 'Identificatiecode betaling aan ontbreekt of is onjuist.', 'X'),
('8030', 'Indicatie cliënt overleden ontbreekt of is onjuist.', 'D'),
('8031', 'Doorsturen toegestaan ontbreekt of is onjuist.', 'D'),
('8032', 'Berekend bedrag ontbreekt of voldoet niet aan format.', 'D'),
('8033', 'Declaratiebedrag ontbreekt of voldoet niet aan format.', 'D'),
('8059', 'Afwijkend berekend bedrag wordt toegezegd.', 'D'),
('8060', 'Er is geen contract met de aangeduide individuele declarant, declarende praktijk of declarerende instelling binnen de aangegeven declaratieperiode voo', 'D'),
('8061', 'De zorgverzekeraar ondersteunt het ontvangen van het declaratiebestand via VECOZO volgens de gebruikte standaard (soort EI-standaard of (sub)versienum', 'X'),
('8062', 'Debetregel en identieke creditregel in hetzelfde bestand is niet toegestaan.', 'D'),
('8063', ' Berekend bedrag mag niet lager zijn dan gedeclareerd bedrag.', 'D'),
('8064', 'Indicaties Debet/Credit mogen niet verschillend zijn binnen één record.', 'D'),
('8066', 'Geen relatie bekend tussen zorgverlener in voorlooprecord en praktijk in voorlooprecord.', 'T'),
('8067', 'Declaratie is reeds eerder ingediend.', 'T'),
('8070', 'De zorgaanbieder dient de declaratie in buiten het servicebureau om, terwijl volgens de zorgverzekeraar er een contract is met een servicebureau die h', 'T'),
('8071', 'Relatie tussen DBC-prestatiecode en DBC-declaratiecode is onjuist.', 'D'),
('8072', 'Prestatie is afgewezen; voor meer informatie, neem contact op met de zorgverzekeraar.', 'D'),
('8073', 'Declaratie is volledig afgewezen.', 'T'),
('8074', 'Declaratie is niet in behandeling genomen; voor meer informatie, neem contact op met de zorgverzekeraar.', 'T'),
('8075', 'Recordtype 03 (Debiteurrecord) niet toegestaan bij bestandsuitwisseling naar zorgverzekeraar.', 'T'),
('8076', 'Soort inhouding ontbreekt of is onjuist.', 'D'),
('8077', 'Bedrag inhouding ontbreekt of is onjuist.', 'D'),
('8080', 'Soort inhouding ontbreekt of is onjuist bij ingevuld bedrag inhouding.', 'D'),
('8084', 'Debetregel is al eerder gecrediteerd.', 'D'),
('8095', 'CR/LF zonder voorafgaand record of record zonder aansluitend CR/LF is niet toegestaan.', 'X'),
('8096', 'Prestatie- en/of tariefrecord is als goed beoordeeld, maar afgewezen omdat het hele bestand is afgekeurd.', 'X'),
('8097', 'Bestand is afgekeurd, omdat volgens afspraak alleen volledig goedgekeurde bestanden toegekend worden.', 'X');
