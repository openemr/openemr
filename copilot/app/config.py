from functools import lru_cache

from pydantic import model_validator
from pydantic_settings import BaseSettings, SettingsConfigDict


class Settings(BaseSettings):
    model_config = SettingsConfigDict(env_file=".env", extra="ignore")

    # Primary LLM provider. Architecture defense: Anthropic is preferred (BAA,
    # prompt caching, clinical-reasoning quality). OpenAI is a runtime fallback
    # for outages or billing issues — same agent loop, different model adapter.
    llm_provider: str = "anthropic"  # "anthropic" or "openai"

    anthropic_api_key: str = ""
    anthropic_model: str = "claude-sonnet-4-6"

    openai_api_key: str = ""
    openai_model: str = "gpt-4o"

    # Base URL convenience field.  When set, openemr_fhir_base and
    # openemr_oauth_base are derived from it (unless overridden individually).
    openemr_base_url: str = ""
    openemr_fhir_base: str = "https://host.docker.internal:9300/apis/default/fhir"
    openemr_oauth_base: str = "https://host.docker.internal:9300/oauth2/default"
    openemr_verify_tls: bool = True  # set False for local self-signed cert

    @model_validator(mode="after")
    def _derive_urls_from_base(self) -> "Settings":
        if self.openemr_base_url:
            base = self.openemr_base_url.rstrip("/")
            if self.openemr_fhir_base == "https://host.docker.internal:9300/apis/default/fhir":
                self.openemr_fhir_base = f"{base}/apis/default/fhir"
            if self.openemr_oauth_base == "https://host.docker.internal:9300/oauth2/default":
                self.openemr_oauth_base = f"{base}/oauth2/default"
        return self

    oauth_client_id: str = ""
    oauth_client_secret: str = ""
    oauth_grant_type: str = "password"  # "password" for user-role; "client_credentials" for system-role JWKS
    oauth_scopes: str = (
        "openid offline_access api:fhir "
        "user/Patient.read user/Observation.read user/MedicationRequest.read "
        "user/Condition.read user/Encounter.read user/AllergyIntolerance.read "
        "user/DocumentReference.read user/Binary.read"
    )
    # `user/Binary.read` is required by the W2 KR5 pending-intakes preview
    # path: when a front-desk DocumentReference's `content[].attachment.url`
    # points to `Binary/{id}` (the OpenEMR-default shape), the preview
    # endpoint chases that reference. Without this scope a SMART/FHIR
    # server that enforces resource scopes returns 403 and the modal
    # reports `binary_not_found`.
    # Legacy single-physician demo (kept for backwards compat with .env)
    oauth_username: str = "admin"
    oauth_password: str = "pass"
    oauth_user_role: str = "users"  # OpenEMR distinguishes users vs portal patients

    # --- SMART app launch (auth-code + PKCE) ---
    # Used in production. Falls back to per-physician password grant when
    # smart_dev_launch_enabled is True (demo-safe).
    oauth_redirect_uri: str = "https://copilot-production-b532.up.railway.app/v1/oauth/callback"
    oauth_authorize_path: str = "/authorize"
    oauth_token_path: str = "/token"
    oauth_refresh_skew_seconds: int = 30

    smart_dev_launch_enabled: bool = True
    # JSON map of physician_user_id -> {"username": "...", "password": "..."}
    # Kept in env (not committed). Example:
    #   SMART_DEV_CREDENTIALS='{"dr_alvarez":{"username":"dr_alvarez","password":"pass"}}'
    smart_dev_credentials: str = "{}"

    # A.7 panel enforcement (workaround). OpenEMR's FHIR Patient resource
    # does NOT expose Patient.generalPractitioner for the Co-Pilot's auth
    # path even when patient_data.providerID is set — verified live on
    # Railway 2026-05-02 (meta.lastUpdated reflects writes immediately,
    # but generalPractitioner stays absent). We therefore maintain the
    # panel in env as a JSON map of physician_user_id -> [patient FHIR
    # uuid, ...]. Example:
    #   PHYSICIAN_PATIENT_PANEL='{"dr_alvarez":["a1ab...","a1ab..."],"dr_chen":[...]}'
    # When a physician has an entry, /v1/sessions enforces against it.
    # Empty/missing → falls back to the FHIR Patient.generalPractitioner
    # path (currently a no-op until OpenEMR fixes its FHIR transformer).
    physician_patient_panel: str = "{}"

    langfuse_public_key: str = ""
    langfuse_secret_key: str = ""
    langfuse_host: str = "https://cloud.langfuse.com"

    demo_physician_user_id: str = "admin"
    demo_physician_username: str = "admin"

    use_mock_fhir: bool = False

    fhir_timeout_seconds: float = 5.0
    agent_max_tool_iterations: int = 8

    # --- Conversation persistence (resume feature) ---
    # SQLite file path. /data is mounted as a Railway volume in production
    # so conversations survive container restarts. In dev (no volume) it
    # falls back to a file in the working directory.
    conversation_db_path: str = "/data/copilot.db"
    # Window for "Resume previous chat?" prompt. A conversation older than
    # this is treated as abandoned; the iframe starts a fresh session.
    resume_window_hours: int = 24
    # Cap on prior turns replayed into the LLM context. Mirrors
    # ARCHITECTURE.md §9.4 (multi-turn cap).
    resume_replay_max_turns: int = 10

    # --- Document ingestion (Week 2) ---
    # SQLite file path for the processed-document dedup store.
    copilot_docs_db_path: str = "./copilot_docs.db"
    # Claude vision model used by VlmExtractor.
    vlm_model_id: str = "claude-sonnet-4-6"

    # --- Front-desk role (W2 LITE — deferred extraction) ---
    # Comma-separated OpenEMR usernames that play the front-desk role.
    # When `physician_user_id` matches one of these, `/v1/documents/attach`
    # bypasses the per-physician panel gate (front desk is allowed to file
    # documents to any patient) and skips agent extraction at upload time.
    # The physician later triggers extraction by clicking the resulting
    # pending-intake banner item, which POSTs `/v1/documents/{doc_id}/process`.
    # Empty string (default) preserves the legacy single-role behavior.
    copilot_front_desk_users: str = ""


@lru_cache
def get_settings() -> Settings:
    return Settings()
