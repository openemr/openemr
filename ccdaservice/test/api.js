var fs = require('fs');
var path = require('path');
var child_process = require('child_process');

var request = require('supertest');
var expect = require('chai').expect;

var config = require('../config');

// URL of server
var url = 'http://localhost:' + config.server.port;

var server;

function cleanup() { // Kills the server
    child_process.spawn('kill', [server.pid]);
}

describe('Server should', function() {
    this.timeout(1500);
    it('startup', function(done) {
        server = child_process.spawn('node', ['app']);
        setTimeout(function() {
            request(url)
                .post('/')
                .send('<xml></xml>') // Send anything to make sure the server is responsive
                .end(function (err, res) {
                    if (err) {
                        console.log(err);
                        expect('Is server running?').to.be.equal(true);
                        done();
                    }
                    else {
                        var response = JSON.parse(res.text);
                        expect(response).to.be.an('object');
                        done();
                    }
                });
        }, 200);
    });
});
describe('Validator api should', function() {
    this.timeout(1000);
    var response;
    it('return a JSON object', function(done) {
        request(url)
            .post('/')
            .send('<xml></xml>')
            .end(function (err, res) {
                if (err) {
                    console.log(err);
                    expect('Is server running?').to.be.equal(true);
                    done();
                }
                else {
                    response = JSON.parse(res.text);
                    expect(response).to.be.an('object');
                    done();
                }
            });
    });
    it('return a JSON object with the correct fields', function(done) {
        expect(response.errorCount).to.be.a('number');
        expect(response.warningCount).to.be.a('number');
        expect(response.ignoredCount).to.be.a('number');
        expect(response.errors).to.be.an('array');
        expect(response.warnings).to.be.an('array');
        expect(response.ignored).to.be.an('array');
        done();
    });
});

after(function(done) {
    cleanup();
    done();
});
