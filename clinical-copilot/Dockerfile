# Clinical Co-Pilot sidecar — production image.
# Multi-stage build: install deps in the builder, ship a slim runtime.
FROM python:3.12-slim AS builder
ENV PIP_NO_CACHE_DIR=1 PIP_DISABLE_PIP_VERSION_CHECK=1
WORKDIR /build
COPY pyproject.toml ./
COPY sidecar ./sidecar
COPY bff ./bff
RUN pip install --upgrade pip wheel \
    && pip install ".[openai,langgraph,postgres,observability,phi]"

FROM python:3.12-slim AS runtime
ENV PYTHONDONTWRITEBYTECODE=1 PYTHONUNBUFFERED=1 PYTHONHASHSEED=random
RUN useradd --uid 10001 --create-home --shell /bin/bash copilot
WORKDIR /app
COPY --from=builder /usr/local/lib/python3.12/site-packages /usr/local/lib/python3.12/site-packages
COPY --from=builder /usr/local/bin /usr/local/bin
COPY sidecar ./sidecar
COPY bff ./bff
COPY ui ./ui
COPY fixtures ./fixtures
USER copilot
EXPOSE 8801
HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD python -c "import urllib.request; urllib.request.urlopen('http://127.0.0.1:8801/health', timeout=2)" || exit 1
CMD ["uvicorn", "sidecar.main:app", "--host", "0.0.0.0", "--port", "8801"]
