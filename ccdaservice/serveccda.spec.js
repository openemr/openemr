'use strict';

const net = require('net');
const { spawn } = require('child_process');
const path = require('path');

const FS = String.fromCharCode(28);
const SERVICE_PORT = 6661;
const SERVICE_HOST = '127.0.0.1';

let serviceProcess;

function connectAndSend(xml) {
    return new Promise((resolve, reject) => {
        const client = new net.Socket();
        let response = '';
        const timeout = setTimeout(() => {
            client.destroy();
            reject(new Error('Connection timed out'));
        }, 10000);
        client.connect(SERVICE_PORT, SERVICE_HOST, () => {
            client.write(xml + FS);
        });
        client.on('data', chunk => {
            response += chunk.toString();
        });
        client.on('end', () => {
            clearTimeout(timeout);
            resolve(response.replace(/\x1c\r\r$/, ''));
        });
        client.on('error', err => {
            clearTimeout(timeout);
            reject(err);
        });
    });
}

function waitForService(retries = 30) {
    return new Promise((resolve, reject) => {
        if (retries <= 0) {
            reject(new Error('Service did not start'));
            return;
        }
        const client = new net.Socket();
        client.connect(SERVICE_PORT, SERVICE_HOST, () => {
            client.end();
            resolve();
        });
        client.on('error', () => {
            setTimeout(() => waitForService(retries - 1).then(resolve, reject), 200);
        });
    });
}

beforeAll(async () => {
    serviceProcess = spawn('node', [path.join(__dirname, 'serveccda.js')], {
        env: { ...process.env, CCDA_SERVICE_HOST: SERVICE_HOST, CCDA_SERVICE_PORT: String(SERVICE_PORT) },
        stdio: 'ignore',
        detached: true,
    });
    serviceProcess.unref();
    await waitForService();
}, 15000);

afterAll(() => {
    if (serviceProcess && serviceProcess.pid) {
        try {
            process.kill(-serviceProcess.pid, 'SIGTERM');
        } catch (_) {
            // already exited
        }
    }
});

describe('serveccda error handling', () => {
    it('returns an error response for XML with missing patient data', async () => {
        // Valid XML wrapper but missing required <patient> element
        const malformedXml = '<CCDA><doc_type>ccd</doc_type></CCDA>';
        const response = await connectAndSend(malformedXml);
        expect(response).toMatch(/^ERROR:/);
    });

    it('stays alive after receiving malformed input', async () => {
        // Send malformed input first
        const malformedXml = '<CCDA><doc_type>ccd</doc_type></CCDA>';
        await connectAndSend(malformedXml);

        // Verify service is still accepting connections
        const secondResponse = await connectAndSend(malformedXml);
        expect(secondResponse).toMatch(/^ERROR:/);
    });

    it('returns a response (not a crash) for empty CCDA', async () => {
        const emptyXml = '<CCDA></CCDA>';
        const response = await connectAndSend(emptyXml);
        // Empty CCDA produces a minimal XML header — the important thing
        // is the service doesn't crash.
        expect(response).toBeTruthy();
    });
});
