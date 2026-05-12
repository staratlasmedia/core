# AI Providers Setup For Core

Phase 9 adds global AI provider configuration for future Core modules. Newsletter uses it first for controlled drafting.

## Rules

- Providers are disabled by default.
- API keys are encrypted at rest.
- Do not commit keys or paste them into docs, prompts, logs, or tickets.
- Do not use a personal ChatGPT/Codex login as backend production authentication.
- Use provider API keys, project keys, service accounts, or equivalent server credentials.
- If a provider is not configured, Core returns a clearly marked placeholder/mock result.

## Supported Provider Types

- OpenAI
- DeepSeek
- Groq
- Generic OpenAI-compatible provider

For OpenAI-compatible providers, configure `base_url`, API key, default model, temperature, max tokens, and rate limits.

## Model Profiles

Use model profiles to separate purposes:

- newsletter
- summary
- rewrite
- title
- social
- telegram
- generic

Social and Telegram profiles are only future-ready metadata in Phase 9. Phase 9 does not implement social or Telegram posting.

## Testing

Use the Filament provider test action only after entering a valid key. The test prompt is short and safe. Core logs provider status, model, and usage metadata when available, never the secret.

Disable the provider immediately if credentials are invalid, quotas are exhausted, or output is not suitable for editorial workflow.

## Newsletter Drafting In Phase 9B

Newsletter AI draft actions are manual only and gated by effective newsletter settings. If `ai_generation_enabled=false`, the action is blocked. If a provider is disabled or missing credentials, the provider test returns a marked mock result and no external request is made.

Do not connect a personal ChatGPT or Codex login as backend authentication. Use provider API credentials intended for server-side use.
