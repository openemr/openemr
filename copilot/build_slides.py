"""Generate DEMO_SLIDES.pptx — Early Submission demo deck.

Focus: what shipped between MVP (Tue 04-28) and Early (Thu 04-30),
       with eval framework + Langfuse integration as the headline items.

Run:  python3 build_slides.py
Out:  /Users/rikki/Desktop/Doc/OOD/openemr/copilot/DEMO_SLIDES.pptx
"""
from __future__ import annotations

from pathlib import Path

from pptx import Presentation
from pptx.dml.color import RGBColor
from pptx.enum.shapes import MSO_SHAPE
from pptx.enum.text import PP_ALIGN, MSO_ANCHOR
from pptx.util import Inches, Pt

OUT_PATH = Path(__file__).parent / "DEMO_SLIDES.pptx"

# Palette
NAVY = RGBColor(0x0E, 0x2A, 0x47)
TEAL = RGBColor(0x1F, 0x7A, 0x8C)
ACCENT = RGBColor(0xE8, 0x6A, 0x33)
GRAY_BG = RGBColor(0xF4, 0xF6, 0xF8)
GRAY_LINE = RGBColor(0xC9, 0xD2, 0xDA)
TEXT_DARK = RGBColor(0x1A, 0x1A, 0x1A)
TEXT_MUTED = RGBColor(0x55, 0x60, 0x6A)
WHITE = RGBColor(0xFF, 0xFF, 0xFF)
GREEN = RGBColor(0x2E, 0x8B, 0x57)


# 16:9
prs = Presentation()
prs.slide_width = Inches(13.333)
prs.slide_height = Inches(7.5)
SW, SH = prs.slide_width, prs.slide_height
BLANK = prs.slide_layouts[6]


# ---------- helpers --------------------------------------------------------

def add_slide():
    return prs.slides.add_slide(BLANK)


def add_rect(slide, x, y, w, h, fill, line=None):
    shp = slide.shapes.add_shape(MSO_SHAPE.RECTANGLE, x, y, w, h)
    shp.fill.solid()
    shp.fill.fore_color.rgb = fill
    if line is None:
        shp.line.fill.background()
    else:
        shp.line.color.rgb = line
        shp.line.width = Pt(0.75)
    shp.shadow.inherit = False
    return shp


def add_text(slide, x, y, w, h, text, *,
             size=14, bold=False, color=TEXT_DARK, align=PP_ALIGN.LEFT,
             anchor=MSO_ANCHOR.TOP, font="Calibri"):
    tb = slide.shapes.add_textbox(x, y, w, h)
    tf = tb.text_frame
    tf.word_wrap = True
    tf.margin_left = Inches(0.05)
    tf.margin_right = Inches(0.05)
    tf.margin_top = Inches(0.02)
    tf.margin_bottom = Inches(0.02)
    tf.vertical_anchor = anchor
    lines = text.split("\n") if isinstance(text, str) else text
    for i, line in enumerate(lines):
        p = tf.paragraphs[0] if i == 0 else tf.add_paragraph()
        p.alignment = align
        run = p.add_run()
        run.text = line
        run.font.name = font
        run.font.size = Pt(size)
        run.font.bold = bold
        run.font.color.rgb = color
    return tb


def slide_header(slide, eyebrow, title, page=None):
    # Top accent bar
    add_rect(slide, 0, 0, SW, Inches(0.18), TEAL)
    # Eyebrow
    add_text(slide, Inches(0.5), Inches(0.3), Inches(10),
             Inches(0.35), eyebrow.upper(),
             size=11, bold=True, color=TEAL)
    # Title
    add_text(slide, Inches(0.5), Inches(0.6), Inches(12.3),
             Inches(0.9), title,
             size=30, bold=True, color=NAVY)
    # Underline
    add_rect(slide, Inches(0.5), Inches(1.45), Inches(1.0),
             Inches(0.05), ACCENT)
    # Page number / footer
    if page is not None:
        add_text(slide, Inches(12.4), Inches(7.05), Inches(0.8),
                 Inches(0.3), str(page),
                 size=10, color=TEXT_MUTED, align=PP_ALIGN.RIGHT)
    add_text(slide, Inches(0.5), Inches(7.05), Inches(8),
             Inches(0.3), "Clinical Co-Pilot · Early Submission Demo",
             size=10, color=TEXT_MUTED)


