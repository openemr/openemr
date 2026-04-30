.PHONY: help dev down build test eval logs shell

IMAGE = copilot-copilot
RUN = docker run --rm -v "$$(pwd):/srv" -w /srv $(IMAGE) sh -c

help:
	@echo "make dev       — run with hot reload on :8080"
	@echo "make down      — stop dev container"
	@echo "make build     — rebuild docker image"
	@echo "make test      — run pytest unit tests"
	@echo "make eval      — run full eval suite + write evals/RESULTS.md"
	@echo "make shell     — interactive shell in the container"

dev:
	docker compose up

down:
	docker compose down

build:
	docker compose build

test:
	$(RUN) "pip install -q pytest pytest-asyncio respx && python -m pytest evals/tools -v"

eval:
	$(RUN) "pip install -q pytest pytest-asyncio respx && python -m pytest evals -v"

eval-live:
	docker run --rm -v "$$(pwd):/srv" -w /srv \
	  -e ANTHROPIC_LIVE=1 -e ANTHROPIC_API_KEY=$$ANTHROPIC_API_KEY \
	  $(IMAGE) sh -c "pip install -q pytest pytest-asyncio respx && python -m pytest evals -v"

logs:
	docker compose logs -f

shell:
	docker run --rm -it -v "$$(pwd):/srv" -w /srv $(IMAGE) bash
