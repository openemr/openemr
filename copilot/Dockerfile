FROM python:3.11-slim AS builder

WORKDIR /build
RUN pip install --no-cache-dir --upgrade pip
COPY pyproject.toml ./
RUN pip install --no-cache-dir --prefix=/install \
    "fastapi>=0.115" "uvicorn[standard]>=0.32" "httpx>=0.27" \
    "anthropic>=0.40" "openai>=1.50" "pydantic>=2.9" "pydantic-settings>=2.5" \
    "langfuse>=2.50,<3" "python-dotenv>=1.0" "sse-starlette>=2.1" \
    "aiosqlite>=0.20" "python-multipart>=0.0.9" \
    "langgraph>=0.2,<0.3" "pyyaml>=6.0"

FROM python:3.11-slim
WORKDIR /srv
COPY --from=builder /install /usr/local
COPY app/ ./app/
COPY . .

# Diagnostic — print full app/ tree so any missing subdir is visible in the
# build log before the import smoke test fails.
RUN echo "=== app/ tree ===" && find app -maxdepth 3 -type d && echo "=== app/tools/ files ===" && ls -la app/tools/ 2>&1 || true

# Make the source pip-installable so app.* modules resolve from site-packages
# regardless of WORKDIR or PYTHONPATH quirks at runtime.
RUN pip install --no-cache-dir --no-deps -e .

# Fail the build immediately if critical source directories are missing
RUN python -c "import app.tools.registry; import app.agent.loop; import app.graph.state; import app.graph.workers.answer_composer; import langgraph; print('All modules verified')"

# /data holds the conversation-history SQLite file. The Railway volume is
# attached to /data via service config (railway.toml + dashboard) — Railway
# rejects the Docker VOLUME instruction at build time, so we only ensure
# the directory exists with the right perms here.
RUN mkdir -p /data && chmod 700 /data

ENV PYTHONUNBUFFERED=1 \
    PYTHONDONTWRITEBYTECODE=1 \
    PORT=8080 \
    CONVERSATION_DB_PATH=/data/copilot.db

EXPOSE 8080
CMD ["sh", "-c", "uvicorn app.main:app --host 0.0.0.0 --port ${PORT}"]
