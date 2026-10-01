<?php

namespace App\Providers\Filament;

use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;

use App\Filament\Pages\Dashboard;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;

use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
     return $panel
    ->default()
    ->id('admin')
    ->path('admin')
    ->login()
    ->favicon(asset('Logo.svg'))
    ->brandLogo(asset('images/fourstep_logo.png'))
    ->brandLogoHeight('60px')
    ->darkMode(false)

    ->colors([
        'primary' => '#2563eb',
    ])

            ->renderHook(
    'panels::head.end',
    fn () => '
    <script>
        document.title = "Fourstep Retail";

        document.addEventListener("DOMContentLoaded", function () {
            document.title = "Fourstep Retail";
        });

        document.addEventListener("livewire:navigated", function () {
            document.title = "Fourstep Retail";
        });
    </script>

    <style>

                    /* Sidebar base */
                    .fi-sidebar,
                    .fi-sidebar-nav,
                    aside.fi-sidebar {
                        background-color: #0f172a !important;
                        width: 240px !important;
                        border-right: none !important;
                    }

                    /* Header */
                    .fi-sidebar-header {
                        background-color: #0f172a !important;
                        border-bottom: 1px solid rgba(255,255,255,0.08) !important;
                    }

                    /* Logo white */
                    .fi-sidebar-header img,
                    .fi-logo img {
                        filter: brightness(0) invert(1) !important;
                    }

                    /* Sidebar items */
                    .fi-sidebar-item-btn {
                        background: transparent !important;
                        color: #ffffff !important;
                        border-radius: 8px !important;
                    }

                    .fi-sidebar-item-btn span {
                        color: #ffffff !important;
                    }

                    .fi-sidebar-item-btn svg {
                        color: #ffffff !important;
                    }

                    /* Hover */
                    .fi-sidebar-item-btn:hover {
                        background-color: rgba(37, 99, 235, 0.2) !important;
                    }

                    .fi-sidebar-item-btn:hover span,
                    .fi-sidebar-item-btn:hover svg {
                        color: #93c5fd !important;
                    }

                    /* Active (KEEP DARK STYLE, no white box) */
                    .fi-sidebar-item-btn.fi-active,
                    .fi-sidebar-item-btn[aria-current="page"] {
                        background-color: rgba(37, 99, 235, 0.25) !important;
                    }

                    /* Group labels */
                    .fi-sidebar-group-label {
                        color: #64748b !important;
                        font-size: 11px !important;
                        font-weight: 600 !important;
                        letter-spacing: 0.08em !important;
                        text-transform: uppercase !important;
                    }

                    /* Scrollbar */
                    .fi-sidebar ::-webkit-scrollbar {
                        width: 4px;
                    }

                    .fi-sidebar ::-webkit-scrollbar-thumb {
                        background: #1e293b;
                        border-radius: 4px;
                    }
/* Hide native horizontal scrollbar */
.fi-ta-content-ctn::-webkit-scrollbar {
    display: none;
}

.fi-ta-content-ctn {
    scrollbar-width: none;
    -ms-overflow-style: none;
}
                </style>
                '
            )
->renderHook(
    'panels::body.end',
    fn () => '
<script>

function initStickyBottomScrollbar() {

    const table = document.querySelector(".fi-ta-content-ctn");

    if (!table) return;

    // Hide if horizontal scroll is not needed
    if (table.scrollWidth <= table.clientWidth) {
        const old = document.getElementById("sticky-bottom-scroll");

        if (old) {
            old.remove();
        }

        return;
    }

    let existing = document.getElementById("sticky-bottom-scroll");

    if (existing) {
        existing.remove();
    }

    let scroll = document.createElement("div");
    scroll.id = "sticky-bottom-scroll";

    scroll.style.position = "fixed";
    scroll.style.bottom = "10px";
    scroll.style.height = "8px";
    scroll.style.overflowX = "auto";
    scroll.style.overflowY = "hidden";
    scroll.style.zIndex = "9999";
    scroll.style.background = "#fff";
    scroll.style.borderRadius = "20px";
    scroll.style.boxShadow = "0 1px 3px rgba(0,0,0,.1)";

    let inner = document.createElement("div");
    inner.style.width = table.scrollWidth + "px";
    inner.style.height = "1px";

    scroll.appendChild(inner);

    document.body.appendChild(scroll);

    function updateScrollbarPosition() {
        const rect = table.getBoundingClientRect();

        scroll.style.left = rect.left + "px";
        scroll.style.width = rect.width + "px";
    }

    function toggleScrollbar() {

        const rect = table.getBoundingClientRect();

        const needsHorizontalScroll =
            table.scrollWidth > table.clientWidth;

        const tableVisible =
            rect.top < window.innerHeight &&
            rect.bottom > 0;

        if (needsHorizontalScroll && tableVisible) {
            scroll.style.display = "block";
        } else {
            scroll.style.display = "none";
        }
    }

    updateScrollbarPosition();
    toggleScrollbar();

    window.addEventListener("resize", updateScrollbarPosition);
    window.addEventListener("resize", toggleScrollbar);
    window.addEventListener("scroll", toggleScrollbar);

    scroll.addEventListener("scroll", function () {
        table.scrollLeft = scroll.scrollLeft;
    });

    table.addEventListener("scroll", function () {
        scroll.scrollLeft = table.scrollLeft;
    });
}

document.addEventListener("DOMContentLoaded", initStickyBottomScrollbar);
document.addEventListener("livewire:navigated", initStickyBottomScrollbar);

/* ===========================
   Auto Logout After Inactivity
=========================== */

const SESSION_TIMEOUT = 15 * 60 * 1000;// 1 minute for testing

let inactivityTimer;

function logoutUser() {
    window.location.href = "/admin-auto-logout";
}

function resetInactivityTimer() {
    clearTimeout(inactivityTimer);
    inactivityTimer = setTimeout(logoutUser, SESSION_TIMEOUT);
}

[
    "mousemove",
    "mousedown",
    "click",
    "keypress",
    "scroll",
    "touchstart",
    "focus"
].forEach(function (event) {
    document.addEventListener(event, resetInactivityTimer);
});

resetInactivityTimer();

</script>
'
)
->navigationGroups([
    NavigationGroup::make()->label('Accounts'),

    NavigationGroup::make()->label('User Access'),

    NavigationGroup::make()->label('Ecommerce Panel'),

    NavigationGroup::make()->label('Member Panel'),

    NavigationGroup::make()->label('Shoppee Panel'),

    NavigationGroup::make()->label('Income Reports'),
])

->sidebarCollapsibleOnDesktop()

->discoverResources(
    in: app_path('Filament/Resources'),
    for: 'App\\Filament\\Resources'
)

->discoverPages(
    in: app_path('Filament/Pages'),
    for: 'App\\Filament\\Pages'
)

->pages([
    Dashboard::class,
])

->widgets([])

            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])

            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}