def bullet_list(slide, x, y, w, h, items, *, size=16, leading=0.42,
                indent_marker="▸"):
    """Render bullets with a small accent marker."""
    line_h = Inches(leading)
    for i, item in enumerate(items):
        row_y = y + i * line_h
        add_text(slide, x, row_y, Inches(0.3), line_h, indent_marker,
                 size=size, bold=True, color=ACCENT,
                 anchor=MSO_ANCHOR.MIDDLE)
        add_text(slide, x + Inches(0.32), row_y, w - Inches(0.32),
                 line_h, item, size=size, color=TEXT_DARK,
                 anchor=MSO_ANCHOR.MIDDLE)


def two_col(slide, left_title, left_items, right_title, right_items,
            top=Inches(1.85)):
    col_w = Inches(6.0)
    gap = Inches(0.33)
    left_x = Inches(0.5)
    right_x = left_x + col_w + gap

    for x, title, items in [
        (left_x, left_title, left_items),
        (right_x, right_title, right_items),
    ]:
        add_rect(slide, x, top, col_w, Inches(5.0), GRAY_BG)
        add_rect(slide, x, top, Inches(0.18), Inches(5.0), TEAL)
        add_text(slide, x + Inches(0.35), top + Inches(0.18),
                 col_w - Inches(0.5), Inches(0.4), title,
                 size=15, bold=True, color=NAVY)
        bullet_list(slide, x + Inches(0.35), top + Inches(0.7),
                    col_w - Inches(0.5), Inches(4.0), items,
                    size=13, leading=0.42)


def code_block(slide, x, y, w, h, lines, size=12):
    add_rect(slide, x, y, w, h, RGBColor(0x14, 0x1E, 0x2E))
    tb = slide.shapes.add_textbox(x + Inches(0.15), y + Inches(0.1),
                                   w - Inches(0.3), h - Inches(0.2))
    tf = tb.text_frame
    tf.word_wrap = True
    tf.margin_left = Inches(0.05)
    tf.margin_right = Inches(0.05)
    tf.margin_top = Inches(0.02)
    tf.margin_bottom = Inches(0.02)
    for i, line in enumerate(lines):
        p = tf.paragraphs[0] if i == 0 else tf.add_paragraph()
        p.alignment = PP_ALIGN.LEFT
        run = p.add_run()
        run.text = line
        run.font.name = "Consolas"
        run.font.size = Pt(size)
        run.font.color.rgb = RGBColor(0xDC, 0xE8, 0xF4)


def table(slide, x, y, col_widths, headers, rows, *,
          row_h=0.42, header_h=0.42):
    total_w = sum(col_widths)
    cur_x = x
    # Header
    for i, hdr in enumerate(headers):
        add_rect(slide, cur_x, y, col_widths[i], Inches(header_h), NAVY)
        add_text(slide, cur_x + Inches(0.12), y, col_widths[i] - Inches(0.2),
                 Inches(header_h), hdr,
                 size=12, bold=True, color=WHITE,
                 anchor=MSO_ANCHOR.MIDDLE)
        cur_x += col_widths[i]
    # Rows
    for r, row in enumerate(rows):
        cur_x = x
        bg = WHITE if r % 2 == 0 else GRAY_BG
        for i, cell in enumerate(row):
            add_rect(slide, cur_x, y + Inches(header_h) + r * Inches(row_h),
                     col_widths[i], Inches(row_h), bg, line=GRAY_LINE)
            add_text(slide, cur_x + Inches(0.12),
                     y + Inches(header_h) + r * Inches(row_h),
                     col_widths[i] - Inches(0.2), Inches(row_h), cell,
                     size=11, color=TEXT_DARK, anchor=MSO_ANCHOR.MIDDLE)
            cur_x += col_widths[i]
    return total_w


