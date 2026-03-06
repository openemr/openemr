FROM node:22-alpine AS build

RUN --mount=type=cache,target=/var/cache/apk \
    apk add g++ \
            make \
            python3
WORKDIR /app
COPY ccdaservice/ ./
RUN --mount=type=cache,target=/root/.npm \
    npm ci --production --ignore-scripts \
 && npm rebuild libxmljs2

FROM node:22-alpine

RUN --mount=type=cache,target=/var/cache/apk \
    apk add libstdc++
WORKDIR /app
COPY --from=build /app ./
COPY docker/ccdaservice/healthcheck.js ./

ENV CCDA_SERVICE_HOST=0.0.0.0
ENV CCDA_SERVICE_PORT=6661

HEALTHCHECK --interval=10s --timeout=3s --start-period=5s --retries=3 \
    CMD ["node", "healthcheck.js"]

CMD ["serveccda.js"]
