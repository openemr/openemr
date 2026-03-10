"use strict";

const s = require("net").createConnection(
    { port: process.env.CCDA_SERVICE_PORT || 6661, host: "127.0.0.1" },
    () => { s.end(); process.exit(0); }
);
s.on("error", () => process.exit(1));
