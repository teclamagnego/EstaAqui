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
            adminTab: 'articulos',
            informesData: null,
            showArticuloForm: false,
            importingExcel: false,
            editingArticulo: null,
            articuloForm: { nombre_producto: '', descripcion_articulo: '', precio_ars: '', categoria: '', imagen_url: '', imagen_file: null },

            // Super Admin
            superAdminAuth: null,
            superAdminComercios: [],
            superAdminLoginData: { email: '', password: '' },
            superAdminError: '',

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
                    this.checkSuperAdminAuth(),
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
                    this.trackClick('vista_comercio', this.tiendaData.comercio.id);
                } catch (e) { console.error('Error cargando tienda:', e); }
                this.loading = false;
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
            }
        };
    }
    </script>
