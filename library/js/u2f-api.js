/*
 * OpenEMR U2F compatibility API backed by the browser WebAuthn API.
 *
 * This file preserves the legacy u2f.register() and u2f.sign() interface used
 * by OpenEMR while allowing existing U2F/CTAP1 security keys to work in modern
 * Chrome, Edge, Firefox, and Safari.
 *
 * Registration requests are constrained to cross-platform authenticators and
 * ES256 so a U2F security key produces the "fido-u2f" attestation format. The
 * WebAuthn result is converted back into the response structure expected by
 * yubico/u2flib-server.
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
'use strict';

var u2f = u2f || {};

u2f.ErrorCodes = {
    OK: 0,
    OTHER_ERROR: 1,
    BAD_REQUEST: 2,
    CONFIGURATION_UNSUPPORTED: 3,
    DEVICE_INELIGIBLE: 4,
    TIMEOUT: 5
};

u2f.base64UrlToBytes_ = function(value) {
    var input = String(value || '').replace(/-/g, '+').replace(/_/g, '/');
    while (input.length % 4) {
        input += '=';
    }

    var binary = window.atob(input);
    var bytes = new Uint8Array(binary.length);
    for (var i = 0; i < binary.length; i++) {
        bytes[i] = binary.charCodeAt(i);
    }
    return bytes;
};

u2f.bytesToBase64Url_ = function(value) {
    var bytes = value instanceof Uint8Array ? value : new Uint8Array(value);
    var binary = '';
    for (var i = 0; i < bytes.length; i++) {
        binary += String.fromCharCode(bytes[i]);
    }

    return window.btoa(binary)
        .replace(/\+/g, '-')
        .replace(/\//g, '_')
        .replace(/=+$/g, '');
};

u2f.concatBytes_ = function(parts) {
    var length = 0;
    for (var i = 0; i < parts.length; i++) {
        length += parts[i].length;
    }

    var result = new Uint8Array(length);
    var offset = 0;
    for (var j = 0; j < parts.length; j++) {
        result.set(parts[j], offset);
        offset += parts[j].length;
    }
    return result;
};

u2f.mapError_ = function(error) {
    if (!error) {
        return u2f.ErrorCodes.OTHER_ERROR;
    }

    switch (error.name) {
        case 'NotAllowedError':
            return u2f.ErrorCodes.TIMEOUT;
        case 'InvalidStateError':
            return u2f.ErrorCodes.DEVICE_INELIGIBLE;
        case 'NotSupportedError':
            return u2f.ErrorCodes.CONFIGURATION_UNSUPPORTED;
        case 'SecurityError':
        case 'DataError':
        case 'TypeError':
            return u2f.ErrorCodes.BAD_REQUEST;
        default:
            return u2f.ErrorCodes.OTHER_ERROR;
    }
};

u2f.failure_ = function(callback, error) {
    window.console.error('U2F/WebAuthn operation failed', error);
    callback({
        errorCode: u2f.mapError_(error),
        errorMessage: error && error.message ? error.message : 'U2F operation failed'
    });
};

u2f.ensureSupported_ = function(callback) {
    if (!window.isSecureContext || !window.PublicKeyCredential || !navigator.credentials) {
        callback({
            errorCode: u2f.ErrorCodes.CONFIGURATION_UNSUPPORTED,
            errorMessage: 'This browser requires HTTPS and WebAuthn support for U2F security keys.'
        });
        return false;
    }
    return true;
};

/*
 * Small CBOR decoder supporting the definite-length values present in a
 * WebAuthn attestation object and COSE EC2 public key.
 */
u2f.CborReader_ = function(bytes, offset) {
    this.bytes = bytes;
    this.offset = offset || 0;
};

u2f.CborReader_.prototype.readLength_ = function(additional) {
    if (additional < 24) {
        return additional;
    }
    if (additional === 24) {
        return this.bytes[this.offset++];
    }
    if (additional === 25) {
        var value16 = (this.bytes[this.offset] << 8) | this.bytes[this.offset + 1];
        this.offset += 2;
        return value16;
    }
    if (additional === 26) {
        var value32 =
            (this.bytes[this.offset] * 0x1000000) +
            (this.bytes[this.offset + 1] << 16) +
            (this.bytes[this.offset + 2] << 8) +
            this.bytes[this.offset + 3];
        this.offset += 4;
        return value32;
    }

    throw new Error('Unsupported CBOR length encoding');
};

