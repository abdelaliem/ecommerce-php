---
name: Professional Commerce
colors:
  surface: '#f8f9fa'
  surface-dim: '#d9dadb'
  surface-bright: '#f8f9fa'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f3f4f5'
  surface-container: '#edeeef'
  surface-container-high: '#e7e8e9'
  surface-container-highest: '#e1e3e4'
  on-surface: '#191c1d'
  on-surface-variant: '#434653'
  inverse-surface: '#2e3132'
  inverse-on-surface: '#f0f1f2'
  outline: '#737784'
  outline-variant: '#c3c6d5'
  surface-tint: '#2559bd'
  primary: '#00327d'
  on-primary: '#ffffff'
  primary-container: '#0047ab'
  on-primary-container: '#a5bdff'
  inverse-primary: '#b1c5ff'
  secondary: '#575f67'
  on-secondary: '#ffffff'
  secondary-container: '#d8e1ea'
  on-secondary-container: '#5b646b'
  tertiary: '#651f00'
  on-tertiary: '#ffffff'
  tertiary-container: '#8b2e01'
  on-tertiary-container: '#ffaa8a'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#dae2ff'
  primary-fixed-dim: '#b1c5ff'
  on-primary-fixed: '#001946'
  on-primary-fixed-variant: '#00419e'
  secondary-fixed: '#dbe4ed'
  secondary-fixed-dim: '#bfc8d0'
  on-secondary-fixed: '#141d23'
  on-secondary-fixed-variant: '#3f484f'
  tertiary-fixed: '#ffdbcf'
  tertiary-fixed-dim: '#ffb59a'
  on-tertiary-fixed: '#380d00'
  on-tertiary-fixed-variant: '#802900'
  background: '#f8f9fa'
  on-background: '#191c1d'
  surface-variant: '#e1e3e4'
typography:
  h1:
    fontFamily: Inter
    fontSize: 2.5rem
    fontWeight: '600'
    lineHeight: '1.2'
    letterSpacing: -0.02em
  h2:
    fontFamily: Inter
    fontSize: 2rem
    fontWeight: '600'
    lineHeight: '1.2'
    letterSpacing: -0.01em
  h3:
    fontFamily: Inter
    fontSize: 1.5rem
    fontWeight: '600'
    lineHeight: '1.3'
  body-lg:
    fontFamily: Inter
    fontSize: 1.125rem
    fontWeight: '400'
    lineHeight: '1.6'
  body-md:
    fontFamily: Inter
    fontSize: 1rem
    fontWeight: '400'
    lineHeight: '1.5'
  label-sm:
    fontFamily: Inter
    fontSize: 0.875rem
    fontWeight: '500'
    lineHeight: '1.4'
    letterSpacing: 0.05em
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  base: 8px
  container-max: 1200px
  gutter: 1.5rem
  section-padding: 5rem
  stack-sm: 0.5rem
  stack-md: 1.5rem
  stack-lg: 3rem
---

## Brand & Style

This design system is built upon the principles of **Minimalism** and **Corporate Modernism**. It prioritizes the product over the interface, using intentional whitespace to reduce cognitive load and drive conversions. The aesthetic is clinical, organized, and high-end, evoking a sense of reliability and efficiency.

The target audience consists of discerning consumers who value speed, clarity, and a frictionless shopping experience. By removing decorative elements and unnecessary motion, the design system establishes an environment of professional trust where the content—images and product specifications—remains the primary focus.

## Colors

The palette is intentionally restrained to maximize the impact of the single accent color. 

- **Primary Blue:** A deep, professional Cobalt Blue used sparingly for calls to action, active states, and critical navigation links.
- **Backgrounds:** Pure white is the default surface color to ensure maximum contrast and a "gallery" feel for product photography.
- **Neutrals:** A scale of cool grays is used for borders, secondary text, and subtle background fills to create structural hierarchy without introducing visual noise.
- **Status:** Standard functional colors (success, error) should be muted to maintain the professional tone.

## Typography

This design system utilizes **Inter** for its systematic, utilitarian nature. The typeface is optimized for screen readability and provides a neutral, corporate tone.

- **Headlines:** Set with slight negative letter-spacing and medium-to-bold weights to create a strong visual anchor.
- **Body Text:** Standardized at 16px (1rem) for optimal legibility with generous line height (1.5) to facilitate scanning of product descriptions.
- **Labels:** Small caps or uppercase labels with increased tracking are used for metadata, category tags, and form headers to distinguish them from narrative content.

## Layout & Spacing

The layout follows a **Fixed Grid** model for desktop to ensure a premium, curated appearance, transitioning to a fluid model for tablet and mobile devices.

- **Grid:** A standard Bootstrap 12-column grid with a 1200px max-width container. 
- **Whitespace:** Use aggressive vertical padding between sections (80px–120px) to give products room to "breathe."
- **Rhythm:** All spacing is derived from a 8px base unit. Component internal padding should favor 16px (2x) or 24px (3x) for a balanced, open feel.

## Elevation & Depth

To maintain a minimalist aesthetic, this design system rejects heavy shadows in favor of **Low-contrast outlines** and **Tonal layering**.

- **Borders:** Use 1px solid lines in a light gray (#DEE2E6) to define containers, cards, and input fields.
- **Depth:** Physical elevation is replaced by flat color shifts. A light gray background (#F8F9FA) is used for secondary content areas or to distinguish the footer from the main body.
- **Interaction:** On hover, borders may darken slightly or the primary blue may be introduced as a subtle 2px bottom border. Avoid large box-shadows; if depth is required for a modal, use a soft, diffused 15% opacity neutral shadow.

## Shapes

The shape language is **Soft** and disciplined. 

- **Corner Radius:** A consistent 4px (0.25rem) radius is applied to buttons, input fields, and product cards. This provides a slight hint of approachability while maintaining a sharp, professional edge.
- **Exceptions:** Icons and specialized tags may use circular (pill) shapes to differentiate them from functional UI elements.

## Components

- **Buttons:** 
  - Primary: Solid blue background with white text. No gradients.
  - Secondary: Outline (1px blue or dark gray) with transparent background.
  - Sizing: Large, comfortable hit targets with horizontal padding at 2x vertical padding.
- **Input Fields:** 
  - Understated 1px borders on all four sides. 
  - On focus, the border color changes to the primary blue with no outer "glow" or "halo" effect.
- **Cards:** 
  - Product cards feature a white background and a 1px border. 
  - Image containers within cards should have a light gray background fill to maintain a consistent aspect ratio even with transparent PNGs.
- **Lists:** 
  - Clean, border-bottom separated list items for specifications or cart items. 
  - Remove all bullets; use indentation and typography weight for hierarchy.
- **Additional Elements:** 
  - **Breadcrumbs:** Small, neutral-colored text to aid navigation without distracting from the header.
  - **Badges:** Small, square-cornered labels for "New" or "Sale," using the primary blue or a muted neutral.