<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="EstaAqui — Encontrá productos de comercios locales cerca tuyo. Comprá directo por WhatsApp.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>EstaAqui — Comercios locales, cerca tuyo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fuse.js@7.0.0"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface-950 text-white min-h-screen font-sans antialiased" x-data="app()" x-cloak>

    {{-- ═══════════════════════════════════════════════════════════════════
         NAVBAR
    ═══════════════════════════════════════════════════════════════════ --}}
    <nav class="sticky top-0 z-50 backdrop-blur-xl bg-surface-950/80 border-b border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3 flex items-center justify-between gap-4">
            {{-- Logo --}}
            <a href="#" @click.prevent="goHome()" class="flex items-center gap-2 shrink-0 group">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center text-lg font-black shadow-lg shadow-primary-500/25 group-hover:shadow-primary-500/40 transition-shadow">
                    📍
                </div>
                <span class="text-xl font-extrabold bg-gradient-to-r from-primary-400 to-accent-400 bg-clip-text text-transparent hidden sm:block">EstaAqui</span>
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

            {{-- Navigation Buttons --}}
            <div class="flex items-center gap-2 shrink-0">
                <template x-if="!comercioAuth">
                    <button @click="view = 'login'" class="text-xs sm:text-sm px-3 sm:px-4 py-2 rounded-xl bg-white/5 border border-white/10 hover:bg-white/10 transition-all font-medium text-white/70 hover:text-white">
                        🏪 Mi Comercio
                    </button>
                </template>
                <template x-if="comercioAuth">
                    <div class="flex items-center gap-2">
                        <button @click="view = 'admin'" class="text-xs sm:text-sm px-3 sm:px-4 py-2 rounded-xl bg-primary-500/20 border border-primary-500/30 hover:bg-primary-500/30 transition-all font-medium text-primary-300">
                            📋 Panel
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
                <h1 class="text-3xl sm:text-5xl font-black tracking-tight leading-tight">
                    Encontrá <span class="bg-gradient-to-r from-primary-400 to-accent-400 bg-clip-text text-transparent">productos locales</span><br>
                    cerca tuyo
                </h1>
                <p class="mt-3 text-base sm:text-lg text-white/50 max-w-lg mx-auto">Catálogo unificado de comercios de tu ciudad. Consultá stock y comprá directo por WhatsApp.</p>
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
                        <div class="w-28 sm:w-32 p-3 bg-white/5 rounded-2xl border border-white/8 hover:border-primary-500/30 hover:bg-white/8 transition-all text-center">
                            <div class="w-12 h-12 mx-auto rounded-xl bg-gradient-to-br from-primary-500/30 to-accent-500/30 flex items-center justify-center text-2xl mb-2">
                                🏬
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
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4" x-show="!loading">
                <template x-for="art in filteredArticulos" :key="art.id">
                    <div class="group bg-white/[0.03] rounded-2xl border border-white/8 overflow-hidden hover:border-primary-500/30 hover:bg-white/[0.06] transition-all duration-300 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-primary-500/5">
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
                                    target="_blank"
                                    rel="noopener"
                                    class="flex items-center gap-1.5 px-3 py-1.5 bg-whatsapp/90 hover:bg-whatsapp rounded-lg text-white text-xs font-semibold transition-all hover:shadow-lg hover:shadow-whatsapp/25 active:scale-95"
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
            <div x-show="tiendaData" class="bg-gradient-to-r from-primary-900/30 to-accent-600/10 rounded-2xl border border-white/8 p-6 sm:p-8 mb-8">
                <div class="flex items-start gap-4 sm:gap-6">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center text-3xl sm:text-4xl shrink-0 shadow-xl shadow-primary-500/20">
                        🏬
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
                            target="_blank"
                            class="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-whatsapp/90 hover:bg-whatsapp rounded-xl text-sm font-semibold text-white transition-all hover:shadow-lg hover:shadow-whatsapp/25"
                        >
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.09.534 4.058 1.474 5.771L.058 23.7l6.064-1.393A11.94 11.94 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.785c-1.89 0-3.64-.525-5.146-1.435l-.368-.22-3.81.875.908-3.716-.24-.383A9.77 9.77 0 012.215 12c0-5.397 4.388-9.785 9.785-9.785S21.785 6.603 21.785 12 17.397 21.785 12 21.785z"/></svg>
                            Contactar comercio
                        </a>
                    </div>
                </div>
            </div>

            {{-- Products --}}
            <h2 class="text-lg font-bold text-white/80 mb-4" x-text="'Productos (' + (tiendaData?.articulos?.length || 0) + ')'"></h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4 pb-16">
                <template x-for="art in (tiendaData?.articulos || [])" :key="art.id">
                    <div class="group bg-white/[0.03] rounded-2xl border border-white/8 overflow-hidden hover:border-primary-500/30 hover:bg-white/[0.06] transition-all duration-300 hover:-translate-y-0.5">
                        <div class="aspect-square overflow-hidden bg-white/5">
                            <img :src="art.imagen_url" :alt="art.nombre_producto" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                        </div>
                        <div class="p-3 sm:p-4">
                            <span class="text-[10px] text-primary-400 font-medium" x-text="art.categoria"></span>
                            <h3 class="text-sm font-semibold text-white/90 leading-snug line-clamp-2 mt-1 mb-1" x-text="art.nombre_producto"></h3>
                            <p class="text-[11px] text-white/35 line-clamp-2 mb-3" x-text="art.descripcion_articulo"></p>
                            <div class="flex items-end justify-between gap-2">
                                <span class="text-lg font-bold text-white" x-text="'$' + Number(art.precio_ars).toLocaleString('es-AR')"></span>
                                <a :href="art.whatsapp_link" target="_blank" rel="noopener" class="flex items-center gap-1.5 px-3 py-1.5 bg-whatsapp/90 hover:bg-whatsapp rounded-lg text-white text-xs font-semibold transition-all hover:shadow-lg hover:shadow-whatsapp/25 active:scale-95">
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

            <div class="bg-white/[0.03] rounded-2xl border border-white/8 p-6 sm:p-8">
                <div class="text-center mb-6">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center text-2xl mb-3 shadow-lg shadow-primary-500/25">🏪</div>
                    <h2 class="text-xl font-bold" x-text="authMode === 'login' ? 'Ingresá a tu comercio' : 'Registrá tu comercio'"></h2>
                    <p class="text-sm text-white/40 mt-1">Gestioná tus productos y recibí consultas</p>
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
                    <button type="submit" :disabled="authLoading" class="w-full py-3 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-400 hover:to-primary-500 rounded-xl text-sm font-bold transition-all disabled:opacity-50 shadow-lg shadow-primary-500/25">
                        <span x-show="!authLoading">Ingresar</span>
                        <span x-show="authLoading">Ingresando...</span>
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
                <button @click="showArticuloForm = true; editingArticulo = null; resetArticuloForm()" class="px-4 py-2 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-400 hover:to-primary-500 rounded-xl text-sm font-bold transition-all shadow-lg shadow-primary-500/25 relative z-20 cursor-pointer pointer-events-auto">
                    + Nuevo Artículo
                </button>
            </div>

            {{-- Artículo Form Modal --}}
            <div x-show="showArticuloForm" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.self="showArticuloForm = false">
                <div class="bg-surface-900 rounded-2xl border border-white/10 p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto">
                    <h3 class="text-lg font-bold mb-4" x-text="editingArticulo ? 'Editar Artículo' : 'Nuevo Artículo'"></h3>
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
                            <label class="block text-xs font-medium text-white/50 mb-1.5">Foto del producto (opcional)</label>
                            <input type="file" accept="image/*" @change="articuloForm.imagen_file = $event.target.files[0]" x-ref="imagenInput" class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-primary-500/50 focus:ring-1 focus:ring-primary-500/25">
                            <template x-if="editingArticulo && editingArticulo.imagen_url">
                                <img :src="editingArticulo.imagen_url" class="mt-2 h-16 rounded-xl object-cover">
                            </template>
                        </div>
                        <div class="flex gap-3">
                            <button type="button" @click="showArticuloForm = false" class="flex-1 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm font-medium text-white/60 hover:bg-white/10 transition">Cancelar</button>
                            <button type="submit" class="flex-1 py-2.5 bg-gradient-to-r from-primary-500 to-primary-600 rounded-xl text-sm font-bold transition-all shadow-lg shadow-primary-500/25">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- My Articles List --}}
            <div class="space-y-3">
                <template x-for="art in misArticulos" :key="art.id">
                    <div class="flex items-center gap-4 bg-white/[0.03] rounded-2xl border border-white/8 p-4 hover:bg-white/[0.05] transition">
                        <img :src="art.imagen_url || ''" :alt="art.nombre_producto" class="w-16 h-16 rounded-xl object-cover bg-white/5 shrink-0">
                        <div class="flex-1 min-w-0">
                            <h4 class="font-semibold text-sm text-white/90 truncate" x-text="art.nombre_producto"></h4>
                            <p class="text-xs text-white/40 mt-0.5" x-text="art.categoria"></p>
                            <p class="text-sm font-bold text-white mt-1" x-text="`$${Number(art.precio_ars).toLocaleString('es-AR')}`"></p>
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
        </div>
    </main>

    {{-- ═══════════════════════════════════════════════════════════════════
         FOOTER
    ═══════════════════════════════════════════════════════════════════ --}}
    <footer class="border-t border-white/5 mt-auto" x-show="view === 'home'">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8 text-center">
            <div class="flex items-center justify-center gap-2 mb-2">
                <span class="text-lg">📍</span>
                <span class="text-sm font-bold bg-gradient-to-r from-primary-400 to-accent-400 bg-clip-text text-transparent">EstaAqui</span>
            </div>
            <p class="text-xs text-white/25">Catálogo unificado de comercios locales · Comprá cerca, comprá local</p>
        </div>
    </footer>

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
            showArticuloForm: false,
            editingArticulo: null,
            articuloForm: { nombre_producto: '', descripcion_articulo: '', precio_ars: '', categoria: '', imagen_url: '', imagen_file: null },

            // CSRF
            get csrfToken() {
                return document.querySelector('meta[name="csrf-token"]').content;
            },

            // ─── Init ─────────────────────────────────────────
            async init() {
                await Promise.all([
                    this.fetchCategorias(),
                    this.fetchComercios(),
                    this.fetchArticulos(),
                    this.checkAuth(),
                ]);
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
                } catch (e) { console.error('Error cargando tienda:', e); }
                this.loading = false;
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

            resetArticuloForm() {
                this.articuloForm = { nombre_producto: '', descripcion_articulo: '', precio_ars: '', categoria: '', imagen_url: '', imagen_file: null };
                if (this.$refs.imagenInput) this.$refs.imagenInput.value = '';
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
                };
                if (this.$refs.imagenInput) this.$refs.imagenInput.value = '';
                this.showArticuloForm = true;
            },

            async saveArticulo() {
                try {
                    const formData = new FormData();
                    formData.append('nombre_producto', this.articuloForm.nombre_producto);
                    if (this.articuloForm.descripcion_articulo) formData.append('descripcion_articulo', this.articuloForm.descripcion_articulo);
                    formData.append('precio_ars', this.articuloForm.precio_ars);
                    formData.append('categoria', this.articuloForm.categoria);
                    if (this.articuloForm.imagen_url) formData.append('imagen_url', this.articuloForm.imagen_url);
                    if (this.articuloForm.imagen_file) formData.append('imagen_file', this.articuloForm.imagen_file);

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
                }
            },

            async deleteArticulo(id) {
                if (!confirm('¿Eliminar este artículo?')) return;
                try {
                    await this.apiFetch(`/api/comercio/articulos/${id}`, { method: 'DELETE' });
                    this.fetchMisArticulos();
                } catch (e) { alert('Error al eliminar'); }
            },
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