# ---------- slides ---------------------------------------------------------

def slide_title():
    s = add_slide()
    # full-bleed navy
    add_rect(s, 0, 0, SW, SH, NAVY)
    # accent bar
    add_rect(s, 0, Inches(3.4), SW, Inches(0.06), ACCENT)
    add_text(s, Inches(0.8), Inches(2.0), Inches(12), Inches(0.5),
             "EARLY SUBMISSION · 2026-04-30",
             size=14, bold=True, color=ACCENT)
    add_text(s, Inches(0.8), Inches(2.6), Inches(12), Inches(1.2),
             "From MVP to Early Submission",
             size=44, bold=True, color=WHITE)
    add_text(s, Inches(0.8), Inches(3.7), Inches(12), Inches(0.7),
             "Building the Clinical Co-Pilot · Eval framework + Langfuse observability",
             size=20, color=RGBColor(0xC9, 0xD2, 0xDA))
    add_text(s, Inches(0.8), Inches(6.6), Inches(12), Inches(0.4),
             "ARCHITECTURE.md · USERS.md · AUDIT.md → /copilot/ agent service",
             size=12, color=RGBColor(0x9A, 0xA8, 0xB5))


def slide_recap_mvp():
    s = add_slide()
    slide_header(s, "Where we started", "MVP — Tuesday 04-28: docs only", 2)
    add_text(s, Inches(0.5), Inches(1.7), Inches(12), Inches(0.4),
             "MVP shipped three deliverables — all written, none running.",
             size=15, color=TEXT_MUTED)

    # Three cards
    cards = [
        ("AUDIT.md",
         "404 lines.",
         "Codebase audit of OpenEMR. Identified call-site-only ACL, raw PHI flow risk, audit gaps."),
        ("USERS.md",
         "153 lines.",
         "Target user (PCP). 3 use cases (UC1 brief, UC2 reasoning, UC3 med safety). Out-of-scope list."),
        ("ARCHITECTURE.md",
         "592 lines.",
         "Full design — agent service, FHIR tools, PHI minimizer, verification gate, Langfuse, evals."),
    ]
    card_w = Inches(4.0)
    gap = Inches(0.15)
    start_x = Inches(0.5)
    top = Inches(2.4)
    for i, (title, sub, body) in enumerate(cards):
        x = start_x + i * (card_w + gap)
        add_rect(s, x, top, card_w, Inches(3.0), GRAY_BG)
        add_rect(s, x, top, card_w, Inches(0.5), NAVY)
        add_text(s, x + Inches(0.25), top, card_w - Inches(0.5),
                 Inches(0.5), title,
                 size=15, bold=True, color=WHITE,
                 anchor=MSO_ANCHOR.MIDDLE)
        add_text(s, x + Inches(0.25), top + Inches(0.65), card_w - Inches(0.5),
                 Inches(0.4), sub,
                 size=12, bold=True, color=ACCENT)
        add_text(s, x + Inches(0.25), top + Inches(1.05), card_w - Inches(0.5),
                 Inches(1.8), body,
                 size=12, color=TEXT_DARK)

    add_text(s, Inches(0.5), Inches(5.8), Inches(12), Inches(0.4),
             "→ Architecture was on paper. No code, no service, no traces, no tests.",
             size=14, bold=True, color=ACCENT)


