FROM node:24-alpine

# Build dependencies for native modules (libxmljs2)
RUN apk add --no-cache python3 make g++

WORKDIR /app

# Copy package files and local dependencies for npm install
COPY package*.json ./
COPY oe-blue-button-generate ./oe-blue-button-generate/
COPY oe-blue-button-meta ./oe-blue-button-meta/
COPY oe-blue-button-util ./oe-blue-button-util/
COPY packages ./packages/

# Install production dependencies
RUN npm ci --omit=dev

# Copy remaining application code
COPY serveccda.js ./
COPY data-stack ./data-stack/
COPY utils ./utils/

# Create non-root user for security
RUN addgroup -g 1000 ccda && adduser -u 1000 -G ccda -s /bin/sh -D ccda
RUN chown -R ccda:ccda /app
USER ccda

# Default to binding all interfaces for Docker networking
ENV CCDA_HOST=0.0.0.0
ENV CCDA_PORT=6661

EXPOSE 6661

HEALTHCHECK --interval=30s --timeout=3s --start-period=5s \
    CMD node -e "const s=require('net').connect(6661,'127.0.0.1');s.on('connect',()=>{s.end();process.exit(0)});s.on('error',()=>process.exit(1))"

CMD ["node", "serveccda.js"]
