# CoreVisys Frontend Design Tokens (Theme System Audit)

**Status:** Audit only - no code changed
**Audited:** 2026-09-19
**Scope:** Public marketing site replacement must reuse this system exactly.

## 1. How the theme system works

- Composable: [`resources/js/Composables/useTheme.js`](../resources/js/Composables/useTheme.js)
- Active theme is written as `data-theme="<id>"` on the `<html>` element by `updateDom()`.
- 5 themes, in order: `terminal` (default fallback), `dark-modern`, `light-modern`, `solarized-dark`, `tokyo-night`.
- `initTheme()` resolution order:
  1. `page.props.auth.user?.theme_preference` (logged-in users), else
  2. `page.props.settings?.default_theme` (backend `SystemSetting default_theme`), else
  3. hardcoded fallback `terminal`.
- `switchTheme(id)` updates the DOM immediately and, only if `page.props.auth.user` exists, POSTs to the `profile.update-theme` route. Guests can switch visually but nothing is persisted for them (no localStorage, no cookie).
- Guard note: `initTheme()` reads `page.props.auth.user?.theme_preference`. It works today because [`HandleInertiaRequests`](../app/Http/Middleware/HandleInertiaRequests.php:30) always shares `auth.user` (null for guests) and `settings.default_theme` for all requests, including guests.

## 2. Where CSS variables are defined

File: [`resources/css/app.css`](../resources/css/app.css)

- `@theme { ... }` block (lines 3-77) creates Tailwind 4 color/font/shadow utilities that map to CSS variables.
- `@layer base` defines the actual variable values:
  - `:root` = the light-modern palette (default when no `data-theme` is set).
  - `[data-theme="dark-modern"]`, `[data-theme="solarized-dark"]`, `[data-theme="tokyo-night"]`, `[data-theme="terminal"]` override them.

### Tailwind 4 `@theme` -> utility mapping (use these classes; never hex/white/gray)

| Tailwind utility prefix | CSS variable | Meaning |
|---|---|---|
| `bg-bg-dark` | `--bg-primary` | page background |
| `bg-bg-acrylic` | `--bg-acrylic` | translucent header |
| `bg-glass`, `border-glass-border` | `--glass-bg`, `--glass-border` | glass panels |
| `bg-panel` | `--panel` | cards / navbar / sidebar |
| `bg-panel-2` | `--panel-2` | secondary surface / hover |
| `border-panel-line` | `--panel-line` | borders / dividers |
| `text-text-primary` | `--text-primary` | headings / body |
| `text-text-secondary` | `--text-secondary` | body / muted headings |
| `text-text-muted` | `--text-muted` | captions / meta |
| `text-adaptive` | `--text-adaptive` | text on brand surfaces |
| `text-brand-primary`, `bg-brand-primary` | `--brand-primary` | primary brand |
| `text-brand-secondary`, `bg-brand-secondary` | `--brand-secondary` | secondary brand |
| `text-brand-accent`, `bg-brand-accent` | `--brand-accent` | accent |
| `bg-amber` / `text-amber` / `hover:bg-amber-hover` / `text-amber-dim` | `--amber`, `--amber-hover`, `--amber-dim` | primary action color (aliased per theme) |
| `bg-teal` / `text-teal` | `--teal` | accent |
| `bg-danger` / `text-danger` | `--danger` | error |
| `bg-provider-stripe` / `bg-provider-bkash` / `bg-provider-rocket` / `bg-provider-offline` | provider vars | payment method colors |

Shadows: `shadow-soft-md`, `shadow-glow-primary`, `shadow-glow-accent` defined in `@theme`.

Key insight: `--amber` is the primary interaction color in every theme (aliased to brand primary in light/dark, gold in `terminal`). All buttons, links and focus rings in auth/dashboard use `amber`. Public pages must do the same.

## 3. Fonts, radius and theme-specific behavior

- `--font-sans`: `"Inter", "Inter Tight", ...` globally; `--font-mono`: `"JetBrains Mono", ...`.
- `terminal` theme overrides `--font-sans` to `"Space Grotesk", system-ui` (set inside `[data-theme="terminal"]`).
- Base `body` font-size is `0.875rem` (14px); `body` applies `text-text-primary bg-bg-dark`.
- Radius tokens: `--radius-3xl: 24px`, `--radius-4xl: 32px`. Components use `rounded-lg` (buttons/inputs), `rounded-[10px]` (Card), `rounded-[24px]/[32px]` (Store).
- Utilities: `.glass`, `.acrylic`, `.text-glow-primary`, `.text-glow-accent`, `.animate-float`, `.animate-pulse-glow`.
- Fonts loaded in [`resources/views/app.blade.php`](../resources/views/app.blade.php:11) (Inter, Inter Tight, JetBrains Mono, Space Grotesk).

