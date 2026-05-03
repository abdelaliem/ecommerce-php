<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>AuraCommerce - Minimalist E-Commerce</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "on-primary-fixed-variant": "#00419e",
                        "error-container": "#ffdad6",
                        "primary-fixed-dim": "#b1c5ff",
                        "background": "#f8f9fa",
                        "on-error-container": "#93000a",
                        "surface": "#f8f9fa",
                        "surface-variant": "#e1e3e4",
                        "on-secondary-fixed": "#141d23",
                        "inverse-on-surface": "#f0f1f2",
                        "inverse-primary": "#b1c5ff",
                        "tertiary": "#651f00",
                        "on-primary-fixed": "#001946",
                        "surface-container-low": "#f3f4f5",
                        "outline": "#737784",
                        "primary-fixed": "#dae2ff",
                        "on-tertiary-fixed-variant": "#802900",
                        "on-background": "#191c1d",
                        "outline-variant": "#c3c6d5",
                        "on-primary": "#ffffff",
                        "tertiary-fixed": "#ffdbcf",
                        "on-error": "#ffffff",
                        "surface-container-lowest": "#ffffff",
                        "surface-dim": "#d9dadb",
                        "surface-container-high": "#e7e8e9",
                        "on-primary-container": "#a5bdff",
                        "surface-container-highest": "#e1e3e4",
                        "on-secondary-container": "#5b646b",
                        "on-secondary": "#ffffff",
                        "secondary-fixed": "#dbe4ed",
                        "primary-container": "#0047ab",
                        "surface-tint": "#2559bd",
                        "on-tertiary-container": "#ffaa8a",
                        "on-secondary-fixed-variant": "#3f484f",
                        "on-tertiary-fixed": "#380d00",
                        "tertiary-container": "#8b2e01",
                        "tertiary-fixed-dim": "#ffb59a",
                        "secondary-container": "#d8e1ea",
                        "on-surface-variant": "#434653",
                        "secondary-fixed-dim": "#bfc8d0",
                        "surface-container": "#edeeef",
                        "surface-bright": "#f8f9fa",
                        "inverse-surface": "#2e3132",
                        "on-surface": "#191c1d",
                        "primary": "#00327d",
                        "secondary": "#575f67",
                        "on-tertiary": "#ffffff",
                        "error": "#ba1a1a"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.125rem",
                        "lg": "0.25rem",
                        "xl": "0.5rem",
                        "full": "0.75rem"
                    },
                    "spacing": {
                        "base": "8px",
                        "stack-lg": "3rem",
                        "container-max": "1200px",
                        "gutter": "1.5rem",
                        "section-padding": "5rem",
                        "stack-md": "1.5rem",
                        "stack-sm": "0.5rem"
                    },
                    "fontFamily": {
                        "h3": ["Inter"],
                        "h1": ["Inter"],
                        "body-md": ["Inter"],
                        "body-lg": ["Inter"],
                        "h2": ["Inter"],
                        "label-sm": ["Inter"]
                    },
                    "fontSize": {
                        "h3": ["1.5rem", { "lineHeight": "1.3", "fontWeight": "600" }],
                        "h1": ["2.5rem", { "lineHeight": "1.2", "letterSpacing": "-0.02em", "fontWeight": "600" }],
                        "body-md": ["1rem", { "lineHeight": "1.5", "fontWeight": "400" }],
                        "body-lg": ["1.125rem", { "lineHeight": "1.6", "fontWeight": "400" }],
                        "h2": ["2rem", { "lineHeight": "1.2", "letterSpacing": "-0.01em", "fontWeight": "600" }],
                        "label-sm": ["0.875rem", { "lineHeight": "1.4", "letterSpacing": "0.05em", "fontWeight": "500" }]
                    }
                }
            }
        }
    </script>
<style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body class="bg-surface-container-lowest text-on-surface font-body-md min-h-screen flex flex-col antialiased">
<!-- TopNavBar -->
<nav class="bg-white dark:bg-slate-950 sticky top-0 w-full z-50 border-b border-gray-200 dark:border-gray-800 flat no shadows">
<div class="max-w-7xl mx-auto px-6 h-16 flex justify-between items-center font-sans text-sm font-medium tracking-tight">
<!-- Brand -->
<a class="text-xl font-bold text-blue-700 dark:text-blue-500 hover:text-blue-700 dark:hover:text-blue-300 transition-colors active:scale-95 transition-transform" href="#">
                AuraCommerce
            </a>
