---
name: coder-model-config
description: Configuración del modelo coder-model (Qwen). Límites de tokens, provider y resolución de problemas.
metadata:
  scope: project
---

# Configuración Coder Model (Qwen)

## Provider
- **Modelo**: `qwen/coder-model`
- **Base URL**: `https://portal.qwen.ai/v1`
- **Auth**: Plugin `opencode-qwencode-auth` (OAuth)

## Límites de Tokens (IMPORTANTE)

### Problema
Si el modelo **aborta tareas grandes**, deja código a la mitad, o tira errores de contexto durante refactors que tocan múltiples archivos, es casi seguro que hay **límites artificiales** en la configuración del CLI que están ahogando al modelo.

### Solución
Verificar y eliminar el bloque `limit` en `~/.config/opencode/opencode.json`:

```json
"provider": {
    "qwen": {
      "models": {
        "coder-model": {
          "attachment": true,
          "id": "coder-model",
          // BORRAR ESTE BLOQUE:
          "limit": {
            "context": 1048576,
            "output": 65536
          }
        }
      }
    }
}
```

El plugin `opencode-qwencode-auth` ya inyecta los límites reales del modelo (`contextWindow: 1048576`, `maxOutput: 65536`) dinámicamente. Si además hardcodeás el `limit` en `opencode.json`, el CLI puede cortar la respuesta antes de que el modelo termine.

### Cómo verificar
```bash
# Si el modelo corta a mitad de código largo:
grep -A5 '"limit"' ~/.config/opencode/opencode.json
```

Si aparece un objeto `limit` con valores numéricos → **eliminarlo**.

## Modelos Disponibles
- `coder-model`: Alias automático (mapea a `qwen3-coder-plus`) — 1M tokens contexto, 64K output
- `qwen3-coder-plus`: Modelo principal de código
- `qwen3-coder-flash`: Respuestas más rápidas, misma ventana de contexto

## Rate Limits
- **2,000 requests/día** gratis vía OAuth
- Límites se resetean a medianoche UTC
- Si aparece error 429: esperar al reset o usar API Key de DashScope
