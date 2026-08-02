<template>
  <a-modal v-model:open="visible" title="Credenciales" :footer="null" width="95%" style="top: 10px;" :body-style="{ maxHeight: 'calc(100vh - 120px)', overflow: 'auto' }">
    <div class="cred-filter-bar">
      <a-input v-model:value="credSearch" placeholder="DNI, nombre, código..." style="width: 260px;" allow-clear />
      <a-select v-model:value="credFilterCargo" placeholder="Cargo" style="width: 160px;" allow-clear>
        <a-select-option v-for="c in credCargosList" :key="c" :value="c">{{ c }}</a-select-option>
      </a-select>
      <a-select v-model:value="credFilterOrigen" placeholder="Origen" style="width: 140px;" allow-clear>
        <a-select-option value="sorteo">SORTEO</a-select-option>
        <a-select-option value="manual">DESIGNACIÓN</a-select-option>
      </a-select>
      <a-button size="small" @click="credClearFilters">✕ Limpiar</a-button>
      <div style="margin-left: auto; display: flex; gap: 8px; align-items: center;">
        <a-radio-group v-model:value="credOrientacion" size="small" button-style="solid">
          <a-radio-button value="horizontal">Horizontal</a-radio-button>
          <a-radio-button value="vertical">Vertical</a-radio-button>
        </a-radio-group>
        <a-tag color="gold">{{ credFiltered.length }} / {{ credencialesData.length }}</a-tag>
        <a-button size="small" type="primary" @click="credImprimir"><PrinterOutlined /> Imprimir / PDF</a-button>
        <a-button size="small" @click="credImprimirZebra"><PrinterOutlined /> Zebra ZC300</a-button>
        <a-button size="small" @click="credImprimirZebraV2"><PrinterOutlined /> Zebra v2</a-button>
      </div>
    </div>

    <a-spin :spinning="credencialesLoading">
      <!-- Horizontal -->
      <div v-if="credFiltered.length && credOrientacion === 'horizontal'" class="cred-grid">
        <div v-for="p in credFiltered" :key="p.id" class="dni-card">
          <div class="dni-guilloche"></div>
          <div class="dni-header">
            <div class="dni-header-logo"><img v-if="credLogoTiny" :src="credLogoTiny" /></div>
            <div class="dni-header-text">
              <div class="dni-header-main">UNIVERSIDAD NACIONAL DEL ALTIPLANO</div>
              <div class="dni-header-sub">{{ credProceso?.nombre || 'ADMISIÓN' }}</div>
            </div>
            <div class="dni-header-logo-right"><img v-if="credLogoDad" :src="credLogoDad" /></div>
          </div>
          <div class="dni-gold-band"></div>
          <div class="dni-body">
            <div class="dni-photo-col">
              <div class="dni-photo-frame">
                <img v-if="p.foto" :src="p.foto" @error="$event.target.style.display='none'; $event.target.nextElementSibling?.style.removeProperty('display')" />
                <div v-if="!p.foto" class="dni-photo-empty">SIN FOTO</div>
              </div>
              <div class="dni-side-num">
                <div class="dni-side-num-label">DNI</div>
                <div class="dni-side-num-val">{{ p.dni }}</div>
              </div>
            </div>
            <div class="dni-data-col">
              <div class="dni-field"><div class="dni-label">Apellidos</div><div class="dni-value-name">{{ p.paterno }} {{ p.materno }}</div></div>
              <div class="dni-field"><div class="dni-label">Nombres</div><div class="dni-value-name">{{ p.nombres }}</div></div>
              <div class="dni-sep"></div>
              <div class="dni-field"><div class="dni-label">Cargo</div><div class="dni-value-cargo">{{ p.cargo }}</div></div>
              <div class="dni-field"><div class="dni-label">Tipo asignación</div><div class="dni-value" :style="p.es_manual ? 'color: #7c3aed !important;' : ''">{{ p.es_manual ? 'DESIGNACIÓN' : 'SORTEO' }}</div></div>
            </div>
          </div>
          <div class="dni-footer">
            <span class="dni-foot-process">ADMISIÓN UNA PUNO</span>
            <span class="dni-foot-code">{{ credProceso?.anio || '2026-II' }}</span>
          </div>
        </div>
      </div>

      <!-- Vertical -->
      <div v-else-if="credFiltered.length && credOrientacion === 'vertical'" class="cred-grid-v">
        <div v-for="p in credFiltered" :key="p.id" class="dni-card-v">
          <div class="dni-guilloche"></div>
          <div class="dni-header">
            <div class="dni-header-logo"><img v-if="credLogoTiny" :src="credLogoTiny" /></div>
            <div class="dni-header-text">
              <div class="dni-header-main">UNIVERSIDAD NACIONAL DEL ALTIPLANO</div>
              <div class="dni-header-sub">{{ credProceso?.nombre || 'ADMISIÓN' }}</div>
            </div>
            <div class="dni-header-logo-right"><img v-if="credLogoDad" :src="credLogoDad" /></div>
          </div>
          <div class="dni-gold-band"></div>
          <div class="dni-body-v">
            <div class="dni-photo-col-v">
              <div class="dni-photo-frame">
                <img v-if="p.foto" :src="p.foto" @error="$event.target.style.display='none'; $event.target.nextElementSibling?.style.removeProperty('display')" />
                <div v-if="!p.foto" class="dni-photo-empty">SIN FOTO</div>
              </div>
              <div class="dni-side-num">
                <div class="dni-side-num-label">DNI</div>
                <div class="dni-side-num-val">{{ p.dni }}</div>
              </div>
            </div>
            <div class="dni-data-col-v">
              <div class="dni-field"><div class="dni-label">Apellidos</div><div class="dni-value-name">{{ p.paterno }} {{ p.materno }}</div></div>
              <div class="dni-field"><div class="dni-label">Nombres</div><div class="dni-value-name">{{ p.nombres }}</div></div>
              <div class="dni-sep"></div>
              <div class="dni-field"><div class="dni-label">Cargo</div><div class="dni-value-cargo">{{ p.cargo }}</div></div>
              <div class="dni-field"><div class="dni-label">Tipo asignación</div><div class="dni-value" :style="p.es_manual ? 'color: #7c3aed !important;' : ''">{{ p.es_manual ? 'DESIGNACIÓN' : 'SORTEO' }}</div></div>
            </div>
          </div>
          <div class="dni-footer">
            <span class="dni-foot-process">ADMISIÓN UNA PUNO</span>
            <span class="dni-foot-code">{{ credProceso?.anio || '2026-II' }}</span>
          </div>
        </div>
      </div>
      <a-empty v-else description="Sin resultados" style="padding: 60px 0;" />
    </a-spin>
  </a-modal>
