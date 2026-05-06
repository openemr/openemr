"""Unit tests for the reranker module."""
from __future__ import annotations

from app.retrieval.rerank import (
    CohereReranker,
    IdentityReranker,
    LocalCrossEncoderReranker,
    Reranker,
    get_reranker,
    reset_reranker_cache,
)


def test_identity_reranker_preserves_order_and_emits_1_0_scores() -> None:
    hits = [{"chunk_id": "a"}, {"chunk_id": "b"}, {"chunk_id": "c"}]
    out, scores = IdentityReranker().rerank("any query", hits)
    assert [h["chunk_id"] for h in out] == ["a", "b", "c"]
    assert scores == [1.0, 1.0, 1.0]


def test_identity_reranker_respects_top_k() -> None:
    hits = [{"chunk_id": "a"}, {"chunk_id": "b"}, {"chunk_id": "c"}]
    out, scores = IdentityReranker().rerank("any query", hits, top_k=2)
    assert len(out) == 2
    assert len(scores) == 2


def test_get_reranker_returns_identity_when_no_credentials_or_st(monkeypatch) -> None:
    monkeypatch.delenv("COHERE_API_KEY", raising=False)
    # Force the sentence_transformers import path to fail.
    import sys
    monkeypatch.setitem(sys.modules, "sentence_transformers", None)
    reset_reranker_cache()

    r = get_reranker()
    assert r.name == "identity"
    assert isinstance(r, IdentityReranker)


def test_get_reranker_picks_cohere_when_key_set(monkeypatch) -> None:
    monkeypatch.setenv("COHERE_API_KEY", "fake-key")
    reset_reranker_cache()

    r = get_reranker()
    assert r.name == "cohere"
    assert isinstance(r, CohereReranker)


def test_reranker_protocol_is_satisfied_by_identity() -> None:
    r: Reranker = IdentityReranker()
    assert hasattr(r, "name")
    assert callable(r.rerank)


def test_local_cross_encoder_constructor_is_lazy(monkeypatch) -> None:
    """Constructing the class must NOT trigger the import."""
    import sys
    monkeypatch.setitem(sys.modules, "sentence_transformers", None)
    reranker = LocalCrossEncoderReranker()
    # Constructor is fine; the heavy import only fires on rerank() call.
    assert reranker.name == "local-cross-encoder"
