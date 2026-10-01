<div style="padding:12px;border-top:1px solid #e5e7eb ;">
    <button
        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
        style="width:100%;display:flex;align-items:center;gap:12px;padding:10px;border-radius:8px;border:none;background:transparent;cursor:pointer;color:#374151;font-weight:500;"
        onmouseover="this.style.background='#f3f4f6'"
        onmouseout="this.style.background='transparent'">

        <!-- MATCHING SIDEBAR ICON -->
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6A2.25 2.25 0 005.25 5.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M18 12l3-3m0 0l-3-3m3 3H9" />
        </svg>

        Logout
    </button>

    <form id="logout-form" method="POST" action="{{ route('filament.admin.auth.logout') }}" style="display:none;">
        @csrf
    </form>
</div>