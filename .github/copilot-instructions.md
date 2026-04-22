## Commit Trailers for AI Assistance

For every commit where an AI assistant helped write the code, add an `Assisted-by`
trailer to the commit message:

```bash
git commit --trailer "Assisted-by: GitHub Copilot" -m "fix(calendar): correct date parsing"
```

Use the name of the specific tool as the trailer value (e.g. `GitHub Copilot`,
`Claude Code`, `ChatGPT`). When the AI agent creates commits automatically, this
trailer is typically added for you. The trailer must appear as a separate line at
the end of the commit message body, in the form `Assisted-by: <Tool Name>`.
