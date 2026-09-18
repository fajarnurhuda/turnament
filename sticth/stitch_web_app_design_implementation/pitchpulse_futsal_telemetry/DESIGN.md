---
name: PitchPulse Futsal Telemetry
colors:
  surface: '#10131c'
  surface-dim: '#10131c'
  surface-bright: '#363943'
  surface-container-lowest: '#0b0e17'
  surface-container-low: '#181b25'
  surface-container: '#1c1f29'
  surface-container-high: '#272a34'
  surface-container-highest: '#32343f'
  on-surface: '#e0e2ef'
  on-surface-variant: '#b9cbb9'
  inverse-surface: '#e0e2ef'
  inverse-on-surface: '#2d303a'
  outline: '#849585'
  outline-variant: '#3b4b3d'
  surface-tint: '#00e478'
  primary: '#f1ffef'
  on-primary: '#003919'
  primary-container: '#00ff87'
  on-primary-container: '#007138'
  inverse-primary: '#006d36'
  secondary: '#bdf4ff'
  on-secondary: '#00363d'
  secondary-container: '#00e3fd'
  on-secondary-container: '#00616d'
  tertiary: '#fffaf9'
  on-tertiary: '#670020'
  tertiary-container: '#ffd4d7'
  on-tertiary-container: '#c40044'
  error: '#ffb4ab'
  on-error: '#690005'
  error-container: '#93000a'
  on-error-container: '#ffdad6'
  primary-fixed: '#60ff98'
  primary-fixed-dim: '#00e478'
  on-primary-fixed: '#00210c'
  on-primary-fixed-variant: '#005227'
  secondary-fixed: '#9cf0ff'
  secondary-fixed-dim: '#00daf3'
  on-secondary-fixed: '#001f24'
  on-secondary-fixed-variant: '#004f58'
  tertiary-fixed: '#ffd9dc'
  tertiary-fixed-dim: '#ffb2ba'
  on-tertiary-fixed: '#400011'
  on-tertiary-fixed-variant: '#910030'
  background: '#10131c'
  on-background: '#e0e2ef'
  surface-variant: '#32343f'
  card-yellow: '#FFD600'
  card-red: '#FF2A4D'
  stadium-emerald: '#00FF87'
  telemetry-cyan: '#00E5FF'
  live-pulse: '#FF3366'
  court-navy: '#0B0E17'
  court-surface: '#121826'
  court-surface-elevated: '#1A2338'
  court-border: '#22304C'
  text-primary: '#F0F6FC'
  text-muted: '#798FA8'
typography:
  display-hero:
    fontFamily: Space Grotesk
    fontSize: 64px
    fontWeight: '700'
    lineHeight: 72px
    letterSpacing: -0.03em
  display-hero-mobile:
    fontFamily: Space Grotesk
    fontSize: 40px
    fontWeight: '700'
    lineHeight: 48px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Space Grotesk
    fontSize: 32px
    fontWeight: '700'
    lineHeight: 40px
    letterSpacing: -0.02em
  headline-lg-mobile:
    fontFamily: Space Grotesk
    fontSize: 24px
    fontWeight: '700'
    lineHeight: 32px
    letterSpacing: -0.01em
  headline-md:
    fontFamily: Space Grotesk
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
  headline-sm:
    fontFamily: Space Grotesk
    fontSize: 16px
    fontWeight: '600'
    lineHeight: 24px
  body-lg:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  body-sm:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '400'
    lineHeight: 16px
  label-telemetry:
    fontFamily: JetBrains Mono
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 18px
    letterSpacing: 0.05em
  label-score:
    fontFamily: Space Grotesk
    fontSize: 48px
    fontWeight: '700'
    lineHeight: 52px
    letterSpacing: -0.02em
  label-badge:
    fontFamily: JetBrains Mono
    fontSize: 11px
    fontWeight: '700'
    lineHeight: 14px
    letterSpacing: 0.08em
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  gutter: 1rem
  gutter-lg: 1.5rem
  margin: 1rem
  margin-md: 1.5rem
  margin-lg: 2.5rem
  space-xs: 0.25rem
  space-sm: 0.5rem
  space-md: 1rem
  space-lg: 1.5rem
  space-xl: 2.5rem
---

## Brand & Style

This design system delivers a high-velocity, broadcast-grade interface built specifically for tournament administration and spectator telemetry. Designed around the relentless speed of indoor futsal, it evokes the electric tension of arena floodlights cutting through an enclosed, dark arena floor.

The visual style merges **Technical High-Contrast** and **Refined Glassmorphic Telemetry**. Deep pitch-black and nocturnal navy backdrops eliminate screen fatigue in dark arena control booths, while energetic stadium green indicators pulse with real-time WebSocket events. The audience experience balances the intensity of television graphics packages with the functional clarity of professional tactical software: razor-sharp numerical readouts, modular match timeline badges, high-contrast scoreboards, and instant visual state feedback for live match control operators.

## Colors

The palette is engineered for low-light arena environments and rapid optical digestion of high-velocity match events.

