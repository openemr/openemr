# Multi-stage build for the Next.js dashboard. Standalone output mode
# (next.config.ts → output: "standalone") lets the runtime stage carry
# only the compiled server + static assets — no node_modules dance.

# ── deps ─────────────────────────────────────────────────────────────
FROM node:24-alpine AS deps
WORKDIR /app
# Tailwind v4's PostCSS plugin uses native modules for some platforms;
# alpine + apk libc6-compat covers the common case.
RUN apk add --no-cache libc6-compat
COPY package.json package-lock.json ./
RUN npm ci

# ── build ────────────────────────────────────────────────────────────
FROM node:24-alpine AS build
WORKDIR /app
COPY --from=deps /app/node_modules ./node_modules
COPY . .
ENV NEXT_TELEMETRY_DISABLED=1
RUN npm run build

# ── run ──────────────────────────────────────────────────────────────
FROM node:24-alpine AS run
WORKDIR /app
ENV NODE_ENV=production
ENV NEXT_TELEMETRY_DISABLED=1
# Run as non-root for defense-in-depth.
RUN addgroup -g 1001 -S nodejs && adduser -S nextjs -u 1001 -G nodejs
COPY --from=build --chown=nextjs:nodejs /app/.next/standalone ./
COPY --from=build --chown=nextjs:nodejs /app/.next/static ./.next/static
COPY --from=build --chown=nextjs:nodejs /app/public ./public
USER nextjs
EXPOSE 3000
ENV PORT=3000
ENV HOSTNAME=0.0.0.0
CMD ["node", "server.js"]