u2f.CborReader_.prototype.read = function() {
    if (this.offset >= this.bytes.length) {
        throw new Error('Unexpected end of CBOR data');
    }

    var initial = this.bytes[this.offset++];
    var major = initial >> 5;
    var additional = initial & 31;
    var length;
    var i;

    if (major === 0) {
        return this.readLength_(additional);
    }
    if (major === 1) {
        return -1 - this.readLength_(additional);
    }
    if (major === 2) {
        length = this.readLength_(additional);
        var byteString = this.bytes.slice(this.offset, this.offset + length);
        this.offset += length;
        return byteString;
    }
    if (major === 3) {
        length = this.readLength_(additional);
        var textBytes = this.bytes.slice(this.offset, this.offset + length);
        this.offset += length;
        return new TextDecoder('utf-8').decode(textBytes);
    }
    if (major === 4) {
        length = this.readLength_(additional);
        var array = [];
        for (i = 0; i < length; i++) {
            array.push(this.read());
        }
        return array;
    }
    if (major === 5) {
        length = this.readLength_(additional);
        var map = new Map();
        for (i = 0; i < length; i++) {
            map.set(this.read(), this.read());
        }
        return map;
    }
    if (major === 6) {
        this.readLength_(additional);
        return this.read();
    }
    if (major === 7) {
        if (additional === 20) {
            return false;
        }
        if (additional === 21) {
            return true;
        }
        if (additional === 22 || additional === 23) {
            return null;
        }
    }

    throw new Error('Unsupported CBOR value');
};

u2f.decodeCbor_ = function(bytes, offset) {
    var reader = new u2f.CborReader_(bytes, offset || 0);
    return {
        value: reader.read(),
        offset: reader.offset
    };
};

u2f.convertRegistration_ = function(credential) {
    var attestationBytes = new Uint8Array(credential.response.attestationObject);
    var attestation = u2f.decodeCbor_(attestationBytes).value;

    if (!(attestation instanceof Map) || attestation.get('fmt') !== 'fido-u2f') {
        throw new Error(
            'The selected authenticator did not return a U2F-compatible attestation. ' +
            'Use a physical U2F/FIDO security key.'
        );
    }

    var authData = attestation.get('authData');
    var attStmt = attestation.get('attStmt');
    if (!(authData instanceof Uint8Array) || !(attStmt instanceof Map)) {
        throw new Error('Invalid U2F attestation response');
    }

    var signature = attStmt.get('sig');
    var certificateChain = attStmt.get('x5c');
    if (!(signature instanceof Uint8Array) ||
        !Array.isArray(certificateChain) ||
        !(certificateChain[0] instanceof Uint8Array)) {
        throw new Error('Missing U2F attestation certificate or signature');
    }

    // rpIdHash(32), flags(1), signCount(4), AAGUID(16), credentialIdLength(2)
    if (authData.length < 55) {
        throw new Error('U2F authenticator data is too short');
    }

    var credentialLength = (authData[53] << 8) | authData[54];
    var credentialStart = 55;
    var credentialEnd = credentialStart + credentialLength;
    if (credentialEnd > authData.length) {
        throw new Error('Invalid U2F credential identifier length');
    }

    var credentialId = authData.slice(credentialStart, credentialEnd);
    var coseResult = u2f.decodeCbor_(authData, credentialEnd);
    var coseKey = coseResult.value;
    if (!(coseKey instanceof Map)) {
        throw new Error('Invalid U2F public key');
    }

    var x = coseKey.get(-2);
    var y = coseKey.get(-3);
    if (!(x instanceof Uint8Array) || !(y instanceof Uint8Array) ||
        x.length !== 32 || y.length !== 32) {
        throw new Error('U2F key did not return a P-256 public key');
    }

    var publicKey = u2f.concatBytes_([
        new Uint8Array([0x04]),
        x,
        y
    ]);

    if (credentialId.length > 255) {
        throw new Error('U2F key handle exceeds the legacy protocol limit');
    }

    var registrationData = u2f.concatBytes_([
        new Uint8Array([0x05]),
        publicKey,
        new Uint8Array([credentialId.length]),
        credentialId,
        certificateChain[0],
        signature
    ]);

    return {
        version: 'U2F_V2',
        registrationData: u2f.bytesToBase64Url_(registrationData),
        clientData: u2f.bytesToBase64Url_(
            new Uint8Array(credential.response.clientDataJSON)
        )
    };
};