<!-- Navigation Links (Web) -->
<div class="hidden md:flex items-center gap-8 h-full pt-5">
<a class="text-blue-700 dark:text-blue-400 border-b-2 border-blue-700 pb-5 hover:text-blue-700 dark:hover:text-blue-300 transition-colors active:scale-95 transition-transform" href="#">Home</a>
<a class="text-gray-600 dark:text-gray-400 pb-5 hover:text-blue-700 dark:hover:text-blue-300 transition-colors active:scale-95 transition-transform" href="#">Shop</a>
<a class="text-gray-600 dark:text-gray-400 pb-5 hover:text-blue-700 dark:hover:text-blue-300 transition-colors active:scale-95 transition-transform" href="#">Categories</a>
<a class="text-gray-600 dark:text-gray-400 pb-5 hover:text-blue-700 dark:hover:text-blue-300 transition-colors active:scale-95 transition-transform" href="#">Contact</a>
</div>
<!-- Trailing Icons -->
<div class="flex items-center gap-4 text-blue-700 dark:text-blue-400">
<button aria-label="shopping_cart" class="hover:text-blue-700 dark:hover:text-blue-300 transition-colors active:scale-95 transition-transform">
<span class="material-symbols-outlined" data-icon="shopping_cart">shopping_cart</span>
</button>
<button aria-label="account_circle" class="hover:text-blue-700 dark:hover:text-blue-300 transition-colors active:scale-95 transition-transform">
<span class="material-symbols-outlined" data-icon="account_circle">account_circle</span>
</button>
</div>
</div>
</nav>
<!-- Main Content Canvas -->
<main class="flex-grow flex flex-col items-center w-full">
<!-- Hero Section -->
<section class="w-full max-w-[1200px] mx-auto px-6 py-section-padding flex flex-col md:flex-row items-center gap-stack-lg border-b border-surface-variant">
<div class="flex-1 flex flex-col items-start gap-stack-md">
<h1 class="font-h1 text-h1 text-on-surface">Elevate Your Everyday Essentials</h1>
<p class="font-body-lg text-body-lg text-secondary max-w-lg">
                    Discover a curated collection of minimalist products designed for modern living. Precision engineering meets uncompromising aesthetics.
                </p>
<div class="flex gap-4 pt-4">
<a class="bg-primary text-on-primary font-label-sm text-label-sm px-8 py-4 rounded-DEFAULT hover:bg-on-primary-fixed-variant transition-colors" href="#">
                        Shop Collection
                    </a>
<a class="border border-outline text-primary font-label-sm text-label-sm px-8 py-4 rounded-DEFAULT hover:border-primary transition-colors" href="#">
                        Learn More
                    </a>
</div>
</div>
<div class="flex-1 w-full relative">
<div class="aspect-[4/3] bg-surface-container-low rounded-lg overflow-hidden border border-outline-variant relative">
<img alt="Hero Product Image" class="w-full h-full object-cover mix-blend-multiply opacity-90" data-alt="A meticulously styled hero shot of premium wireless headphones resting on a pristine, minimalist concrete desk. The lighting is soft, diffused, and high-key, creating a bright, modern corporate aesthetic. The background features subtle, low-contrast shadows and clean geometric lines, emphasizing the product's sleek design. The color palette is strictly neutral with stark whites, soft grays, and the deep matte black of the headphones, conveying high-end luxury and technological sophistication." src="https://lh3.googleusercontent.com/aida-public/AB6AXuAuG1o0L1lLw91CWT-RlLY6AOpvg7_Q6xv7kwTbMi9A9ML302xnr_fJNC6skOmkErwKJrMKpP-XmjdN76Ofb0nBIhUilW_DwBozZ5Bqs-jokY8tEIulzxqg0qfWIvRSZIpLTU_C3Nro0-RhbPJNUC8g3i60FOK3R2k_LAAOBoILX4CokdiYJCAKIXCvBmanlUGr5z86bxshRB48JVGm1zxYSoWre0lIsCRWKY7mntZP6bacrNc9BzCXNuoqBXoP97bOpbeeqrZ6E6ME"/>
</div>
</div>
</section>
<!-- Product Grid Section -->
<section class="w-full max-w-[1200px] mx-auto px-6 py-section-padding flex flex-col gap-stack-lg">
<div class="flex justify-between items-end border-b border-surface-variant pb-4">
<div>
<h2 class="font-h2 text-h2 text-on-surface">New Arrivals</h2>
<p class="font-body-md text-body-md text-secondary mt-2">Latest additions to our minimalist catalog.</p>
</div>
<a class="font-label-sm text-label-sm text-primary hover:text-on-primary-fixed-variant flex items-center gap-1 transition-colors" href="#">
                    View All <span class="material-symbols-outlined text-sm">arrow_forward</span>