</template>

<script setup>
import { ref, computed } from 'vue'
import { PrinterOutlined } from '@ant-design/icons-vue'
import { notification } from 'ant-design-vue'
import axios from 'axios'

const props = defineProps({
  sorteoId: { type: Number, default: null },
  filtroCargo: { type: Number, default: null },
  filtroOrigen: { type: String, default: null },
})

const visible = ref(false)
const credencialesData = ref([])
const credencialesLoading = ref(false)
const credSearch = ref('')
const credFilterCargo = ref(undefined)
const credFilterOrigen = ref(undefined)
const credOrientacion = ref('horizontal')
const credProceso = ref(null)
const credLogoTiny = ref('')
const credLogoDad = ref('')

const open = async () => {
  if (!props.sorteoId) return
  credencialesLoading.value = true
  visible.value = true
  credSearch.value = ''
  credFilterCargo.value = undefined
  credFilterOrigen.value = undefined
  try {
    const res = await axios.post('sorteo/credenciales-data', {
      id_sorteo: props.sorteoId,
      id_cargo: props.filtroCargo || null,
      filtro_origen: props.filtroOrigen || null,
    })
    credencialesData.value = res.data.seleccionados
    credProceso.value = res.data.proceso
    credLogoTiny.value = res.data.logo_tiny
    credLogoDad.value = res.data.logo_dad
  } catch {
    notification.error({ message: 'Error', description: 'No se pudieron cargar las credenciales', placement: 'topRight' })
  } finally {
    credencialesLoading.value = false
  }
}

defineExpose({ open })

const credCargosList = computed(() => [...new Set(credencialesData.value.map(p => p.cargo).filter(Boolean))].sort())

const credFiltered = computed(() => {
  return credencialesData.value.filter(p => {
    const s = credSearch.value.toLowerCase().trim()
    const matchSearch = !s || `${p.dni} ${p.paterno} ${p.materno} ${p.nombres} ${p.codigo_trabajador}`.toLowerCase().includes(s)
    const matchCargo = !credFilterCargo.value || p.cargo === credFilterCargo.value
    const matchOrigen = !credFilterOrigen.value || (credFilterOrigen.value === 'manual' ? p.es_manual : !p.es_manual)
    return matchSearch && matchCargo && matchOrigen
  })
})

const credClearFilters = () => {
  credSearch.value = ''
  credFilterCargo.value = undefined
  credFilterOrigen.value = undefined
}

