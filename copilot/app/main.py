"""FastAPI entry — Clinical Co-Pilot."""
from __future__ import annotations

import uuid
from contextlib import asynccontextmanager
from pathlib import Path

from fastapi import Depends, FastAPI, HTTPException
from fastapi.responses import FileResponse, JSONResponse
from pydantic import BaseModel, Field

from app.agent.loop import run_turn
from app.config import Settings, get_settings
from app.fhir.client import FhirClient, FhirError
from app.observability.trace import get_tracer
from app.phi.session import sessions


@asynccontextmanager
async def lifespan(app: FastAPI):
    settings = get_settings()
    app.state.fhir = FhirClient(settings)
    app.state.tracer = get_tracer(settings)
    yield
    await app.state.fhir.aclose()


app = FastAPI(
    title="Clinical Co-Pilot",
    version="0.1.0",
    description="AI agent for OpenEMR — point-of-care clinical context for primary care.",
    lifespan=lifespan,
)


@app.get("/healthz")
async def healthz():
    return {"status": "ok", "service": "clinical-copilot"}


WEB_DIR = Path(__file__).parent / "web"


@app.get("/")
async def index():
    return FileResponse(WEB_DIR / "index.html")


@app.get("/v1/patient/{patient_id}/raw")
async def patient_raw(patient_id: str, settings: Settings = Depends(get_settings)):
    fhir: FhirClient = app.state.fhir
    try:
        resource = await fhir.get_resource("Patient", patient_id)
    except FhirError as e:
        raise HTTPException(status_code=e.status or 502, detail=str(e))
    return JSONResponse(resource)


class StartSessionRequest(BaseModel):
    patient_id: str
    physician_user_id: str | None = None


class StartSessionResponse(BaseModel):
    session_id: str
    patient_pseudonym: str


@app.post("/v1/sessions", response_model=StartSessionResponse)
async def start_session(
    body: StartSessionRequest, settings: Settings = Depends(get_settings)
):
    session_id = str(uuid.uuid4())
    physician = body.physician_user_id or settings.demo_physician_user_id
    pseudo = sessions.create(session_id, physician, body.patient_id)
    return StartSessionResponse(
        session_id=session_id, patient_pseudonym=pseudo.patient_pseudonym()
    )


class ChatRequest(BaseModel):
    session_id: str
    question: str
    proposed_drug: str | None = Field(
        default=None,
        description="UC3 helper — pass when the question is 'is X safe to add?'",
    )


@app.post("/v1/chat")
async def chat(body: ChatRequest, settings: Settings = Depends(get_settings)):
    session = sessions.get(body.session_id)
    if not session:
        raise HTTPException(status_code=404, detail="session not found or expired")
    fhir: FhirClient = app.state.fhir
    output = await run_turn(
        settings=settings,
        fhir=fhir,
        session=session,
        question=body.question,
        proposed_drug=body.proposed_drug,
    )
    payload = {
        "response": output.response.model_dump(),
        "trace": output.trace.model_dump(),
    }
    app.state.tracer.emit(output.trace, payload["response"])
    return payload
