<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Eliú — sistema de controle de cadastros e gestão de membros de igrejas evangélicas.">

        <title>{{ config('app.name', 'Eliú') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link
            href="https://fonts.bunny.net/css?family=fraunces:500,600,700|manrope:400,500,600,700&display=swap"
            rel="stylesheet"
        />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            :root {
                --eliu-ink: #0c1a24;
                --eliu-deep: #122a38;
                --eliu-mid: #1a3d52;
                --eliu-gold: #c9a227;
                --eliu-gold-soft: #e8c96a;
                --eliu-mist: #e8eef2;
                --eliu-paper: #f4f7f9;
            }

            .font-display {
                font-family: 'Fraunces', ui-serif, Georgia, serif;
            }

            .font-ui {
                font-family: 'Manrope', ui-sans-serif, system-ui, sans-serif;
            }

            .eliu-hero {
                background:
                    radial-gradient(ellipse 90% 70% at 70% 20%, rgba(201, 162, 39, 0.18), transparent 55%),
                    radial-gradient(ellipse 60% 50% at 15% 85%, rgba(26, 61, 82, 0.9), transparent 50%),
                    linear-gradient(165deg, var(--eliu-ink) 0%, var(--eliu-deep) 45%, #0a1520 100%);
            }

            .eliu-grid {
                background-image:
                    linear-gradient(rgba(232, 238, 242, 0.04) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(232, 238, 242, 0.04) 1px, transparent 1px);
                background-size: 48px 48px;
                mask-image: radial-gradient(ellipse 80% 70% at 50% 40%, black 20%, transparent 75%);
            }

            .eliu-beam {
                background: linear-gradient(
                    105deg,
                    transparent 0%,
                    rgba(232, 201, 106, 0.07) 45%,
                    transparent 70%
                );
                animation: eliu-beam-drift 14s ease-in-out infinite alternate;
            }

            @keyframes eliu-beam-drift {
                from {
                    transform: translateX(-4%) rotate(-2deg);
                    opacity: 0.7;
                }
                to {
                    transform: translateX(6%) rotate(1deg);
                    opacity: 1;
                }
            }

            @keyframes eliu-rise {
                from {
                    opacity: 0;
                    transform: translateY(1.25rem);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .eliu-rise {
                animation: eliu-rise 0.9s cubic-bezier(0.22, 1, 0.36, 1) both;
            }

            .eliu-rise-delay-1 {
                animation-delay: 0.12s;
            }

            .eliu-rise-delay-2 {
                animation-delay: 0.24s;
            }

            .eliu-rise-delay-3 {
                animation-delay: 0.36s;
            }

            .eliu-module:hover .eliu-module-arrow {
                transform: translateX(0.25rem);
            }

            @media (prefers-reduced-motion: reduce) {
                .eliu-beam,
                .eliu-rise {
                    animation: none !important;
                }
            }
        </style>
    </head>
    <body class="font-ui antialiased text-[var(--eliu-ink)] bg-[var(--eliu-paper)]">
        <a
            href="#modulos"
            class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:rounded-md focus:bg-white focus:px-4 focus:py-2 focus:shadow"
        >
            Ir para os módulos
        </a>

        {{-- Hero: brand + CTA (first viewport) --}}
        <header class="eliu-hero relative min-h-dvh overflow-hidden text-[var(--eliu-mist)]">
            <div class="eliu-grid pointer-events-none absolute inset-0" aria-hidden="true"></div>
            <div class="eliu-beam pointer-events-none absolute -inset-x-1/4 inset-y-0 w-[150%]" aria-hidden="true"></div>

            {{-- Abstract arched light — visual anchor, full-bleed atmosphere --}}
            <svg
                class="pointer-events-none absolute inset-x-0 bottom-0 h-[55%] w-full opacity-30"
                viewBox="0 0 1200 480"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
                aria-hidden="true"
            >
                <path
                    d="M120 480V220C120 120 280 40 600 40C920 40 1080 120 1080 220V480"
                    stroke="url(#eliu-arch)"
                    stroke-width="1.5"
                />
                <path
                    d="M280 480V260C280 180 400 110 600 110C800 110 920 180 920 260V480"
                    stroke="url(#eliu-arch)"
                    stroke-width="1"
                    opacity="0.6"
                />
                <line x1="600" y1="40" x2="600" y2="480" stroke="url(#eliu-arch)" stroke-width="1" opacity="0.35" />
                <defs>
                    <linearGradient id="eliu-arch" x1="600" y1="40" x2="600" y2="480" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#e8c96a" stop-opacity="0.9" />
                        <stop offset="1" stop-color="#e8c96a" stop-opacity="0" />
                    </linearGradient>
                </defs>
            </svg>

            <div class="relative z-10 mx-auto flex min-h-dvh max-w-6xl flex-col px-6 py-8 sm:px-10 lg:px-12">
                <nav class="flex items-center justify-between gap-4 eliu-rise">
                    <p class="font-display text-lg font-semibold tracking-wide text-[var(--eliu-gold-soft)] sm:text-xl">
                        Eliú
                    </p>

                    @if (Route::has('login'))
                        <div class="flex items-center gap-3 text-sm font-medium">
                            @auth
                                <a
                                    href="{{ url('/secretary') }}"
                                    class="rounded-md px-3 py-2 text-[var(--eliu-mist)]/85 transition hover:text-white"
                                >
                                    Secretaria
                                </a>
                                <a
                                    href="{{ url('/admin') }}"
                                    class="rounded-md border border-white/15 bg-white/5 px-4 py-2 text-white transition hover:border-[var(--eliu-gold)]/50 hover:bg-white/10"
                                >
                                    Administração
                                </a>
                                @if (Route::has('logout'))
                                    <form method="POST" action="{{ route('logout') }}" class="inline">
                                        @csrf
                                        <button
                                            type="submit"
                                            class="rounded-md px-3 py-2 text-[var(--eliu-mist)]/75 transition hover:text-white"
                                        >
                                            Sair
                                        </button>
                                    </form>
                                @endif
                            @else
                                <a
                                    href="{{ route('login') }}"
                                    class="rounded-md border border-white/15 bg-white/5 px-4 py-2 text-white transition hover:border-[var(--eliu-gold)]/50 hover:bg-white/10"
                                >
                                    Entrar
                                </a>
                            @endauth
                        </div>
                    @endif
                </nav>

                <div class="flex flex-1 flex-col justify-center py-16 sm:py-20 lg:max-w-2xl lg:py-24">
                    <p class="eliu-rise eliu-rise-delay-1 mb-4 text-xs font-semibold uppercase tracking-[0.22em] text-[var(--eliu-gold-soft)]/90">
                        Gestão eclesiástica
                    </p>

                    <h1 class="eliu-rise eliu-rise-delay-1 font-display text-4xl font-semibold leading-[1.08] tracking-tight text-white sm:text-5xl lg:text-6xl">
                        Eliú
                    </h1>

                    <p class="eliu-rise eliu-rise-delay-2 mt-5 max-w-xl text-base leading-relaxed text-[var(--eliu-mist)]/80 sm:text-lg">
                        Controle de cadastros e dados dos membros — com secretaria operacional e, em breve, tesouraria integrada.
                    </p>

                    <div class="eliu-rise eliu-rise-delay-3 mt-10 flex flex-wrap items-center gap-4">
                        @auth
                            <a
                                href="{{ url('/secretary') }}"
                                class="inline-flex items-center justify-center rounded-md bg-[var(--eliu-gold)] px-6 py-3 text-sm font-semibold text-[var(--eliu-ink)] transition hover:bg-[var(--eliu-gold-soft)]"
                            >
                                Abrir Secretaria
                            </a>
                            <a
                                href="{{ url('/admin') }}"
                                class="inline-flex items-center justify-center rounded-md border border-white/20 px-6 py-3 text-sm font-semibold text-white transition hover:border-white/40 hover:bg-white/5"
                            >
                                Administração
                            </a>
                            @if (Route::has('logout'))
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="inline-flex items-center justify-center rounded-md px-2 py-3 text-sm font-medium text-[var(--eliu-mist)]/75 underline-offset-4 transition hover:text-white hover:underline"
                                    >
                                        Sair da conta
                                    </button>
                                </form>
                            @endif
                        @else
                            @if (Route::has('login'))
                                <a
                                    href="{{ route('login') }}"
                                    class="inline-flex items-center justify-center rounded-md bg-[var(--eliu-gold)] px-6 py-3 text-sm font-semibold text-[var(--eliu-ink)] transition hover:bg-[var(--eliu-gold-soft)]"
                                >
                                    Acessar o sistema
                                </a>
                            @endif
                            <a
                                href="#modulos"
                                class="inline-flex items-center justify-center rounded-md px-2 py-3 text-sm font-medium text-[var(--eliu-mist)]/75 underline-offset-4 transition hover:text-white hover:underline"
                            >
                                Ver módulos
                            </a>
                        @endauth
                    </div>
                </div>

                <p class="eliu-rise eliu-rise-delay-3 pb-2 text-xs text-[var(--eliu-mist)]/45">
                    Ambiente seguro para equipes da igreja
                </p>
            </div>
        </header>

        {{-- Modules: interactive destinations (cards OK as action containers) --}}
        <main id="modulos" class="border-t border-[var(--eliu-ink)]/8 bg-[var(--eliu-paper)]">
            <section class="mx-auto max-w-6xl px-6 py-16 sm:px-10 sm:py-20 lg:px-12">
                <div class="max-w-2xl">
                    <h2 class="font-display text-2xl font-semibold tracking-tight text-[var(--eliu-ink)] sm:text-3xl">
                        Módulos do sistema
                    </h2>
                    <p class="mt-3 text-[var(--eliu-mid)]/90 leading-relaxed">
                        Escolha o painel conforme sua função. Cada área concentra as ferramentas necessárias no dia a dia da igreja.
                    </p>
                </div>

                <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 lg:gap-5">
                    <a
                        href="{{ url('/secretary') }}"
                        class="eliu-module group flex flex-col rounded-xl border border-[var(--eliu-ink)]/10 bg-white p-6 transition hover:border-[var(--eliu-gold)]/40 hover:shadow-[0_12px_40px_-20px_rgba(12,26,36,0.35)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--eliu-gold)]"
                    >
                        <span class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--eliu-gold)]">
                            Operacional
                        </span>
                        <span class="mt-3 font-display text-xl font-semibold text-[var(--eliu-ink)]">
                            Secretaria
                        </span>
                        <span class="mt-2 flex-1 text-sm leading-relaxed text-[var(--eliu-mid)]/85">
                            Cadastro de membros, registros e rotinas da secretaria da igreja.
                        </span>
                        <span class="eliu-module-arrow mt-6 inline-flex items-center gap-2 text-sm font-semibold text-[var(--eliu-deep)] transition">
                            Acessar
                            <svg class="h-4 w-4" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                <path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                    </a>

                    <a
                        href="{{ url('/admin') }}"
                        class="eliu-module group flex flex-col rounded-xl border border-[var(--eliu-ink)]/10 bg-white p-6 transition hover:border-[var(--eliu-gold)]/40 hover:shadow-[0_12px_40px_-20px_rgba(12,26,36,0.35)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--eliu-gold)]"
                    >
                        <span class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--eliu-gold)]">
                            Gestão
                        </span>
                        <span class="mt-3 font-display text-xl font-semibold text-[var(--eliu-ink)]">
                            Administração
                        </span>
                        <span class="mt-2 flex-1 text-sm leading-relaxed text-[var(--eliu-mid)]/85">
                            Configurações, estrutura e acompanhamento administrativo do sistema.
                        </span>
                        <span class="eliu-module-arrow mt-6 inline-flex items-center gap-2 text-sm font-semibold text-[var(--eliu-deep)] transition">
                            Acessar
                            <svg class="h-4 w-4" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                <path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                    </a>

                    <div
                        class="flex flex-col rounded-xl border border-dashed border-[var(--eliu-ink)]/15 bg-[var(--eliu-mist)]/40 p-6 sm:col-span-2 lg:col-span-1"
                        aria-disabled="true"
                    >
                        <span class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--eliu-mid)]/70">
                            Em breve
                        </span>
                        <span class="mt-3 font-display text-xl font-semibold text-[var(--eliu-ink)]/80">
                            Tesouraria
                        </span>
                        <span class="mt-2 flex-1 text-sm leading-relaxed text-[var(--eliu-mid)]/75">
                            Ofertas, dízimos e controle financeiro — módulo planejado para a próxima etapa.
                        </span>
                        <span class="mt-6 text-sm font-medium text-[var(--eliu-mid)]/55">
                            Disponível em breve
                        </span>
                    </div>
                </div>
            </section>
        </main>

        <footer class="border-t border-[var(--eliu-ink)]/8 bg-white">
            <div class="mx-auto flex max-w-6xl flex-col gap-2 px-6 py-8 text-sm text-[var(--eliu-mid)]/70 sm:flex-row sm:items-center sm:justify-between sm:px-10 lg:px-12">
                <p class="font-display font-medium text-[var(--eliu-ink)]/80">Eliú</p>
                <p>{{ config('app.name') }}</p>
            </div>
        </footer>
    </body>
</html>