def slide_delta_overview():
    s = add_slide()
    slide_header(s, "What changed", "Early Submission — what we shipped this week", 3)

    # Phase chips
    phases = [
        ("Phase A", "Skeleton + OAuth2 + FHIR roundtrip", "✅"),
        ("Phase B", "8 FHIR tools · PHI minimizer · ACL mirror", "✅"),
        ("Phase C", "Agent loop · Layer-1 + Layer-2 verification", "✅"),
        ("Phase D", "Langfuse observability · Eval suite (17/17)", "✅"),
        ("Phase E", "Chat UI · Railway deploy", "✅"),
    ]
    top = Inches(1.85)
    chip_h = Inches(0.85)
    chip_gap = Inches(0.15)
    for i, (label, body, mark) in enumerate(phases):
        y = top + i * (chip_h + chip_gap)
        add_rect(s, Inches(0.5), y, Inches(12.3), chip_h, GRAY_BG)
        add_rect(s, Inches(0.5), y, Inches(0.18), chip_h, TEAL)
        add_text(s, Inches(0.85), y, Inches(2.0), chip_h, label,
                 size=15, bold=True, color=NAVY,
                 anchor=MSO_ANCHOR.MIDDLE)
        add_text(s, Inches(2.85), y, Inches(8.6), chip_h, body,
                 size=14, color=TEXT_DARK, anchor=MSO_ANCHOR.MIDDLE)
        add_text(s, Inches(11.5), y, Inches(1.2), chip_h, mark,
                 size=22, color=GREEN, anchor=MSO_ANCHOR.MIDDLE,
                 align=PP_ALIGN.CENTER)

    add_text(s, Inches(0.5), Inches(7.05), Inches(12), Inches(0.3),
             "Today's focus: Phase D — what the eval suite proves, what Langfuse captures.",
             size=12, color=TEXT_MUTED)


def slide_architecture_traceback():
    s = add_slide()
    slide_header(s, "From design to code",
                 "Every architecture section has a corresponding directory", 4)
    headers = ["ARCHITECTURE.md §", "Component", "Where it lives", "Status"]
    rows = [
        ("§2.1 Custom orchestration", "Tool-calling loop (OpenAI + Anthropic)", "app/agent/loop.py", "✅"),
        ("§3.1–3.2 Tools",            "8 FHIR-backed tools, 5-step pattern",   "app/tools/",       "✅"),
        ("§3.3 PHI minimizer",        "Strip + session pseudonyms",            "app/phi/",         "✅"),
        ("§3.4 Trust boundaries",     "OAuth2 + ACL mirror",                   "app/fhir/, acl/",  "✅"),
        ("§4.1 Layer-1 verifier",     "Source attribution",                    "verification/attribution.py", "✅"),
        ("§4.1 Layer-2 verifier",     "Domain rules (allergy, leakage)",       "verification/rules.py",       "✅ (2 of 4)"),
        ("§5  Observability",         "Langfuse wrapper",                      "app/observability/trace.py",  "✅"),
        ("§6  Evaluation",            "pytest harness + RESULTS.md",           "evals/",                       "✅"),
        ("§10 Deployment",            "Railway service `copilot`",             "Dockerfile · railway.toml",    "✅"),
    ]
    table(s, Inches(0.5), Inches(1.85),
          [Inches(2.7), Inches(3.4), Inches(4.4), Inches(1.8)],
          headers, rows, row_h=0.42)
    add_text(s, Inches(0.5), Inches(6.5), Inches(12), Inches(0.4),
             "One env var swaps Anthropic ↔ OpenAI. Both adapters tested. "
             "Sunday will switch back to Anthropic Sonnet 4.6.",
             size=12, color=TEXT_MUTED)


# -------------------- EVAL ----------------------------