u2f.register = function(appId, registerRequests, registeredKeys, callback, timeoutSeconds) {
    if (!u2f.ensureSupported_(callback)) {
        return;
    }

    try {
        if (!Array.isArray(registerRequests) || registerRequests.length === 0) {
            throw new Error('Missing U2F registration request');
        }

        var request = registerRequests[0];
        var userId = new Uint8Array(32);
        window.crypto.getRandomValues(userId);

        var excludeCredentials = [];
        if (Array.isArray(registeredKeys)) {
            for (var i = 0; i < registeredKeys.length; i++) {
                if (registeredKeys[i] && registeredKeys[i].keyHandle) {
                    excludeCredentials.push({
                        type: 'public-key',
                        id: u2f.base64UrlToBytes_(registeredKeys[i].keyHandle),
                        transports: ['usb', 'nfc', 'ble']
                    });
                }
            }
        }

        var publicKey = {
            challenge: u2f.base64UrlToBytes_(request.challenge),
            rp: {
                id: window.location.hostname,
                name: 'OpenEMR'
            },
            user: {
                id: userId,
                name: 'openemr-u2f-user',
                displayName: 'OpenEMR U2F User'
            },
            pubKeyCredParams: [
                {
                    type: 'public-key',
                    alg: -7
                }
            ],
            timeout: Math.max(1, Number(timeoutSeconds) || 60) * 1000,
            excludeCredentials: excludeCredentials,
            authenticatorSelection: {
                authenticatorAttachment: 'cross-platform',
                residentKey: 'discouraged',
                requireResidentKey: false,
                userVerification: 'discouraged'
            },
            attestation: 'direct'
        };

        navigator.credentials.create({publicKey: publicKey})
            .then(function(credential) {
                callback(u2f.convertRegistration_(credential));
            })
            .catch(function(error) {
                u2f.failure_(callback, error);
            });
    } catch (error) {
        u2f.failure_(callback, error);
    }
};

u2f.sign = function(appId, challenge, registeredKeys, callback, timeoutSeconds) {
    if (!u2f.ensureSupported_(callback)) {
        return;
    }

    try {
        if (!Array.isArray(registeredKeys) || registeredKeys.length === 0) {
            throw new Error('No U2F security keys are registered');
        }

        var allowCredentials = [];
        for (var i = 0; i < registeredKeys.length; i++) {
            allowCredentials.push({
                type: 'public-key',
                id: u2f.base64UrlToBytes_(registeredKeys[i].keyHandle),
                transports: ['usb', 'nfc', 'ble']
            });
        }

        var publicKey = {
            challenge: u2f.base64UrlToBytes_(challenge),
            timeout: Math.max(1, Number(timeoutSeconds) || 60) * 1000,
            rpId: window.location.hostname,
            allowCredentials: allowCredentials,
            userVerification: 'discouraged',
            extensions: {
                appid: appId
            }
        };

        navigator.credentials.get({publicKey: publicKey})
            .then(function(credential) {
                var response = credential.response;
                var signatureData = u2f.concatBytes_([
                    new Uint8Array(response.authenticatorData),
                    new Uint8Array(response.signature)
                ]);

                callback({
                    keyHandle: u2f.bytesToBase64Url_(
                        new Uint8Array(credential.rawId)
                    ),
                    signatureData: u2f.bytesToBase64Url_(signatureData),
                    clientData: u2f.bytesToBase64Url_(
                        new Uint8Array(response.clientDataJSON)
                    )
                });
            })
            .catch(function(error) {
                u2f.failure_(callback, error);
            });
    } catch (error) {
        u2f.failure_(callback, error);
    }
};
