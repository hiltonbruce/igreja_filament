{{-- Decorative atmosphere — light + dark variants (dark matches welcome hero) --}}
<div class="eliu-atmosphere" aria-hidden="true">
    <div class="eliu-atmosphere__hero"></div>
    <div class="eliu-atmosphere__grid"></div>
    <div class="eliu-atmosphere__beam"></div>

    {{-- Light arch --}}
    <svg
        class="eliu-atmosphere__arch eliu-atmosphere__arch--light"
        viewBox="0 0 1200 480"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
        preserveAspectRatio="xMidYMax meet"
    >
        <path
            d="M120 480V220C120 120 280 40 600 40C920 40 1080 120 1080 220V480"
            stroke="url(#eliu-arch-light)"
            stroke-width="1.5"
        />
        <path
            d="M280 480V260C280 180 400 110 600 110C800 110 920 180 920 260V480"
            stroke="url(#eliu-arch-light)"
            stroke-width="1"
            opacity="0.55"
        />
        <line
            x1="600"
            y1="40"
            x2="600"
            y2="480"
            stroke="url(#eliu-arch-light)"
            stroke-width="1"
            opacity="0.3"
        />
        <defs>
            <linearGradient
                id="eliu-arch-light"
                x1="600"
                y1="40"
                x2="600"
                y2="480"
                gradientUnits="userSpaceOnUse"
            >
                <stop stop-color="#c9a227" stop-opacity="0.55" />
                <stop offset="1" stop-color="#1a3d52" stop-opacity="0" />
            </linearGradient>
        </defs>
    </svg>

    {{-- Dark arch (welcome) --}}
    <svg
        class="eliu-atmosphere__arch eliu-atmosphere__arch--dark"
        viewBox="0 0 1200 480"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
        preserveAspectRatio="xMidYMax meet"
    >
        <path
            d="M120 480V220C120 120 280 40 600 40C920 40 1080 120 1080 220V480"
            stroke="url(#eliu-arch-filament)"
            stroke-width="1.5"
        />
        <path
            d="M280 480V260C280 180 400 110 600 110C800 110 920 180 920 260V480"
            stroke="url(#eliu-arch-filament)"
            stroke-width="1"
            opacity="0.6"
        />
        <line
            x1="600"
            y1="40"
            x2="600"
            y2="480"
            stroke="url(#eliu-arch-filament)"
            stroke-width="1"
            opacity="0.35"
        />
        <defs>
            <linearGradient
                id="eliu-arch-filament"
                x1="600"
                y1="40"
                x2="600"
                y2="480"
                gradientUnits="userSpaceOnUse"
            >
                <stop stop-color="#e8c96a" stop-opacity="0.9" />
                <stop offset="1" stop-color="#e8c96a" stop-opacity="0" />
            </linearGradient>
        </defs>
    </svg>
</div>
