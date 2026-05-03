FROM python:3.11-slim AS builder

WORKDIR /build
RUN pip install --no-cache-dir --upgrade pip
COPY pyproject.toml ./
RUN pip install --no-cache-dir --prefix=/install \
    "fastapi>=0.115" "uvicorn[standard]>=0.32" "httpx>=0.27" \
    "anthropic>=0.40" "openai>=1.50" "pydantic>=2.9" "pydantic-settings>=2.5" \
    "langfuse>=2.50,<3" "python-dotenv>=1.0" "sse-starlette>=2.1" \
    "aiosqlite>=0.20"

FROM python:3.11-slim
WORKDIR /srv
COPY --from=builder /install /usr/local
COPY app ./app

# /data holds the conversation-history SQLite file. Mounted as a Railway
# volume in production so it survives container restarts. The app creates
# the file with mode 0600 on first init.
RUN mkdir -p /data && chmod 700 /data
VOLUME ["/data"]

ENV PYTHONUNBUFFERED=1 \
    PYTHONDONTWRITEBYTECODE=1 \
    PORT=8080 \
    CONVERSATION_DB_PATH=/data/copilot.db

EXPOSE 8080
CMD ["sh", "-c", "uvicorn app.main:app --host 0.0.0.0 --port ${PORT}"]
