# About
This directory holds the npm workspace for `oe-cqm-service`, the CQM calculator
OpenEMR uses for eCQM and QRDA reporting. It runs on port 6660 and is started
on demand by OpenEMR.

C-CDA documents are no longer generated here. They are built in PHP by
`OpenEMR\Cda\InternalToCdaConverter`, and schematron validation is handled by
`OpenEMR\Services\Cda\Schematron\SchematronValidator`. Neither needs Node.
The directory keeps its name so existing installs, CI and Docker setups that
install the CQM dependencies from this path continue to work.

## Install or update
Stop any running node processes first, then from `openemr/ccdaservice` run:
- `npm ci --omit=dev`

Latest version tested is node v24.1.0.

## Use
* CQM calculation starts the service automatically when a report needs it.
* C-CDA documents are enabled separately in
  `Admin->Config->Connectors->Enable C-CDA Documents`.

#### License
        Copyright 2018-2023 sjpadgett@gmail.com
        https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