const credImprimir = () => {
  const w = window.open('', '_blank')
  if (!w) return

  const isVertical = credOrientacion.value === 'vertical'

  const cardHtml = (p) => {
    const header = `
      <div class="dni-header">
        <div class="dni-header-logo">${credLogoTiny.value ? `<img src="${credLogoTiny.value}" />` : ''}</div>
        <div class="dni-header-text">
          <div class="dni-header-main">UNIVERSIDAD NACIONAL DEL ALTIPLANO</div>
          <div class="dni-header-sub">${credProceso.value?.nombre || 'DIRECCIÓN DE ADMISIÓN'}</div>
        </div>
        <div class="dni-header-logo-right">${credLogoDad.value ? `<img src="${credLogoDad.value}" />` : ''}</div>
      </div>`

    const goldBand = `<div class="dni-gold-band"></div>`

    const photoBlock = `
      <div class="dni-photo-frame">
        ${p.foto ? `<img src="${p.foto}" />` : `<div class="dni-photo-empty">SIN FOTO</div>`}
      </div>
      <div class="dni-side-num">
        <div class="dni-side-num-label">DNI</div>
        <div class="dni-side-num-val">${p.dni || '—'}</div>
      </div>`

    const dataBlock = `
      <div class="dni-field"><div class="dni-label">Apellidos</div><div class="dni-value-name">${p.paterno || ''} ${p.materno || ''}</div></div>
      <div class="dni-field"><div class="dni-label">Nombres</div><div class="dni-value-name">${p.nombres || '—'}</div></div>
      <div class="dni-sep"></div>
      <div class="dni-field"><div class="dni-label">Cargo Asignado</div><div class="dni-value-cargo">${p.cargo || '—'}</div></div>
      <div class="dni-field"><div class="dni-label">Tipo asignación</div><div class="dni-value" style="${p.es_manual ? 'color: #7c3aed !important; font-weight: 800;' : ''}">${p.es_manual ? 'DESIGNACIÓN' : 'SORTEO'}</div></div>`

    const footer = `
      <div class="dni-footer">
        <span class="dni-foot-process">ADMISIÓN UNA PUNO</span>
        <span class="dni-foot-code">${credProceso.value?.anio || '2026-II'}</span>
      </div>`

    if (isVertical) {
      return `
      <div class="dni-card">
        <div class="dni-guilloche"></div>
        ${header}
        ${goldBand}
        <div class="dni-body-v">
          <div class="dni-photo-col-v">${photoBlock}</div>
          <div class="dni-data-col-v">${dataBlock}</div>
        </div>
        ${footer}
      </div>`
    }

    return `
      <div class="dni-card">
        <div class="dni-guilloche"></div>
        ${header}
        ${goldBand}
        <div class="dni-body">
          <div class="dni-photo-col">${photoBlock}</div>
          <div class="dni-data-col">${dataBlock}</div>
        </div>
        ${footer}
      </div>`
  }

  const items = credFiltered.value.map(cardHtml).join('')

  const baseCss = `
    * { margin: 0; padding: 0; box-sizing: border-box; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important; }
    html, body { background: #fff !important; }
    body { font-family: Helvetica, Arial, sans-serif; }
    .dni-guilloche { position: absolute; top: 0; left: 0; right: 0; bottom: 0; z-index: 0; pointer-events: none; background-image: url('${window.location.origin}/imagenes/guilloche.svg'); background-repeat: repeat; opacity: 0.85; }
    .dni-header { display: flex; align-items: center; padding: 0 2.5mm; background: #10243f !important; position: relative; z-index: 1; }
    .dni-header-logo, .dni-header-logo-right { width: 5mm; height: 5mm; flex-shrink: 0; }
    .dni-header-logo img, .dni-header-logo-right img { width: 5mm; height: 5mm; display: block; }
    .dni-header-text { flex: 1; text-align: center; }
    .dni-header-main { font-size: 10px; font-weight: 800; color: #fff !important; text-transform: uppercase; letter-spacing: 0.3px; line-height: 1.1; }
    .dni-header-sub { font-size: 9px; font-weight: 500; color: #d7b85b !important; text-transform: uppercase; margin-top: 0.5mm; }
    .dni-gold-band { height: 1mm; background: #c9a227 !important; position: relative; z-index: 1; }
    .dni-photo-frame { border-radius: 2px; overflow: hidden; border: 0.5pt solid #10243f; box-shadow: 0 1px 3px rgba(0,0,0,0.15); }
    .dni-photo-frame img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .dni-photo-empty { display: flex; align-items: center; justify-content: center; background: #e5e7eb !important; font-size: 6px; font-weight: 700; color: #6b7280 !important; border-radius: 2px; }
    .dni-side-num { text-align: center; }
    .dni-side-num-label { font-size: 4.5px; font-weight: 700; color: #6b7280 !important; text-transform: uppercase; }
    .dni-side-num-val { font-size: 11px; font-weight: 900; color: #10243f !important; font-family: 'Courier New', monospace; letter-spacing: 0.4px; line-height: 1; margin-top: 0.3mm; }
    .dni-field { display: flex; flex-direction: column; margin-bottom: 1.2mm; }
    .dni-field:last-child { margin-bottom: 0; }
    .dni-label { font-size: 5px; font-weight: 700; color: #6b7280 !important; text-transform: uppercase; letter-spacing: 0.3px; }
    .dni-value { font-size: 7.5px; font-weight: 600; color: #10243f !important; line-height: 1.2; margin-top: 0.3mm; }
    .dni-value-name { font-size: 10px; font-weight: 800; color: #10243f !important; text-transform: uppercase; line-height: 1.15; margin-top: 0.3mm; }
    .dni-value-cargo { font-size: 12px; font-weight: 700; color: #c9a227 !important; text-transform: uppercase; line-height: 1.1; margin-top: 0.3mm; }
    .dni-sep { height: 0.3mm; background: #c9a227 !important; margin: 1mm 0; }
    .dni-footer { display: flex; align-items: center; justify-content: space-between; padding: 0 2.5mm; background: #10243f !important; position: relative; z-index: 1; }
    .dni-foot-process { font-size: 10px; color: #d7b85b !important; font-weight: 600; text-transform: uppercase; }
    .dni-foot-code { font-size: 10px; color: #fff !important; font-weight: 700; font-family: 'Courier New', monospace; }`

  const layoutCss = isVertical ? `
    @page { size: A4 portrait; margin: 5mm; }
    .cred-grid { display: grid; grid-template-columns: repeat(3, 54mm); gap: 2mm; justify-content: center; }
    .dni-card { width: 54mm; height: 85.6mm; border-radius: 3mm; overflow: hidden; background: #fff !important; border: 0.4pt solid #b7c0cc; page-break-inside: avoid; display: flex; flex-direction: column; position: relative; }
    .dni-guilloche { background-size: 216px 340px; }
    .dni-header { height: 11mm; }
    .dni-header-main { font-size: 6.5px !important; line-height: 1.2; }
    .dni-header-sub { font-size: 12.5px !important; }
    .dni-body-v { flex: 1; display: flex; flex-direction: column; align-items: center; padding: 3mm 3mm 1mm; position: relative; z-index: 1; background: rgba(240,244,250,0.35) !important; }
    .dni-photo-col-v { display: flex; flex-direction: column; align-items: center; gap: 1.5mm; margin-bottom: 2.5mm; }
    .dni-photo-frame { width: 20mm; height: 26mm; }
    .dni-photo-empty { width: 20mm; height: 26mm; }
    .dni-data-col-v { width: 100%; }
    .dni-footer { height: 9mm; }`
  : `
    @page { size: A4 portrait; margin: 5mm; }
    .cred-grid { display: grid; grid-template-columns: repeat(2, 85.6mm); gap: 2mm; justify-content: center; }
    .dni-card { width: 85.6mm; height: 54mm; border-radius: 3mm; overflow: hidden; background: #fff !important; border: 0.4pt solid #b7c0cc; page-break-inside: avoid; display: flex; flex-direction: column; position: relative; }
    .dni-guilloche { background-size: 340px 216px; }
    .dni-header { height: 9mm; }
    .dni-body { flex: 1; display: flex; position: relative; z-index: 1; background: rgba(240,244,250,0.35) !important; }
    .dni-photo-col { width: 22mm; padding: 2mm 0; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 1.2mm; }
    .dni-photo-frame { width: 17mm; height: 24mm; }
    .dni-photo-empty { width: 17mm; height: 24mm; }
    .dni-data-col { flex: 1; padding: 2mm 2.5mm; display: flex; flex-direction: column; justify-content: center; }
    .dni-footer { height: 9mm; }`

  w.document.write(`<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Credenciales</title>
<style>
  ${baseCss}
  ${layoutCss}
</style></head><body>
<div class="cred-grid">${items}</div>
</body></html>`)
  w.document.close()
  w.focus()
  setTimeout(() => { w.print() }, 800)
}

