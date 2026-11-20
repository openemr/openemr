## Commit Trailers for AI Assistance

Add a trailer naming the tool to every commit an AI assistant helped make,
including docs and deletions: `Generated-By` only if the AI made every change
in the commit, `Assisted-By` if a person made any. Put it on its own line at the
end of the commit message.

```bash
git commit --trailer "Assisted-By: GitHub Copilot" -m "fix(calendar): correct date parsing"
```