## 4. `initTheme()` call sites

| Layout / page | Calls `initTheme()` | File |
|---|---|---|
| Guest (auth) layout | yes, `onBeforeMount` | [`GuestLayout.vue`](../resources/js/Layouts/GuestLayout.vue:8) |
| Auth pages (Login/Register/...) | yes, `onBeforeMount` | [`Login.vue`](../resources/js/Pages/Auth/Login.vue:24) |
| Authenticated (dashboard/admin) | yes, `onBeforeMount` | [`AuthenticatedLayout.vue`](../resources/js/Layouts/AuthenticatedLayout.vue:13) |
| Public pages (old Welcome/Contact/etc.) | no | never call initTheme |

Public pages MUST call `initTheme()` in `PublicLayout` the same way `GuestLayout` does (`onBeforeMount(initTheme)`).

## 5. Preventing wrong-theme flash on first paint

- [`app.blade.php`](../resources/views/app.blade.php) does not set `data-theme` on `<html>`.
- However, [`resources/js/app.js`](../resources/js/app.js:19) sets it synchronously in the Inertia `setup()` before `mount(el)`:
  `document.documentElement.setAttribute('data-theme', userTheme || defaultTheme);`
  This runs before Vue renders content, so the wrong-theme flash is largely mitigated. Small residual risk: `<html>` has no theme attribute while the bundle loads (flash of light `:root` palette on slow connections).
- Proposed (NOT implemented) fix: add an inline script in `<head>` of `app.blade.php` that reads the server-rendered `settings.default_theme` / user `theme_preference` and sets `data-theme` before CSS paints. Do not implement without approval.

## 6. Theme switcher UI

- A switcher already exists in [`AuthenticatedLayout.vue`](../resources/js/Layouts/AuthenticatedLayout.vue:92): a 5-swatch grid inside the profile dropdown using `switchTheme(t.id)` with a check mark on the active theme.
- It uses a hardcoded inline `:style` background per theme id (hex) - the only allowed exception. Reuse this switcher component/pattern in the public navbar; do not build a new one.

## 7. Existing UI components and icon set

Shared UI components (`resources/js/Components/UI/`): `Alert.vue`, `Badge.vue`, `Button.vue`, `Card.vue`, `Divider.vue`, `InputLabel.vue`, `TextInput.vue`, `TerminalBlock.vue`, `Table.vue`, `TableCell.vue`, `TableHead.vue`, `TableHeaderCell.vue`, `TableRow.vue`, `TableEmpty.vue`.

Legacy Breeze components (`resources/js/Components/`): `ApplicationLogo.vue`, `Checkbox.vue`, `DangerButton.vue`, `Dropdown.vue`, `DropdownLink.vue`, `InputError.vue`, `InputLabel.vue`, `Modal.vue`, `NavLink.vue`, `PrimaryButton.vue`, `ResponsiveNavLink.vue`, `Reveal.vue`, `SecondaryButton.vue`, `TextInput.vue`.

Signatures:
- `Button.vue`: variants `primary` (`bg-amber text-[#1A1305]`), `secondary`, `danger`; slots `icon-before`, default, `icon-after`.
- `Card.vue`: `bg-panel-2 border border-panel-line rounded-[10px] p-[20px_22px]`.
- `Badge.vue`: statuses `success | danger | amber | default`.

Icon set: `lucide-vue-next` (auth/dashboard use it). `@lucide/vue` is also present. Public pages should use `lucide-vue-next`.

## 8. Rules for the new public site (summary)

1. Use only the theme utilities above. No `bg-white`, `text-gray-*`, `#hex`, `rgb()` - except the existing switcher swatches.
2. `PublicLayout` must call `initTheme()` (`onBeforeMount`).
3. No new themes; do not edit `useTheme.js` or `profile.update-theme`.
4. Every page must pass WCAG AA in all 5 themes; verify focus rings, hover, borders, long legal text.
5. Font behavior follows the theme (`terminal` = Space Grotesk); same look as auth/dashboard.
6. No guest theme persistence (matches current code).