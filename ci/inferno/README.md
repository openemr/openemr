# OpenEMR Inferno Testing Environment

This directory contains the Docker Compose configuration necessary to run the ONC Inferno Certification test suite against an OpenEMR instance.

## Overview

Inferno is a testing tool developed by the Office of the National Coordinator (ONC) for Health Information Technology to verify compliance with healthcare interoperability standards, particularly FHIR APIs and related security requirements.

The setup in this directory allows you to:
- Run an OpenEMR instance configured for testing
- Run the Inferno testing tools in the same Docker network
- Execute automated compliance tests against the OpenEMR FHIR API

## Quick Start

To run the Inferno test suite against OpenEMR:

```bash
./run.sh
```

This script will:
1. Start all necessary Docker containers defined in `compose.yml`
2. Configure the OpenEMR instance for testing
3. Start the Inferno test suite services including:
   - The main Inferno application
   - Worker nodes for test processing
   - NGINX for the web interface
   - Redis for caching and messaging
   - HL7 validator service

## Architecture

This Docker Compose setup extends the Inferno test tools from the `onc-certification-g10-test-kit` directory while placing them in the same Docker network as the OpenEMR service. This ensures:

- All services can communicate with each other using service names
- Volume mounts in the extended services are relative to the `onc-certification-g10-test-kit` directory
- Custom OpenEMR configuration can be injected for testing purposes

## Services

- **mysql**: Database for OpenEMR
- **openemr**: The OpenEMR instance to be tested
- **inferno**: The main Inferno testing application
- **worker**: Processes test jobs from the queue
- **nginx**: Web server for the Inferno UI (available at http://localhost:8000)
- **redis**: Caching and message queue
- **hl7_validator_service**: Validates HL7 message formats

## Configuration

The OpenEMR instance is configured with:
- Ports: 8080 (HTTP) and 8523 (HTTPS)
- Development mode enabled for testing
- Direct volume mounting of OpenEMR source code

## Additional Resources

- [OpenEMR FHIR Documentation](../../FHIR_README.md)
- [ONC Certification Information](https://www.healthit.gov/topic/certification-ehrs/certification-health-it)
