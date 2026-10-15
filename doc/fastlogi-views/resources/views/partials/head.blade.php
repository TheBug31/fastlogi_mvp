<meta charset="utf-8"/>
<meta name="viewport" content="@yield('viewport', 'width=device-width, initial-scale=1.0')"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
<title>@yield('title', 'FastLogi') · FastLogi</title>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Geist:wght@100..900&family=Space+Grotesk:wght@100..900&display=swap" rel="stylesheet"/>
<style>@layer base{html,body{margin:0;padding:0;}body{overscroll-behavior:none;}main>:first-child{margin-top:0!important;}main>:last-child{margin-bottom:0!important;}}::-webkit-scrollbar{display:none;}.pb-safe{padding-bottom:env(safe-area-inset-bottom,0px);}.pt-safe{padding-top:env(safe-area-inset-top,0px);}</style>
{{-- MVP: Tailwind por CDN. Para producción migrar a Vite (ver README, tailwind.config.js). --}}
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
  "darkMode": "class",
  "theme": {
    "extend": {
      "colors": {
        "primary-fixed": "#71f8e4",
        "secondary-container": "#dae2fd",
        "surface-container-lowest": "#ffffff",
        "secondary": "#565e74",
        "primary-fixed-dim": "#4fdbc8",
        "on-primary-container": "#00423b",
        "on-surface": "#0b1c30",
        "secondary-fixed": "#dae2fd",
        "outline-variant": "#bbcac6",
        "on-secondary-container": "#5c647a",
        "on-tertiary": "#ffffff",
        "tertiary-container": "#e49200",
        "tertiary-fixed": "#ffddb8",
        "surface-container-low": "#eff4ff",
        "primary": "#006b5f",
        "inverse-surface": "#213145",
        "primary-container": "#14b8a6",
        "surface-container-highest": "#d3e4fe",
        "surface-variant": "#d3e4fe",
        "on-secondary-fixed-variant": "#3f465c",
        "error-container": "#ffdad6",
        "on-error": "#ffffff",
        "background": "#f8f9ff",
        "on-primary-fixed": "#00201c",
        "error": "#ba1a1a",
        "on-background": "#0b1c30",
        "surface-dim": "#cbdbf5",
        "surface": "#f8f9ff",
        "inverse-on-surface": "#eaf1ff",
        "on-surface-variant": "#3c4947",
        "on-error-container": "#93000a",
        "on-tertiary-fixed-variant": "#653e00",
        "surface-container": "#e5eeff",
        "on-secondary-fixed": "#131b2e",
        "surface-tint": "#006b5f",
        "tertiary": "#855300",
        "on-primary": "#ffffff",
        "secondary-fixed-dim": "#bec6e0",
        "outline": "#6c7a77",
        "on-primary-fixed-variant": "#005048",
        "on-secondary": "#ffffff",
        "surface-bright": "#f8f9ff",
        "surface-container-high": "#dce9ff",
        "on-tertiary-fixed": "#2a1700",
        "on-tertiary-container": "#543300",
        "tertiary-fixed-dim": "#ffb95f",
        "inverse-primary": "#4fdbc8"
      },
      "borderRadius": {
        "DEFAULT": "0.25rem",
        "lg": "0.5rem",
        "xl": "0.75rem",
        "full": "9999px"
      },
      "spacing": {
        "space-xl": "2.5rem",
        "space-md": "1rem",
        "space-sm": "0.5rem",
        "margin-desktop": "2rem",
        "space-lg": "1.5rem",
        "gutter": "1rem",
        "gutter-desktop": "1.5rem",
        "space-xs": "0.25rem",
        "margin": "1rem"
      },
      "fontFamily": {
        "label-lg": [
          "Geist"
        ],
        "body-md": [
          "Geist"
        ],
        "headline-sm": [
          "Space Grotesk"
        ],
        "headline-lg": [
          "Space Grotesk"
        ],
        "body-sm": [
          "Geist"
        ],
        "headline-xl-mobile": [
          "Space Grotesk"
        ],
        "label-md": [
          "Geist"
        ],
        "display-lg-mobile": [
          "Space Grotesk"
        ],
        "display-lg": [
          "Space Grotesk"
        ],
        "data-mono": [
          "Geist"
        ],
        "headline-xl": [
          "Space Grotesk"
        ],
        "body-lg": [
          "Geist"
        ],
        "label-sm": [
          "Geist"
        ]
      },
      "fontSize": {
        "label-lg": [
          "13px",
          {
            "lineHeight": "16px",
            "letterSpacing": "0.04em",
            "fontWeight": "600"
          }
        ],
        "body-md": [
          "14px",
          {
            "lineHeight": "20px",
            "fontWeight": "400"
          }
        ],
        "headline-sm": [
          "18px",
          {
            "lineHeight": "24px",
            "letterSpacing": "-0.01em",
            "fontWeight": "600"
          }
        ],
        "headline-lg": [
          "24px",
          {
            "lineHeight": "30px",
            "letterSpacing": "-0.02em",
            "fontWeight": "600"
          }
        ],
        "body-sm": [
          "12px",
          {
            "lineHeight": "16px",
            "fontWeight": "400"
          }
        ],
        "headline-xl-mobile": [
          "24px",
          {
            "lineHeight": "30px",
            "letterSpacing": "-0.01em",
            "fontWeight": "600"
          }
        ],
        "label-md": [
          "11px",
          {
            "lineHeight": "14px",
            "letterSpacing": "0.06em",
            "fontWeight": "600"
          }
        ],
        "display-lg-mobile": [
          "32px",
          {
            "lineHeight": "36px",
            "letterSpacing": "-0.02em",
            "fontWeight": "700"
          }
        ],
        "display-lg": [
          "48px",
          {
            "lineHeight": "52px",
            "letterSpacing": "-0.03em",
            "fontWeight": "700"
          }
        ],
        "data-mono": [
          "13px",
          {
            "lineHeight": "18px",
            "letterSpacing": "0.01em",
            "fontWeight": "500"
          }
        ],
        "headline-xl": [
          "32px",
          {
            "lineHeight": "38px",
            "letterSpacing": "-0.02em",
            "fontWeight": "600"
          }
        ],
        "body-lg": [
          "16px",
          {
            "lineHeight": "24px",
            "fontWeight": "400"
          }
        ],
        "label-sm": [
          "10px",
          {
            "lineHeight": "12px",
            "letterSpacing": "0.08em",
            "fontWeight": "700"
          }
        ]
      }
    }
  }
}
</script>
@stack('head')
