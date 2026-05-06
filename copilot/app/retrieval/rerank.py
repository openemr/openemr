"""Reranker layer for the W2 hybrid retrieval pipeline.

PRD-Core req #3 ("hybrid RAG + rerank") asks that "only top grounded
evidence reaches the answer model." Today's evidence_retriever runs
BM25 over the 12-chunk seed corpus; this module sits between BM25 and
the answer composer to permit downstream rescoring.

Three implementations ship:

  - ``IdentityReranker`` — the CI / no-credentials default. Passes
    BM25 hits through unchanged, returning a parallel ``[1.0, 1.0, …]``
    score list. Zero ops cost.
  - ``CohereReranker`` — calls Cohere Rerank when ``COHERE_API_KEY`` is
    set. Lazy-imports ``cohere``; raises only on actual call, not
    import.
  - ``LocalCrossEncoderReranker`` — uses
    ``sentence-transformers/cross-encoder/ms-marco-MiniLM-L6-v2`` as a
    local fallback when Cohere is unavailable but ``sentence_transformers``
    is installed. Lazy-imports the heavy dep.

``get_reranker()`` selects based on environment + import availability,
with the order: COHERE_API_KEY → Cohere; else local cross-encoder if
importable; else IdentityReranker.

Dense retrieval (OpenAI embeddings) is **deferred to Final** — adding
it now would require either paid API calls in CI or downloading an
embedding model. Identity-default rerank closes the rerank surface
without that cost.
"""
from __future__ import annotations

import logging
import os
from typing import Any, Protocol

logger = logging.getLogger("copilot.retrieval.rerank")


class Reranker(Protocol):
    """Re-score / re-order retrieval hits.

    Implementations take a query string + the BM25 (or hybrid) hits
    list and return ``(reordered_hits, scores)``. Score ordering matches
    the returned hits — index 0 has the highest score.
    """

    name: str

    def rerank(
        self, query: str, hits: list[Any], *, top_k: int | None = None
    ) -> tuple[list[Any], list[float]]: ...


# ────────────────────────────────────────────────────────────────────


class IdentityReranker:
    """No-op reranker — preserves BM25 ordering and emits 1.0 scores.

    The CI / no-credentials default. Architecturally satisfies "rerank
    exists" without paying for it.
    """

    name = "identity"

    def rerank(
        self, query: str, hits: list[Any], *, top_k: int | None = None
    ) -> tuple[list[Any], list[float]]:
        out_hits = list(hits)
        if top_k is not None:
            out_hits = out_hits[:top_k]
        return out_hits, [1.0] * len(out_hits)


# ────────────────────────────────────────────────────────────────────


class CohereReranker:
    """Real Cohere Rerank — gated by ``COHERE_API_KEY``.

    Lazy-imports ``cohere`` so missing the package doesn't break
    module-level imports. Calling ``rerank`` without the package or
    key raises ``RuntimeError``.
    """

    name = "cohere"

    def __init__(self, api_key: str, model: str = "rerank-english-v3.0") -> None:
        self._api_key = api_key
        self._model = model

    def rerank(
        self, query: str, hits: list[Any], *, top_k: int | None = None
    ) -> tuple[list[Any], list[float]]:
        try:
            import cohere  # noqa: PLC0415
        except ImportError as e:  # pragma: no cover — env-dependent
            raise RuntimeError("cohere package not installed") from e

        client = cohere.Client(self._api_key)
        # Hits are GuidelineHit objects with a ``text`` attribute.
        texts = [getattr(h, "text", str(h)) for h in hits]
        n = top_k if top_k is not None else len(hits)
        resp = client.rerank(model=self._model, query=query, documents=texts, top_n=n)
        # ``resp.results`` is a list of {index, relevance_score} entries.
        ordered_hits: list[Any] = []
        scores: list[float] = []
        for r in resp.results:
            ordered_hits.append(hits[r.index])
            scores.append(float(r.relevance_score))
        return ordered_hits, scores


# ────────────────────────────────────────────────────────────────────


class LocalCrossEncoderReranker:
    """Local fallback using ``sentence-transformers/cross-encoder``.

    Lazy-imports ``sentence_transformers`` so the ~80MB dep isn't pulled
    in CI. Caches the model on first ``rerank`` call.
    """

    name = "local-cross-encoder"

    def __init__(self, model_name: str = "cross-encoder/ms-marco-MiniLM-L6-v2") -> None:
        self._model_name = model_name
        self._model: Any | None = None

    def _load(self) -> Any:
        if self._model is None:
            from sentence_transformers import CrossEncoder  # noqa: PLC0415
            self._model = CrossEncoder(self._model_name)
        return self._model

    def rerank(
        self, query: str, hits: list[Any], *, top_k: int | None = None
    ) -> tuple[list[Any], list[float]]:
        if not hits:
            return [], []
        try:
            model = self._load()
        except ImportError as e:  # pragma: no cover — env-dependent
            raise RuntimeError(
                "sentence_transformers not installed; cannot use local cross-encoder"
            ) from e
        texts = [getattr(h, "text", str(h)) for h in hits]
        pairs = [(query, t) for t in texts]
        raw_scores = model.predict(pairs)
        scored = sorted(zip(hits, raw_scores), key=lambda p: float(p[1]), reverse=True)
        if top_k is not None:
            scored = scored[:top_k]
        return [s[0] for s in scored], [float(s[1]) for s in scored]


# ────────────────────────────────────────────────────────────────────


_CACHED: Reranker | None = None


def get_reranker() -> Reranker:
    """Pick the best available reranker.

    Order:
      1. ``COHERE_API_KEY`` set → ``CohereReranker``
      2. ``sentence_transformers`` importable → ``LocalCrossEncoderReranker``
      3. Default → ``IdentityReranker``

    Cached at module level — first call sets the choice; subsequent calls
    return the same instance.
    """
    global _CACHED
    if _CACHED is not None:
        return _CACHED

    if os.environ.get("COHERE_API_KEY"):
        _CACHED = CohereReranker(api_key=os.environ["COHERE_API_KEY"])
        logger.info("reranker=cohere")
        return _CACHED

    try:
        import sentence_transformers  # noqa: F401, PLC0415
        _CACHED = LocalCrossEncoderReranker()
        logger.info("reranker=local-cross-encoder")
        return _CACHED
    except ImportError:
        pass

    _CACHED = IdentityReranker()
    logger.info("reranker=identity")
    return _CACHED


def reset_reranker_cache() -> None:
    """Test helper — re-evaluate get_reranker() on next call."""
    global _CACHED
    _CACHED = None


__all__ = [
    "Reranker",
    "IdentityReranker",
    "CohereReranker",
    "LocalCrossEncoderReranker",
    "get_reranker",
    "reset_reranker_cache",
]
