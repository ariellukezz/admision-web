<template>
    <Head title="Perfil de Postulante" />
    <AuthenticatedLayout>
        <div style="background: var(--content-bg, #f1f5f9);">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">

                <!-- ===== HERO HEADER ===== -->
                <div class="rounded-3xl overflow-hidden shadow-lg mb-4" style="background: var(--card-bg, #ffffff);">
                    <!-- Cover -->
                    <div class="relative h-28 sm:h-36"
                        style="background: linear-gradient(135deg, #1e3a8a 0%, #3730a3 40%, #6366f1 100%);">
                        <div class="absolute inset-0 opacity-20"
                            style="background-image: radial-gradient(circle at 80% 20%, white 1px, transparent 1px), radial-gradient(circle at 30% 70%, white 1px, transparent 1px); background-size: 40px 40px;">
                        </div>
                        <div class="absolute top-3 right-4 flex gap-2">
                            <button @click="abrir()"
                                class="px-3 py-1 rounded-full text-xs font-medium text-white bg-white/20 hover:bg-white/30 backdrop-blur-sm transition-colors flex items-center gap-1.5">
                                <EditOutlined /> Editar
                            </button>
                        </div>
                    </div>

                    <div class="px-5 sm:px-8 pb-5">
                        <div class="flex flex-col sm:flex-row sm:items-end gap-4 -mt-16 sm:-mt-14">
                            <!-- Foto -->
                            <div class="relative shrink-0 mx-auto sm:mx-0">
                                <div class="w-28 h-28 sm:w-32 sm:h-32 rounded-3xl border-4 border-white bg-gray-100 overflow-hidden shadow-xl">
                                    <img v-if="fotoLoaded" :src="foto" @error="fotoLoaded = false"
                                        class="w-full h-full object-cover" />
                                    <div v-else class="w-full h-full flex items-center justify-center"
                                        style="background: linear-gradient(135deg, #e0e7ff, #c7d2fe);">
                                        <UserOutlined style="font-size: 3rem; color: #818cf8;" />
                                    </div>
                                </div>
                                <div class="absolute -bottom-1.5 -right-1.5 w-7 h-7 rounded-full border-[3px] border-white flex items-center justify-center shadow-md"
                                    :style="info.sexo === 'M' ? 'background: #3b82f6;' : 'background: #ec4899;'">
                                    <span class="text-white text-[10px] font-bold">{{ info.sexo || '?' }}</span>
                                </div>
                            </div>

                            <!-- Name & info -->
                            <div class="flex-1 text-center sm:text-left sm:pb-3">
                                <h1 class="text-xl sm:text-2xl font-bold tracking-tight" style="color: #1e293b;">
                                    {{ info.nombres }} {{ info.primer_apellido }} {{ info.segundo_apellido }}
                                </h1>
                                <div class="flex flex-wrap justify-center sm:justify-start items-center gap-x-3 gap-y-1 mt-1.5 text-sm" style="color: #64748b;">
                                    <span class="flex items-center gap-1"><IdcardOutlined /> {{ info.nro_doc }}</span>
                                    <span class="hidden sm:inline" style="color: #cbd5e1;">·</span>
                                    <span class="flex items-center gap-1"><MailOutlined /> {{ info.email }}</span>
                                    <span class="hidden sm:inline" style="color: #cbd5e1;">·</span>
                                    <span class="flex items-center gap-1"><PhoneOutlined /> {{ info.celular }}</span>
                                </div>
                                <div class="flex flex-wrap justify-center sm:justify-start items-center gap-x-3 gap-y-1 mt-1 text-sm" style="color: #94a3b8;">
                                    <span v-if="info.fec_nacimiento" class="flex items-center gap-1"><CalendarOutlined /> {{ formatDate(info.fec_nacimiento) }}</span>
                                    <span v-if="info.departamento" class="flex items-center gap-1"><EnvironmentOutlined /> {{ info.departamento }}, {{ info.distrito }}</span>
                                    <span v-if="info.cod_orcid" class="flex items-center gap-1"><IdcardOutlined /> ORCID: {{ info.cod_orcid }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Cuenta vinculada bar -->
                        <div class="mt-3 flex items-center gap-2.5 px-4 py-1.5 rounded-2xl text-sm"
                            :class="usuarioVinculado ? '' : ''"
                            :style="usuarioVinculado
                                ? 'background: #f0fdf4; border: 1px solid #bbf7d0;'
                                : 'background: #fef2f2; border: 1px solid #fecaca;'">
                            <CheckCircleFilled v-if="usuarioVinculado" style="color: #22c55e; font-size: 1rem;" />
                            <CloseCircleFilled v-else style="color: #ef4444; font-size: 1rem;" />
                            <template v-if="usuarioVinculado">
                                <span style="color: #166534;">
                                    <strong>{{ usuarioVinculado.name }}</strong> · DNI: {{ usuarioVinculado.dni }} · {{ usuarioVinculado.email }}
                                </span>
                            </template>
                            <template v-else>
                                <span style="color: #991b1b;">Sin cuenta vinculada</span>
                                <a-button size="small" type="primary" @click="mostrarVincular = true">Vincular</a-button>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- ===== LAYOUT: SIDEBAR + CONTENT ===== -->
                <div class="flex gap-4">

                    <!-- ===== SIDEBAR NAV ===== -->
                    <div class="hidden lg:flex flex-col w-60 shrink-0">
                        <div class="rounded-2xl shadow-sm sticky top-4 overflow-hidden" style="background: var(--card-bg, #ffffff); border: 1px solid var(--card-border);">
                            <button v-for="tab in navTabs" :key="tab.key"
                                @click="tabActivo = tab.key"
                                class="w-full flex items-center gap-3 px-4 py-3 text-left text-sm transition-colors border-l-[3px]"
                                :style="tabActivo === tab.key
                                    ? 'border-color: #4f46e5; background: #eef2ff; color: #4338ca; font-weight: 600;'
                                    : 'border-color: transparent; color: #64748b;'">
                                <component :is="tab.icon" :style="tabActivo === tab.key ? 'color: #4f46e5;' : 'color: #94a3b8;'" />
                                {{ tab.label }}
                                <span class="ml-auto text-xs px-1.5 py-0.5 rounded-full"
                                    :style="tabActivo === tab.key ? 'background: #c7d2fe; color: #4338ca;' : 'background: var(--content-bg, #f1f5f9); color: #94a3b8;'"
                                    v-if="tab.count !== undefined && tab.count > 0">
                                    {{ tab.count }}
                                </span>
                            </button>
                        </div>

                        <!-- Quick stats in sidebar -->
                        <div class="rounded-2xl shadow-sm mt-3 p-4" style="background: var(--card-bg, #ffffff); border: 1px solid var(--card-border);">
                            <div class="text-xs font-semibold uppercase tracking-wider mb-3" style="color: #94a3b8;">Resumen</div>
                            <div class="space-y-2.5">
                                <div class="flex items-center justify-between text-sm">
                                    <span style="color: #64748b;">Inscripciones</span>
                                    <span class="font-bold" style="color: #2563eb;">{{ inscripciones }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span style="color: #64748b;">Preinscripciones</span>
                                    <span class="font-bold" style="color: #0891b2;">{{ preinscripciones }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span style="color: #64748b;">Ingresos biom.</span>
                                    <span class="font-bold" style="color: #7c3aed;">{{ control_biometrico }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span style="color: #64748b;">Carreras previas</span>
                                    <span class="font-bold" style="color: #16a34a;">{{ carrerasTerminadas }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span style="color: #64748b;">Documentos</span>
                                    <span class="font-bold" style="color: #ea580c;">{{ documentos.length }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span style="color: #64748b;">Descargas</span>
                                    <span class="font-bold" style="color: #475569;">{{ downloads }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ===== MOBILE NAV (horizontal scroll) ===== -->
                    <div class="lg:hidden -mx-4 px-4 mb-4 overflow-x-auto">
                        <div class="flex gap-2 pb-2" style="min-width: max-content;">
                            <button v-for="tab in navTabs" :key="tab.key"
                                @click="tabActivo = tab.key"
                                class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-medium whitespace-nowrap transition-colors"
                                :style="tabActivo === tab.key
                                    ? 'background: #4f46e5; color: white;'
                                    : 'background: var(--card-bg, #ffffff); color: #64748b; border: 1px solid var(--card-border);'">
                                <component :is="tab.icon" />
                                {{ tab.label }}
                                <span v-if="tab.count !== undefined && tab.count > 0"
                                    class="px-1.5 py-0.5 rounded-full text-[10px]"
                                    :style="tabActivo === tab.key ? 'background: rgba(255,255,255,0.25);' : 'background: var(--content-bg, #f1f5f9);'">
                                    {{ tab.count }}
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- ===== CONTENT AREA ===== -->
                    <div class="flex-1 min-w-0">

                        <!-- ===== PUNTAJES ===== -->
                        <div v-show="tabActivo === 'puntajes'"
                            class="rounded-2xl shadow-sm p-4 sm:p-5" style="background: var(--card-bg, #ffffff); border: 1px solid var(--card-border);">
                            <div class="flex items-center gap-2 mb-4">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: #fef3c7;">
                                    <TrophyOutlined style="color: #d97706; font-size: 1.1rem;" />
                                </div>
                                <h2 class="text-lg font-bold" style="color: #1e293b;">Puntajes y Resultados</h2>
                            </div>

                            <div v-if="!puntajes.length" class="py-12 text-center">
                                <TrophyOutlined style="font-size: 2.5rem; color: #e2e8f0;" />
                                <p class="mt-3 text-sm" style="color: #94a3b8;">Sin resultados de examen registrados</p>
                            </div>

                            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div v-for="p in puntajes" :key="p.id"
                                    class="rounded-2xl p-5 transition-shadow hover:shadow-md"
                                    :style="p.apto
                                        ? 'background: linear-gradient(135deg, #f0fdf4, #dcfce7); border: 1px solid #bbf7d0;'
                                        : 'background: linear-gradient(135deg, #f8fafc, #f1f5f9); border: 1px solid var(--card-border);'">
                                    <div class="flex items-start justify-between mb-3">
                                        <div>
                                            <div class="text-sm font-semibold" style="color: #334155;">{{ p.modalidad || 'Sin modalidad' }}</div>
                                            <div class="text-xs mt-0.5" style="color: #94a3b8;">
                                                Proceso #{{ p.id_proceso }}
                                                <span v-if="p.fecha"> · {{ formatDate(p.fecha) }}</span>
                                            </div>
                                        </div>
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold"
                                            :style="p.apto ? 'background: #22c55e; color: white;' : 'background: #94a3b8; color: white;'">
                                            {{ p.apto ? 'APTO' : 'NO APTO' }}
                                        </span>
                                    </div>
                                    <div class="flex items-end gap-4">
                                        <div>
                                            <div class="text-3xl font-extrabold" :style="p.apto ? 'color: #15803d;' : 'color: #475569;'">
                                                {{ p.puntaje ?? '—' }}
                                            </div>
                                            <div class="text-xs" style="color: #94a3b8;">Puntaje</div>
                                        </div>
                                        <div v-if="p.puesto">
                                            <div class="text-lg font-bold" style="color: #475569;">{{ p.puesto }}</div>
                                            <div class="text-xs" style="color: #94a3b8;">Puesto mod.</div>
                                        </div>
                                        <div v-if="p.puesto_general">
                                            <div class="text-lg font-bold" style="color: #475569;">{{ p.puesto_general }}</div>
                                            <div class="text-xs" style="color: #94a3b8;">Puesto gen.</div>
                                        </div>
                                        <div v-if="p.aula" class="ml-auto">
                                            <div class="text-sm font-medium" style="color: #475569;">Aula {{ p.aula }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ===== PASOS ===== -->
                        <div v-show="tabActivo === 'pasos'"
                            class="rounded-2xl shadow-sm p-4 sm:p-5" style="background: var(--card-bg, #ffffff); border: 1px solid var(--card-border);">
                            <div class="flex items-center gap-2 mb-4">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: #dbeafe;">
                                    <CheckSquareOutlined style="color: #2563eb; font-size: 1.1rem;" />
                                </div>
                                <h2 class="text-lg font-bold" style="color: #1e293b;">Pasos Realizados</h2>
                            </div>

                            <div v-if="!pasos.length" class="py-12 text-center">
                                <CheckSquareOutlined style="font-size: 2.5rem; color: #e2e8f0;" />
                                <p class="mt-3 text-sm" style="color: #94a3b8;">Sin pasos registrados</p>
                            </div>

                            <div v-else>
                                <!-- Timeline -->
                                <div class="relative pl-8">
                                    <div class="absolute left-[14px] top-0 bottom-0 w-0.5" style="background: #e2e8f0;"></div>
                                    <div v-for="(paso, idx) in pasos" :key="paso.id" class="relative pb-4 last:pb-0">
                                        <div class="absolute left-[-30px] w-7 h-7 rounded-full flex items-center justify-center border-2"
                                            :style="paso.avance
                                                ? 'background: #22c55e; border-color: #16a34a;'
                                                : 'background: var(--card-bg, #ffffff); border-color: var(--card-border);'">
                                            <CheckOutlined v-if="paso.avance" style="color: white; font-size: 0.7rem;" />
                                            <span v-else class="text-xs" style="color: #cbd5e1;">{{ idx + 1 }}</span>
                                        </div>
                                        <div class="flex items-center justify-between p-3 rounded-xl"
                                            :style="paso.avance ? 'background: #f0fdf4;' : 'background: var(--content-bg, #f1f5f9);'">
                                            <span class="text-sm font-medium" :style="paso.avance ? 'color: #166534;' : 'color: #64748b;'">
                                                {{ paso.nombre }}
                                            </span>
                                            <span class="text-xs px-2 py-0.5 rounded-full"
                                                :style="paso.avance ? 'background: #dcfce7; color: #16a34a;' : 'background: var(--content-bg, #f1f5f9); color: #94a3b8;'">
                                                {{ paso.avance ? 'Completado' : 'Pendiente' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Avance por proceso -->
                                <div v-if="avance.length" class="mt-6 pt-5" style="border-top: 1px solid var(--card-border);">
                                    <div class="text-sm font-semibold mb-3" style="color: #475569;">Avance por proceso</div>
                                    <div v-for="av in avance" :key="av.id" class="mb-3">
                                        <div class="flex justify-between text-xs mb-1">
                                            <span style="color: #64748b;">Proceso #{{ av.id_proceso }}</span>
                                            <span class="font-semibold" :style="av.avance >= 100 ? 'color: #16a34a;' : 'color: #6366f1;'">{{ av.avance || 0 }}%</span>
                                        </div>
                                        <div class="h-2 rounded-full overflow-hidden" style="background: var(--content-bg, #f1f5f9);">
                                            <div class="h-full rounded-full transition-all duration-500"
                                                :style="`width: ${av.avance || 0}%; background: ${av.avance >= 100 ? '#22c55e' : 'linear-gradient(90deg, #6366f1, #818cf8)'};`">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ===== DOCUMENTOS ===== -->
                        <div v-show="tabActivo === 'documentos'"
                            class="rounded-2xl shadow-sm p-4 sm:p-5" style="background: var(--card-bg, #ffffff); border: 1px solid var(--card-border);">
                            <div class="flex items-center gap-2 mb-4">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: #ffedd5;">
                                    <FileTextOutlined style="color: #ea580c; font-size: 1.1rem;" />
                                </div>
                                <h2 class="text-lg font-bold" style="color: #1e293b;">Documentos</h2>
                            </div>

                            <div v-if="!documentos.length" class="py-12 text-center">
                                <FileTextOutlined style="font-size: 2.5rem; color: #e2e8f0;" />
                                <p class="mt-3 text-sm" style="color: #94a3b8;">Sin documentos registrados</p>
                            </div>

                            <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div v-for="doc in documentos" :key="doc.id"
                                    class="rounded-xl p-4 transition-shadow hover:shadow-md"
                                    style="background: var(--content-bg, #f1f5f9); border: 1px solid var(--card-border);">
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0"
                                            :style="doc.verificado === 1 || doc.verificado === 2
                                                ? 'background: #dcfce7;'
                                                : 'background: #fef3c7;'">
                                            <CheckCircleFilled v-if="doc.verificado === 1 || doc.verificado === 2" style="color: #22c55e;" />
                                            <ClockCircleOutlined v-else style="color: #f59e0b;" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-medium truncate" style="color: #334155;">{{ doc.nombre }}</div>
                                            <div class="text-xs mt-0.5" style="color: #94a3b8;">{{ doc.tipo_documento?.nombre || 'Sin tipo' }}</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between mt-3">
                                        <span class="text-xs px-2 py-0.5 rounded-full"
                                            :style="`background: ${docEstadoBg(doc.estado)}; color: ${docEstadoFg(doc.estado)};`">
                                            {{ docEstadoTexto(doc.estado) }}
                                        </span>
                                        <span class="text-xs" style="color: #cbd5e1;">{{ doc.revisado_at ? formatDate(doc.revisado_at) : '' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ===== PAGOS ===== -->
                        <div v-show="tabActivo === 'pagos'"
                            class="rounded-2xl shadow-sm p-4 sm:p-5" style="background: var(--card-bg, #ffffff); border: 1px solid var(--card-border);">
                            <div class="flex items-center gap-2 mb-4">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: #d1fae5;">
                                    <DollarOutlined style="color: #059669; font-size: 1.1rem;" />
                                </div>
                                <h2 class="text-lg font-bold" style="color: #1e293b;">Pagos y Comprobantes</h2>
                            </div>

                            <div v-if="!comprobantes.length" class="py-12 text-center">
                                <DollarOutlined style="font-size: 2.5rem; color: #e2e8f0;" />
                                <p class="mt-3 text-sm" style="color: #94a3b8;">Sin comprobantes registrados</p>
                            </div>

                            <div v-else class="space-y-2">
                                <div v-for="comp in comprobantes" :key="comp.id"
                                    class="flex items-center gap-3 p-4 rounded-xl transition-colors hover:bg-gray-50"
                                    style="border: 1px solid var(--card-border);">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0"
                                        :style="comp.verificado ? 'background: #dcfce7;' : 'background: #fef9c3;'">
                                        <CheckCircleFilled v-if="comp.verificado" style="color: #22c55e;" />
                                        <ClockCircleOutlined v-else style="color: #eab308;" />
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-sm font-medium" style="color: #334155;">Operación: {{ comp.nro_operacion }}</div>
                                        <div class="text-xs mt-0.5" style="color: #94a3b8;">
                                            {{ comp.fecha ? formatDate(comp.fecha) : '' }}
                                            <span v-if="comp.monto"> · S/ {{ comp.monto }}</span>
                                        </div>
                                    </div>
                                    <span class="text-xs px-2.5 py-1 rounded-full font-medium"
                                        :style="comp.verificado ? 'background: #dcfce7; color: #16a34a;' : 'background: #fef9c3; color: #a16207;'">
                                        {{ comp.verificado ? 'Verificado' : 'Pendiente' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- ===== INSCRIPCIONES ===== -->
                        <div v-show="tabActivo === 'inscripciones'"
                            class="rounded-2xl shadow-sm p-4 sm:p-5" style="background: var(--card-bg, #ffffff); border: 1px solid var(--card-border);">
                            <div class="flex items-center gap-2 mb-4">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: #e0e7ff;">
                                    <FormOutlined style="color: #4f46e5; font-size: 1.1rem;" />
                                </div>
                                <h2 class="text-lg font-bold" style="color: #1e293b;">Inscripciones</h2>
                            </div>

                            <div v-if="!inscripcionesData.length && !preinscripcionesData.length" class="py-12 text-center">
                                <FormOutlined style="font-size: 2.5rem; color: #e2e8f0;" />
                                <p class="mt-3 text-sm" style="color: #94a3b8;">Sin inscripciones registradas</p>
                            </div>

                            <div v-else class="space-y-5">
                                <div v-if="inscripcionesData.length">
                                    <div class="text-xs font-semibold uppercase tracking-wider mb-2" style="color: #94a3b8;">Inscripciones</div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div v-for="ins in inscripcionesData" :key="ins.id"
                                            class="rounded-xl p-4 transition-shadow hover:shadow-md"
                                            style="background: var(--content-bg, #f1f5f9); border: 1px solid var(--card-border);">
                                            <div class="flex items-start justify-between gap-2">
                                                <div class="flex-1 min-w-0">
                                                    <div class="text-sm font-semibold" style="color: #334155;">
                                                        {{ ins.programa?.nombre || 'Sin programa' }}
                                                    </div>
                                                    <div class="text-xs mt-1" style="color: #94a3b8;">
                                                        Cod: {{ ins.codigo }} · {{ ins.modalidad?.nombre || '—' }}
                                                    </div>
                                                </div>
                                                <span class="text-xs px-2 py-0.5 rounded-full shrink-0"
                                                    :style="ins.estado ? 'background: #dcfce7; color: #16a34a;' : 'background: var(--content-bg, #f1f5f9); color: #64748b;'">
                                                    {{ ins.estado ? 'Activo' : 'Inactivo' }}
                                                </span>
                                            </div>
                                            <div class="text-xs mt-2" style="color: #cbd5e1;">{{ formatDate(ins.created_at) }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="preinscripcionesData.length">
                                    <div class="text-xs font-semibold uppercase tracking-wider mb-2" style="color: #94a3b8;">Preinscripciones</div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div v-for="pre in preinscripcionesData" :key="pre.id"
                                            class="rounded-xl p-4 transition-shadow hover:shadow-md"
                                            style="background: var(--content-bg, #f1f5f9); border: 1px solid var(--card-border);">
                                            <div class="flex items-start justify-between gap-2">
                                                <div class="flex-1 min-w-0">
                                                    <div class="text-sm font-semibold" style="color: #334155;">
                                                        {{ pre.programa?.nombre || 'Sin programa' }}
                                                    </div>
                                                    <div class="text-xs mt-1" style="color: #94a3b8;">
                                                        {{ pre.modalidad?.nombre || '—' }}
                                                    </div>
                                                </div>
                                                <span class="text-xs px-2 py-0.5 rounded-full shrink-0"
                                                    :style="pre.estado ? 'background: #dcfce7; color: #16a34a;' : 'background: var(--content-bg, #f1f5f9); color: #64748b;'">
                                                    {{ pre.estado ? 'Activo' : 'Inactivo' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ===== REVISIONES ===== -->
                        <div v-show="tabActivo === 'revisiones'"
                            class="rounded-2xl shadow-sm p-4 sm:p-5" style="background: var(--card-bg, #ffffff); border: 1px solid var(--card-border);">
                            <div class="flex items-center gap-2 mb-4">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: #ede9fe;">
                                    <AuditOutlined style="color: #7c3aed; font-size: 1.1rem;" />
                                </div>
                                <h2 class="text-lg font-bold" style="color: #1e293b;">Revisiones</h2>
                            </div>

                            <div v-if="!revisiones.length" class="py-12 text-center">
                                <AuditOutlined style="font-size: 2.5rem; color: #e2e8f0;" />
                                <p class="mt-3 text-sm" style="color: #94a3b8;">Sin solicitudes de revisión</p>
                            </div>

                            <div v-else class="space-y-2">
                                <div v-for="rev in revisiones" :key="rev.id"
                                    class="flex flex-wrap items-center gap-3 p-4 rounded-xl"
                                    style="border: 1px solid var(--card-border);">
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-medium" style="color: #334155;">
                                            Solicitud #{{ rev.id }} · Vez {{ rev.veces }}
                                        </div>
                                        <div class="text-xs mt-0.5" style="color: #94a3b8;">
                                            <span v-if="rev.solicitada_at">Solicitada: {{ formatDate(rev.solicitada_at) }}</span>
                                            <span v-if="rev.finalizada_at"> · Finalizada: {{ formatDate(rev.finalizada_at) }}</span>
                                        </div>
                                    </div>
                                    <span class="text-xs px-2.5 py-1 rounded-full font-medium"
                                        :style="`background: ${revEstadoBg(rev.estado)}; color: ${revEstadoFg(rev.estado)};`">
                                        {{ rev.estado }}
                                    </span>
                                    <span v-if="rev.apto !== null" class="text-xs px-2.5 py-1 rounded-full font-medium"
                                        :style="rev.apto ? 'background: #dcfce7; color: #16a34a;' : 'background: #fecaca; color: #dc2626;'">
                                        {{ rev.apto ? 'APTO' : 'NO APTO' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- ===== ACTIVIDAD ===== -->
                        <div v-show="tabActivo === 'actividad'"
                            class="rounded-2xl shadow-sm p-4 sm:p-5" style="background: var(--card-bg, #ffffff); border: 1px solid var(--card-border);">
                            <div class="flex items-center gap-2 mb-4">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: #fce7f3;">
                                    <HistoryOutlined style="color: #db2777; font-size: 1.1rem;" />
                                </div>
                                <h2 class="text-lg font-bold" style="color: #1e293b;">Actividad Reciente</h2>
                            </div>

                            <div v-if="!dataSource.length" class="py-12 text-center">
                                <HistoryOutlined style="font-size: 2.5rem; color: #e2e8f0;" />
                                <p class="mt-3 text-sm" style="color: #94a3b8;">Sin actividad registrada</p>
                            </div>

                            <div v-else class="space-y-2">
                                <div v-for="(act, idx) in dataSource" :key="idx"
                                    class="flex items-center gap-3 p-3 rounded-xl"
                                    style="background: var(--content-bg, #f1f5f9);">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                                        :style="accionBg(act.acciones)">
                                        <component :is="accionIcon(act.acciones)" :style="accionIconStyle(act.acciones)" />
                                    </div>
                                    <div class="flex-1 min-w-0 text-sm">
                                        <span class="font-medium" style="color: #334155;">{{ act.usuario || 'Sistema' }}</span>
                                        <span class="mx-1.5" style="color: #94a3b8;">{{ act.acciones }}</span>
                                        <span style="color: #64748b;">N° {{ act.registro }}</span>
                                        <span style="color: #94a3b8;"> en {{ act.tabla }}</span>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <div class="text-xs" style="color: #64748b;">{{ act.direccion }}</div>
                                        <div class="text-xs" style="color: #cbd5e1;">{{ formatDateTime(act.fecha) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ===== CARRERAS PREVIAS ===== -->
                        <div v-show="tabActivo === 'carreras'"
                            class="rounded-2xl shadow-sm p-4 sm:p-5" style="background: var(--card-bg, #ffffff); border: 1px solid var(--card-border);">
                            <div class="flex items-center gap-2 mb-4">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: #ccfbf1;">
                                    <BookOutlined style="color: #0d9488; font-size: 1.1rem;" />
                                </div>
                                <h2 class="text-lg font-bold" style="color: #1e293b;">Carreras Previas</h2>
                            </div>

                            <div v-if="!carrerasPrevias.length" class="py-12 text-center">
                                <BookOutlined style="font-size: 2.5rem; color: #e2e8f0;" />
                                <p class="mt-3 text-sm" style="color: #94a3b8;">Sin carreras previas registradas</p>
                            </div>

                            <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div v-for="cp in carrerasPrevias" :key="cp.id"
                                    class="rounded-xl p-4 transition-shadow hover:shadow-md"
                                    style="background: var(--content-bg, #f1f5f9); border: 1px solid var(--card-border);">
                                    <div class="text-sm font-semibold" style="color: #334155;">{{ cp.nombre }}</div>
                                    <div class="text-xs mt-1" style="color: #94a3b8;">
                                        Código: {{ cp.cod_car }}
                                        <span v-if="cp.fecha"> · {{ formatDate(cp.fecha) }}</span>
                                    </div>
                                    <span v-if="cp.condicion" class="inline-block mt-2 text-xs px-2 py-0.5 rounded-full"
                                        style="background: #e0e7ff; color: #4338ca;">
                                        {{ cp.condicion }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- ===== PROCESOS ===== -->
                        <div v-show="tabActivo === 'procesos'"
                            class="rounded-2xl shadow-sm p-4 sm:p-5" style="background: var(--card-bg, #ffffff); border: 1px solid var(--card-border);">
                            <div class="flex items-center gap-2 mb-4">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: var(--content-bg, #f1f5f9);">
                                    <AppstoreOutlined style="color: #475569; font-size: 1.1rem;" />
                                </div>
                                <h2 class="text-lg font-bold" style="color: #1e293b;">Procesos</h2>
                            </div>

                            <div v-if="!pro.length" class="py-12 text-center">
                                <AppstoreOutlined style="font-size: 2.5rem; color: #e2e8f0;" />
                                <p class="mt-3 text-sm" style="color: #94a3b8;">Sin procesos</p>
                            </div>

                            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                <button v-for="item in pro" :key="item.id_proceso"
                                    @click="seleccionado = seleccionado == item.id_proceso ? null : item.id_proceso"
                                    class="text-left p-4 rounded-2xl transition-all"
                                    :style="seleccionado == item.id_proceso
                                        ? 'background: #eef2ff; border: 2px solid #6366f1;'
                                        : 'background: var(--content-bg, #f1f5f9); border: 1px solid var(--card-border);'">
                                    <div class="flex items-center justify-between">
                                        <div class="font-medium text-sm" style="color: #334155;">{{ item.proceso }}</div>
                                        <RightOutlined :style="seleccionado == item.id_proceso ? 'color: #6366f1;' : 'color: #cbd5e1;'" />
                                    </div>
                                    <div class="text-xs mt-1" style="color: #94a3b8;">Código: {{ item.codigo }}</div>
                                </button>
                            </div>
                            <div v-if="seleccionado" class="mt-4">
                                <Grafico />
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>

        <!-- Modal Editar -->
        <a-modal v-model:visible="open" title="Editar Datos" :footer="null" width="600px">
            <a-empty description="Formulario de edición en desarrollo" />
        </a-modal>

        <!-- Modal Vincular Usuario -->
        <a-modal v-model:visible="mostrarVincular" title="Vincular Postulante con Usuario"
            @ok="vincularUsuario" :confirmLoading="vinculando" okText="Vincular" cancelText="Cancelar">
            <div style="margin-bottom: 12px;">
                <p style="font-size: .8125rem; color: #595959;">
                    Para vincular, busca un usuario por DNI. Se asignará el DNI del postulante a la cuenta del usuario.
                </p>
            </div>
            <div style="display: flex; gap: 8px; margin-bottom: 16px;">
                <a-input v-model:value="dniBuscar" placeholder="Ingresar DNI del usuario"
                    style="flex: 1" @pressEnter="buscarUsuario" />
                <a-button type="primary" @click="buscarUsuario" :loading="buscandoUsuario">Buscar</a-button>
            </div>
            <div v-if="usuarioEncontrado" style="padding: 12px; border: 1px solid #d9d9d9; border-radius: 8px; background: #f6ffed;">
                <div style="font-weight: 600;">{{ usuarioEncontrado.nombre }}</div>
                <div style="font-size: .8125rem; color: #595959;">
                    DNI: {{ usuarioEncontrado.dni }} | Email: {{ usuarioEncontrado.email }}
                </div>
            </div>
            <div v-if="errorBusqueda" style="padding: 12px; border: 1px solid #ffa39e; border-radius: 8px; background: #fff2f0;">
                <span style="color: #cf1322; font-size: .8125rem;">{{ errorBusqueda }}</span>
            </div>
        </a-modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    UserOutlined, MailOutlined, PhoneOutlined, CalendarOutlined,
    EnvironmentOutlined, IdcardOutlined, EditOutlined,
    CheckCircleFilled, CloseCircleFilled, ClockCircleOutlined,
    CheckOutlined, TrophyOutlined, CheckSquareOutlined, FileTextOutlined,
    DollarOutlined, FormOutlined, AuditOutlined, HistoryOutlined,
    BookOutlined, AppstoreOutlined, RightOutlined,
    PlusOutlined, MinusOutlined, DeleteOutlined,
} from '@ant-design/icons-vue';
import { notification } from 'ant-design-vue';
import axios from 'axios';
import dayjs from 'dayjs';
import Grafico from './components/grafico.vue';

const open = ref(false);
const mostrarVincular = ref(false);
const dniBuscar = ref('');
const usuarioEncontrado = ref(null);
const errorBusqueda = ref('');
const buscandoUsuario = ref(false);
const vinculando = ref(false);
const tabActivo = ref('inscripciones');
const fotoLoaded = ref(true);
const seleccionado = ref(null);

const abrir = () => { open.value = true; };

const props = defineProps({
    foto: String,
    info: Object,
    infoColegio: Object,
    preinscripciones: Number,
    inscripciones: Number,
    control_biometrico: Number,
    pro: Array,
    usuarioVinculado: Object,
    carrerasTerminadas: Number,
    downloads: Number,
    actividades: Array,
    puntajes: Array,
    pasos: Array,
    avance: Array,
    documentos: Array,
    comprobantes: Array,
    revisiones: Array,
    inscripcionesData: Array,
    preinscripcionesData: Array,
    carrerasPrevias: Array,
});

const navTabs = computed(() => [
    { key: 'puntajes', label: 'Puntajes', icon: TrophyOutlined, count: props.puntajes?.length },
    { key: 'pasos', label: 'Pasos', icon: CheckSquareOutlined, count: props.pasos?.length },
    { key: 'documentos', label: 'Documentos', icon: FileTextOutlined, count: props.documentos?.length },
    { key: 'pagos', label: 'Pagos', icon: DollarOutlined, count: props.comprobantes?.length },
    { key: 'inscripciones', label: 'Inscripciones', icon: FormOutlined, count: (props.inscripcionesData?.length || 0) + (props.preinscripcionesData?.length || 0) },
    { key: 'revisiones', label: 'Revisiones', icon: AuditOutlined, count: props.revisiones?.length },
    { key: 'actividad', label: 'Actividad', icon: HistoryOutlined, count: props.actividades?.length },
    { key: 'carreras', label: 'Carreras', icon: BookOutlined, count: props.carrerasPrevias?.length },
    { key: 'procesos', label: 'Procesos', icon: AppstoreOutlined, count: props.pro?.length },
]);

const formatDate = (date) => {
    if (!date) return '';
    return dayjs(date).format('DD/MM/YYYY');
};

const formatDateTime = (date) => {
    if (!date) return '';
    return dayjs(date).format('DD/MM/YYYY HH:mm');
};

const docEstadoTexto = (estado) => {
    const map = { 0: 'Pendiente', 1: 'Subido', 2: 'Aprobado', 3: 'Rechazado' };
    return map[estado] ?? 'Desconocido';
};
const docEstadoBg = (estado) => {
    const map = { 0: '#f1f5f9', 1: '#dbeafe', 2: '#dcfce7', 3: '#fecaca' };
    return map[estado] ?? '#f1f5f9';
};
const docEstadoFg = (estado) => {
    const map = { 0: '#64748b', 1: '#2563eb', 2: '#16a34a', 3: '#dc2626' };
    return map[estado] ?? '#64748b';
};

const revEstadoBg = (estado) => {
    const map = { 'pendiente': '#fef3c7', 'en_proceso': '#dbeafe', 'completada': '#dcfce7', 'cancelada': '#fecaca' };
    return map[estado] ?? '#f1f5f9';
};
const revEstadoFg = (estado) => {
    const map = { 'pendiente': '#a16207', 'en_proceso': '#2563eb', 'completada': '#16a34a', 'cancelada': '#dc2626' };
    return map[estado] ?? '#64748b';
};

const accionBg = (accion) => {
    if (accion === 'insertó') return '#dcfce7';
    if (accion === 'actualizó') return '#dbeafe';
    if (accion === 'eliminó') return '#fecaca';
    return '#f1f5f9';
};
const accionIcon = (accion) => {
    if (accion === 'insertó') return PlusOutlined;
    if (accion === 'actualizó') return EditOutlined;
    if (accion === 'eliminó') return DeleteOutlined;
    return HistoryOutlined;
};
const accionIconStyle = (accion) => {
    if (accion === 'insertó') return 'color: #16a34a; font-size: 0.85rem;';
    if (accion === 'actualizó') return 'color: #2563eb; font-size: 0.85rem;';
    if (accion === 'eliminó') return 'color: #dc2626; font-size: 0.85rem;';
    return 'color: #94a3b8; font-size: 0.85rem;';
};

const buscarUsuario = async () => {
    if (!dniBuscar.value) return;
    buscandoUsuario.value = true;
    errorBusqueda.value = '';
    usuarioEncontrado.value = null;
    try {
        const res = await axios.post('/admin/buscar-usuario-dni', { dni: dniBuscar.value });
        if (res.data.estado) {
            usuarioEncontrado.value = res.data.datos;
        } else {
            errorBusqueda.value = res.data.mensaje;
        }
    } catch (e) {
        errorBusqueda.value = 'Error al buscar usuario';
    } finally {
        buscandoUsuario.value = false;
    }
};

const vincularUsuario = async () => {
    if (!usuarioEncontrado.value || !props.info?.id_postulante) return;
    vinculando.value = true;
    try {
        const res = await axios.post('/admin/vincular-postulante-usuario', {
            postulante_id: props.info.id_postulante,
            user_id: usuarioEncontrado.value.id,
        });
        if (res.data.estado) {
            notification.success({ message: res.data.mensaje });
            mostrarVincular.value = false;
            window.location.reload();
        } else {
            notification.error({ message: res.data.mensaje });
        }
    } catch (e) {
        notification.error({ message: 'Error al vincular' });
    } finally {
        vinculando.value = false;
    }
};

const dataSource = ref(props.actividades || []);
</script>

<style>
.theme-dark .ant-table,
.theme-hybrid .ant-table {
    background: transparent !important;
    color: var(--card-text) !important;
}
.theme-dark .ant-table-thead > tr > th,
.theme-hybrid .ant-table-thead > tr > th {
    background: var(--table-header-bg) !important;
    color: var(--card-text) !important;
    border-bottom: 1px solid var(--card-border) !important;
}
.theme-dark .ant-table-tbody > tr > td,
.theme-hybrid .ant-table-tbody > tr > td {
    color: var(--card-text) !important;
    border-bottom: 1px solid var(--card-border) !important;
    background: var(--card-bg) !important;
}
.theme-dark .ant-table-tbody > tr:hover > td,
.theme-hybrid .ant-table-tbody > tr:hover > td {
    background: var(--hover-bg) !important;
}
.theme-dark .ant-table-tbody > tr:nth-child(even) > td,
.theme-hybrid .ant-table-tbody > tr:nth-child(even) > td {
    background: var(--row-even) !important;
}
</style>