const credImprimirZebra = () => {
  const w = window.open('', '_blank')
  if (!w) return

  const isVertical = credOrientacion.value === 'vertical'

  const cardHtml = (p) => {
    const header = `
      <div class="dni-header">
        <div class="dni-header-logo">${credLogoTiny.value ? `<img src="${credLogoTiny.value}" />` : ''}</div>
        <div class="dni-header-text">
          <div class="dni-header-main">UNIVERSIDAD NACIONAL DEL ALTIPLANO</div>
          <div class="dni-header-sub">${credProceso.value?.nombre || 'DIRECCIÓN DE ADMISIÓN'}</div>
        </div>
        <div class="dni-header-logo-right">${credLogoDad.value ? `<img src="${credLogoDad.value}" />` : ''}</div>
      </div>`

    const goldBand = `<div class="dni-gold-band"></div>`

    const photoBlock = `
      <div class="dni-photo-frame">
        ${p.foto ? `<img src="${p.foto}" />` : `<div class="dni-photo-empty">SIN FOTO</div>`}
      </div>
      <div class="dni-side-num">
        <div class="dni-side-num-label">DNI</div>
        <div class="dni-side-num-val">${p.dni || '—'}</div>
      </div>`

    const dataBlock = `
      <div class="dni-field"><div class="dni-label">Apellidos</div><div class="dni-value-name">${p.paterno || ''} ${p.materno || ''}</div></div>
      <div class="dni-field"><div class="dni-label">Nombres</div><div class="dni-value-name">${p.nombres || '—'}</div></div>
      <div class="dni-sep"></div>
      <div class="dni-field"><div class="dni-label">Cargo Asignado</div><div class="dni-value-cargo">${p.cargo || '—'}</div></div>
      <div class="dni-field"><div class="dni-label">Tipo asignación</div><div class="dni-value" style="${p.es_manual ? 'color: #7c3aed !important; font-weight: 800;' : ''}">${p.es_manual ? 'DESIGNACIÓN' : 'SORTEO'}</div></div>`

    const footer = `
      <div class="dni-footer">
        <span class="dni-foot-process">ADMISIÓN UNA PUNO</span>
        <span class="dni-foot-code">${credProceso.value?.anio || '2026-II'}</span>
      </div>`

    if (isVertical) {
      return `
      <div class="dni-card">
        <div class="dni-guilloche"></div>
        ${header}
        ${goldBand}
        <div class="dni-body-v">
          <div class="dni-photo-col-v">${photoBlock}</div>
          <div class="dni-data-col-v">${dataBlock}</div>
        </div>
        ${footer}
      </div>`
    }

    return `
      <div class="dni-card">
        <div class="dni-guilloche"></div>
        ${header}
        ${goldBand}
        <div class="dni-body">
          <div class="dni-photo-col">${photoBlock}</div>
          <div class="dni-data-col">${dataBlock}</div>
        </div>
        ${footer}
      </div>`
  }

  const code39 = { '0':'NNNWWNWNN','1':'WNNWNNNNW','2':'NNWWNNNNW','3':'WNWNNNNWN','4':'NNNWWNNNW','5':'WNNWWNNNN','6':'NNWWWNNNN','7':'NNNWNNWNW','8':'WNNWNNWNN','9':'NNWWNNWNN','*':'NWNNWNWNN' }

  const cardBackHtml = (p) => {
    const dniStr = '*' + (p.dni || '00000000') + '*'
    let bars = ''
    for (let i = 0; i < dniStr.length; i++) {
      const pattern = code39[dniStr[i]] || code39['0']
      for (let j = 0; j < 9; j++) {
        const isBar = j % 2 === 0
        const isWide = pattern[j] === 'W'
        if (isBar) {
          bars += `<span class="bar${isWide ? ' bar-w' : ' bar-n'}"></span>`
        } else {
          bars += `<span class="space${isWide ? ' space-w' : ' space-n'}"></span>`
        }
      }
      bars += '<span class="space space-n"></span>'
    }

    const backHeader = `
      <div class="dni-header">
        <div class="dni-header-logo">${credLogoTiny.value ? `<img src="${credLogoTiny.value}" />` : ''}</div>
        <div class="dni-header-text">
          <div class="dni-header-main">UNIVERSIDAD NACIONAL DEL ALTIPLANO</div>
          <div class="dni-header-sub">DIRECCIÓN DE ADMISIÓN</div>
        </div>
        <div class="dni-header-logo-right">${credLogoDad.value ? `<img src="${credLogoDad.value}" />` : ''}</div>
      </div>`

    const backFooter = `
      <div class="dni-footer">
        <span class="dni-foot-process">ADMISIÓN UNA PUNO</span>
        <span class="dni-foot-code">${credProceso.value?.anio || '2026-II'}</span>
      </div>`

    return `
      <div class="dni-card dni-card-back">
        <div class="dni-guilloche"></div>
        ${backHeader}
        <div class="dni-gold-band"></div>
        <div class="dni-back-body">
          <div class="dni-back-title">CREDENCIAL OFICIAL</div>
          <div class="dni-back-instr">Esta credencial es personal e intransferible. Es obligatorio portarla y exhibirla durante el proceso de admisión. En caso de pérdida, comunicarse inmediatamente con la Dirección de Admisión.</div>
          <div class="dni-back-barcode">
            <div class="barcode">${bars}</div>
            <div class="barcode-text">${p.dni || '—'}</div>
          </div>
          <div class="dni-back-firma">
            <div class="firma-line"></div>
            <div class="firma-label">FIRMA DEL TITULAR</div>
          </div>
        </div>
        ${backFooter}
      </div>`
  }

  const items = credFiltered.value.map(p => cardHtml(p) + cardBackHtml(p)).join('')

  const baseCss = `
    * { margin: 0; padding: 0; box-sizing: border-box; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important; }
    html, body { background: #fff !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    body { font-family: Helvetica, Arial, sans-serif; }
    .dni-card { overflow: hidden; background: #fff !important; display: flex; flex-direction: column; position: relative; page-break-after: always; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .dni-card:last-child { page-break-after: auto; }
    .dni-card-back { display: flex; flex-direction: column; }
    .dni-back-body { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: flex-start; padding: 1.5mm 3mm; position: relative; z-index: 1; background: rgba(240,244,250,0.35) !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .dni-back-title { font-size: 2.2mm; font-weight: 800; color: #10243f !important; text-transform: uppercase; letter-spacing: 0.5px; text-align: center; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .dni-back-instr { font-size: 1.6mm; font-weight: 600; color: #10243f !important; text-align: center; line-height: 1.4; margin-top: 1mm; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .dni-back-barcode { display: flex; flex-direction: column; align-items: center; margin-top: 2mm; }
    .barcode { display: flex; align-items: flex-end; height: 5mm; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .barcode .bar { display: inline-block; height: 100%; background: #10243f !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .barcode .bar-w { width: 0.5mm; }
    .barcode .bar-n { width: 0.17mm; }
    .barcode .space { display: inline-block; }
    .barcode .space-w { width: 0.4mm; }
    .barcode .space-n { width: 0.12mm; }
    .barcode-text { font-family: 'Courier New', monospace; font-size: 1.5mm; font-weight: 700; color: #10243f !important; text-align: center; letter-spacing: 1px; margin-top: 0.5mm; }
    .dni-back-firma { display: flex; flex-direction: column; align-items: center; margin-top: 4mm; }
    .firma-line { width: 30mm; height: 0; border-top: 0.3mm solid #10243f; }
    .firma-label { font-size: 1.2mm; font-weight: 700; color: #6b7280 !important; text-transform: uppercase; margin-top: 0.5mm; letter-spacing: 0.3px; }
    .dni-guilloche { position: absolute; top: 0; left: 0; right: 0; bottom: 0; z-index: 0; pointer-events: none; background-image: url('${window.location.origin}/imagenes/guilloche_zebra.svg'); background-repeat: repeat; opacity: 1; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .dni-header { display: flex; align-items: center; padding: 0 2.5mm; background: #10243f !important; position: relative; z-index: 1; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .dni-header-logo, .dni-header-logo-right { width: 5mm; height: 5mm; flex-shrink: 0; }
    .dni-header-logo img, .dni-header-logo-right img { width: 5mm; height: 5mm; display: block; }
    .dni-header-text { flex: 1; text-align: center; }
    .dni-header-main { font-size: 2.6mm; font-weight: 800; color: #fff !important; text-transform: uppercase; letter-spacing: 0.3px; line-height: 1.1; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .dni-header-sub { font-weight: 500; color: #d7b85b !important; text-transform: uppercase; margin-top: 0.5mm; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .dni-gold-band { height: 1mm; background: #c9a227 !important; position: relative; z-index: 1; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .dni-photo-frame { border-radius: 0.5mm; overflow: hidden; border: 0.3mm solid #10243f; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .dni-photo-frame img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .dni-photo-empty { display: flex; align-items: center; justify-content: center; background: #e5e7eb !important; font-size: 1.5mm; font-weight: 700; color: #6b7280 !important; border-radius: 0.5mm; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .dni-side-num { text-align: center; }
    .dni-side-num-label { font-size: 1.2mm; font-weight: 700; color: #6b7280 !important; text-transform: uppercase; }
    .dni-side-num-val { font-size: 2.8mm; font-weight: 900; color: #10243f !important; font-family: 'Courier New', monospace; letter-spacing: 0.4px; line-height: 1; margin-top: 0.3mm; }
    .dni-field { display: flex; flex-direction: column; margin-bottom: 1.2mm; }
    .dni-field:last-child { margin-bottom: 0; }
    .dni-label { font-size: 1.3mm; font-weight: 700; color: #6b7280 !important; text-transform: uppercase; letter-spacing: 0.3px; }
    .dni-value { font-size: 2mm; font-weight: 600; color: #10243f !important; line-height: 1.2; margin-top: 0.3mm; }
    .dni-value-name { font-size: 2.6mm; font-weight: 800; color: #10243f !important; text-transform: uppercase; line-height: 1.15; margin-top: 0.3mm; }
    .dni-value-cargo { font-size: 4mm; font-weight: 800; color: #f97316 !important; text-transform: uppercase; line-height: 1.1; margin-top: 0.3mm; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .dni-sep { height: 0.3mm; background: #c9a227 !important; margin: 1mm 0; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .dni-footer { display: flex; align-items: center; justify-content: space-between; padding: 0 2.5mm; background: #10243f !important; position: relative; z-index: 1; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .dni-foot-process { font-size: 2.6mm; color: #d7b85b !important; font-weight: 600; text-transform: uppercase; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .dni-foot-code { font-size: 2.6mm; color: #fff !important; font-weight: 700; font-family: 'Courier New', monospace; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }`

  const layoutCss = isVertical ? `
    @page { size: 54mm 85.6mm; margin: 0; }
    .dni-card { width: 54mm; height: 85.6mm; border: none; border-radius: 0; }
    .dni-guilloche { background-size: 216px 342px; }
    .dni-header { height: 11mm; }
    .dni-header-main { font-size: 1.7mm !important; line-height: 1.2; }
    .dni-header-sub { font-size: 2.5mm !important; }
    .dni-body-v { flex: 1; display: flex; flex-direction: column; align-items: center; padding: 3mm 3mm 1mm; position: relative; z-index: 1; background: rgba(240,244,250,0.35) !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .dni-photo-col-v { display: flex; flex-direction: column; align-items: center; gap: 1.5mm; margin-bottom: 2.5mm; }
    .dni-photo-frame { width: 23mm; height: 30mm; }
    .dni-photo-empty { width: 23mm; height: 30mm; }
    .dni-data-col-v { width: 100%; }
    .dni-value-cargo { font-size: 4.4mm !important; }
    .dni-back-title { font-size: 2.6mm !important; }
    .dni-back-instr { font-size: 1.9mm !important; }
    .barcode-text { font-size: 1.8mm !important; }
    .firma-label { font-size: 1.5mm !important; }
    .dni-back-firma { margin-top: 11mm !important; }
    .dni-footer { height: 9mm; }`
  : `
    @page { size: 85.6mm 54mm; margin: 0; }
    .dni-card { width: 85.6mm; height: 54mm; border: none; border-radius: 0; }
    .dni-guilloche { background-size: 342px 216px; opacity: 0.65; }
    .dni-header { height: 9mm; }
    .dni-body { flex: 1; display: flex; position: relative; z-index: 1; background: rgba(240,244,250,0.35) !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .dni-photo-col { width: 24mm; padding: 2mm 0; margin-left: 2mm; margin-top: 2.5mm; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 1.2mm; }
    .dni-photo-frame { width: 20mm; height: 28mm; }
    .dni-photo-empty { width: 20mm; height: 28mm; }
    .dni-data-col { flex: 1; padding: 2mm 2.5mm; display: flex; flex-direction: column; justify-content: center; }
    .dni-back-firma { margin-top: 8mm !important; }
    .dni-footer { height: 9mm; }`

  w.document.write(`<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Credenciales — Zebra ZC300</title>
<style>
  ${baseCss}
  ${layoutCss}
</style></head><body>
${items}
</body></html>`)
  w.document.close()
  w.focus()
  setTimeout(() => { w.print() }, 800)
}

