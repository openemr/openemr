// jshint node:true
const fs = require('fs');
const express = require('express');
const router = express.Router();
const path = require('path');
const validator = require('cda-schematron');
const config = require('../config');

// Where to look for resource files
let baseDirectory = path.join(config.server.appDirectory, config.validator.baseDirectory);
let schemaType = 'ccda';
let schematron = null;

function fetchSchema(type = 'ccda') {
    schemaType = type;
    baseDirectory += "/" + type;
    let contents = fs.readdirSync(baseDirectory);
    for (let i = 0; i < contents.length; i++) {
        if (contents[i].slice(-4) === '.sch') {
            schematron = fs.readFileSync(path.join(baseDirectory, contents[i]), 'utf-8').toString();
            console.log('Using ' + contents[i]);
            break;
        }
    }
    if (!schematron) {
        console.log('\nERROR: A schematron (.sch) could not be found in the following directory:');
        console.log(baseDirectory);
        console.log('\nPlease add one and try again.\n');
        process.exit();
    }
}

// Load schematron default once at start, using path in arguments or in config
fetchSchema('ccda');

module.exports = function (logger) {
    router.post('/', function (req, res) {
        reqType = req.query.type;
        let xml = req.body.toString();
        logger.info('Validating.. (size: ' + xml.length + ')');
        if (reqType !== schemaType) {
            logger.info('Changing Schema (New Schema: ' + reqType + ')');
            baseDirectory = path.join(config.server.appDirectory, config.validator.baseDirectory);
            fetchSchema(reqType);
        }

        let results = validator.validate(xml, schematron, {
            includeWarnings: config.validator.includeWarnings,
            resourceDir: baseDirectory,
            xmlSnippetMaxLength: config.validator.xmlSnippetMaxLength
        });

        res.json(results);
    });

    return router;
};
