# Design System - VIVA Admin Dashboard

## Direction

**Personality:** Boldness & Clarity (Dark-mode premium admin)
**Foundation:** Slate-950 (deep dark)
**Depth:** Glassmorphism + Subtle borders
**Accent:** Amber-500 (brand gold)

## Tokens

### Spacing
Base: 4px
Scale: 4, 8, 12, 16, 24, 32, 48

### Colors
```
--bg-primary: slate-950
--bg-surface: white/[0.02]
--bg-hover: white/[0.04]
--border: white/[0.05]
--foreground: white
--secondary: slate-300
--muted: slate-400
--faint: slate-500
--accent: amber-500
--accent-glow: amber-500/20
--success: emerald-400
--danger: rose-400
--info: sky-400
```

### Radius
Scale: 8px, 12px, 16px, 24px (rounded, modern)

### Typography
Font: system-ui via Tailwind (sans)
Scale: 10, 11, 12, 13, 14 (base), 16, 18, 24, 30
Weights: 400, 500, 600, 700, 900
Mono: font-mono (for numeric data)

## Patterns

### Card/Surface
- Background: bg-white/[0.02]
- Border: border border-white/[0.05]
- Radius: rounded-3xl
- Padding: p-8 (desktop: p-10)
- Shadow: shadow-2xl
- Hover: hover:bg-white/[0.04]

### Button Primary
- Background: bg-amber-500 hover:bg-amber-400
- Text: text-slate-900 font-bold
- Padding: px-6 py-3
- Radius: rounded-xl
- Shadow: shadow-[0_5px_20px_-5px_rgba(245,158,11,0.5)]

### Table
- Header: text-[10px] font-bold tracking-widest uppercase text-slate-500
- Cell: px-4 py-3 text-sm font-medium text-slate-300
- Row hover: hover:bg-white/[0.02]
- Dividers: divide-y divide-white/5

### Input (Ghost)
- Background: bg-black/20
- Border: border border-white/5
- Focus: focus:border-amber-500/50 focus:ring-1 focus:ring-amber-500/50
- Radius: rounded-xl
- Padding: px-5 py-3.5

### Sidebar
- Width: w-72
- Background: bg-slate-900/80 backdrop-blur-2xl
- Active item: bg-white/5 shadow-[inset_4px_0_0_rgb(251,191,36)]
- Item spacing: space-y-1.5

## Decisions

| Decision | Rationale | Date |
|----------|-----------|------|
| Dark slate-950 | Premium admin feel, brand consistency | 2026-03-20 |
| Amber accent | Matches VIVA brand gold | 2026-03-20 |
| Glassmorphism cards | Modern premium depth without harsh shadows | 2026-03-20 |
| Rounded-3xl | Soft, approachable despite dark theme | 2026-03-20 |
| 10px tracking-widest labels | Technical precision for data labels | 2026-03-20 |