const credImprimirZebraV2 = () => {
  const w = window.open('', '_blank')
  if (!w) return

  const code39 = { '0':'NNNWWNWNN','1':'WNNWNNNNW','2':'NNWWNNNNW','3':'WNWNNNNWN','4':'NNNWWNNNW','5':'WNNWWNNNN','6':'NNWWWNNNN','7':'NNNWNNWNW','8':'WNNWNNWNN','9':'NNWWNNWNN','*':'NWNNWNWNN' }

  const buildBarcode = (dni) => {
    const dniStr = '*' + (dni || '00000000') + '*'
    let bars = ''
    for (let i = 0; i < dniStr.length; i++) {
      const pattern = code39[dniStr[i]] || code39['0']
      for (let j = 0; j < 9; j++) {
        const isBar = j % 2 === 0
        const isWide = pattern[j] === 'W'
        if (isBar) {
          bars += `<span class="bar${isWide ? ' bar-w' : ' bar-n'}"></span>`
        } else {
          bars += `<span class="space${isWide ? ' space-w' : ' space-n'}"></span>`
        }
      }
      bars += '<span class="space space-n"></span>'
    }
    return bars
  }

  const tplUrl = `${window.location.origin}/imagenes/credencial_v2_template.png`
  // 1024px = 54mm → scale = 54 / (1024 * 0.264583) = 0.199316
  const S = 0.199316

  const cardFrontHtml = (p) => {
    const fullName = `${p.nombres || ''} ${p.paterno || ''} ${p.materno || ''}`.trim()
    const examen = credProceso.value?.nombre || 'EXAMEN GENERAL'
    const fechaEmision = credProceso.value?.anio || '2026-II'

    return `
      <div class="v2-page">
        <div class="v2-scale">
          <div class="v2-card">
            <img class="v2-bg" src="${tplUrl}" />

            <div class="v2-uni">UNIVERSIDAD NACIONAL DEL ALTIPLANO</div>
            <div class="v2-puno"><span class="v2-puno-line"></span>PUNO<span class="v2-puno-line"></span></div>

            <div class="v2-photo">
              ${p.foto ? `<img src="${p.foto}" />` : `<div class="v2-photo-empty">SIN FOTO</div>`}
            </div>

            <div class="v2-field" style="top:630px;left:675px;width:280px;">
              <div class="v2-label">DNI:</div>
              <div class="v2-value">${p.dni || '—'}</div>
            </div>
            <div class="v2-field" style="top:748px;left:675px;width:280px;">
              <div class="v2-label">CARGO:</div>
              <div class="v2-value">${p.cargo || '—'}</div>
            </div>
            <div class="v2-field" style="top:868px;left:675px;width:300px;">
              <div class="v2-label">ÁREA:</div>
              <div class="v2-value-sm">DIRECCIÓN GENERAL DE ADMISIÓN</div>
            </div>

            <div class="v2-name">${fullName || '—'}</div>

            <div class="v2-box-green">${examen}</div>
            <div class="v2-box-gold">
              <div class="v2-gold-label">FECHA DE EMISIÓN:</div>
              <div class="v2-gold-value">${fechaEmision}</div>
            </div>
          </div>
        </div>
      </div>`
  }

  const cardBackHtml = (p) => {
    const fullName = `${p.nombres || ''} ${p.paterno || ''} ${p.materno || ''}`.trim()
    const bars = buildBarcode(p.dni)

    return `
      <div class="v2-page">
        <div class="v2-scale">
          <div class="v2-card v2-card-back">
            <div class="v2-back-title">CREDENCIAL OFICIAL</div>
            <div class="v2-back-instr">Esta credencial es personal e intransferible. Es obligatorio portarla y exhibirla durante el proceso de admisión. En caso de pérdida, comunicarse inmediatamente con la Dirección de Admisión.</div>
            <div class="v2-back-barcode">
              <div class="v2-barcode">${bars}</div>
              <div class="v2-barcode-text">${p.dni || '—'}</div>
            </div>
            <div class="v2-back-firma">
              <div class="v2-firma-line"></div>
              <div class="v2-firma-label">FIRMA DEL TITULAR</div>
            </div>
            <div class="v2-back-name">${fullName || '—'}</div>
          </div>
        </div>
      </div>`
  }

  const items = credFiltered.value.map(p => cardFrontHtml(p) + cardBackHtml(p)).join('')

  const css = `
    * { margin: 0; padding: 0; box-sizing: border-box; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important; }
    html, body { background: #fff !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    body { font-family: Arial, Helvetica, sans-serif; }

    @page { size: 54mm 81mm; margin: 0; }

    .v2-page { width: 54mm; height: 81mm; overflow: hidden; position: relative; page-break-after: always; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .v2-page:last-child { page-break-after: auto; }
    .v2-scale { transform: scale(${S}); transform-origin: top left; position: absolute; top: 0; left: 0; }
    .v2-card { width: 1024px; height: 1536px; position: relative; overflow: hidden; }
    .v2-bg { position: absolute; top: 0; left: 0; width: 1024px; height: 1536px; z-index: 0; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }

    .v2-uni { position: absolute; top: 85px; left: 280px; width: 410px; z-index: 2; font-size: 36px; font-weight: 800; color: #004831; text-transform: uppercase; text-align: center; line-height: 1.15; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .v2-puno { position: absolute; top: 145px; left: 280px; width: 410px; z-index: 2; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 40px; font-weight: 700; color: #C5A028; letter-spacing: 2px; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .v2-puno-line { display: inline-block; width: 80px; height: 2px; background: #C5A028; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }

    .v2-photo { position: absolute; top: 520px; left: 48px; width: 435px; height: 555px; z-index: 1; overflow: hidden; border-radius: 8px; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .v2-photo img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .v2-photo-empty { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: #e5e7eb; font-size: 28px; font-weight: 700; color: #6b7280; }

    .v2-field { position: absolute; z-index: 1; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .v2-label { font-size: 32px; font-weight: 700; color: #004831; text-transform: uppercase; line-height: 1.1; }
    .v2-value { font-size: 42px; font-weight: 700; color: #111111; line-height: 1.1; margin-top: 6px; }
    .v2-value-sm { font-size: 36px; font-weight: 700; color: #111111; line-height: 1.15; margin-top: 6px; }

    .v2-name { position: absolute; top: 1090px; left: 130px; width: 400px; height: 110px; z-index: 1; font-size: 42px; font-weight: 700; color: #004831; text-transform: uppercase; line-height: 1.05; text-align: left; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }

    .v2-box-green { position: absolute; top: 1255px; left: 80px; width: 425px; height: 110px; z-index: 1; display: flex; align-items: center; justify-content: center; font-size: 38px; font-weight: 700; color: #FFFFFF; text-transform: uppercase; text-align: center; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }

    .v2-box-gold { position: absolute; top: 1255px; left: 550px; width: 400px; height: 110px; z-index: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .v2-gold-label { font-size: 26px; font-weight: 700; color: #004831; line-height: 1.1; }
    .v2-gold-value { font-size: 34px; font-weight: 700; color: #111111; line-height: 1.1; margin-top: 6px; }

    /* ===== Back ===== */
    .v2-card-back { background: #fff !important; }
    .v2-back-title { position: absolute; top: 60px; left: 0; width: 1024px; z-index: 1; font-size: 30px; font-weight: 800; color: #004831; text-transform: uppercase; text-align: center; letter-spacing: 2px; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .v2-back-instr { position: absolute; top: 140px; left: 120px; width: 784px; z-index: 1; font-size: 24px; font-weight: 600; color: #10243f; text-align: center; line-height: 1.5; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .v2-back-barcode { position: absolute; top: 470px; left: 0; width: 1024px; z-index: 1; display: flex; flex-direction: column; align-items: center; }
    .v2-barcode { display: flex; align-items: flex-end; height: 70px; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .v2-barcode .bar { display: inline-block; height: 100%; background: #10243f !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .v2-barcode .bar-w { width: 2.5px; }
    .v2-barcode .bar-n { width: 0.8px; }
    .v2-barcode .space { display: inline-block; }
    .v2-barcode .space-w { width: 2px; }
    .v2-barcode .space-n { width: 0.6px; }
    .v2-barcode-text { font-family: 'Courier New', monospace; font-size: 20px; font-weight: 700; color: #10243f; text-align: center; letter-spacing: 3px; margin-top: 6px; }
    .v2-back-firma { position: absolute; top: 720px; left: 0; width: 1024px; z-index: 1; display: flex; flex-direction: column; align-items: center; }
    .v2-firma-line { width: 500px; border-top: 2px solid #10243f; }
    .v2-firma-label { font-size: 16px; font-weight: 700; color: #6b7280; text-transform: uppercase; margin-top: 6px; letter-spacing: 2px; }
    .v2-back-name { position: absolute; top: 900px; left: 0; width: 1024px; z-index: 1; font-size: 26px; font-weight: 700; color: #004831; text-align: center; text-transform: uppercase; }`

  w.document.write(`<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Credenciales — Zebra ZC300 v2</title>
<style>${css}</style></head><body>
${items}
</body></html>`)
  w.document.close()
  w.focus()
  setTimeout(() => { w.print() }, 800)
}
</script>

