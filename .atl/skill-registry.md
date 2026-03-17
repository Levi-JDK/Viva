# Skill Registry - Viva Project

Generated: 2026-03-17

## User-Level Skills

### SDD Workflow (from ~/.config/opencode/skills/)

| Trigger | Skill | Path |
|---------|-------|------|
| "iniciar sdd" | sdd-init | ~/.config/opencode/skills/sdd-init/SKILL.md |
| "explorar" | sdd-explore | ~/.config/opencode/skills/sdd-explore/SKILL.md |
| "proponer" | sdd-propose | ~/.config/opencode/skills/sdd-propose/SKILL.md |
| "especificar" | sdd-spec | ~/.config/opencode/skills/sdd-spec/SKILL.md |
| "diseñar" | sdd-design | ~/.config/opencode/skills/sdd-design/SKILL.md |
| "tareas" | sdd-tasks | ~/.config/opencode/skills/sdd-tasks/SKILL.md |
| "implementar" | sdd-apply | ~/.config/opencode/skills/sdd-apply/SKILL.md |
| "verificar" | sdd-verify | ~/.config/opencode/skills/sdd-verify/SKILL.md |
| "archivar" | sdd-archive | ~/.config/opencode/skills/sdd-archive/SKILL.md |

### Framework/Library Skills (from ~/.config/opencode/skills/)

| Trigger | Skill | Path |
|---------|-------|------|
| "React" | react-19 | ~/.config/opencode/skills/react-19/SKILL.md |
| "Next.js" | nextjs-15 | ~/.config/opencode/skills/nextjs-15/SKILL.md |
| "TypeScript" | typescript | ~/.config/opencode/skills/typescript/SKILL.md |
| "Tailwind" | tailwind-4 | ~/.config/opencode/skills/tailwind-4/SKILL.md |
| "Zod" | zod-4 | ~/.config/opencode/skills/zod-4/SKILL.md |
| "Zustand" | zustand-5 | ~/.config/opencode/skills/zustand-5/SKILL.md |
| "AI SDK" | ai-sdk-5 | ~/.config/opencode/skills/ai-sdk-5/SKILL.md |
| "Django" | django-drf | ~/.config/opencode/skills/django-drf/SKILL.md |
| "Pytest" | pytest | ~/.config/opencode/skills/pytest/SKILL.md |
| "Playwright" | playwright | ~/.config/opencode/skills/playwright/SKILL.md |
| "Angular" | angular | ~/.config/opencode/skills/angular/SKILL.md |
| ".NET" | dotnet | ~/.config/opencode/skills/dotnet/SKILL.md |

### Tool Skills (from ~/.config/opencode/skills/)

| Trigger | Skill | Path |
|---------|-------|------|
| "skill-sync" | skill-sync | ~/.config/opencode/skills/skill-sync/SKILL.md |
| "skill-creator" | skill-creator | ~/.config/opencode/skills/skill-creator/SKILL.md |
| "jira-task" | jira-task | ~/.config/opencode/skills/jira-task/SKILL.md |
| "jira-epic" | jira-epic | ~/.config/opencode/skills/jira-epic/SKILL.md |
| "pr-review" | pr-review | ~/.config/opencode/skills/pr-review/SKILL.md |
| "homebrew-release" | homebrew-release | ~/.config/opencode/skills/homebrew-release/SKILL.md |
| "stream-deck" | stream-deck | ~/.config/opencode/skills/stream-deck/SKILL.md |
| "technical-review" | technical-review | ~/.config/opencode/skills/technical-review/SKILL.md |

## Project-Level Skills (from .atl/skills/)

| Trigger | Skill | Description |
|---------|-------|-------------|
| "revisar seguridad" | security-audit | Analiza código en busca de vulnerabilidades |
| "crear api" | create-api | Genera nueva API REST |
| "crear helper" | create-helper | Genera función helper reutilizable |
| "Crear pipeline Redis-PostgreSQL" | redis-async-worker | Workers asíncronos con Redis (Predis) |
| "Crear worker asíncrono" | redis-async-worker | Sistema de colas con retry y DLQ |

## Project Conventions

| File | Path | Notes |
|------|------|-------|
| AGENTS.md | AGENTS.md | Reglas del proyecto y estructura de skills |
| openspec/config.yaml | openspec/config.yaml | Configuración SDD |

---

## Usage

Para usar un skill:
1. Mencionar el trigger en la conversación
2. El orquestador cargará el skill apropiado

Para actualizar este registry:
- "actualizar skills" o "actualizar registry"

---

## Notas Importantes

- **Stack**: PHP + PostgreSQL + Vanilla JS + TailwindCSS
- **Redis**: Usa Predis (no phpredis) para compatibilidad cross-platform
- **Workers**: Sistema asíncrono con retry y Dead Letter Queue
- **Seguridad**: Nunca hacer commit de credenciales, usar .env
- **Multi-plataforma**: Desarrollo (Windows/Linux) → Testing → Producción (Linux)