def slide_eval_intro():
    s = add_slide()
    slide_header(s, "Eval framework",
                 "How we know the agent is right — before it ships", 5)
    add_text(s, Inches(0.5), Inches(1.85), Inches(12.3), Inches(0.6),
             "Eval is the offline truth. It runs in CI on every change, "
             "asserts properties of TurnTrace, and blocks releases when something regresses.",
             size=15, color=TEXT_DARK)

    # Big number
    add_rect(s, Inches(0.5), Inches(2.9), Inches(4.0), Inches(3.0), NAVY)
    add_text(s, Inches(0.5), Inches(3.0), Inches(4.0), Inches(0.5),
             "TESTS PASSING",
             size=12, bold=True, color=ACCENT,
             align=PP_ALIGN.CENTER)
    add_text(s, Inches(0.5), Inches(3.5), Inches(4.0), Inches(2.0),
             "17 / 17",
             size=72, bold=True, color=WHITE,
             align=PP_ALIGN.CENTER)
    add_text(s, Inches(0.5), Inches(5.4), Inches(4.0), Inches(0.4),
             "0 failed · 0 skipped",
             size=14, color=RGBColor(0xC9, 0xD2, 0xDA),
             align=PP_ALIGN.CENTER)

    # Right column principles
    items = [
        "Same gate code as production — no test-doubles for the verifier",
        "Hand-crafted FHIR fixtures, deterministic & fast",
        "Synthea = demo data, not eval data (different jobs)",
        "RESULTS.md auto-regenerated every run — repo status never lies",
    ]
    add_text(s, Inches(4.85), Inches(2.9), Inches(8.0), Inches(0.4),
             "Principles", size=15, bold=True, color=NAVY)
    bullet_list(s, Inches(4.85), Inches(3.35), Inches(8.0),
                Inches(2.5), items, size=14, leading=0.55)


def slide_eval_categories():
    s = add_slide()
    slide_header(s, "Eval framework",
                 "Three categories · two are non-negotiable", 6)
    headers = ["Category", "What it checks", "Example tests"]
    rows = [
        ("Factual / attribution",
         "Every clinical claim cites a record_id from a real tool result",
         "test_attribution_passes_when_all_claims_anchored\ntest_attribution_strips_unanchored_claim"),
        ("Adversarial  ★ 100% pass",
         "Cross-patient leakage · prompt injection · ACL bypass",
         "test_cross_patient_leakage_hard_blocks\ntest_prompt_injection_does_not_leak_other_patients\ntest_acl_denies_unknown_role"),
        ("Failure-mode handling",
         "Refuses on missing data · blocks contraindicated meds",
         "test_uc1_refuses_when_no_prior_encounter\ntest_allergy_contraindication_blocks_safe_verdict"),
    ]
    table(s, Inches(0.5), Inches(1.85),
          [Inches(3.0), Inches(4.5), Inches(4.8)],
          headers, rows, row_h=1.1)
    add_text(s, Inches(0.5), Inches(6.0), Inches(12.3), Inches(0.6),
             "★ Cross-patient leakage and ACL bypass are hard 100%-pass gates "
             "in evals/RESULTS.md — anything below blocks the release.",
             size=13, color=ACCENT, bold=True)


def slide_eval_layout_and_run():
    s = add_slide()
    slide_header(s, "Eval framework",
                 "Layout · how it runs · what it produces", 7)

    # Left — file tree
    add_rect(s, Inches(0.5), Inches(1.85), Inches(6.1), Inches(4.6), GRAY_BG)
    add_text(s, Inches(0.7), Inches(1.95), Inches(6), Inches(0.4),
             "evals/  layout",
             size=14, bold=True, color=NAVY)
    code_block(s, Inches(0.7), Inches(2.4), Inches(5.7), Inches(3.95), [
        "evals/",
        "├── conftest.py              # markers + RESULTS.md autogen",
        "├── RESULTS.md               # auto-written after every run",
        "├── tools/",
        "│   ├── test_phi_minimizer.py        7 tests",
        "│   └── test_tool_integration.py     3 tests",
        "└── agent/",
        "    ├── test_verification.py         4 tests",
        "    └── test_scenarios.py            3 live-LLM",
    ], size=11)

    # Right — commands
    add_rect(s, Inches(6.85), Inches(1.85), Inches(6.0), Inches(4.6), GRAY_BG)
    add_text(s, Inches(7.05), Inches(1.95), Inches(6), Inches(0.4),
             "How it runs",
             size=14, bold=True, color=NAVY)
    code_block(s, Inches(7.05), Inches(2.4), Inches(5.6), Inches(1.4), [
        "$ make eval         # offline — mocked FHIR",
        "$ make eval-live    # ANTHROPIC_LIVE=1, hits real API",
    ], size=11)
    add_text(s, Inches(7.05), Inches(3.95), Inches(5.6), Inches(0.4),
             "Hooks (conftest.py)", size=13, bold=True, color=NAVY)
    bullets = [
        "live_llm marker → skips real-LLM tests unless ANTHROPIC_LIVE=1",
        "pytest_sessionfinish → writes evals/RESULTS.md",
        "Repo status stays honest — RESULTS.md never hand-edited",
    ]
    bullet_list(s, Inches(7.05), Inches(4.4), Inches(5.6), Inches(2.0),
                bullets, size=12, leading=0.5)

    add_text(s, Inches(0.5), Inches(6.7), Inches(12.3), Inches(0.4),
             "→ One command. Deterministic offline. Real-LLM scenarios behind a flag.",
             size=13, color=TEXT_MUTED, bold=True)


