"""Configuration loaded from the environment.

Centralised so every component shares the same view of provider, model,
and timeout settings. See ``.env.example`` for the canonical list.
"""

from __future__ import annotations

from functools import lru_cache

from sidecar._compat import StrEnum

from pydantic import Field, field_validator
from pydantic_settings import BaseSettings, SettingsConfigDict


class LLMProvider(StrEnum):
    OPENAI = "openai"
    AZURE = "azure"
    MOCK = "mock"


class Settings(BaseSettings):
    """Sidecar settings.

    Every field has a safe default so the service starts in mock-LLM mode
    without any environment configuration. This makes the eval suite and
    smoke tests runnable in CI without an OpenAI key.
    """

    model_config = SettingsConfigDict(
        env_file=".env",
        env_file_encoding="utf-8",
        env_prefix="COPILOT_",
        case_sensitive=False,
        extra="ignore",
    )

    # ─── LLM ────────────────────────────────────────────────────────────
    # Default to live OpenAI; mock is opt-in and gated by allow_mock so the
    # OpenEMR launch button can never accidentally route through it.
    llm_provider: LLMProvider = LLMProvider.OPENAI
    openai_model: str = "gpt-4o-mini"
    allow_mock: bool = False
    openai_api_key: str | None = Field(default=None, alias="OPENAI_API_KEY")
    openai_base_url: str | None = Field(default=None, alias="OPENAI_BASE_URL")
    azure_openai_endpoint: str | None = Field(default=None, alias="AZURE_OPENAI_ENDPOINT")
    azure_openai_api_key: str | None = Field(default=None, alias="AZURE_OPENAI_API_KEY")
    azure_openai_deployment: str | None = Field(default=None, alias="AZURE_OPENAI_DEPLOYMENT")

    @field_validator(
        "openai_api_key", "openai_base_url",
        "azure_openai_endpoint", "azure_openai_api_key", "azure_openai_deployment",
        mode="before",
    )
    @classmethod
    def _empty_string_is_none(cls, v: object) -> object:
        """An empty .env value (``KEY=``) is loaded as ``""`` by pydantic-
        settings. The OpenAI SDK breaks if ``base_url=""`` is passed (it
        builds requests against the empty origin → ``APIConnectionError``).
        Treat empty strings as missing for every URL/key field."""
        if isinstance(v, str) and v.strip() == "":
            return None
        return v

    # Per-pair LLM settings.
    pair_judge_max_concurrency: int = 20
    pair_judge_timeout_seconds: float = 30.0
    pair_judge_max_pairs: int = 200  # cap per ARCHITECTURE.md §8 risk row

    # ─── OpenEMR FHIR ──────────────────────────────────────────────────
    openemr_fhir_base: str = "https://localhost:9300/apis/default/fhir"
    openemr_oauth_base: str = "https://localhost:9300/oauth2/default"
    openemr_client_id: str = "clinical-copilot"
    openemr_client_secret: str | None = None
    fhir_verify_ssl: bool = False

    # ─── Cache + audit ─────────────────────────────────────────────────
    database_url: str = "postgresql+psycopg://copilot:copilot@localhost:5433/copilot"
    snapshot_ttl_seconds: int = 86_400  # 24 hours
    audit_retention_days: int = 2_555  # 7 years

    # ─── Service binding ───────────────────────────────────────────────
    bff_host: str = "0.0.0.0"
    bff_port: int = 8800
    sidecar_host: str = "0.0.0.0"
    sidecar_port: int = 8801
    task_token_lifetime_seconds: int = 300
    bff_jwt_signing_key: str = "change-me-to-a-32-byte-hex-string"
    sidecar_url: str = "http://localhost:8801"

    # ─── PHI ───────────────────────────────────────────────────────────
    phi_scrub_enabled: bool = True


@lru_cache(maxsize=1)
def get_settings() -> Settings:
    """Return the singleton settings instance."""
    return Settings()
