'use strict';

const net = require('net');
const { spawn } = require('child_process');
const path = require('path');
const assert = require('node:assert/strict');
const { after, before, describe, it } = require('node:test');

const FS = String.fromCharCode(28);
const SERVICE_HOST = '127.0.0.1';

let serviceProcess;
let servicePort;

function findAvailablePort() {
    return new Promise((resolve, reject) => {
        const socket = net.createServer();
        socket.on('error', reject);
        socket.listen(0, SERVICE_HOST, () => {
            const address = socket.address();
            socket.close(error => {
                if (error) {
                    reject(error);
                    return;
                }
                resolve(address.port);
            });
        });
    });
}

function connectAndSend(xml) {
    return new Promise((resolve, reject) => {
        const client = new net.Socket();
        let response = '';
        const timeout = setTimeout(() => {
            client.destroy();
            reject(new Error('Connection timed out'));
        }, 10000);
        client.connect(servicePort, SERVICE_HOST, () => {
            client.write(xml + FS);
        });
        client.on('data', chunk => {
            response += chunk.toString();
        });
        client.on('end', () => {
            clearTimeout(timeout);
            const terminator = FS + '\r\r';
            resolve(response.endsWith(terminator) ? response.slice(0, -terminator.length) : response);
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
        client.connect(servicePort, SERVICE_HOST, () => {
            client.end();
            resolve();
        });
        client.on('error', () => {
            setTimeout(() => waitForService(retries - 1).then(resolve, reject), 200);
        });
    });
}

before(async () => {
    servicePort = await findAvailablePort();
    serviceProcess = spawn('node', [path.join(__dirname, 'serveccda.js')], {
        env: { ...process.env, CCDA_SERVICE_HOST: SERVICE_HOST, CCDA_SERVICE_PORT: String(servicePort) },
        stdio: 'ignore',
        detached: true,
    });
    serviceProcess.unref();
    await waitForService();
}, { timeout: 15000 });

after(() => {
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
        const malformedXml = '<CCDA><doc_type>ccd</doc_type></CCDA>';
        const response = await connectAndSend(malformedXml);
        assert.match(response, /^ERROR:/);
    });

    it('stays alive after receiving malformed input', async () => {
        const malformedXml = '<CCDA><doc_type>ccd</doc_type></CCDA>';
        await connectAndSend(malformedXml);

        const secondResponse = await connectAndSend(malformedXml);
        assert.match(secondResponse, /^ERROR:/);
    });

    it('returns an error response for an empty frame', async () => {
        const response = await connectAndSend('');
        assert.match(response, /^ERROR:/);
    });
});
