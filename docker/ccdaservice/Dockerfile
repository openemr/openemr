FROM node:22-alpine AS build

RUN apk add --no-cache python3 make g++
WORKDIR /app
COPY ccdaservice/package.json ccdaservice/package-lock.json ./
COPY ccdaservice/oe-blue-button-generate/ ./oe-blue-button-generate/
COPY ccdaservice/packages/ ./packages/
RUN npm ci --production --ignore-scripts && npm rebuild libxmljs2

FROM node:22-alpine

RUN apk add --no-cache libstdc++
WORKDIR /app
COPY ccdaservice/ ./
COPY --from=build /app/node_modules ./node_modules
COPY --from=build /app/oe-blue-button-generate ./oe-blue-button-generate
COPY --from=build /app/packages ./packages

ENV CCDA_SERVICE_HOST=0.0.0.0
ENV CCDA_SERVICE_PORT=6661
EXPOSE 6661

HEALTHCHECK --interval=10s --timeout=3s --start-period=5s --retries=3 \
    CMD node -e "const s=require('net').createConnection({port:6661,host:'127.0.0.1'},()=>{s.end();process.exit(0)});s.on('error',()=>process.exit(1))"

CMD ["node", "serveccda.js"]
