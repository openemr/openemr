// jshint node:true
const http = require('http');
const fs = require('fs');
const config = require('./config');
const express = require('express');
const path = require('path');
const bodyParser = require('body-parser');

const temp = create();
const app = temp[0];
const logger = temp[1];
const port = config.server.port;
app.set('port', port);

const server = http.createServer(app);
logger.info('HTTP server listening on port %s', port);

/**
 * Listen on provided port, on all network interfaces.
 */
server.listen(port);
server.on('error', onError);

/**
 * Event listener for HTTP server "error" event.
 */

function onError(error) {
    if (error.syscall !== 'listen') {
        throw error;
    }

    let bind = typeof port === 'string' ?
        'Pipe ' + port :
        'Port ' + port;

    // handle specific listen errors with friendly messages
    switch (error.code) {
        case 'EACCES':
            logger.error(bind + ' requires elevated privileges');
            process.exit(1);
            break;
        case 'EADDRINUSE':
            logger.error(bind + ' is already in use');
            process.exit(1);
            break;
        default:
            throw error;
    }
}

function create() {
    const app = express();
    // Create base directory, if it doesn't already exist
    let baseDirectory = path.join(__dirname, config.validator.baseDirectory);
    fs.existsSync(baseDirectory) || fs.mkdirSync(baseDirectory);

    // Configure logging
    const winston = require('winston');
    const morgan = require('morgan');
    const logDirectory = path.join(__dirname, config.server.logDirectory);
    fs.existsSync(logDirectory) || fs.mkdirSync(logDirectory);
    const logger = new winston.Logger({
        transports: [
            new winston.transports.File({
                level: 'info',
                filename: logDirectory + '/validation_logs.log',
                handleExceptions: true,
                json: true,
                maxsize: 5242880, //5MB
                maxFiles: 5,
                colorize: false
            }),
            new winston.transports.Console({
                level: 'debug',
                handleExceptions: true,
                json: false,
                colorize: true
            })
        ],
        exitOnError: false
    });
    logger.stream = {
        write: function (message) {
            logger.info(message);
        }
    };
    app.use(morgan('dev', {stream: logger.stream}));

    // Set up data pipeline
    app.use(bodyParser.raw({type: '*/*', limit: '50mb'}));

    // Log incoming requests
    app.use(function (req, res, next) {
        logger.info(((req.headers['x-forwarded-for'] || '').split(',')[0]
            || req.connection.remoteAddress) + ' ' + req.method + ' ' + req.path);
        next();
    });

    // Configure paths
    app.use('/', require(path.join(__dirname, config.server.routesDirectory, 'index'))(logger));

    // Catch 404 and forward to error handler
    app.use(function (req, res, next) {
        let err = new Error('Not Found');
        err.status = 404;
        next(err);
    });

    app.use(function (err, req, res) {
        if (!err.status) {
            logger.error(err);
            throw err;
        }
        res.status(err.status || 500);
        res.send('<b>' + err.status + ':</b> ' + err.message);
    });

    return [app, logger];
}