<style>
.cred-filter-bar {
  display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
  margin-bottom: 12px; padding-bottom: 10px;
  border-bottom: 1px solid var(--card-border, #e2e8f0);
}
.cred-grid {
  display: grid; grid-template-columns: repeat(auto-fill, 85.6mm);
  gap: 8px; justify-content: center;
}
.dni-card {
  width: 85.6mm; height: 54mm; border-radius: 6px; overflow: hidden;
  background: #fff; box-shadow: 0 2px 6px rgba(0,0,0,0.12);
  position: relative; display: flex; flex-direction: column;
  border: 0.5pt solid #b7c0cc;
}
.dni-guilloche {
  position: absolute; top: 0; left: 0; right: 0; bottom: 0; z-index: 0; pointer-events: none;
  background-image: url('/imagenes/guilloche.svg');
  background-size: 340px 216px;
  background-repeat: repeat;
  opacity: 0.85;
}
.dni-header {
  display: flex; align-items: center; height: 9mm; padding: 0 2.5mm;
  background: #10243f; position: relative; z-index: 1;
}
.dni-header-logo, .dni-header-logo-right { width: 5mm; height: 5mm; flex-shrink: 0; }
.dni-header-logo img, .dni-header-logo-right img { width: 5mm; height: 5mm; display: block; }
.dni-header-text { flex: 1; text-align: center; }
.dni-header-main { font-size: 10px; font-weight: 800; color: #fff; text-transform: uppercase; letter-spacing: 0.3px; line-height: 1.1; }
.dni-header-sub { font-size: 9px; font-weight: 500; color: #d7b85b; text-transform: uppercase; margin-top: 0.5mm; }
.dni-gold-band { height: 1mm; background: #c9a227; position: relative; z-index: 1; }
.dni-body { flex: 1; display: flex; position: relative; z-index: 1; background: rgba(240,244,250,0.35); }
.dni-photo-col { width: 22mm; padding: 2mm 0; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 1.2mm; }
.dni-photo-frame { width: 17mm; height: 24mm; border-radius: 2px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.15); border: 0.5pt solid #10243f; }
.dni-photo-frame img { width: 100%; height: 100%; object-fit: cover; display: block; }
.dni-photo-empty { width: 17mm; height: 24mm; display: flex; align-items: center; justify-content: center; background: #e5e7eb; font-size: 6px; font-weight: 700; color: #6b7280; border-radius: 2px; }
.dni-side-num { text-align: center; }
.dni-side-num-label { font-size: 4.5px; font-weight: 700; color: #6b7280; text-transform: uppercase; }
.dni-side-num-val { font-size: 11px; font-weight: 900; color: #10243f; font-family: 'Courier New', monospace; letter-spacing: 0.4px; line-height: 1; margin-top: 0.3mm; }
.dni-data-col { flex: 1; padding: 2mm 2.5mm; display: flex; flex-direction: column; justify-content: center; }
.dni-field { display: flex; flex-direction: column; margin-bottom: 1.2mm; }
.dni-field:last-child { margin-bottom: 0; }
.dni-label { font-size: 5px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.3px; }
.dni-value { font-size: 7.5px; font-weight: 600; color: #10243f; line-height: 1.2; margin-top: 0.3mm; }
.dni-value-name { font-size: 10px; font-weight: 800; color: #10243f; text-transform: uppercase; line-height: 1.15; margin-top: 0.3mm; }
.dni-value-cargo { font-size: 12px; font-weight: 700; color: #c9a227; text-transform: uppercase; line-height: 1.1; margin-top: 0.3mm; }
.dni-sep { height: 0.3mm; background: #c9a227; margin: 1mm 0; }
.dni-footer { height: 9mm; display: flex; align-items: center; justify-content: space-between; padding: 0 2.5mm; background: #10243f; position: relative; z-index: 1; }
.dni-foot-process { font-size: 10px; color: #d7b85b; font-weight: 600; text-transform: uppercase; }
.dni-foot-code { font-size: 10px; color: #fff; font-weight: 700; font-family: 'Courier New', monospace; }

/* ===== Vertical ===== */
.cred-grid-v {
  display: grid; grid-template-columns: repeat(auto-fill, 54mm);
  gap: 8px; justify-content: center;
}
.dni-card-v {
  width: 54mm; height: 85.6mm; border-radius: 6px; overflow: hidden;
  background: #fff; box-shadow: 0 2px 6px rgba(0,0,0,0.12);
  position: relative; display: flex; flex-direction: column;
  border: 0.5pt solid #b7c0cc;
}
.dni-card-v .dni-guilloche { background-size: 216px 340px; }
.dni-card-v .dni-header { height: 11mm; }
.dni-card-v .dni-header-main { font-size: 6.5px; line-height: 1.2; }
.dni-card-v .dni-header-sub { font-size: 12.5px; }
.dni-body-v {
  flex: 1; display: flex; flex-direction: column; align-items: center;
  padding: 3mm 3mm 1mm; position: relative; z-index: 1;
  background: rgba(240,244,250,0.35);
}
.dni-photo-col-v {
  display: flex; flex-direction: column; align-items: center;
  gap: 1.5mm; margin-bottom: 2.5mm;
}
.dni-card-v .dni-photo-frame { width: 20mm; height: 26mm; }
.dni-card-v .dni-photo-empty { width: 20mm; height: 26mm; }
.dni-data-col-v { width: 100%; }
.dni-card-v .dni-footer { height: 9mm; }
</style>