# -------------------- LANGFUSE -------------------------

def slide_langfuse_intro():
    s = add_slide()
    slide_header(s, "Langfuse observability",
                 "How we know what the agent did — after it ran", 8)
    add_text(s, Inches(0.5), Inches(1.85), Inches(12.3), Inches(0.6),
             "Online forensics for the agent. One file. Fire-and-forget. "
             "Cannot break the agent — ever.",
             size=15, color=TEXT_DARK)

    items_left = [
        "One file: app/observability/trace.py (~90 lines)",
        "Two-tier design — Langfuse if keys present, no-op fallback otherwise",
        "Lazy import — SDK never loaded in noop path",
        "All exceptions in emit() are caught + warned, never raised",
    ]
    items_right = [
        "Agent never imports Langfuse — only main.py does",
        "Loop builds TurnTrace; tracer.emit() ships it",
        "Same TurnTrace object eval asserts on",
        "Fall-back logs are structured stdout — Railway-friendly",
    ]
    two_col(s, "Design rules", items_left,
            "Decoupling", items_right, top=Inches(2.7))


def slide_langfuse_touchpoints():
    s = add_slide()
    slide_header(s, "Langfuse observability",
                 "Exactly three touch points — that's the entire surface area", 9)

    # Big code block
    code_block(s, Inches(0.5), Inches(1.85), Inches(12.3), Inches(2.4), [
        "# main.py:15      lazy import",
        "from app.observability.trace import get_tracer",
        "",
        "# main.py:23      one tracer per process, built at FastAPI startup",
        "app.state.tracer = get_tracer(settings)",
        "",
        "# main.py:107     called once per /v1/chat request",
        "app.state.tracer.emit(output.trace, payload['response'])",
    ], size=14)

    add_text(s, Inches(0.5), Inches(4.5), Inches(12.3), Inches(0.5),
             "get_tracer() — two-tier fallback",
             size=15, bold=True, color=NAVY)
    code_block(s, Inches(0.5), Inches(5.0), Inches(12.3), Inches(1.7), [
        "def get_tracer(settings):",
        "    if settings.langfuse_public_key and settings.langfuse_secret_key:",
        "        try:    return LangfuseTracer(settings)",
        "        except: pass",
        "    return _NoopTracer()    # stdout structured logs",
    ], size=13)
    add_text(s, Inches(0.5), Inches(6.85), Inches(12.3), Inches(0.4),
             "Decouples 'we always emit a trace' from 'Langfuse must be reachable'.",
             size=12, color=TEXT_MUTED, bold=True)


