AI Cost Analysis

The AI layer will add two kinds of cost to the project: one-time development cost and ongoing monthly operating cost. The largest cost is not only the LLM usage. Because OpenEMR handles protected health information, the project also needs security review, HIPAA/PHI controls, audit logging, authorization checks, testing, monitoring, incident response, and ongoing maintenance.

Actual dev spend to date: not confirmed from invoices or time logs. For planning purposes, this should be tracked separately as developer hours, AI/API test usage, hosting, security tools, and compliance review. If the project has only completed planning documents so far, the direct AI runtime spend should be treated as near zero, but the labor cost should still be counted.

Estimated one-time build cost for a safe AI MVP is $80,000–$180,000. This includes architecture, AI broker service, OpenEMR API/FHIR integration, permission checks, prompt templates, response verification, audit logs, admin controls, rate limits, testing, and documentation. A production-ready version with stronger HIPAA controls, vendor review, PHI redaction, encrypted storage, monitoring, queueing, evaluation tools, and clinical review workflows could cost $250,000–$600,000+.

Projected monthly costs depend on usage. These estimates assume AI features such as chart summaries, document summaries, message drafting, and clinician-reviewed note assistance. They include LLM usage, hosting, database/storage, monitoring, logging, support, maintenance, and security operations.

Users	Estimated monthly cost	Notes
100 users	$1,500–$6,000/month	Small pilot. Most cost is fixed infrastructure, monitoring, support, and maintenance.
1,000 users	$8,000–$30,000/month	Requires stronger rate limits, queues, support process, and usage tracking.
10,000 users	$45,000–$160,000/month	Requires production scaling, stronger observability, larger support load, and stricter cost controls.
100,000 users	$300,000–$900,000+/month	Enterprise-scale operation. Requires dedicated engineering, compliance, security, infrastructure, and vendor management.

LLM usage can stay manageable if the system limits the amount of chart data sent per request. The biggest risk is allowing broad chart reads, large document summaries, repeated FHIR calls, or bulk patient analysis. Those workflows could make costs rise quickly. The project should use strict token limits, retrieval limits, caching rules, per-user quotas, and model routing. Smaller models should handle simple administrative drafts, while larger models should be reserved for complex clinical summarization.

The main tradeoff is safety versus speed. A fast, cheap build could connect an AI model directly to patient data, but that would create serious PHI, authorization, compliance, and audit risk. A safer build costs more upfront because it requires access controls, review workflows, logging, monitoring, and failure handling. For OpenEMR, the safer approach is required. The AI layer should start with a narrow pilot, measure real usage and cost per AI action, then scale only after the project proves that the workflows are secure, clinically reviewed, and financially sustainable.