</a>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
<!-- Product Card 1 -->
<div class="group flex flex-col gap-3">
<div class="aspect-square bg-surface-container-low rounded-DEFAULT border border-surface-variant overflow-hidden relative">
<img alt="Smart Watch" class="w-full h-full object-cover mix-blend-multiply group-hover:scale-105 transition-transform duration-500" data-alt="A close-up studio shot of a modern, minimalist smartwatch with a clean white face and a subtle silver bezel, resting flat on a perfectly smooth, light gray surface. The lighting is intensely bright and flat, creating a high-key, clinical aesthetic devoid of harsh shadows. The overall mood is sterile, precise, and premium, utilizing a strict palette of whites, cool grays, and metallic silver accents, reflecting a high-end corporate e-commerce style." src="https://lh3.googleusercontent.com/aida-public/AB6AXuCdfU_eJ7ctCD_PSj3ibwz5MP591DvdHJf7IE7_G7O8zNCH3ikQNYTqPe7WmwEut2NQGMCsXqTCHEXdrzpNV6Bz-0q_jefHBvRGwp3oNyYVPl66ME5GD_47A7wGtMhyvYNUJW2yTolCddF49M_jvxw0QtjWuySboqaObkTj3NHV-qyQvafANzNhTGnywN9Jr_mGbFAq3TCk2ZQTDfUDbslhJ1G0u3SWva7WwYCAQB7RhDbfJoPN20sCNBPbMv0ATX3uMf_ocrJFcLtw"/>
<div class="absolute top-2 left-2 bg-primary text-on-primary px-2 py-1 font-label-sm text-xs rounded-sm">NEW</div>
</div>
<div class="flex flex-col">
<h3 class="font-label-sm text-label-sm text-on-surface truncate">Aura Minimalist Timepiece</h3>
<p class="font-body-md text-body-md text-secondary mt-1">$249.00</p>
</div>
</div>
<!-- Product Card 2 -->
<div class="group flex flex-col gap-3">
<div class="aspect-square bg-surface-container-low rounded-DEFAULT border border-surface-variant overflow-hidden relative">
<img alt="Ceramic Mug" class="w-full h-full object-cover mix-blend-multiply group-hover:scale-105 transition-transform duration-500" data-alt="A pristine, matte white ceramic coffee mug sitting alone against an expansive, pure white backdrop. The composition is starkly minimalist and symmetrical. Soft, even lighting eliminates almost all shadows, resulting in a clinically clean, high-key image. The visual style is highly controlled, focusing entirely on the subtle curvature of the mug's handle and its smooth texture. The color palette is overwhelmingly white with only the faintest whisper of gray to define the object's form." src="https://lh3.googleusercontent.com/aida-public/AB6AXuBxoeJdYTTwduc1yDSKzrTLGXtJJbrrvxJ2t5DfqbFIWti_Eo-qK2uJlyJxb3ws1vye4e7j1ltgKNr_TACUfeFAo4M6Zpk8xRZgSPaQLbuuvjaS24V3Q5-7y4hkxwhQ4U62I3gMaNGQivyezQhfWQOfc8iKlBFz3R2hsSxhh0zgzSCMGfrIGqQ896nMf-mVul5TNVb-pK1GVTDWbdTxfFcINbo3mEn1s9qhay_mFLCpzh6wg-Ep_04o6l5iF_s9B3Rhb1aTKdvFGA2c"/>
</div>
<div class="flex flex-col">
<h3 class="font-label-sm text-label-sm text-on-surface truncate">Matte Ceramic Mug</h3>
<p class="font-body-md text-body-md text-secondary mt-1">$28.00</p>
</div>
</div>
<!-- Product Card 3 -->
<div class="group flex flex-col gap-3">
<div class="aspect-square bg-surface-container-low rounded-DEFAULT border border-surface-variant overflow-hidden relative">
<img alt="Leather Stool" class="w-full h-full object-cover mix-blend-multiply group-hover:scale-105 transition-transform duration-500" data-alt="A sleek, low-profile wooden stool with a simple tan leather seat, positioned against a stark white studio background. The design is undeniably minimalist and Scandinavian-inspired. The lighting is incredibly crisp and bright, ensuring a high-key, modern look that emphasizes clean lines and natural materials without feeling warm or rustic. The palette is restricted to the pale blonde wood, the muted tan leather, and the expansive white negative space, creating a professional, catalog-ready aesthetic." src="https://lh3.googleusercontent.com/aida-public/AB6AXuBO6cjlkd4kx7OIdBt2RzY4WePqDZSHS1OcnKZg5yAlNcaDvdjBJ5UlCmmruXRzm8n12uCg8W0PQaoSFHNK7Au6mYWOR2K7tHln5VoB3Sb3Wzbuwpgm1UDrqil8hi3vRKgMdj7JOklP8CqFw5i5PLwj0tsEC3_RpWGyKtKp5px6ePQMPnRxif7GonvouhwXDtUut_f2TeJFBmD7dh7tHNN145jp4ohxvEohfhqYfullnwNppxTaGxUVpPzyKilpehJ7PTlz2v9VNDy_"/>
</div>
<div class="flex flex-col">
<h3 class="font-label-sm text-label-sm text-on-surface truncate">Nordic Leather Stool</h3>
<p class="font-body-md text-body-md text-secondary mt-1">$185.00</p>
</div>
</div>
<!-- Product Card 4 -->
<div class="group flex flex-col gap-3">
<div class="aspect-square bg-surface-container-low rounded-DEFAULT border border-surface-variant overflow-hidden relative">
<img alt="Wireless Earbuds" class="w-full h-full object-cover mix-blend-multiply group-hover:scale-105 transition-transform duration-500" data-alt="A pair of sleek, matte white wireless earbuds resting inside their open charging case, set against an utterly flawless white surface. The image is an exercise in extreme minimalism and high-key photography. The lighting is flat and intense, reducing shadows to faint, elegant gradients that merely hint at the objects' contours. The aesthetic is highly technical, clean, and modern, using a palette entirely composed of clinical whites and soft, cool grays to project a premium tech identity." src="https://lh3.googleusercontent.com/aida-public/AB6AXuCHh0i-_AbMzve2QIC-bLXniJ_YSRapdqRMHAREbm7wmr8-3x51WgCcrIEwPB2tuPo_1jTxaiCPPVLuhxRndqTsjAu9_PIwdb6jATiyxnC3RKcIeG3V_O8WrAbnM7Y781scNPJ9de_CRe0QAMcaghy4AV4qUvcLlMYvEgLfVLpmGJvq3T7ecw6NArfiU53Ed6WyDbigGXwFs3_T2ZDFB-cUFgTaTZ6kERD_wt47WMyInEPjTh4kjG60hCds83wWKQI-2-wI010OoJV1"/>
</div>
<div class="flex flex-col">
<h3 class="font-label-sm text-label-sm text-on-surface truncate">Aura Pods Pro</h3>
<p class="font-body-md text-body-md text-secondary mt-1">$149.00</p>
</div>
</div>
</div>
</section>
</main>
<!-- Footer -->
<footer class="bg-gray-50 dark:bg-slate-900 w-full mt-auto border-t border-gray-200 dark:border-gray-800 flat no shadows">
<div class="max-w-7xl mx-auto py-12 px-6 flex flex-col md:flex-row justify-between items-center gap-4">
<div class="text-base font-semibold text-gray-900 dark:text-gray-100">
                AuraCommerce
            </div>
<div class="flex flex-wrap justify-center gap-6 text-xs font-normal text-gray-500 dark:text-gray-400">
<a class="text-gray-500 dark:text-gray-400 hover:text-blue-700 dark:hover:text-blue-300 underline underline-offset-4 cursor-pointer" href="#">Privacy Policy</a>
<a class="text-gray-500 dark:text-gray-400 hover:text-blue-700 dark:hover:text-blue-300 underline underline-offset-4 cursor-pointer" href="#">Terms of Service</a>
<a class="text-gray-500 dark:text-gray-400 hover:text-blue-700 dark:hover:text-blue-300 underline underline-offset-4 cursor-pointer" href="#">Returns</a>
<a class="text-gray-500 dark:text-gray-400 hover:text-blue-700 dark:hover:text-blue-300 underline underline-offset-4 cursor-pointer" href="#">Shipping Info</a>
</div>
<div class="text-xs font-normal text-gray-500 dark:text-gray-400">
                © 2024 AuraCommerce. All rights reserved.
            </div>
</div>
</footer>
</body></html>
