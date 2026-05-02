from functools import lru_cache

from pydantic_settings import BaseSettings, SettingsConfigDict


class Settings(BaseSettings):
    model_config = SettingsConfigDict(env_file=".env", extra="ignore")

    # Primary LLM provider. Architecture defense: Anthropic is preferred (BAA,
    # prompt caching, clinical-reasoning quality). OpenAI is a runtime fallback
    # for outages or billing issues — same agent loop, different model adapter.
    llm_provider: str = "anthropic"  # "anthropic" or "openai"

    anthropic_api_key: str = ""
    anthropic_model: str = "claude-sonnet-4-5-20250929"

    openai_api_key: str = ""
    openai_model: str = "gpt-4o"

    openemr_fhir_base: str = "https://host.docker.internal:9300/apis/default/fhir"
    openemr_oauth_base: str = "https://host.docker.internal:9300/oauth2/default"
    openemr_verify_tls: bool = True  # set False for local self-signed cert

    oauth_client_id: str = ""
    oauth_client_secret: str = ""
    oauth_grant_type: str = "password"  # "password" for user-role; "client_credentials" for system-role JWKS
    oauth_scopes: str = (
        "openid offline_access api:fhir "
        "user/Patient.read user/Observation.read user/MedicationRequest.read "
        "user/Condition.read user/Encounter.read user/AllergyIntolerance.read "
        "user/DocumentReference.read"
    )
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

    langfuse_public_key: str = ""
    langfuse_secret_key: str = ""
    langfuse_host: str = "https://cloud.langfuse.com"

    demo_physician_user_id: str = "admin"
    demo_physician_username: str = "admin"

    use_mock_fhir: bool = False

    fhir_timeout_seconds: float = 5.0
    agent_max_tool_iterations: int = 8


@lru_cache
def get_settings() -> Settings:
    return Settings()
