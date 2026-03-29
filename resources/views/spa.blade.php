<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="EstaAqui — Encontrá productos de comercios locales cerca tuyo. Comprá directo por WhatsApp.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>EstaAqui — Comercios locales, cerca tuyo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fuse.js@7.0.0"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        // Inline script to prevent theme flash
        (function() {
            const theme = localStorage.getItem('theme') || 'dark';
            if (theme === 'light') {
                document.documentElement.classList.add('light');
            } else {
                document.documentElement.classList.remove('light');
            }
        })();
    </script>
    
    {{-- PWA Tags --}}
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#ea580c">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="EstaAqui">
</head>
<body class="min-h-screen font-sans antialiased transition-colors duration-500" :class="theme" x-data="app()" x-cloak>

    {{-- ═══════════════════════════════════════════════════════════════════
         NAVBAR
    ═══════════════════════════════════════════════════════════════════ --}}
    <nav class="sticky top-0 z-50 backdrop-blur-xl bg-surface-950/80 border-b border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3 flex items-center justify-between gap-4">
            {{-- Logo --}}
            <a href="#" @click.prevent="goHome()" class="flex items-center gap-2 shrink-0 group">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center text-xl font-black shadow-lg shadow-primary-500/30 group-hover:scale-105 transition-transform">
                    📍
                </div>
                <span class="text-xl font-black tracking-tight text-white hidden sm:block">EstaAqui</span>
            </a>

            {{-- Search Bar --}}
            <div class="flex-1 max-w-xl relative" x-show="view === 'home'">
                <div class="relative">
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input
                        type="text"
                        x-model="searchQuery"
                        @input.debounce.300ms="performSearch()"
                        placeholder="Buscar productos, categorías..."
                        id="search-input"
                        class="w-full pl-10 pr-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white placeholder-white/30 focus:outline-none focus:border-primary-500/50 focus:bg-white/8 focus:ring-1 focus:ring-primary-500/25 transition-all"
                    >
                    <button x-show="searchQuery" @click="searchQuery = ''; performSearch()" class="absolute right-3 top-1/2 -translate-y-1/2 text-white/30 hover:text-white transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                {{-- PWA Install Button --}}
                <template x-if="deferredPrompt">
                    <button @click="installPWA()" class="flex items-center gap-1.5 px-3 py-1.5 bg-primary-500/10 hover:bg-primary-500/20 text-primary-400 text-[10px] font-bold rounded-xl border border-primary-500/20 transition-all animate-pulse">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0L8 8m4-4v12"/></svg>
                        Instalar App
                    </button>
                </template>

                {{-- Theme Switcher ALWAYS visible --}}
                <div class="flex items-center">
                    <button @click="toggleTheme()" class="p-2 sm:px-3 sm:py-2 rounded-xl bg-surface-100/30 border border-surface-200/10 hover:bg-surface-200/20 transition-all flex items-center justify-center gap-2 group" title="Modo Claro/Oscuro">
                        <span x-show="theme === 'light'" class="text-sm">☀️</span>
                        <span x-show="theme === 'dark'" class="text-sm">🌙</span>
                        <span class="text-[10px] font-bold hidden sm:block pointer-events-none" x-text="theme === 'dark' ? 'OSCURO' : 'CLARO'"></span>
                    </button>
                </div>

                {{-- Non-auth actions --}}
                <template x-if="!comercioAuth">
                    <button @click="view = 'login'" class="text-xs sm:text-sm px-3 sm:px-4 py-2 rounded-xl bg-white/5 border border-white/10 hover:bg-white/10 transition-all font-medium text-white/70 hover:text-white">
                        Mi Comercio
                    </button>
                </template>

                {{-- Auth actions --}}
                <template x-if="comercioAuth">
                    <div class="flex items-center gap-2">
                        <button @click="view = 'admin'" class="text-xs sm:text-sm px-3 sm:px-4 py-2 rounded-xl bg-primary-500/20 border border-primary-500/30 hover:bg-primary-500/30 transition-all font-medium text-primary-300">
                            Panel
                        </button>
                        <button @click="logoutComercio()" class="text-xs sm:text-sm px-3 py-2 rounded-xl bg-white/5 hover:bg-red-500/20 transition-all text-white/50 hover:text-red-300">
                            Salir
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </nav>

    {{-- ═══════════════════════════════════════════════════════════════════
         HOME — CATÁLOGO
    ═══════════════════════════════════════════════════════════════════ --}}
    <main x-show="view === 'home'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">

        {{-- Hero --}}
        <section class="relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-b from-primary-900/30 via-transparent to-transparent"></div>
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 pt-12 pb-6 sm:pt-16 sm:pb-8 text-center">
                <h1 class="text-4xl sm:text-6xl font-black tracking-tight leading-none mb-4">
                    Encontrá <span class="text-primary-500">productos</span><br>
                    de tu ciudad
                </h1>
                <p class="mt-4 text-base sm:text-lg text-white/50 max-w-lg mx-auto leading-relaxed">Tu catálogo local unificado. Comprá directo, fácil y rápido por WhatsApp sin intermediarios.</p>
            </div>
        </section>

        {{-- Category Chips --}}
        <section class="max-w-7xl mx-auto px-4 sm:px-6 py-4">
            <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-hide" id="category-filters">
                <button
                    @click="selectedCategoria = null; fetchArticulos()"
                    :class="selectedCategoria === null ? 'bg-primary-500 text-white border-primary-400 shadow-lg shadow-primary-500/25' : 'bg-white/5 text-white/60 border-white/10 hover:bg-white/10 hover:text-white'"
                    class="shrink-0 px-4 py-2 rounded-full text-sm font-medium border transition-all duration-200"
                >
                    Todos
                </button>
                <template x-for="cat in categorias" :key="cat.id">
                    <button
                        @click="selectedCategoria = cat.nombre; fetchArticulos()"
                        :class="selectedCategoria === cat.nombre ? 'bg-primary-500 text-white border-primary-400 shadow-lg shadow-primary-500/25' : 'bg-white/5 text-white/60 border-white/10 hover:bg-white/10 hover:text-white'"
                        class="shrink-0 px-4 py-2 rounded-full text-sm font-medium border transition-all duration-200"
                    >
                        <span x-text="cat.icono + ' ' + cat.nombre"></span>
                    </button>
                </template>
            </div>
        </section>

        {{-- Comercios destacados --}}
        <section class="max-w-7xl mx-auto px-4 sm:px-6 pb-4" x-show="!searchQuery && !selectedCategoria">
            <h2 class="text-lg font-bold text-white/80 mb-3">🏪 Comercios</h2>
            <div class="flex gap-3 overflow-x-auto pb-2 scrollbar-hide">
                <template x-for="com in comercios" :key="com.id">
                    <button @click="goToTienda(com.slug)" class="shrink-0 group">
                        <div class="w-28 sm:w-32 p-3 bg-white/5 rounded-3xl border border-white/8 hover:border-primary-500/50 hover:bg-white/10 transition-all text-center">
                            <div class="w-12 h-12 mx-auto rounded-xl bg-gradient-to-br from-primary-500/30 to-accent-500/30 flex items-center justify-center text-2xl mb-2 overflow-hidden border border-white/5">
                                <template x-if="com.logo_url">
                                    <img :src="com.logo_url" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!com.logo_url">
                                    <span>🏬</span>
                                </template>
                            </div>
                            <p class="text-xs font-semibold text-white/80 truncate" x-text="com.nombre"></p>
                            <p class="text-[10px] text-white/40 mt-0.5" x-text="com.zona_barrio"></p>
                            <p class="text-[10px] text-primary-400 mt-1" x-text="com.articulos_count + ' productos'"></p>
                        </div>
                    </button>
                </template>
            </div>
        </section>

        {{-- Grilla de Productos --}}
        <section class="max-w-7xl mx-auto px-4 sm:px-6 pb-16">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-white/80">
                    <span x-show="!searchQuery && !selectedCategoria">🔥 Todos los productos</span>
                    <span x-show="searchQuery" x-text="`Resultados para '${searchQuery}'`"></span>
                    <span x-show="selectedCategoria && !searchQuery" x-text="selectedCategoria"></span>
                </h2>
                <span class="text-xs text-white/30" x-text="filteredArticulos.length + ' productos'"></span>
            </div>

            {{-- Loading --}}
            <div x-show="loading" class="flex justify-center py-20">
                <div class="w-8 h-8 border-2 border-primary-500/30 border-t-primary-500 rounded-full animate-spin"></div>
            </div>

            {{-- Empty State --}}
            <div x-show="!loading && filteredArticulos.length === 0" class="text-center py-20">
                <div class="text-5xl mb-4">🔍</div>
                <p class="text-white/40 text-lg">No encontramos productos</p>
                <p class="text-white/25 text-sm mt-1">Intentá con otra búsqueda o categoría</p>
            </div>

            {{-- Product Grid --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-6" x-show="!loading">
                <template x-for="art in filteredArticulos" :key="art.id">
                    <div @click="openArticuloDetail(art)" class="cursor-pointer group bg-white/[0.03] rounded-[32px] border border-white/8 overflow-hidden hover:border-primary-500/50 hover:bg-white/[0.06] transition-all duration-500 hover:-translate-y-1 hover:shadow-2xl hover:shadow-primary-500/10">
                        {{-- Image --}}
                        <div class="aspect-square overflow-hidden bg-white/5 relative">
                            <img
                                :src="art.imagen_url || 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjQwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iNDAwIiBoZWlnaHQ9IjQwMCIgZmlsbD0iIzFhMWEyZSIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBkb21pbmFudC1iYXNlbGluZT0iY2VudHJhbCIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZm9udC1zaXplPSI2MCIgZmlsbD0iIzMzMzM0NCIgZm9udC1mYW1pbHk9InNhbnMtc2VyaWYiPjwvdGV4dD48L3N2Zz4='"
                                :alt="art.nombre_producto"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                loading="lazy"
                            >
                            <div class="absolute top-2 right-2">
                                <span class="text-[10px] px-2 py-1 rounded-full bg-surface-950/70 backdrop-blur-sm text-white/60 border border-white/10" x-text="art.categoria"></span>
                            </div>
                        </div>
                        {{-- Info --}}
                        <div class="p-3 sm:p-4">
                            <button @click="goToTienda(art.comercio.slug)" class="flex items-center gap-1.5 mb-2 group/shop">
                                <div class="w-5 h-5 rounded-md bg-primary-500/20 flex items-center justify-center text-[10px]">🏪</div>
                                <span class="text-[11px] text-primary-400 font-medium truncate group-hover/shop:text-primary-300 transition" x-text="art.comercio.nombre"></span>
                                <span class="text-[10px] text-white/25">·</span>
                                <span class="text-[10px] text-white/30 truncate" x-text="art.comercio.zona_barrio"></span>
                            </button>
                            <h3 class="text-sm font-semibold text-white/90 leading-snug line-clamp-2 mb-1" x-text="art.nombre_producto"></h3>
                            <p class="text-[11px] text-white/35 line-clamp-2 mb-3 leading-relaxed" x-text="art.descripcion_articulo"></p>
                            <div class="flex items-end justify-between gap-2">
                                <span class="text-lg font-bold text-white" x-text="'$' + Number(art.precio_ars).toLocaleString('es-AR')"></span>
                                    <a
                                    :href="art.whatsapp_link"
                                    @click.stop="trackClick('click_whatsapp_articulo', art.comercio_id, art.id)"
                                    target="_blank"
                                    rel="noopener"
                                    class="flex items-center gap-1.5 px-4 py-2 bg-whatsapp hover:bg-whatsapp/90 rounded-2xl text-white text-xs font-bold transition-all hover:shadow-lg hover:shadow-whatsapp/30 active:scale-95"
                                    id="whatsapp-btn"
                                >
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.09.534 4.058 1.474 5.771L.058 23.7l6.064-1.393A11.94 11.94 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.785c-1.89 0-3.64-.525-5.146-1.435l-.368-.22-3.81.875.908-3.716-.24-.383A9.77 9.77 0 012.215 12c0-5.397 4.388-9.785 9.785-9.785S21.785 6.603 21.785 12 17.397 21.785 12 21.785z"/></svg>
                                    Stock
                                </a>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Load More --}}
            <div x-show="!loading && hasMore && filteredArticulos.length > 0" class="flex justify-center mt-8">
                <button @click="loadMore()" class="px-6 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm font-medium text-white/60 hover:bg-white/10 hover:text-white transition-all">
                    Cargar más
                </button>
            </div>
        </section>
    </main>

    {{-- ═══════════════════════════════════════════════════════════════════
         TIENDA — VISTA INDIVIDUAL
    ═══════════════════════════════════════════════════════════════════ --}}
    <main x-show="view === 'tienda'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">
            {{-- Back --}}
            <button @click="goHome()" class="flex items-center gap-1.5 text-sm text-white/40 hover:text-white mb-6 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Volver al catálogo
            </button>

            {{-- Shop Header --}}
            <div x-show="tiendaData" class="bg-gradient-to-br from-white/5 to-white/[0.02] rounded-[32px] border border-white/8 p-6 sm:p-10 mb-8 relative overflow-hidden">
                <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-primary-500/10 rounded-full blur-3xl"></div>
                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6 sm:gap-8 relative z-10 text-center sm:text-left">
                    <div class="w-24 h-24 sm:w-32 sm:h-32 rounded-3xl bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center text-4xl sm:text-5xl shrink-0 shadow-2xl shadow-primary-500/30 overflow-hidden border-2 border-white/10">
                        <template x-if="tiendaData?.comercio?.logo_url">
                            <img :src="tiendaData.comercio.logo_url" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!tiendaData?.comercio?.logo_url">
                            <span>🏬</span>
                        </template>
                    </div>
                    <div class="min-w-0">
                        <h1 class="text-2xl sm:text-3xl font-black" x-text="tiendaData?.comercio?.nombre"></h1>
                        <div class="flex flex-wrap items-center gap-2 mt-2">
                            <span class="inline-flex items-center gap-1 text-sm text-white/50">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span x-text="tiendaData?.comercio?.zona_barrio"></span>
                            </span>
                            <template x-if="tiendaData?.comercio?.direccion">
                                <span class="text-sm text-white/30" x-text="'· ' + tiendaData?.comercio?.direccion"></span>
                            </template>
                        </div>
                        <p class="text-sm text-white/40 mt-2 max-w-xl leading-relaxed" x-text="tiendaData?.comercio?.descripcion"></p>
                        <a
                            :href="'https://wa.me/' + tiendaData?.comercio?.whatsapp"
                            @click="trackClick('click_whatsapp_comercio', tiendaData?.comercio?.id)"
                            target="_blank"
                            class="inline-flex items-center gap-2 mt-6 px-6 py-3 bg-whatsapp hover:bg-whatsapp/90 rounded-2xl text-sm font-bold text-white transition-all hover:shadow-xl hover:shadow-whatsapp/30 active:scale-95"
                        >
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.09.534 4.058 1.474 5.771L.058 23.7l6.064-1.393A11.94 11.94 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.785c-1.89 0-3.64-.525-5.146-1.435l-.368-.22-3.81.875.908-3.716-.24-.383A9.77 9.77 0 012.215 12c0-5.397 4.388-9.785 9.785-9.785S21.785 6.603 21.785 12 17.397 21.785 12 21.785z"/></svg>
                            Contactar por WhatsApp
                        </a>
                    </div>
                </div>
            </div>

            {{-- Products --}}
            <h2 class="text-xl font-black text-white/90 mb-6" x-text="'Nuestros Productos (' + (tiendaData?.articulos?.length || 0) + ')'"></h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6 pb-16">
                <template x-for="art in (tiendaData?.articulos || [])" :key="art.id">
                    <div @click="openArticuloDetail(art)" class="cursor-pointer group bg-white/[0.03] rounded-[32px] border border-white/8 overflow-hidden hover:border-primary-500/50 hover:bg-white/[0.06] transition-all duration-500 hover:-translate-y-1">
                        <div class="aspect-square overflow-hidden bg-white/5">
                            <img :src="art.imagen_url" :alt="art.nombre_producto" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                        </div>
                        <div class="p-3 sm:p-4">
                            <span class="text-[10px] text-primary-400 font-medium" x-text="art.categoria"></span>
                            <h3 class="text-sm font-semibold text-white/90 leading-snug line-clamp-2 mt-1 mb-1" x-text="art.nombre_producto"></h3>
                            <p class="text-[11px] text-white/35 line-clamp-2 mb-3" x-text="art.descripcion_articulo"></p>
                            <div class="flex items-end justify-between gap-2">
                                <span class="text-lg font-black text-white" x-text="'$' + Number(art.precio_ars).toLocaleString('es-AR')"></span>
                                <a :href="art.whatsapp_link" @click.stop="trackClick('click_whatsapp_articulo', tiendaData?.comercio?.id, art.id)" target="_blank" rel="noopener" class="flex items-center gap-1.5 px-4 py-2 bg-whatsapp hover:bg-whatsapp/90 rounded-2xl text-white text-xs font-bold transition-all hover:shadow-lg hover:shadow-whatsapp/30 active:scale-95">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.09.534 4.058 1.474 5.771L.058 23.7l6.064-1.393A11.94 11.94 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.785c-1.89 0-3.64-.525-5.146-1.435l-.368-.22-3.81.875.908-3.716-.24-.383A9.77 9.77 0 012.215 12c0-5.397 4.388-9.785 9.785-9.785S21.785 6.603 21.785 12 17.397 21.785 12 21.785z"/></svg>
                                    Stock
                                </a>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </main>

    {{-- ═══════════════════════════════════════════════════════════════════
         LOGIN / REGISTER COMERCIO
    ═══════════════════════════════════════════════════════════════════ --}}
    <main x-show="view === 'login'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
        <div class="max-w-md mx-auto px-4 py-12">
            <button @click="goHome()" class="flex items-center gap-1.5 text-sm text-white/40 hover:text-white mb-8 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Volver
            </button>

            <div class="bg-white/[0.03] rounded-[32px] border border-white/8 p-8 sm:p-10 shadow-2xl">
                <div class="text-center mb-8">
                    <div class="w-16 h-16 mx-auto rounded-3xl bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center text-3xl mb-4 shadow-xl shadow-primary-500/30">🏪</div>
                    <h2 class="text-2xl font-black" x-text="authMode === 'login' ? 'Tu Comercio' : 'Registrate'"></h2>
                    <p class="text-sm text-white/40 mt-2">Gestioná tus productos y recibí consultas directas</p>
                </div>

                {{-- Error --}}
                <div x-show="authError" class="mb-4 p-3 bg-red-500/10 border border-red-500/20 rounded-xl text-red-400 text-sm" x-text="authError"></div>

                {{-- Login Form --}}
                <form x-show="authMode === 'login'" @submit.prevent="loginComercio()" class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-white/50 mb-1.5">Email</label>
                        <input type="email" x-model="authForm.email" required class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white placeholder-white/25 focus:outline-none focus:border-primary-500/50 focus:ring-1 focus:ring-primary-500/25" placeholder="tu@email.com">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-white/50 mb-1.5">Contraseña</label>
                        <input type="password" x-model="authForm.password" required class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white placeholder-white/25 focus:outline-none focus:border-primary-500/50 focus:ring-1 focus:ring-primary-500/25" placeholder="••••••">
                    </div>
                    <button type="submit" :disabled="authLoading" class="w-full py-4 bg-primary-500 hover:bg-primary-600 rounded-2xl text-sm font-black transition-all disabled:opacity-50 shadow-lg shadow-primary-500/25">
                        <span x-show="!authLoading" x-text="authMode === 'login' ? 'Entrar ahora' : 'Crear mi cuenta'"></span>
                        <span x-show="authLoading">Procesando...</span>
                    </button>
                </form>

                {{-- Register Form --}}
                <form x-show="authMode === 'register'" @submit.prevent="registerComercio()" class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-white/50 mb-1.5">Nombre del comercio</label>
                        <input type="text" x-model="authForm.nombre" required class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white placeholder-white/25 focus:outline-none focus:border-primary-500/50 focus:ring-1 focus:ring-primary-500/25" placeholder="Mi Tienda">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-white/50 mb-1.5">Email</label>
                        <input type="email" x-model="authForm.email" required class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white placeholder-white/25 focus:outline-none focus:border-primary-500/50 focus:ring-1 focus:ring-primary-500/25" placeholder="tu@email.com">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-white/50 mb-1.5">WhatsApp</label>
                            <input type="text" x-model="authForm.whatsapp" required class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white placeholder-white/25 focus:outline-none focus:border-primary-500/50 focus:ring-1 focus:ring-primary-500/25" placeholder="5491155001234">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-white/50 mb-1.5">Zona / Barrio</label>
                            <input type="text" x-model="authForm.zona_barrio" required class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white placeholder-white/25 focus:outline-none focus:border-primary-500/50 focus:ring-1 focus:ring-primary-500/25" placeholder="Palermo">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-white/50 mb-1.5">Contraseña</label>
                        <input type="password" x-model="authForm.password" required minlength="6" class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white placeholder-white/25 focus:outline-none focus:border-primary-500/50 focus:ring-1 focus:ring-primary-500/25" placeholder="Mínimo 6 caracteres">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-white/50 mb-1.5">Confirmar Contraseña</label>
                        <input type="password" x-model="authForm.password_confirmation" required class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white placeholder-white/25 focus:outline-none focus:border-primary-500/50 focus:ring-1 focus:ring-primary-500/25" placeholder="Repetí tu contraseña">
                    </div>
                    <button type="submit" :disabled="authLoading" class="w-full py-3 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-400 hover:to-primary-500 rounded-xl text-sm font-bold transition-all disabled:opacity-50 shadow-lg shadow-primary-500/25">
                        <span x-show="!authLoading">Crear cuenta</span>
                        <span x-show="authLoading">Registrando...</span>
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <button @click="authMode = authMode === 'login' ? 'register' : 'login'; authError = ''" class="text-sm text-primary-400 hover:text-primary-300 transition">
                        <span x-text="authMode === 'login' ? '¿No tenés cuenta? Registrate' : '¿Ya tenés cuenta? Ingresá'"></span>
                    </button>
                </div>
            </div>
        </div>
    </main>

    {{-- ═══════════════════════════════════════════════════════════════════
         ADMIN PANEL — COMERCIO
    ═══════════════════════════════════════════════════════════════════ --}}
    <main x-show="view === 'admin'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 py-6">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6 relative z-10">
                <div>
                    <h1 class="text-2xl font-bold">📋 Mi Panel</h1>
                    <p class="text-sm text-white/40 mt-0.5" x-text="comercioAuth?.nombre"></p>
                </div>
                <div class="flex flex-col items-end gap-2">
                    <a :href="`/api/comercio/articulos/ejemplo-excel?_token=${csrfToken}`" download class="text-xs text-primary-400 hover:text-primary-300 underline transition">⬇️ Descargar Excel de ejemplo</a>
                    <div class="flex items-center gap-2">
                        <button @click="$refs.excelInput.click()" :disabled="importingExcel" class="px-4 py-2 bg-white/5 border border-white/10 hover:bg-white/10 rounded-xl text-sm font-bold transition-all shadow-lg relative z-20 cursor-pointer pointer-events-auto flex items-center gap-2 disabled:opacity-50">
                            <span x-show="!importingExcel">📥 Importar Excel</span>
                            <span x-show="importingExcel">Cargando...</span>
                        </button>
                        <input type="file" accept=".xlsx,.csv" x-ref="excelInput" class="hidden" @change="importExcel($event)">
                        
                        <button @click="showArticuloForm = true; editingArticulo = null; resetArticuloForm()" class="px-4 py-2 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-400 hover:to-primary-500 rounded-xl text-sm font-bold transition-all shadow-lg shadow-primary-500/25 relative z-20 cursor-pointer pointer-events-auto">
                            + Nuevo Artículo
                        </button>
                    </div>
                </div>
            </div>

            {{-- Tabs --}}
            <div class="flex gap-6 border-b border-white/10 mb-6">
                <button @click="adminTab = 'articulos'" class="pb-3 text-sm font-medium transition-all" :class="adminTab === 'articulos' ? 'text-primary-400 border-b-2 border-primary-500' : 'text-white/50 hover:text-white'">
                    📦 Mis Artículos
                </button>
                <button @click="adminTab = 'informes'; fetchInformes()" class="pb-3 text-sm font-medium transition-all" :class="adminTab === 'informes' ? 'text-primary-400 border-b-2 border-primary-500' : 'text-white/50 hover:text-white'">
                    📊 Informes
                </button>
                <button @click="adminTab = 'perfil'; initPerfilForm()" class="pb-3 text-sm font-medium transition-all" :class="adminTab === 'perfil' ? 'text-primary-400 border-b-2 border-primary-500' : 'text-white/50 hover:text-white'">
                    👤 Perfil
                </button>
            </div>

            <div x-show="adminTab === 'articulos'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            {{-- Artículo Form Modal --}}
            <div x-show="showArticuloForm" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md" @click.self="showArticuloForm = false">
                <div class="bg-surface-900 rounded-[32px] border border-white/10 p-8 w-full max-w-lg max-h-[90vh] overflow-y-auto shadow-3xl">
                    <h3 class="text-xl font-black mb-6" x-text="editingArticulo ? 'Editar Artículo' : 'Nuevo Artículo'"></h3>
                    <form @submit.prevent="saveArticulo()" class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-white/50 mb-1.5">Nombre del producto *</label>
                            <input type="text" x-model="articuloForm.nombre_producto" required class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-primary-500/50 focus:ring-1 focus:ring-primary-500/25">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-white/50 mb-1.5">Descripción</label>
                            <textarea x-model="articuloForm.descripcion_articulo" rows="3" class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-primary-500/50 focus:ring-1 focus:ring-primary-500/25 resize-none"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-white/50 mb-1.5">Precio (ARS) *</label>
                                <input type="number" step="0.01" x-model="articuloForm.precio_ars" required class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-primary-500/50 focus:ring-1 focus:ring-primary-500/25">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-white/50 mb-1.5">Categoría *</label>
                                <select x-model="articuloForm.categoria" required class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-primary-500/50 focus:ring-1 focus:ring-primary-500/25">
                                    <option value="">Seleccionar</option>
                                    <template x-for="cat in categorias" :key="cat.id">
                                        <option :value="cat.nombre" x-text="cat.nombre"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-white/50 mb-1.5">Orden de prioridad (Opcional)</label>
                            <input type="number" x-model="articuloForm.orden" class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-primary-500/50 focus:ring-1 focus:ring-primary-500/25" placeholder="0">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-white/50 mb-1.5">Foto del producto (opcional)</label>
                            <div class="flex flex-col gap-2">
                                <div class="flex gap-2">
                                    <button type="button" @click="$refs.imagenInput.click()" class="flex-1 py-2 bg-white/5 border border-white/10 rounded-xl text-xs font-medium hover:bg-white/10 transition flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                        Subir Archivo
                                    </button>
                                    <button type="button" @click="$refs.imagenInputCamera.click()" class="flex-1 py-2 bg-primary-500/10 border border-primary-500/20 rounded-xl text-xs font-medium text-primary-300 hover:bg-primary-500/20 transition flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        Tomar Foto
                                    </button>
                                </div>
                                <input type="file" accept="image/*" @change="articuloForm.imagen_file = $event.target.files[0]" x-ref="imagenInput" class="hidden">
                                <input type="file" accept="image/*" capture="environment" @change="articuloForm.imagen_file = $event.target.files[0]" x-ref="imagenInputCamera" class="hidden">
                                <p class="text-[10px] text-white/30" x-show="articuloForm.imagen_file" x-text="'Seleccionado: ' + articuloForm.imagen_file?.name"></p>
                            </div>
                            <template x-if="editingArticulo && editingArticulo.imagen_url && !articuloForm.imagen_file">
                                <img :src="editingArticulo.imagen_url" class="mt-2 h-16 rounded-xl object-cover border border-white/10">
                            </template>
                        </div>

                        {{-- Carousel Images --}}
                        <div class="pt-2 border-t border-white/5">
                            <label class="block text-xs font-medium text-white/50 mb-3 uppercase tracking-wider">Fotos adicionales (Carrusel)</label>
                            
                            {{-- Existing images preview --}}
                            <template x-if="editingArticulo && editingArticulo.imagenes && editingArticulo.imagenes.length > 0">
                                <div class="grid grid-cols-4 gap-2 mb-4">
                                    <template x-for="img in editingArticulo.imagenes" :key="img.id">
                                        <div class="relative group aspect-square rounded-xl overflow-hidden border border-white/10" x-show="!articuloForm.deletedImagenes.includes(img.id)">
                                            <img :src="img.url" class="w-full h-full object-cover">
                                            <button type="button" @click="articuloForm.deletedImagenes.push(img.id)" class="absolute inset-0 bg-red-600/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            {{-- New images selection --}}
                            <div class="flex flex-col gap-2">
                                <button type="button" @click="$refs.imagenesInput.click()" class="w-full py-3 bg-white/5 border border-dashed border-white/20 rounded-xl text-xs font-medium text-white/40 hover:text-white/60 hover:bg-white/10 transition flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Agregar más fotos al carrusel
                                </button>
                                <input type="file" accept="image/*" multiple @change="articuloForm.imagenes_file = $event.target.files" x-ref="imagenesInput" class="hidden">
                                <div x-show="articuloForm.imagenes_file.length > 0" class="flex flex-wrap gap-1 p-2 bg-primary-500/5 rounded-xl border border-primary-500/10">
                                    <span class="text-[10px] text-primary-400 font-bold" x-text="articuloForm.imagenes_file.length + ' fotos nuevas seleccionadas'"></span>
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-3 pt-2">
                            <button type="button" @click="showArticuloForm = false" class="flex-1 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm font-medium text-white/60 hover:bg-white/10 transition">Cancelar</button>
                            <button type="submit" :disabled="articuloLoading" class="flex-1 py-2.5 bg-gradient-to-r from-primary-500 to-primary-600 rounded-xl text-sm font-bold transition-all shadow-lg shadow-primary-500/25 disabled:opacity-50">
                                <span x-show="!articuloLoading">Guardar</span>
                                <span x-show="articuloLoading">Guardando...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- My Articles List --}}
            <div class="space-y-3">
                <template x-for="art in misArticulos" :key="art.id">
                    <div class="flex items-center gap-4 bg-white/[0.03] rounded-3xl border border-white/8 p-4 hover:bg-white/[0.05] transition">
                        <img :src="art.imagen_url || ''" :alt="art.nombre_producto" class="w-16 h-16 rounded-2xl object-cover bg-white/5 shrink-0">
                        <div class="flex-1 min-w-0">
                            <h4 class="font-bold text-sm text-white/90 truncate" x-text="art.nombre_producto"></h4>
                            <p class="text-xs text-white/40 mt-0.5" x-text="art.categoria"></p>
                            <p class="text-sm font-black text-white mt-1" x-text="`$${Number(art.precio_ars).toLocaleString('es-AR')}`"></p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button @click="editArticulo(art)" class="p-2 bg-white/5 rounded-lg hover:bg-primary-500/20 transition text-white/40 hover:text-primary-300">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <button @click="deleteArticulo(art.id)" class="p-2 bg-white/5 rounded-lg hover:bg-red-500/20 transition text-white/40 hover:text-red-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                </template>
                <div x-show="misArticulos.length === 0" class="text-center py-16">
                    <div class="text-5xl mb-4">📦</div>
                    <p class="text-white/40">No tenés artículos todavía</p>
                    <p class="text-white/25 text-sm mt-1">Empezá agregando tu primer producto</p>
                </div>
            </div>
            </div> {{-- End Articulos Tab --}}

            {{-- Informes Tab --}}
            <div x-show="adminTab === 'informes'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <template x-if="informesData">
                    <div class="space-y-6">
                        {{-- Resumen Totales --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-gradient-to-br from-white/5 to-white/[0.02] border border-white/10 rounded-2xl p-5 shadow-lg relative overflow-hidden">
                                <div class="absolute -right-4 -bottom-4 text-7xl opacity-5">🏪</div>
                                <p class="text-xs text-white/50 font-medium mb-1">Visitas al Perfil</p>
                                <p class="text-3xl font-black text-white" x-text="informesData.totales.vistas_perfil"></p>
                            </div>
                            <div class="bg-gradient-to-br from-whatsapp/20 to-whatsapp/5 border border-whatsapp/20 rounded-2xl p-5 shadow-lg shadow-whatsapp/5 relative overflow-hidden">
                                <div class="absolute -right-4 -bottom-4 text-7xl opacity-5">💬</div>
                                <p class="text-xs text-whatsapp font-medium mb-1">Clicks a WhatsApp (Perfil)</p>
                                <p class="text-3xl font-black text-white" x-text="informesData.totales.clicks_whatsapp_perfil"></p>
                            </div>
                        </div>

                        {{-- Articulos Clicks --}}
                        <div class="bg-white/[0.03] border border-white/10 rounded-2xl overflow-hidden">
                            <div class="px-5 py-4 border-b border-white/10 bg-white/[0.02]">
                                <h3 class="text-sm font-bold text-white/80">🔥 Artículos más consultados (WhatsApp)</h3>
                            </div>
                            <div class="divide-y divide-white/5">
                                <template x-for="art in informesData.articulos" :key="art.articulo_id">
                                    <div class="flex items-center justify-between p-4 px-5 hover:bg-white/[0.02] transition">
                                        <div class="font-medium text-sm text-white/90 truncate mr-4" x-text="art.nombre"></div>
                                        <div class="flex items-center gap-2 shrink-0">
                                            <span class="text-xs font-bold bg-white/10 text-white/80 px-2 py-1 rounded-md" x-text="art.clicks + ' clicks'"></span>
                                        </div>
                                    </div>
                                </template>
                                <div x-show="informesData.articulos.length === 0" class="p-8 text-center text-sm text-white/40">
                                    Aún no hay interacciones con los artículos.
                                </div>
                            </div>
                        </div>

                        {{-- Recientes --}}
                        <div class="bg-white/[0.03] border border-white/10 rounded-2xl overflow-hidden">
                            <div class="px-5 py-4 border-b border-white/10 bg-white/[0.02]">
                                <h3 class="text-sm font-bold text-white/80">📝 Registro de actividad reciente</h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse text-xs">
                                    <thead class="bg-white/[0.02] text-white/40 border-b border-white/5">
                                        <tr>
                                            <th class="p-3 px-5 font-medium">Fecha</th>
                                            <th class="p-3 px-5 font-medium">Evento</th>
                                            <th class="p-3 px-5 font-medium">Detalle</th>
                                            <th class="p-3 px-5 font-medium whitespace-nowrap">IP / Info</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-white/5 text-white/70">
                                        <template x-for="log in informesData.recientes">
                                            <tr class="hover:bg-white/[0.02]">
                                                <td class="p-3 px-5 whitespace-nowrap" x-text="log.fecha"></td>
                                                <td class="p-3 px-5 font-medium" :class="{'text-whatsapp': log.tipo.includes('whatsapp'), 'text-primary-400': log.tipo === 'vista_comercio'}" x-text="log.tipo === 'vista_comercio' ? 'Vista Perfil' : (log.tipo === 'click_whatsapp_articulo' ? 'Consulta Artículo' : 'WhatsApp Comer.')"></td>
                                                <td class="p-3 px-5 max-w-[150px] truncate" x-text="log.articulo || '-'"></td>
                                                <td class="p-3 px-5 font-mono text-[10px] text-white/30 truncate max-w-[150px]" :title="log.user_agent" x-text="log.ip"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                                <div x-show="informesData.recientes.length === 0" class="p-8 text-center text-sm text-white/40">
                                    Aún no hay actividad registrada.
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
                <div x-show="!informesData" class="py-20 text-center">
                    <div class="w-8 h-8 border-2 border-primary-500/30 border-t-primary-500 rounded-full animate-spin mx-auto mb-4"></div>
                    <p class="text-white/40 text-sm">Cargando informes...</p>
                </div>
            </div>

            {{-- Perfil Tab --}}
            <div x-show="adminTab === 'perfil'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <div class="bg-white/[0.03] border border-white/10 rounded-2xl p-6 sm:p-8">
                    <h3 class="text-lg font-bold mb-6 flex items-center gap-2">
                        <span class="p-2 bg-primary-500/20 rounded-lg text-primary-400">👤</span>
                        Configuración del Comercio
                    </h3>
                    
                    <form @submit.prevent="savePerfil()" class="space-y-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            {{-- Logo Upload --}}
                            <div class="sm:col-span-2 flex flex-col items-center sm:flex-row gap-6 p-4 bg-white/5 rounded-2xl border border-dashed border-white/10">
                                <div class="w-24 h-24 rounded-2xl bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center text-4xl shrink-0 overflow-hidden shadow-xl shadow-primary-500/10 border border-white/10">
                                    <template x-if="perfilForm.logo_url && !perfilForm.logo_file">
                                        <img :src="perfilForm.logo_url" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="perfilForm.logo_file">
                                        <img :src="URL.createObjectURL(perfilForm.logo_file)" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!perfilForm.logo_url && !perfilForm.logo_file">
                                        <span>🏬</span>
                                    </template>
                                </div>
                                <div class="flex-1 text-center sm:text-left">
                                    <p class="text-sm font-bold text-white/90 mb-1">Logo del Comercio</p>
                                    <p class="text-xs text-white/40 mb-4">Recomendado: Cuadrado (800x800px). Máximo 10MB.</p>
                                    <div class="flex flex-wrap justify-center sm:justify-start gap-2">
                                        <button type="button" @click="$refs.logoInput.click()" class="px-4 py-2 bg-white/10 hover:bg-white/20 rounded-xl text-xs font-bold transition flex items-center gap-2">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                            Seleccionar archivo
                                        </button>
                                        <button type="button" @click="$refs.logoInputCamera.click()" class="px-4 py-2 bg-primary-500/20 text-primary-300 hover:bg-primary-500/30 rounded-xl text-xs font-bold transition flex items-center gap-2">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            Tomar Foto
                                        </button>
                                    </div>
                                    <input type="file" accept="image/*" @change="perfilForm.logo_file = $event.target.files[0]" x-ref="logoInput" class="hidden">
                                    <input type="file" accept="image/*" capture="environment" @change="perfilForm.logo_file = $event.target.files[0]" x-ref="logoInputCamera" class="hidden">
                                </div>
                            </div>

                            <div class="sm:col-span-1">
                                <label class="block text-xs font-medium text-white/50 mb-1.5 uppercase tracking-wider">Nombre del Comercio</label>
                                <input type="text" x-model="perfilForm.nombre" required class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-primary-500/50 focus:ring-1 focus:ring-primary-500/25 transition-all">
                            </div>
                            
                            <div class="sm:col-span-1">
                                <label class="block text-xs font-medium text-white/50 mb-1.5 uppercase tracking-wider">WhatsApp (con código de país)</label>
                                <input type="text" x-model="perfilForm.whatsapp" required class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-primary-500/50 focus:ring-1 focus:ring-primary-500/25 transition-all" placeholder="Ej: 5491155001234">
                            </div>

                            <div class="sm:col-span-1">
                                <label class="block text-xs font-medium text-white/50 mb-1.5 uppercase tracking-wider">Zona / Barrio</label>
                                <input type="text" x-model="perfilForm.zona_barrio" required class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-primary-500/50 focus:ring-1 focus:ring-primary-500/25 transition-all">
                            </div>

                            <div class="sm:col-span-1">
                                <label class="block text-xs font-medium text-white/50 mb-1.5 uppercase tracking-wider">Dirección Exacta (Opcional)</label>
                                <input type="text" x-model="perfilForm.direccion" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-primary-500/50 focus:ring-1 focus:ring-primary-500/25 transition-all">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-white/50 mb-1.5 uppercase tracking-wider">Descripción del Comercio</label>
                                <textarea x-model="perfilForm.descripcion" rows="4" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-primary-500/50 focus:ring-1 focus:ring-primary-500/25 transition-all resize-none"></textarea>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4">
                            <button type="submit" :disabled="authLoading" class="px-8 py-3 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-400 hover:to-primary-500 rounded-xl text-sm font-bold transition-all shadow-lg shadow-primary-500/25 disabled:opacity-50">
                                <span x-show="!authLoading">Guardar Cambios</span>
                                <span x-show="authLoading">Guardando...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    {{-- ═══════════════════════════════════════════════════════════════════
         SUPER ADMIN
    ═══════════════════════════════════════════════════════════════════ --}}
    <main x-show="view === 'superadmin_login'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
        <div class="max-w-md mx-auto px-4 py-20">
            <div class="bg-surface-900 rounded-2xl border border-white/10 p-8 shadow-2xl">
                <div class="text-center mb-6">
                    <span class="text-3xl">🛡️</span>
                    <h2 class="text-xl font-bold mt-2">Acceso Administrador</h2>
                </div>
                <div x-show="superAdminError" class="mb-4 p-3 bg-red-500/10 border border-red-500/20 rounded-xl text-red-400 text-sm" x-text="superAdminError"></div>
                <form @submit.prevent="loginSuperAdmin()" class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-white/50 mb-1.5">Email</label>
                        <input type="email" x-model="superAdminLoginData.email" required class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-primary-500/50">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-white/50 mb-1.5">Contraseña</label>
                        <input type="password" x-model="superAdminLoginData.password" required class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-primary-500/50">
                    </div>
                    <button type="submit" :disabled="authLoading" class="w-full py-3 bg-gradient-to-r from-primary-500 to-primary-600 rounded-xl text-sm font-bold shadow-lg transition disabled:opacity-50">
                        Ingresar
                    </button>
                </form>
                <div class="mt-4 text-center">
                    <button @click="goHome()" class="text-xs text-white/40 hover:text-white transition">Volver al sitio</button>
                </div>
            </div>
        </div>
    </main>

    <main x-show="view === 'superadmin_panel'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">
            <div class="flex items-center justify-between mb-8 border-b border-white/10 pb-4">
                <div>
                    <h1 class="text-2xl font-black text-white/90">🛡️ Panel Super Admin</h1>
                    <p class="text-sm text-white/40">Gestión de comercios</p>
                </div>
                <button @click="logoutSuperAdmin()" class="px-4 py-2 bg-white/5 border border-white/10 hover:bg-white/10 rounded-xl text-sm font-bold text-white/80 transition shadow-lg">
                    Salir
                </button>
            </div>

            <div class="bg-surface-900/50 rounded-2xl border border-white/5 overflow-hidden mb-12">
                <div class="p-6 border-b border-white/5 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-white/60 uppercase tracking-widest">Listado de Comercios</h2>
                    <span class="text-xs text-white/30" x-text="superAdminComercios.length + ' comercios registrados'"></span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead class="bg-white/[0.03] text-white/50">
                            <tr>
                                <th class="p-4 font-medium">Comercio</th>
                                <th class="p-4 font-medium">Prioridad</th>
                                <th class="p-4 font-medium">Ingreso</th>
                                <th class="p-4 font-medium">Artículos</th>
                                <th class="p-4 font-medium">Interacciones</th>
                                <th class="p-4 font-medium">Estado</th>
                                <th class="p-4 font-medium text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 text-white/80">
                            <template x-for="com in superAdminComercios" :key="com.id">
                                <tr class="hover:bg-white/[0.02]">
                                    <td class="p-4">
                                        <div class="font-bold text-white/90 truncate max-w-[200px]" x-text="com.nombre"></div>
                                        <div class="text-[10px] text-white/40 mt-0.5" x-text="com.email"></div>
                                    </td>
                                    <td class="p-4">
                                        <input type="number" :value="com.orden" @change="updateSuperAdminOrden(com.id, $event.target.value)" class="w-16 bg-white/5 border border-white/10 rounded px-2 py-1 text-xs text-center focus:border-primary-500/50 outline-none">
                                    </td>
                                    <td class="p-4 text-xs whitespace-nowrap" x-text="com.fecha_ingreso"></td>
                                    <td class="p-4 text-xs" x-text="com.articulos_count + ' arts'"></td>
                                    <td class="p-4 text-xs">
                                        <div class="text-white/60">Totales: <span class="font-bold text-white/80" x-text="com.total_clicks"></span></div>
                                        <div class="text-whatsapp">WA: <span class="font-bold" x-text="com.whatsapp_clicks"></span></div>
                                    </td>
                                    <td class="p-4">
                                        <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md text-[10px] font-bold" :class="com.activo ? 'bg-green-500/10 text-green-400 border border-green-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20'">
                                            <span x-text="com.activo ? 'HABILITADO' : 'SUSPENDIDO'"></span>
                                        </span>
                                    </td>
                                    <td class="p-4 text-right whitespace-nowrap space-x-2">
                                        <button @click="toggleSuperAdminComercio(com.id)" class="text-xs px-2 py-1 rounded bg-white/5 hover:bg-white/10 transition" x-text="com.activo ? 'Suspender' : 'Habilitar'"></button>
                                        <button @click="resetSuperAdminPassword(com.id)" class="text-xs px-2 py-1 rounded bg-orange-500/10 text-orange-400 hover:bg-orange-500/20 transition">Reset Clave</button>
                                        <button @click="deleteSuperAdminComercio(com.id)" class="text-xs px-2 py-1 rounded bg-red-500/10 text-red-500 hover:bg-red-500/20 transition">Eliminar</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- App Configuration Section --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-surface-900 border border-white/10 rounded-2xl p-6 shadow-xl">
                    <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                        📱 Configuración App (PWA)
                    </h2>
                    <div class="space-y-6">
                        <div>
                            <label class="block text-xs font-medium text-white/50 mb-3 uppercase">Nombre de la Aplicación</label>
                            <div class="flex gap-2">
                                <input type="text" x-model="appSettings.app_name" class="flex-1 px-4 py-2 bg-white/5 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-primary-500/50">
                                <button @click="saveSettings()" :disabled="authLoading" class="px-4 py-2 bg-primary-500/10 border border-primary-500/20 text-primary-400 hover:bg-primary-500/20 text-xs font-bold rounded-xl transition disabled:opacity-50">
                                    Guardar
                                </button>
                            </div>
                        </div>
                        <div class="pt-4 border-t border-white/5">
                            <label class="block text-xs font-medium text-white/50 mb-3 uppercase">Icono de la Aplicación</label>
                            <div class="flex items-center gap-6 p-4 bg-white/5 rounded-xl border border-white/5">
                                <div class="w-20 h-20 bg-black rounded-2xl overflow-hidden border border-white/10 shrink-0">
                                    <img :src="appSettings.app_icon || '/icon-192.png'" class="w-full h-full object-cover">
                                </div>
                                <div class="flex-1">
                                    <p class="text-[11px] text-white/40 mb-3">Subí una imagen cuadrada (512x512px recomendado) para usar como icono del celular.</p>
                                    <input type="file" @change="updateAppIcon($event)" accept="image/*" class="hidden" x-ref="appIconInput">
                                    <button @click="$refs.appIconInput.click()" :disabled="authLoading" class="px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white text-xs font-bold rounded-lg transition disabled:opacity-50">
                                        Cambiar Icono
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </main>

    <footer class="max-w-7xl mx-auto px-4 sm:px-6 py-12 border-t border-white/5 mt-auto">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6 opacity-40 hover:opacity-100 transition-opacity duration-500">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-white/5 flex items-center justify-center text-sm">📍</div>
                <div class="flex flex-col">
                    <span class="text-xs font-black tracking-tight text-white uppercase" x-text="appSettings.app_name"></span>
                    <span class="text-[10px] text-white/50">© 2026 — Todos los derechos reservados</span>
                </div>
            </div>
            
            <div class="flex items-center gap-6">
                <button @click="goToSuperAdmin()" class="group flex items-center gap-2 text-[10px] text-white/30 hover:text-primary-400 transition-colors uppercase tracking-widest font-bold">
                    <span class="w-1.5 h-1.5 rounded-full bg-white/20 group-hover:bg-primary-500 transition-colors"></span>
                    Acceso Super Admin
                </button>
            </div>
        </div>
    </footer>

    {{-- ═══════════════════════════════════════════════════════════════════
         ARTICLE DETAIL MODAL
    ═══════════════════════════════════════════════════════════════════ --}}
    <div x-show="showArticuloDetail" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/80 backdrop-blur-md"
         @keydown.escape.window="showArticuloDetail = false"
         x-cloak>
        <div class="bg-surface-900 w-full max-w-4xl max-h-[90vh] rounded-3xl overflow-hidden border border-white/10 shadow-2xl flex flex-col md:flex-row relative" @click.away="showArticuloDetail = false">
            {{-- Close Button --}}
            <button @click="showArticuloDetail = false" class="absolute top-4 right-4 z-10 w-10 h-10 bg-black/40 hover:bg-black/60 backdrop-blur-md rounded-full flex items-center justify-center text-white transition-all border border-white/10">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            {{-- Image / Carousel --}}
            <div class="w-full md:w-1/2 bg-black flex items-center justify-center relative aspect-square md:aspect-auto">
                {{-- Carousel Main --}}
                <template x-if="currentSlides.length > 0">
                    <div class="w-full h-full">
                        <template x-for="(src, index) in currentSlides" :key="index">
                            <div x-show="currentSlide === index" 
                                 x-transition:enter="transition ease-out duration-500"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 class="absolute inset-0">
                                <img :src="src" class="w-full h-full object-contain">
                            </div>
                        </template>
                    </div>
                </template>

                {{-- Carousel Controls --}}
                <template x-if="currentSlides.length > 1">
                    <div class="absolute inset-0 flex items-center justify-between px-4">
                        <button @click="prevSlide()" class="w-10 h-10 bg-black/20 hover:bg-black/40 rounded-full flex items-center justify-center text-white backdrop-blur-sm border border-white/5 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <button @click="nextSlide()" class="w-10 h-10 bg-black/20 hover:bg-black/40 rounded-full flex items-center justify-center text-white backdrop-blur-sm border border-white/5 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </template>

                {{-- Carousel Indicators --}}
                <template x-if="currentSlides.length > 1">
                    <div class="absolute bottom-4 left-0 right-0 flex justify-center gap-1.5">
                        <template x-for="(src, index) in currentSlides" :key="index">
                            <div class="h-1.5 rounded-full transition-all duration-300" :class="currentSlide === index ? 'w-6 bg-primary-500' : 'w-1.5 bg-white/30'"></div>
                        </template>
                    </div>
                </template>
            </div>

            {{-- Info --}}
            <div class="w-full md:w-1/2 p-6 sm:p-8 flex flex-col justify-between overflow-y-auto">
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <span class="px-2.5 py-1 bg-primary-500/10 text-primary-400 text-[10px] font-bold rounded-lg border border-primary-500/20 tracking-wider uppercase" x-text="articuloSeleccionado?.categoria"></span>
                        <template x-if="articuloSeleccionado?.comercio">
                            <span class="text-[10px] text-white/40 flex items-center gap-1">
                                🏪 <span x-text="articuloSeleccionado.comercio.nombre"></span>
                            </span>
                        </template>
                    </div>
                    <h2 class="text-2xl font-black text-white/90 leading-tight mb-4" x-text="articuloSeleccionado?.nombre_producto"></h2>
                    <p class="text-white/60 text-sm leading-relaxed mb-6 whitespace-pre-wrap" x-text="articuloSeleccionado?.descripcion_articulo || 'Sin descripción disponible.'"></p>
                    
                    <div class="flex items-center gap-4 mb-8">
                        <div class="text-3xl font-black text-white" x-text="'$' + Number(articuloSeleccionado?.precio_ars || 0).toLocaleString('es-AR')"></div>
                    </div>
                </div>

                <div class="space-y-3">
                    <a :href="articuloSeleccionado?.whatsapp_link" 
                       @click="trackClick('click_whatsapp_articulo', articuloSeleccionado?.comercio_id, articuloSeleccionado?.id)"
                       target="_blank"
                       class="w-full flex items-center justify-center gap-3 py-4 bg-whatsapp hover:bg-whatsapp/90 text-white rounded-2xl font-black transition-all shadow-xl shadow-whatsapp/20 active:scale-[0.98]">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.09.534 4.058 1.474 5.771L.058 23.7l6.064-1.393A11.94 11.94 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.785c-1.89 0-3.64-.525-5.146-1.435l-.368-.22-3.81.875.908-3.716-.24-.383A9.77 9.77 0 012.215 12c0-5.397 4.388-9.785 9.785-9.785S21.785 6.603 21.785 12 17.397 21.785 12 21.785z"/></svg>
                        CONSULTAR POR WHATSAPP
                    </a>
                    <button @click="showArticuloDetail = false" class="text-xs text-white/30 hover:text-white/50 w-full pt-2 transition">Seguir viendo más productos</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════
         ALPINE.JS APP
    ═══════════════════════════════════════════════════════════════════ --}}
    <script>
    function app() {
        return {
            // State
            view: 'home',
            loading: true,
            searchQuery: '',
            selectedCategoria: null,
            categorias: [],
            comercios: [],
            allArticulos: [],
            filteredArticulos: [],
            currentPage: 1,
            hasMore: false,
            fuseInstance: null,
            theme: localStorage.getItem('theme') || 'dark',

            // UI State
            articuloSeleccionado: null,
            showArticuloDetail: false,
            currentSlide: 0,

            toggleTheme() {
                this.theme = (this.theme === 'dark') ? 'light' : 'dark';
                localStorage.setItem('theme', this.theme);
            },

            applyTheme() {
                if (this.theme === 'light') {
                    document.documentElement.classList.add('light');
                } else {
                    document.documentElement.classList.remove('light');
                }
            },

            // Tienda
            tiendaData: null,

            // Auth
            comercioAuth: null,
            authMode: 'login',
            authForm: { nombre: '', email: '', password: '', password_confirmation: '', whatsapp: '', zona_barrio: '' },
            authError: '',
            authLoading: false,

            // Admin
            misArticulos: [],
            adminTab: 'articulos',
            informesData: null,
            showArticuloForm: false,
            importingExcel: false,
            editingArticulo: null,
            articuloForm: { 
                nombre_producto: '', 
                descripcion_articulo: '', 
                precio_ars: '', 
                categoria: '', 
                imagen_url: '', 
                imagen_file: null, 
                imagenes_file: [], 
                deletedImagenes: [],
                orden: 0 
            },
            perfilForm: { nombre: '', descripcion: '', whatsapp: '', zona_barrio: '', direccion: '', logo_url: '', logo_file: null },

            // Super Admin
            superAdminAuth: null,
            superAdminComercios: [],
            superAdminLoginData: { email: '', password: '' },
            superAdminError: '',
            articuloLoading: false,
            
            // PWA
            appSettings: { app_icon: '', app_name: 'EstaAqui' },
            deferredPrompt: null,

            // CSRF
            get csrfToken() {
                return document.querySelector('meta[name="csrf-token"]').content;
            },

            // ─── Init ─────────────────────────────────────────
            async init() {
                this.applyTheme();
                this.$watch('theme', () => this.applyTheme());
                
                // Initialize state without blocking theme application
                this.checkAuth();
                this.checkSuperAdminAuth();
                this.fetchCategorias();
                this.fetchComercios();
                this.fetchArticulos();
                this.fetchSettings();

                window.addEventListener('beforeinstallprompt', (e) => {
                    e.preventDefault();
                    this.deferredPrompt = e;
                });
                
                if ('serviceWorker' in navigator) {
                    navigator.serviceWorker.register('/sw.js');
                }
            },

            // ─── API Helpers ──────────────────────────────────
            async apiFetch(url, options = {}) {
                const defaults = {
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    credentials: 'same-origin',
                };
                if (options.body instanceof FormData) {
                    delete defaults.headers['Content-Type'];
                }
                const res = await fetch(url, { ...defaults, ...options });
                if (!res.ok) {
                    const err = await res.json().catch(() => ({}));
                    throw err;
                }
                return res.json();
            },

            // ─── Data Fetching ────────────────────────────────
            async fetchCategorias() {
                try {
                    this.categorias = await this.apiFetch('/api/categorias');
                } catch (e) { console.error('Error cargando categorías:', e); }
            },

            async fetchComercios() {
                try {
                    this.comercios = await this.apiFetch('/api/comercios');
                } catch (e) { console.error('Error cargando comercios:', e); }
            },

            async fetchArticulos() {
                this.loading = true;
                try {
                    let url = '/api/articulos?page=1';
                    if (this.selectedCategoria) url += '&categoria=' + encodeURIComponent(this.selectedCategoria);

                    const data = await this.apiFetch(url);
                    this.allArticulos = data.data;
                    this.filteredArticulos = data.data;
                    this.currentPage = data.current_page;
                    this.hasMore = data.next_page_url !== null;

                    // Build Fuse index
                    this.fuseInstance = new Fuse(this.allArticulos, {
                        keys: ['nombre_producto', 'descripcion_articulo', 'categoria'],
                        threshold: 0.4,
                        distance: 100,
                        minMatchCharLength: 2,
                    });
                } catch (e) { console.error('Error cargando artículos:', e); }
                this.loading = false;
            },

            async loadMore() {
                try {
                    let url = `/api/articulos?page=${this.currentPage + 1}`;
                    if (this.selectedCategoria) url += '&categoria=' + encodeURIComponent(this.selectedCategoria);

                    const data = await this.apiFetch(url);
                    this.allArticulos = [...this.allArticulos, ...data.data];
                    this.currentPage = data.current_page;
                    this.hasMore = data.next_page_url !== null;

                    // Rebuild Fuse
                    this.fuseInstance = new Fuse(this.allArticulos, {
                        keys: ['nombre_producto', 'descripcion_articulo', 'categoria'],
                        threshold: 0.4,
                        distance: 100,
                        minMatchCharLength: 2,
                    });

                    this.performSearch();
                } catch (e) { console.error('Error cargando más:', e); }
            },

            // ─── Search ───────────────────────────────────────
            performSearch() {
                if (!this.searchQuery.trim()) {
                    this.filteredArticulos = this.allArticulos;
                    return;
                }
                if (this.fuseInstance) {
                    this.filteredArticulos = this.fuseInstance.search(this.searchQuery).map(r => r.item);
                }
            },

            // ─── Navigation ───────────────────────────────────
            goHome() {
                this.view = 'home';
                this.tiendaData = null;
            },

            async goToTienda(slug) {
                this.view = 'tienda';
                this.loading = true;
                try {
                    this.tiendaData = await this.apiFetch(`/api/comercios/${slug}`);
                    this.trackClick('vista_comercio', this.tiendaData.comercio.id);
                } catch (e) { console.error('Error cargando tienda:', e); }
                this.loading = false;
            },

            openArticuloDetail(art) {
                this.articuloSeleccionado = art;
                this.showArticuloDetail = true;
                this.currentSlide = 0;
            },

            get currentSlides() {
                if (!this.articuloSeleccionado) return [];
                const slides = [];
                if (this.articuloSeleccionado.imagen_url) slides.push(this.articuloSeleccionado.imagen_url);
                if (this.articuloSeleccionado.imagenes) {
                    this.articuloSeleccionado.imagenes.forEach(img => slides.push(img.url));
                }
                return slides;
            },

            nextSlide() {
                this.currentSlide = (this.currentSlide + 1) % this.currentSlides.length;
            },

            prevSlide() {
                this.currentSlide = (this.currentSlide - 1 + this.currentSlides.length) % this.currentSlides.length;
            },

            async trackClick(tipo, comercioId, articuloId = null) {
                if (!comercioId) return;
                try {
                    await this.apiFetch('/api/track-click', {
                        method: 'POST',
                        body: JSON.stringify({ tipo, comercio_id: comercioId, articulo_id: articuloId })
                    });
                } catch (e) {} // Silent error
            },

            // ─── Auth ─────────────────────────────────────────
            async checkAuth() {
                try {
                    const data = await this.apiFetch('/api/comercio/me');
                    this.comercioAuth = data.comercio;
                } catch (e) { this.comercioAuth = null; }
            },

            async loginComercio() {
                this.authLoading = true;
                this.authError = '';
                try {
                    const data = await this.apiFetch('/api/comercio/login', {
                        method: 'POST',
                        body: JSON.stringify({ email: this.authForm.email, password: this.authForm.password }),
                    });
                    this.comercioAuth = data.comercio;
                    this.view = 'admin';
                    this.fetchMisArticulos();
                } catch (e) {
                    this.authError = e.errors?.email?.[0] || e.message || 'Error de autenticación';
                }
                this.authLoading = false;
            },

            async registerComercio() {
                this.authLoading = true;
                this.authError = '';
                try {
                    const data = await this.apiFetch('/api/comercio/register', {
                        method: 'POST',
                        body: JSON.stringify(this.authForm),
                    });
                    this.comercioAuth = data.comercio;
                    this.view = 'admin';
                    this.fetchMisArticulos();
                } catch (e) {
                    const errors = e.errors || {};
                    this.authError = Object.values(errors).flat().join('. ') || e.message || 'Error al registrar';
                }
                this.authLoading = false;
            },

            async logoutComercio() {
                try {
                    await this.apiFetch('/api/comercio/logout', { method: 'POST' });
                } catch (e) {}
                this.comercioAuth = null;
                this.view = 'home';
            },

            // ─── Admin CRUD ───────────────────────────────────
            async fetchMisArticulos() {
                try {
                    this.misArticulos = await this.apiFetch('/api/comercio/articulos');
                } catch (e) { console.error('Error cargando mis artículos:', e); }
            },

            async fetchInformes() {
                try {
                    this.informesData = await this.apiFetch('/api/comercio/informes');
                } catch (e) { console.error('Error cargando informes', e); }
            },

            resetArticuloForm() {
                this.articuloForm = { 
                    nombre_producto: '', 
                    descripcion_articulo: '', 
                    precio_ars: '', 
                    categoria: '', 
                    imagen_url: '', 
                    imagen_file: null, 
                    imagenes_file: [], 
                    deletedImagenes: [],
                    orden: 0 
                };
                if (this.$refs.imagenInput) this.$refs.imagenInput.value = '';
                if (this.$refs.imagenesInput) this.$refs.imagenesInput.value = '';
                if (this.$refs.imagenInputCamera) this.$refs.imagenInputCamera.value = '';
            },

            editArticulo(art) {
                this.editingArticulo = art;
                this.articuloForm = {
                    nombre_producto: art.nombre_producto,
                    descripcion_articulo: art.descripcion_articulo || '',
                    precio_ars: art.precio_ars,
                    categoria: art.categoria,
                    imagen_url: art.imagen_url || '',
                    imagen_file: null,
                    imagenes_file: [],
                    deletedImagenes: [],
                    orden: art.orden || 0,
                };
                if (this.$refs.imagenInput) this.$refs.imagenInput.value = '';
                if (this.$refs.imagenInputCamera) this.$refs.imagenInputCamera.value = '';
                this.showArticuloForm = true;
            },

            initPerfilForm() {
                if (!this.comercioAuth) return;
                this.perfilForm = {
                    nombre: this.comercioAuth.nombre,
                    descripcion: this.comercioAuth.descripcion || '',
                    whatsapp: this.comercioAuth.whatsapp,
                    zona_barrio: this.comercioAuth.zona_barrio,
                    direccion: this.comercioAuth.direccion || '',
                    logo_url: this.comercioAuth.logo_url || '',
                    logo_file: null
                };
            },

            async savePerfil() {
                this.authLoading = true;
                try {
                    const formData = new FormData();
                    formData.append('_method', 'PUT');
                    formData.append('nombre', this.perfilForm.nombre);
                    formData.append('descripcion', this.perfilForm.descripcion);
                    formData.append('whatsapp', this.perfilForm.whatsapp);
                    formData.append('zona_barrio', this.perfilForm.zona_barrio);
                    formData.append('direccion', this.perfilForm.direccion);
                    if (this.perfilForm.logo_file) {
                        formData.append('logo_file', this.perfilForm.logo_file);
                    }

                    const data = await this.apiFetch('/api/comercio/perfil', {
                        method: 'POST',
                        body: formData
                    });
                    this.comercioAuth = data.comercio;
                    alert('Perfil actualizado con éxito');
                } catch (e) {
                    alert('Error al actualizar perfil: ' + (e.message || 'Verificá los datos'));
                } finally {
                    this.authLoading = false;
                }
            },

            async saveArticulo() {
                if (this.articuloLoading) return;
                this.articuloLoading = true;
                try {
                    const formData = new FormData();
                    formData.append('nombre_producto', this.articuloForm.nombre_producto);
                    if (this.articuloForm.descripcion_articulo) formData.append('descripcion_articulo', this.articuloForm.descripcion_articulo);
                    formData.append('precio_ars', this.articuloForm.precio_ars);
                    formData.append('categoria', this.articuloForm.categoria);
                    formData.append('orden', this.articuloForm.orden || 0);
                    if (this.articuloForm.imagen_url) formData.append('imagen_url', this.articuloForm.imagen_url);
                    if (this.articuloForm.imagen_file) formData.append('imagen_file', this.articuloForm.imagen_file);
                    
                    if (this.articuloForm.imagenes_file && this.articuloForm.imagenes_file.length) {
                        for (let i = 0; i < this.articuloForm.imagenes_file.length; i++) {
                            formData.append('imagenes_file[]', this.articuloForm.imagenes_file[i]);
                        }
                    }

                    if (this.editingArticulo && this.articuloForm.deletedImagenes.length) {
                        for (let id of this.articuloForm.deletedImagenes) {
                            await this.apiFetch(`/api/comercio/articulos/${this.editingArticulo.id}/imagenes/${id}`, {
                                method: 'DELETE'
                            });
                        }
                    }

                    if (this.editingArticulo) {
                        formData.append('_method', 'PUT');
                        await this.apiFetch(`/api/comercio/articulos/${this.editingArticulo.id}`, {
                            method: 'POST',
                            body: formData,
                        });
                    } else {
                        await this.apiFetch('/api/comercio/articulos', {
                            method: 'POST',
                            body: formData,
                        });
                    }
                    this.showArticuloForm = false;
                    this.fetchMisArticulos();
                } catch (e) {
                    alert('Error al guardar: ' + (e.message || 'Verificá los datos'));
                } finally {
                    this.articuloLoading = false;
                }
            },

            async deleteArticulo(id) {
                if (!confirm('¿Eliminar este artículo?')) return;
                try {
                    await this.apiFetch(`/api/comercio/articulos/${id}`, { method: 'DELETE' });
                    this.fetchMisArticulos();
                } catch (e) { alert('Error al eliminar'); }
            },

            async importExcel(event) {
                const file = event.target.files[0];
                if (!file) return;

                const formData = new FormData();
                formData.append('excel_file', file);

                this.importingExcel = true;
                if (this.$refs.excelInput) {
                    this.$refs.excelInput.value = ''; // reset file input
                }

                try {
                    const res = await this.apiFetch('/api/comercio/articulos/importar-excel', {
                        method: 'POST',
                        body: formData
                    });
                    
                    let msg = res.message || 'Importación exitosa';
                    if (res.errors && res.errors.length > 0) {
                        msg += '\n\nErrores encontrados:\n' + res.errors.slice(0, 10).join('\n') + (res.errors.length > 10 ? '\n...y más.' : '');
                    }
                    alert(msg);
                    this.fetchMisArticulos();
                } catch (e) {
                    let errorMessage = e.message || 'Error desconocido';
                    if (e.errors && e.errors.excel_file) {
                        errorMessage = e.errors.excel_file[0];
                    }
                    alert('Error al importar: ' + errorMessage);
                }
                this.importingExcel = false;
            },

            // ─── Super Admin Functions ─────────────────────────
            goToSuperAdmin() {
                if (this.superAdminAuth) {
                    this.view = 'superadmin_panel';
                    this.fetchSuperAdminComercios();
                } else {
                    this.view = 'superadmin_login';
                }
            },

            async checkSuperAdminAuth() {
                try {
                    const data = await this.apiFetch('/api/admin/me');
                    this.superAdminAuth = data.user;
                } catch (e) { this.superAdminAuth = null; }
            },

            async loginSuperAdmin() {
                this.authLoading = true;
                this.superAdminError = '';
                try {
                    const data = await this.apiFetch('/api/admin/login', {
                        method: 'POST',
                        body: JSON.stringify(this.superAdminLoginData)
                    });
                    this.superAdminAuth = data.user;
                    this.view = 'superadmin_panel';
                    this.fetchSuperAdminComercios();
                } catch (e) {
                    this.superAdminError = e.errors?.email?.[0] || 'Credenciales incorrectas';
                }
                this.authLoading = false;
            },

            async saveSettings() {
                this.authLoading = true;
                try {
                    await this.apiFetch('/api/admin/settings', {
                        method: 'POST',
                        body: JSON.stringify({ app_name: this.appSettings.app_name })
                    });
                    alert('Configuración guardada');
                } catch (e) { alert('Error al guardar configuración'); }
                this.authLoading = false;
            },

            async logoutSuperAdmin() {
                try {
                    await this.apiFetch('/api/admin/logout', { method: 'POST' });
                } catch (e) {}
                this.superAdminAuth = null;
                this.view = 'home';
            },

            async fetchSuperAdminComercios() {
                try {
                    this.superAdminComercios = await this.apiFetch('/api/admin/comercios');
                } catch (e) { console.error('Error cargando comercios admin', e); }
            },

            async toggleSuperAdminComercio(id) {
                if(!confirm('¿Seguro quieres cambiar el estado de este comercio?')) return;
                try {
                    await this.apiFetch(`/api/admin/comercios/${id}/toggle-status`, { method: 'POST' });
                    this.fetchSuperAdminComercios();
                } catch (e) { alert('Error cambiando estado'); }
            },

            async deleteSuperAdminComercio(id) {
                if(!confirm('¡PELIGRO! ¿Estás absolutamente seguro de ELIMINAR todo este comercio? Toda su info y artículos se perderán.')) return;
                try {
                    await this.apiFetch(`/api/admin/comercios/${id}`, { method: 'DELETE' });
                    this.fetchSuperAdminComercios();
                } catch (e) { alert('Error eliminando comercio'); }
            },

            async resetSuperAdminPassword(id) {
                const newPass = prompt('Ingresa la nueva contraseña (mínimo 6 caracteres):');
                if(!newPass || newPass.length < 6) {
                    if(newPass) alert('La contraseña debe tener al menos 6 caracteres');
                    return;
                }
                try {
                    await this.apiFetch(`/api/admin/comercios/${id}/reset-password`, {
                        method: 'POST',
                        body: JSON.stringify({ password: newPass })
                    });
                    alert('Contraseña reseteada correctamente.');
                } catch (e) { alert('Error reseteando contraseña'); }
            },

            async updateSuperAdminOrden(id, orden) {
                try {
                    await this.apiFetch(`/api/admin/comercios/${id}/update-orden`, {
                        method: 'POST',
                        body: JSON.stringify({ orden: parseInt(orden) })
                    });
                } catch (e) { console.error('Error actualizando orden', e); }
            }
        };
    }
    </script>

    <style>
        [x-cloak] { display: none !important; }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    </style>
</body>
</html>