def slide_langfuse_payload():
    s = add_slide()
    slide_header(s, "Langfuse observability",
                 "What ships per turn — and the PHI rule", 10)

    code_block(s, Inches(0.5), Inches(1.85), Inches(7.5), Inches(4.8), [
        "langfuse.trace(",
        "  name      = 'agent_turn',",
        "  user_id   = trace.user_id,",
        "  session_id= trace.session_id,",
        "  metadata  = {",
        "    'patient_pseudonym':     'Patient-a7b3',  # ← never real",
        "    'verification_passed':   True,",
        "    'verification_rejections': [...],         # Layer-1",
        "    'domain_rule_rejections':  [...],         # Layer-2",
        "    'tool_call_sequence':    ['get_patient_summary',...],",
        "    'tool_latencies_ms':     {...},",
        "    'tool_failures':         {...},",
        "  },",
        "  input  = {'question': trace.question_text},",
        "  output = response,",
        "  usage  = {'input': …, 'output': …, 'cache_read_input': …},",
        ")",
    ], size=11)

    # Right — PHI rule + 2-log split
    add_text(s, Inches(8.3), Inches(1.85), Inches(4.6), Inches(0.4),
             "The PHI rule (load-bearing)",
             size=14, bold=True, color=NAVY)
    add_text(s, Inches(8.3), Inches(2.3), Inches(4.6), Inches(2.0),
             "Pseudonyms only in Langfuse. Real IDs go to OpenEMR's "
             "audit table — the HIPAA trail.\n\nTwo logs. Two audiences. "
             "Trace data and audit data never co-mingle.",
             size=12, color=TEXT_DARK)

    headers = ["", "Langfuse", "OpenEMR audit"]
    rows = [
        ("Audience", "Engineers", "Compliance"),
        ("IDs", "Pseudonyms", "Real"),
        ("Retention", "Engineering", "HIPAA-mandated"),
    ]
    table(s, Inches(8.3), Inches(4.4),
          [Inches(1.3), Inches(1.65), Inches(1.65)],
          headers, rows, row_h=0.42, header_h=0.42)


# -------------------- INTEGRATION ---------------------

def slide_workflow():
    s = add_slide()
    slide_header(s, "End-to-end workflow",
                 "One chat turn — eval and Langfuse share the same TurnTrace", 11)

    # Vertical pipeline
    box_w = Inches(11.0)
    box_x = Inches(1.15)
    y = Inches(1.85)
    steps = [
        ("Physician asks question via /v1/chat", NAVY),
        ("Agent loop builds TurnTrace as it runs:\n"
         "ACL check → tool sequence → token counts → Layer-1 + Layer-2 verification",
         TEAL),
        ("Verifier sets verification_passed; "
         "rejections go into verification_rejections / domain_rule_rejections",
         TEAL),
        ("main.py:107  →  tracer.emit(trace, response)", ACCENT),
        ("→ Langfuse (engineering trace, pseudonyms)\n"
         "→ OpenEMR audit table (compliance, real IDs · separate write)",
         NAVY),
        ("Response with cited record_ids returned to physician", GREEN),
    ]
    h = Inches(0.78)
    gap = Inches(0.1)
    for i, (txt, color) in enumerate(steps):
        yy = y + i * (h + gap)
        add_rect(s, box_x, yy, box_w, h, GRAY_BG)
        add_rect(s, box_x, yy, Inches(0.18), h, color)
        add_text(s, box_x + Inches(0.35), yy, box_w - Inches(0.5), h, txt,
                 size=13, color=TEXT_DARK, anchor=MSO_ANCHOR.MIDDLE)
        if i < len(steps) - 1:
            # Down-arrow indicator on right
            add_text(s, box_x + box_w + Inches(0.05),
                     yy + h - Inches(0.05), Inches(0.4), gap + Inches(0.3),
                     "↓", size=14, bold=True, color=ACCENT,
                     align=PP_ALIGN.CENTER)


def slide_eval_vs_langfuse():
    s = add_slide()
    slide_header(s, "Two sides of the same TurnTrace",
                 "Same data shape · different lifecycles", 12)
    headers = ["", "Eval (offline)", "Langfuse (online)"]
    rows = [
        ("When", "CI on every change", "Production, every chat turn"),
        ("Input", "Hand-crafted FHIR fixtures", "Real (synthetic) patient traffic"),
        ("Asserts on", "TurnTrace properties", "Ships TurnTrace as a span"),
        ("Catches", "Regressions before deploy", "Drift / latency / cost after deploy"),
        ("Fails", "Blocks the release",     "Never blocks the agent"),
        ("Output", "RESULTS.md + exit code", "Langfuse UI + structured stdout fallback"),
    ]
    table(s, Inches(0.5), Inches(1.85),
          [Inches(2.5), Inches(4.7), Inches(5.1)],
          headers, rows, row_h=0.5, header_h=0.45)
    add_text(s, Inches(0.5), Inches(5.6), Inches(12.3), Inches(1.5),
             "One canonical telemetry object. Two lifecycles. "
             "No parallel logging code path to drift.",
             size=15, bold=True, color=ACCENT)