- **Primary (`#00FF87` - Stadium Emerald):** Signifies live match play, successful goals, verified outcomes, active timers, and primary action buttons.
- **Secondary (`#00E5FF` - Telemetry Cyan):** Used for analytical statistics, assist metrics, secondary tactical navigation, and data visualizations.
- **Tertiary (`#FF3366` - Live Pulse):** Signals live broadcast indicators, sudden match state disruptions, and critical alert indicators.
- **Neutral (`#0B0E17` - Court Navy):** Acts as the foundational darkness, simulating the stadium tunnel and bench seating, ensuring maximum contrast without using pure flat blacks.
- **Named Colors:**
  - `card-yellow` (`#FFD600`): Futsal regulatory caution card and tactical warnings.
  - `card-red` (`#FF2A4D`): Regulatory ejection card, penalty alerts, and critical errors.
  - `court-surface` (`#121826`) & `court-surface-elevated` (`#1A2338`): Distinct structural card and panel backgrounds.
  - `court-border` (`#22304C`): Precise division borders for high-density tables.

## Typography

The type ecosystem relies on a three-tier hierarchy:

1. **Space Grotesk** commands all high-impact editorial moments, match headers, tournament stage titles, and team names. Its geometric, technical angles echo court boundaries and digital sports score displays.
2. **Inter** provides neutral, crystal-clear readability for structural navigation, administrative forms, multi-column standings, and match commentary threads.
3. **JetBrains Mono** delivers fixed-width stability for real-time telemetry: stopwatches, match minute markers (`14'`), player squad numbers, and tabular standings points (P, W, D, L, GD, PTS). This ensures numbers never shift or jitter when updated via WebSocket broadcasts.

## Layout & Spacing

The layout is anchored by a structured 12-column fluid grid system engineered for high data density on desktop control panels and compact vertical stacking on spectator mobile devices:

- **Desktop (>= 1024px):** Uses 12 columns with a 24px (`1.5rem`) gutter and 40px outer margins. Supports split-view administrative setups: live operator controls on the left (8 cols) and real-time event logs with match timeline streaming on the right (4 cols).
- **Tablet (768px - 1023px):** Collapses to 8 columns with 16px gutters and 24px margins. Tabbed views replace simultaneous side-by-side match operations.
- **Mobile (< 768px):** Uses 4 columns with 16px gutters and 16px margins. Scoreboard cards convert into high-contrast single-column telemetry banners with horizontal scrolling player pills and bottom-anchored timeline streams.

## Elevation & Depth

Visual hierarchy uses tonal surface layering and electric illumination rather than heavy dropshadows, ensuring zero muddying on dark displays:

- **Level 0 (Canvas):** Pure base tone (`court-navy`: `#0B0E17`).
- **Level 1 (Panels & Brackets):** `court-surface` (`#121826`) bounded by crisp 1px borders of `court-border` (`#22304C`).
- **Level 2 (Interactive Cards & Timeline Nodes):** `court-surface-elevated` (`#1A2338`) with hairline outlines (`rgba(255, 255, 255, 0.08)`).
- **Active / Focused Telemetry Glow:** Modals, live score badges, and action triggers project a diffused aura (`box-shadow: 0 0 24px -4px rgba(0, 255, 135, 0.2)` or `rgba(255, 51, 102, 0.25)` for active match state updates).
- **Backdrop Blurs:** Floating sticky scorebars and mobile navigation use `backdrop-filter: blur(12px)` over `rgba(11, 14, 23, 0.85)` to maintain continuous ground-plane orientation while scrolling.

## Shapes

The design adopts a sharp, architectural **Soft (Level 1)** aesthetic. Corner radii remain modest (`0.25rem` base, `0.5rem` for cards, and `0.75rem` for outer structural overlays) to retain an analytical, command-center precision. Full pill radii are reserved exclusively for minute tags, status pips, and event-type markers to contrast against the sharp structural grids.

## Components

### Buttons & Quick Match Actions
- **Primary (Live Control / Start Match):** Filled `#00FF87` with `#0B0E17` bold typography, corner radius `rounded-sm`. On hover, casts a subtle stadium-green ambient glow.
- **Critical Action (Red Card / Stop Match):** Filled `#FF2A4D` with `#FFFFFF` text.
- **Tactical Secondary:** Bordered with `#22304C`, surface `#1A2338`, text `#F0F6FC`. On active state, border changes to `#00E5FF`.

### Match Timeline Stream
- **Chronology Axis:** A vertical 2px guide in `#22304C`.
- **Event Nodes:** Distinct icons enclosed in 28px circular markers:
  - Goal: `#00FF87` circular ring with football icon.
  - Yellow Card: `#FFD600` solid block badge.
  - Red Card: `#FF2A4D` solid block badge.
  - Substitution / Assist: `#00E5FF` icon badge.
- **Event Content:** Minute marker in `label-badge` styling using `JetBrains Mono`, followed by player name in `Inter Medium` and tactical details in `text-muted`.

### Scoreboard Telemetry Card
- High-contrast panel (`court-surface`) divided symmetrically between Home and Away.
- Team logos placed at 48px square container with subtle circular rim.
- Digit readout styled using `label-score` Space Grotesk 48px bold, flanked by the live match state pill (`1ST HALF`, `HT`, `2ND HALF`, `FT`) pulsing with `#FF3366` tertiary live indicator.

### Standings & Leaderboard Tables
- Striped tabular layout alternating between `#121826` and `#161E30`.
- Position ranks 1-2 highlight qualifying spots with a 3px left border accent in `#00FF87`.
- Metric numbers (Goals, Assists, Points) rendered exclusively in `JetBrains Mono` aligned to the right.

### Input Fields & Rapid Event Modals
- Form controls feature `#121826` background with 1px `#22304C` borders.
- Focus switches border to `#00FF87` with an ambient glow.
- Quick-select buttons for jersey numbers and foul types structured as uniform tap targets (minimum 44px) for rapid tournament desk entry.