.PHONY: help dev down build test eval eval-fast eval-baseline eval-live logs shell

IMAGE = copilot-copilot
RUN = docker run --rm -v "$$(pwd):/srv" -w /srv $(IMAGE) sh -c

help:
	@echo "make dev           — run with hot reload on :8080"
	@echo "make down          — stop dev container"
	@echo "make build         — rebuild docker image"
	@echo "make test          — pytest tools subset"
	@echo "make eval          — pytest full + W2 50-case eval gate (writes evals/RESULTS.md)"
	@echo "make eval-fast     — W2 eval gate FAST_SUBSET (~12 cases) — pre-push hook target"
	@echo "make eval-baseline — re-freeze evals/baseline.json from current rates"
	@echo "make eval-live     — pytest with ANTHROPIC_LIVE=1 (real LLM calls; consumes credit)"
	@echo "make shell         — interactive shell in the container"

dev:
	docker compose up

down:
	docker compose down

build:
	docker compose build

test:
	$(RUN) "pip install -q pytest pytest-asyncio respx && python -m pytest evals/tools -v"

eval:
	$(RUN) "pip install -q pytest pytest-asyncio respx pyyaml && python -m pytest evals -v && python -m evals.runner --cases-root evals/cases --with-corpus"

# Pre-push hook target — must complete in <2 min, no live LLM, no real FHIR.
# Runs the FAST_SUBSET (~12 cases) including 2 retrieval cases that build a
# small in-process SQLite corpus.
eval-fast:
	$(RUN) "pip install -q pyyaml && python -m evals.runner --fast --with-corpus --cases-root evals/cases"

eval-baseline:
	$(RUN) "pip install -q pyyaml && python -m evals.runner --regenerate-baseline --cases-root evals/cases --with-corpus"

eval-live:
	docker run --rm -v "$$(pwd):/srv" -w /srv \
	  -e ANTHROPIC_LIVE=1 -e ANTHROPIC_API_KEY=$$ANTHROPIC_API_KEY \
	  $(IMAGE) sh -c "pip install -q pytest pytest-asyncio respx && python -m pytest evals -v"

logs:
	docker compose logs -f

shell:
	docker run --rm -it -v "$$(pwd):/srv" -w /srv $(IMAGE) bash