def slide_status():
    s = add_slide()
    slide_header(s, "Status & next",
                 "Early Submission ready · Sunday Final delta", 13)

    # Left — Done
    add_text(s, Inches(0.5), Inches(1.85), Inches(6.0), Inches(0.4),
             "Done — Early Submission",
             size=15, bold=True, color=NAVY)
    done_items = [
        "17 / 17 eval tests passing (evals/RESULTS.md)",
        "Langfuse wrapper deployed; pseudonyms only",
        "No-op fallback active until self-hosted Langfuse provisioned",
        "Agent live at copilot-production-b532.up.railway.app",
        "OpenAI gpt-4o primary; Anthropic adapter wired (1 env var to switch)",
    ]
    bullet_list(s, Inches(0.5), Inches(2.35), Inches(6.0), Inches(4.0),
                done_items, size=13, leading=0.55)

    # Right — Sunday
    add_text(s, Inches(6.85), Inches(1.85), Inches(6.0), Inches(0.4),
             "Sunday — Final delta",
             size=15, bold=True, color=NAVY)
    todo_items = [
        "Provision self-hosted Langfuse on Railway",
        "Layer-2 rules 3 & 4: renal-dose check + QTc check",
        "Expand test_scenarios.py from 3 → ~10 cases",
        "Synthea import — fix volume perms or direct MySQL load",
        "Switch back to Anthropic Sonnet 4.6 (LLM_PROVIDER=anthropic)",
        "Auth-code + PKCE in place of password grant",
    ]
    bullet_list(s, Inches(6.85), Inches(2.35), Inches(6.0), Inches(4.0),
                todo_items, size=13, leading=0.55)

    # Bottom callout
    add_rect(s, Inches(0.5), Inches(6.4), Inches(12.3), Inches(0.7), NAVY)
    add_text(s, Inches(0.5), Inches(6.4), Inches(12.3), Inches(0.7),
             "Architecture-faithful. Eval-gated. Observable. PHI-clean.  "
             "What MVP described, Early ships.",
             size=14, bold=True, color=WHITE,
             anchor=MSO_ANCHOR.MIDDLE, align=PP_ALIGN.CENTER)


def slide_thanks():
    s = add_slide()
    add_rect(s, 0, 0, SW, SH, NAVY)
    add_rect(s, 0, Inches(3.4), SW, Inches(0.06), ACCENT)
    add_text(s, Inches(0.8), Inches(2.7), Inches(12), Inches(1.0),
             "Thank you",
             size=54, bold=True, color=WHITE)
    add_text(s, Inches(0.8), Inches(3.7), Inches(12), Inches(0.6),
             "Demo follows — UC1 brief · UC2 reasoning · UC3 medication safety",
             size=18, color=RGBColor(0xC9, 0xD2, 0xDA))
    add_text(s, Inches(0.8), Inches(6.6), Inches(12), Inches(0.4),
             "https://copilot-production-b532.up.railway.app",
             size=13, color=ACCENT)


# ---------- build ----------------------------------------------------------

def main():
    slide_title()
    slide_recap_mvp()
    slide_delta_overview()
    slide_architecture_traceback()
    slide_eval_intro()
    slide_eval_categories()
    slide_eval_layout_and_run()
    slide_langfuse_intro()
    slide_langfuse_touchpoints()
    slide_langfuse_payload()
    slide_workflow()
    slide_eval_vs_langfuse()
    slide_status()
    slide_thanks()
    prs.save(str(OUT_PATH))
    print(f"wrote {OUT_PATH} — {len(prs.slides)} slides")


if __name__ == "__main__":
    main()
