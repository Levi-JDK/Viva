<!-- ══════ PANEL: VALIDACIONES IA ══════ -->
<section id="panel-validaciones" class="admin-panel hidden">
    <div class="mb-8 flex flex-col md:flex-row md:justify-between md:items-end gap-6">
        <div>
            <h2 class="text-3xl font-black tracking-tight text-white mb-2">Validaciones IA</h2>
            <p class="text-sm text-slate-400 font-medium tracking-wide">Revisá productos pendientes, aprobados y rechazados por el flujo de validación.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2" id="admin-validation-tabs">
            <button type="button" data-action="admin-validation:list" data-status="pending_review"
                class="admin-validation-tab px-5 py-2.5 rounded-full border border-rose-500/30 bg-rose-500/10 text-rose-300 text-xs font-bold tracking-widest uppercase transition-all">
                Pendientes
            </button>
            <button type="button" data-action="admin-validation:list" data-status="approved"
                class="admin-validation-tab px-5 py-2.5 rounded-full border border-white/10 bg-white/5 text-slate-400 text-xs font-bold tracking-widest uppercase transition-all">
                Aprobados
            </button>
            <button type="button" data-action="admin-validation:list" data-status="rejected"
                class="admin-validation-tab px-5 py-2.5 rounded-full border border-white/10 bg-white/5 text-slate-400 text-xs font-bold tracking-widest uppercase transition-all">
                Rechazados
            </button>
        </div>
    </div>

    <div class="bg-slate-900 border border-white/10 rounded-3xl p-6 relative shadow-[0_0_40px_rgba(0,0,0,0.8)] min-h-[400px]">
        <div class="absolute top-0 right-0 w-64 h-64 bg-rose-500/5 rounded-full blur-3xl pointer-events-none -mt-32 -mr-32"></div>

        <div id="admin-validation-loader" class="absolute inset-0 z-10 bg-slate-900/80 backdrop-blur-sm hidden items-center justify-center rounded-3xl">
            <i class="fas fa-circle-notch fa-spin text-4xl text-rose-400"></i>
        </div>

        <div class="admin-table-responsive overflow-x-auto relative z-10">
            <table class="w-full text-left border-collapse" id="admin-validation-table">
                <thead>
                    <tr class="border-b border-white/10 text-[10px] font-bold tracking-widest uppercase text-slate-500">
                        <th class="px-4 py-3 w-12">ID</th>
                        <th class="px-4 py-3">Nombre</th>
                        <th class="px-4 py-3">Productor</th>
                        <th class="px-4 py-3 w-24">Precio</th>
                        <th class="px-4 py-3 w-16 text-center">Stock</th>
                        <th class="px-4 py-3 w-32">Decisión IA</th>
                        <th class="px-4 py-3 w-24">Fecha</th>
                        <th class="px-4 py-3 w-44 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody id="admin-validation-tbody" class="divide-y divide-white/5 text-sm text-slate-300 font-medium">
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-slate-500 italic">Seleccioná una pestaña para cargar validaciones.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div id="admin-validation-pagination" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pt-6 mt-6 border-t border-white/5">
            <p id="admin-validation-page-info" class="text-xs font-semibold tracking-wide text-slate-500">Página 1</p>
            <div class="flex items-center gap-2">
                <button type="button" data-action="admin-validation:page" data-direction="prev"
                    class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-slate-300 text-xs font-bold transition-colors disabled:opacity-40 disabled:cursor-not-allowed" id="admin-validation-prev">
                    Anterior
                </button>
                <button type="button" data-action="admin-validation:page" data-direction="next"
                    class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-slate-300 text-xs font-bold transition-colors disabled:opacity-40 disabled:cursor-not-allowed" id="admin-validation-next">
                    Siguiente
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Modal Evidence -->
<div id="admin-validation-evidence-modal" class="fixed inset-0 z-[200] hidden items-center justify-center opacity-0 transition-opacity duration-200" style="background: rgba(0,0,0,0.85);">
    <div class="bg-slate-900 border border-white/10 rounded-3xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-y-auto mx-4 transform scale-95 transition-transform duration-200">
        <div class="sticky top-0 z-10 bg-slate-900/95 border-b border-white/5 px-6 py-5 flex items-center justify-between rounded-t-3xl">
            <div class="min-w-0 flex-1 mr-4">
                <h3 class="text-lg sm:text-xl font-black text-white truncate" id="admin-validation-modal-title">Evidence IA</h3>
                <p class="text-[10px] sm:text-[11px] text-slate-400 uppercase tracking-widest font-bold mt-0.5" id="admin-validation-modal-subtitle">Detalle técnico</p>
            </div>
            <button type="button" data-action="admin-validation:close-evidence" class="w-8 h-8 shrink-0 rounded-full bg-white/5 hover:bg-rose-500/20 text-slate-400 hover:text-rose-400 flex items-center justify-center transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="admin-validation-evidence-content" class="p-6 sm:p-8 text-sm text-slate-300 space-y-4"></div>
    </div>
</div>
