const app = require('express')();
const bodyParser = require('body-parser');
const compression = require('compression');
const winston = require('winston');
const calculator = require('cqm-execution').Calculator;

app.use(compression());
app.use(bodyParser.json({ limit: '50mb' }));
app.use(bodyParser.urlencoded({ extended: true, limit: '50mb', parameterLimit: '5000' }));

const LISTEN_PORT = 6660; // Port to listen on. Hardcoded elsewhere to 6660 as a safe port.
const SERVER_HOST = '127.0.0.1'; // Listen on loopback interface so not exposed to outside world
const REQUIRED_PARAMS = ['measure', 'valueSets', 'patients']; // Required params for calculation

process.on('SIGTERM', shutDown);
process.on('SIGINT', shutDown);

let connections = [];

app.on('connection', connection => {
    connections.push(connection);
    connection.on('close', () => connections = connections.filter(curr => curr !== connection));
});

function shutDown() {
    server.close(() => {
        console.log('Closed out remaining connections');
        process.exit(0);
    });

    setTimeout(() => {
        console.error('Could not close connections in time, forcefully shutting down');
        process.exit(1);
    }, 10000);

    connections.forEach(curr => curr.end());
    setTimeout(() => connections.forEach(curr => curr.destroy()), 5000);
}

// All logging is to the console, docker can save these messages to file if desired
const logger = winston.createLogger({
  level: 'info',
  format: winston.format.combine(
    winston.format.timestamp(),
    winston.format.simple()
  ),
  transports: (process.env.NODE_ENV == 'test') ? [new winston.transports.File({ filename: 'test_log.info'})] : [ new winston.transports.Console() ]
});

app.get('/health', function (request, response) {
  logger.log({ level: 'info', message: 'GET /health. headers: ' + JSON.stringify(request.headers) });
  response.send({
    'uptime': process.uptime()
  });
});

/**
 * Version; Informs a client which version of js-ecqm-engine and cqm-models this
 * service is currently utilizing.
 *
 * @name Version
 * @route {GET} /version
 */
app.get('/version', function (request, response) {
  logger.log({ level: 'info', message: 'GET /version. headers: ' + JSON.stringify(request.headers) });
  response.send({
    'cqm-execution': '?', //response.send(engine.version) TODO: Add this when cqm-execution is in NPM
    'cqm-models': '?' //response.send(models.version) TODO: Add this when cqm-models is in NPM
  });
});

/**
 * Calculate a CQM.
 *
 * @name Calculate
 * @route {POST} /calculate
 * @bodyparam measure - the cqm to calculate against.
 * @bodyparam valueSets - array of the value sets to use when calculating the measure
 * @bodyparam patients - an array of cqm-models based patients to calculate for.
 * @bodyparam options - optional params for things like generating pretty results.
 */
app.post('/calculate', function (request, response) {
  // Certain params are required for this action, make sure they exist.
  let missing = []
  REQUIRED_PARAMS.forEach(function (param) {
    if (!request.body[param]) {
      missing.push(param);
    }
  });
  // If there are missing params, return a 400 with a description of which
  // params were missing.
  if (missing.length) {
    logger.log({ level: 'error', message: `GET /calculate. missing params ${missing.join(', ')}, headers: ${JSON.stringify(request.headers)}` });
    response.status(400).send({
      error: `Missing required parameter(s): ${missing.join(', ')}`, request: request.body
    });
    return;
  }

  // Grab params from request.
  const {measure, valueSets, patients, options = {}} = request.body

  const valueSetsObj = JSON.parse(valueSets)
  if (!Array.isArray(valueSetsObj)){
    logger.log({ level: 'error', message: 'GET /calculate. valueSets not passed as an array, headers: ' + JSON.stringify(request.headers) });
    response.status(400).send({'input error': 'value sets must be passed in as an array'});
    return;
  }
  const measureObj = JSON.parse(measure)
  const patientsObj = JSON.parse(patients)
  const optionsObj = JSON.parse(options)
  try {
    results = calculator.calculate(measureObj, patientsObj, valueSetsObj, optionsObj);
    logger.log({ level: 'info', message: 'GET /calculate. measure: ' + measureObj['cms_id'] + ' patient_count: ' + patientsObj.length });
    response.json(results);
  } catch(error) {
    logger.log({ level: 'error', message: `GET /calculate. error in the calculation engine: ${error} headers: ${JSON.stringify(request.headers)}` });
    response.status(500).send({'error in the calculation engine': error});
    return;
  }

});

/**
 * Version; Informs a client which version of js-ecqm-engine and cqm-models this
 * service is currently utilizing.
 *
 * @name Version
 * @route {GET} /version
 */
app.get('/shutdown', function (request, response) {
    logger.log({ level: 'info', message: 'GET /shutdown. headers: ' + JSON.stringify(request.headers) });
    shutDown();
    response.send({
        'shutdown': true,
    });
});

app.use(function (request, response, next) {
  response.status(404).send();
});

const server = app.listen(LISTEN_PORT, SERVER_HOST, () =>
{
    logger.log({level: 'info', message: 'cqm-execution-service is now listening on port ' + LISTEN_PORT});
    app.emit("listening")
});

module.exports = server
