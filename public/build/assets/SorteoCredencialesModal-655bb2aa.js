import{r as _,Q as V,o as c,y as F,w as u,v as ii,n as ti,M as ei,d as t,a as p,c as f,H as U,b as I,t as r,F as R,u as P,f as A,z as H,I as oi,S as ai,j as ni,B as di,af as si,a8 as li,_ as ri,ae as ci,a5 as mi}from"./app-04d7fd67.js";import{P as B}from"./PrinterOutlined-7c5afc7d.js";import"./AntdIcon-65cfa17b.js";const pi={class:"cred-filter-bar"},vi={style:{"margin-left":"auto",display:"flex",gap:"8px","align-items":"center"}},ui={key:0,class:"cred-grid"},fi={class:"dni-header"},gi={class:"dni-header-logo"},hi=["src"],bi={class:"dni-header-text"},xi={class:"dni-header-sub"},Ni={class:"dni-header-logo-right"},wi=["src"],yi={class:"dni-body"},ki={class:"dni-photo-col"},_i={class:"dni-photo-frame"},Ii=["src"],ji={key:1,class:"dni-photo-empty"},zi={class:"dni-side-num"},$i={class:"dni-side-num-val"},Ai={class:"dni-data-col"},Ci={class:"dni-field"},Wi={class:"dni-value-name"},Di={class:"dni-field"},Ei={class:"dni-value-name"},Oi={class:"dni-field"},Si={class:"dni-value-cargo"},Li={class:"dni-field"},Ti={class:"dni-footer"},Fi={class:"dni-foot-code"},Ui={key:1,class:"cred-grid-v"},Ri={class:"dni-header"},Pi={class:"dni-header-logo"},Bi=["src"],Mi={class:"dni-header-text"},Vi={class:"dni-header-sub"},Hi={class:"dni-header-logo-right"},Zi=["src"],Gi={class:"dni-body-v"},Yi={class:"dni-photo-col-v"},Qi={class:"dni-photo-frame"},Xi=["src"],qi={key:1,class:"dni-photo-empty"},Ji={class:"dni-side-num"},Ki={class:"dni-side-num-val"},it={class:"dni-data-col-v"},tt={class:"dni-field"},et={class:"dni-value-name"},ot={class:"dni-field"},at={class:"dni-value-name"},nt={class:"dni-field"},dt={class:"dni-value-cargo"},st={class:"dni-field"},lt={class:"dni-footer"},rt={class:"dni-foot-code"},ut={__name:"SorteoCredencialesModal",props:{sorteoId:{type:Number,default:null},filtroCargo:{type:Number,default:null},filtroOrigen:{type:String,default:null}},setup(Z,{expose:G}){const O=Z,L=_(!1),S=_([]),T=_(!1),E=_(""),C=_(void 0),W=_(void 0),D=_("horizontal"),h=_(null),N=_(""),w=_("");G({open:async()=>{if(O.sorteoId){T.value=!0,L.value=!0,E.value="",C.value=void 0,W.value=void 0;try{const o=await ii.post("sorteo/credenciales-data",{id_sorteo:O.sorteoId,id_cargo:O.filtroCargo||null,filtro_origen:O.filtroOrigen||null});S.value=o.data.seleccionados,h.value=o.data.proceso,N.value=o.data.logo_tiny,w.value=o.data.logo_dad}catch{ti.error({message:"Error",description:"No se pudieron cargar las credenciales",placement:"topRight"})}finally{T.value=!1}}}});const Y=V(()=>[...new Set(S.value.map(o=>o.cargo).filter(Boolean))].sort()),z=V(()=>S.value.filter(o=>{const i=E.value.toLowerCase().trim(),j=!i||`${o.dni} ${o.paterno} ${o.materno} ${o.nombres} ${o.codigo_trabajador}`.toLowerCase().includes(i),b=!C.value||o.cargo===C.value,y=!W.value||(W.value==="manual"?o.es_manual:!o.es_manual);return j&&b&&y})),Q=()=>{E.value="",C.value=void 0,W.value=void 0},X=()=>{const o=window.open("","_blank");if(!o)return;const i=D.value==="vertical",j=v=>{var e,l;const $=`
      <div class="dni-header">
        <div class="dni-header-logo">${N.value?`<img src="${N.value}" />`:""}</div>
        <div class="dni-header-text">
          <div class="dni-header-main">UNIVERSIDAD NACIONAL DEL ALTIPLANO</div>
          <div class="dni-header-sub">${((e=h.value)==null?void 0:e.nombre)||"DIRECCIÓN DE ADMISIÓN"}</div>
        </div>
        <div class="dni-header-logo-right">${w.value?`<img src="${w.value}" />`:""}</div>
      </div>`,n='<div class="dni-gold-band"></div>',a=`
      <div class="dni-photo-frame">
        ${v.foto?`<img src="${v.foto}" />`:'<div class="dni-photo-empty">SIN FOTO</div>'}
      </div>
      <div class="dni-side-num">
        <div class="dni-side-num-label">DNI</div>
        <div class="dni-side-num-val">${v.dni||"—"}</div>
      </div>`,s=`
      <div class="dni-field"><div class="dni-label">Apellidos</div><div class="dni-value-name">${v.paterno||""} ${v.materno||""}</div></div>
      <div class="dni-field"><div class="dni-label">Nombres</div><div class="dni-value-name">${v.nombres||"—"}</div></div>
      <div class="dni-sep"></div>
      <div class="dni-field"><div class="dni-label">Cargo Asignado</div><div class="dni-value-cargo">${v.cargo||"—"}</div></div>
      <div class="dni-field"><div class="dni-label">Tipo asignación</div><div class="dni-value" style="${v.es_manual?"color: #7c3aed !important; font-weight: 800;":""}">${v.es_manual?"DESIGNACIÓN":"SORTEO"}</div></div>`,m=`
      <div class="dni-footer">
        <span class="dni-foot-process">ADMISIÓN UNA PUNO</span>
        <span class="dni-foot-code">${((l=h.value)==null?void 0:l.anio)||"2026-II"}</span>
      </div>`;return i?`
      <div class="dni-card">
        <div class="dni-guilloche"></div>
        ${$}
        ${n}
        <div class="dni-body-v">
          <div class="dni-photo-col-v">${a}</div>
          <div class="dni-data-col-v">${s}</div>
        </div>
        ${m}
      </div>`:`
      <div class="dni-card">
        <div class="dni-guilloche"></div>
        ${$}
        ${n}
        <div class="dni-body">
          <div class="dni-photo-col">${a}</div>
          <div class="dni-data-col">${s}</div>
        </div>
        ${m}
      </div>`},b=z.value.map(j).join(""),y=`
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
    .dni-foot-code { font-size: 10px; color: #fff !important; font-weight: 700; font-family: 'Courier New', monospace; }`,k=i?`
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
    .dni-footer { height: 9mm; }`:`
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
    .dni-footer { height: 9mm; }`;o.document.write(`<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Credenciales</title>
<style>
  ${y}
  ${k}
</style></head><body>
<div class="cred-grid">${b}</div>
</body></html>`),o.document.close(),o.focus(),setTimeout(()=>{o.print()},800)},q=()=>{const o=window.open("","_blank");if(!o)return;const i=D.value==="vertical",j=n=>{var d,x;const a=`
      <div class="dni-header">
        <div class="dni-header-logo">${N.value?`<img src="${N.value}" />`:""}</div>
        <div class="dni-header-text">
          <div class="dni-header-main">UNIVERSIDAD NACIONAL DEL ALTIPLANO</div>
          <div class="dni-header-sub">${((d=h.value)==null?void 0:d.nombre)||"DIRECCIÓN DE ADMISIÓN"}</div>
        </div>
        <div class="dni-header-logo-right">${w.value?`<img src="${w.value}" />`:""}</div>
      </div>`,s='<div class="dni-gold-band"></div>',m=`
      <div class="dni-photo-frame">
        ${n.foto?`<img src="${n.foto}" />`:'<div class="dni-photo-empty">SIN FOTO</div>'}
      </div>
      <div class="dni-side-num">
        <div class="dni-side-num-label">DNI</div>
        <div class="dni-side-num-val">${n.dni||"—"}</div>
      </div>`,e=`
      <div class="dni-field"><div class="dni-label">Apellidos</div><div class="dni-value-name">${n.paterno||""} ${n.materno||""}</div></div>
      <div class="dni-field"><div class="dni-label">Nombres</div><div class="dni-value-name">${n.nombres||"—"}</div></div>
      <div class="dni-sep"></div>
      <div class="dni-field"><div class="dni-label">Cargo Asignado</div><div class="dni-value-cargo">${n.cargo||"—"}</div></div>
      <div class="dni-field"><div class="dni-label">Tipo asignación</div><div class="dni-value" style="${n.es_manual?"color: #7c3aed !important; font-weight: 800;":""}">${n.es_manual?"DESIGNACIÓN":"SORTEO"}</div></div>`,l=`
      <div class="dni-footer">
        <span class="dni-foot-process">ADMISIÓN UNA PUNO</span>
        <span class="dni-foot-code">${((x=h.value)==null?void 0:x.anio)||"2026-II"}</span>
      </div>`;return i?`
      <div class="dni-card">
        <div class="dni-guilloche"></div>
        ${a}
        ${s}
        <div class="dni-body-v">
          <div class="dni-photo-col-v">${m}</div>
          <div class="dni-data-col-v">${e}</div>
        </div>
        ${l}
      </div>`:`
      <div class="dni-card">
        <div class="dni-guilloche"></div>
        ${a}
        ${s}
        <div class="dni-body">
          <div class="dni-photo-col">${m}</div>
          <div class="dni-data-col">${e}</div>
        </div>
        ${l}
      </div>`},b={0:"NNNWWNWNN",1:"WNNWNNNNW",2:"NNWWNNNNW",3:"WNWNNNNWN",4:"NNNWWNNNW",5:"WNNWWNNNN",6:"NNWWWNNNN",7:"NNNWNNWNW",8:"WNNWNNWNN",9:"NNWWNNWNN","*":"NWNNWNWNN"},y=n=>{var l;const a="*"+(n.dni||"00000000")+"*";let s="";for(let d=0;d<a.length;d++){const x=b[a[d]]||b[0];for(let g=0;g<9;g++){const K=g%2===0,M=x[g]==="W";K?s+=`<span class="bar${M?" bar-w":" bar-n"}"></span>`:s+=`<span class="space${M?" space-w":" space-n"}"></span>`}s+='<span class="space space-n"></span>'}const m=`
      <div class="dni-header">
        <div class="dni-header-logo">${N.value?`<img src="${N.value}" />`:""}</div>
        <div class="dni-header-text">
          <div class="dni-header-main">UNIVERSIDAD NACIONAL DEL ALTIPLANO</div>
          <div class="dni-header-sub">DIRECCIÓN DE ADMISIÓN</div>
        </div>
        <div class="dni-header-logo-right">${w.value?`<img src="${w.value}" />`:""}</div>
      </div>`,e=`
      <div class="dni-footer">
        <span class="dni-foot-process">ADMISIÓN UNA PUNO</span>
        <span class="dni-foot-code">${((l=h.value)==null?void 0:l.anio)||"2026-II"}</span>
      </div>`;return`
      <div class="dni-card dni-card-back">
        <div class="dni-guilloche"></div>
        ${m}
        <div class="dni-gold-band"></div>
        <div class="dni-back-body">
          <div class="dni-back-title">CREDENCIAL OFICIAL</div>
          <div class="dni-back-instr">Esta credencial es personal e intransferible. Es obligatorio portarla y exhibirla durante el proceso de admisión. En caso de pérdida, comunicarse inmediatamente con la Dirección de Admisión.</div>
          <div class="dni-back-barcode">
            <div class="barcode">${s}</div>
            <div class="barcode-text">${n.dni||"—"}</div>
          </div>
          <div class="dni-back-firma">
            <div class="firma-line"></div>
            <div class="firma-label">FIRMA DEL TITULAR</div>
          </div>
        </div>
        ${e}
      </div>`},k=z.value.map(n=>j(n)+y(n)).join(""),v=`
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
    .dni-foot-code { font-size: 2.6mm; color: #fff !important; font-weight: 700; font-family: 'Courier New', monospace; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }`,$=i?`
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
    .dni-footer { height: 9mm; }`:`
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
    .dni-footer { height: 9mm; }`;o.document.write(`<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Credenciales — Zebra ZC300</title>
<style>
  ${v}
  ${$}
</style></head><body>
${k}
</body></html>`),o.document.close(),o.focus(),setTimeout(()=>{o.print()},800)},J=()=>{const o=window.open("","_blank");if(!o)return;const i={0:"NNNWWNWNN",1:"WNNWNNNNW",2:"NNWWNNNNW",3:"WNWNNNNWN",4:"NNNWWNNNW",5:"WNNWWNNNN",6:"NNWWWNNNN",7:"NNNWNNWNW",8:"WNNWNNWNN",9:"NNWWNNWNN","*":"NWNNWNWNN"},j=a=>{const s="*"+(a||"00000000")+"*";let m="";for(let e=0;e<s.length;e++){const l=i[s[e]]||i[0];for(let d=0;d<9;d++){const x=d%2===0,g=l[d]==="W";x?m+=`<span class="bar${g?" bar-w":" bar-n"}"></span>`:m+=`<span class="space${g?" space-w":" space-n"}"></span>`}m+='<span class="space space-n"></span>'}return m},b=`${window.location.origin}/imagenes/credencial_v2_template.png`,y=.199316,k=a=>{var l,d;const s=`${a.nombres||""} ${a.paterno||""} ${a.materno||""}`.trim(),m=((l=h.value)==null?void 0:l.nombre)||"EXAMEN GENERAL",e=((d=h.value)==null?void 0:d.anio)||"2026-II";return`
      <div class="v2-page">
        <div class="v2-scale">
          <div class="v2-card">
            <img class="v2-bg" src="${b}" />

            <div class="v2-uni">UNIVERSIDAD NACIONAL DEL ALTIPLANO</div>
            <div class="v2-puno"><span class="v2-puno-line"></span>PUNO<span class="v2-puno-line"></span></div>

            <div class="v2-photo">
              ${a.foto?`<img src="${a.foto}" />`:'<div class="v2-photo-empty">SIN FOTO</div>'}
            </div>

            <div class="v2-field" style="top:630px;left:675px;width:280px;">
              <div class="v2-label">DNI:</div>
              <div class="v2-value">${a.dni||"—"}</div>
            </div>
            <div class="v2-field" style="top:748px;left:675px;width:280px;">
              <div class="v2-label">CARGO:</div>
              <div class="v2-value">${a.cargo||"—"}</div>
            </div>
            <div class="v2-field" style="top:868px;left:675px;width:300px;">
              <div class="v2-label">ÁREA:</div>
              <div class="v2-value-sm">DIRECCIÓN GENERAL DE ADMISIÓN</div>
            </div>

            <div class="v2-name">${s||"—"}</div>

            <div class="v2-box-green">${m}</div>
            <div class="v2-box-gold">
              <div class="v2-gold-label">FECHA DE EMISIÓN:</div>
              <div class="v2-gold-value">${e}</div>
            </div>
          </div>
        </div>
      </div>`},v=a=>{const s=`${a.nombres||""} ${a.paterno||""} ${a.materno||""}`.trim();return`
      <div class="v2-page">
        <div class="v2-scale">
          <div class="v2-card v2-card-back">
            <div class="v2-back-title">CREDENCIAL OFICIAL</div>
            <div class="v2-back-instr">Esta credencial es personal e intransferible. Es obligatorio portarla y exhibirla durante el proceso de admisión. En caso de pérdida, comunicarse inmediatamente con la Dirección de Admisión.</div>
            <div class="v2-back-barcode">
              <div class="v2-barcode">${j(a.dni)}</div>
              <div class="v2-barcode-text">${a.dni||"—"}</div>
            </div>
            <div class="v2-back-firma">
              <div class="v2-firma-line"></div>
              <div class="v2-firma-label">FIRMA DEL TITULAR</div>
            </div>
            <div class="v2-back-name">${s||"—"}</div>
          </div>
        </div>
      </div>`},$=z.value.map(a=>k(a)+v(a)).join(""),n=`
    * { margin: 0; padding: 0; box-sizing: border-box; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important; }
    html, body { background: #fff !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    body { font-family: Arial, Helvetica, sans-serif; }

    @page { size: 54mm 81mm; margin: 0; }

    .v2-page { width: 54mm; height: 81mm; overflow: hidden; position: relative; page-break-after: always; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .v2-page:last-child { page-break-after: auto; }
    .v2-scale { transform: scale(${y}); transform-origin: top left; position: absolute; top: 0; left: 0; }
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
    .v2-back-name { position: absolute; top: 900px; left: 0; width: 1024px; z-index: 1; font-size: 26px; font-weight: 700; color: #004831; text-align: center; text-transform: uppercase; }`;o.document.write(`<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Credenciales — Zebra ZC300 v2</title>
<style>${n}</style></head><body>
${$}
</body></html>`),o.document.close(),o.focus(),setTimeout(()=>{o.print()},800)};return(o,i)=>{const j=oi,b=ai,y=ni,k=di,v=si,$=li,n=ri,a=ci,s=mi,m=ei;return c(),F(m,{open:L.value,"onUpdate:open":i[6]||(i[6]=e=>L.value=e),title:"Credenciales",footer:null,width:"95%",style:{top:"10px"},"body-style":{maxHeight:"calc(100vh - 120px)",overflow:"auto"}},{default:u(()=>[t("div",pi,[p(j,{value:E.value,"onUpdate:value":i[0]||(i[0]=e=>E.value=e),placeholder:"DNI, nombre, código...",style:{width:"260px"},"allow-clear":""},null,8,["value"]),p(y,{value:C.value,"onUpdate:value":i[1]||(i[1]=e=>C.value=e),placeholder:"Cargo",style:{width:"160px"},"allow-clear":""},{default:u(()=>[(c(!0),f(R,null,U(Y.value,e=>(c(),F(b,{key:e,value:e},{default:u(()=>[I(r(e),1)]),_:2},1032,["value"]))),128))]),_:1},8,["value"]),p(y,{value:W.value,"onUpdate:value":i[2]||(i[2]=e=>W.value=e),placeholder:"Origen",style:{width:"140px"},"allow-clear":""},{default:u(()=>[p(b,{value:"sorteo"},{default:u(()=>i[7]||(i[7]=[I("SORTEO")])),_:1}),p(b,{value:"manual"},{default:u(()=>i[8]||(i[8]=[I("DESIGNACIÓN")])),_:1})]),_:1},8,["value"]),p(k,{size:"small",onClick:Q},{default:u(()=>i[9]||(i[9]=[I("✕ Limpiar")])),_:1}),t("div",vi,[p($,{value:D.value,"onUpdate:value":i[3]||(i[3]=e=>D.value=e),size:"small","button-style":"solid"},{default:u(()=>[p(v,{value:"horizontal"},{default:u(()=>i[10]||(i[10]=[I("Horizontal")])),_:1}),p(v,{value:"vertical"},{default:u(()=>i[11]||(i[11]=[I("Vertical")])),_:1})]),_:1},8,["value"]),p(n,{color:"gold"},{default:u(()=>[I(r(z.value.length)+" / "+r(S.value.length),1)]),_:1}),p(k,{size:"small",type:"primary",onClick:X},{default:u(()=>[p(P(B)),i[12]||(i[12]=I(" Imprimir / PDF"))]),_:1}),p(k,{size:"small",onClick:q},{default:u(()=>[p(P(B)),i[13]||(i[13]=I(" Zebra ZC300"))]),_:1}),p(k,{size:"small",onClick:J},{default:u(()=>[p(P(B)),i[14]||(i[14]=I(" Zebra v2"))]),_:1})])]),p(s,{spinning:T.value},{default:u(()=>[z.value.length&&D.value==="horizontal"?(c(),f("div",ui,[(c(!0),f(R,null,U(z.value,e=>{var l,d;return c(),f("div",{key:e.id,class:"dni-card"},[i[23]||(i[23]=t("div",{class:"dni-guilloche"},null,-1)),t("div",fi,[t("div",gi,[N.value?(c(),f("img",{key:0,src:N.value},null,8,hi)):A("",!0)]),t("div",bi,[i[15]||(i[15]=t("div",{class:"dni-header-main"},"UNIVERSIDAD NACIONAL DEL ALTIPLANO",-1)),t("div",xi,r(((l=h.value)==null?void 0:l.nombre)||"ADMISIÓN"),1)]),t("div",Ni,[w.value?(c(),f("img",{key:0,src:w.value},null,8,wi)):A("",!0)])]),i[24]||(i[24]=t("div",{class:"dni-gold-band"},null,-1)),t("div",yi,[t("div",ki,[t("div",_i,[e.foto?(c(),f("img",{key:0,src:e.foto,onError:i[4]||(i[4]=x=>{var g;x.target.style.display="none",(g=x.target.nextElementSibling)==null||g.style.removeProperty("display")})},null,40,Ii)):A("",!0),e.foto?A("",!0):(c(),f("div",ji,"SIN FOTO"))]),t("div",zi,[i[16]||(i[16]=t("div",{class:"dni-side-num-label"},"DNI",-1)),t("div",$i,r(e.dni),1)])]),t("div",Ai,[t("div",Ci,[i[17]||(i[17]=t("div",{class:"dni-label"},"Apellidos",-1)),t("div",Wi,r(e.paterno)+" "+r(e.materno),1)]),t("div",Di,[i[18]||(i[18]=t("div",{class:"dni-label"},"Nombres",-1)),t("div",Ei,r(e.nombres),1)]),i[21]||(i[21]=t("div",{class:"dni-sep"},null,-1)),t("div",Oi,[i[19]||(i[19]=t("div",{class:"dni-label"},"Cargo",-1)),t("div",Si,r(e.cargo),1)]),t("div",Li,[i[20]||(i[20]=t("div",{class:"dni-label"},"Tipo asignación",-1)),t("div",{class:"dni-value",style:H(e.es_manual?"color: #7c3aed !important;":"")},r(e.es_manual?"DESIGNACIÓN":"SORTEO"),5)])])]),t("div",Ti,[i[22]||(i[22]=t("span",{class:"dni-foot-process"},"ADMISIÓN UNA PUNO",-1)),t("span",Fi,r(((d=h.value)==null?void 0:d.anio)||"2026-II"),1)])])}),128))])):z.value.length&&D.value==="vertical"?(c(),f("div",Ui,[(c(!0),f(R,null,U(z.value,e=>{var l,d;return c(),f("div",{key:e.id,class:"dni-card-v"},[i[33]||(i[33]=t("div",{class:"dni-guilloche"},null,-1)),t("div",Ri,[t("div",Pi,[N.value?(c(),f("img",{key:0,src:N.value},null,8,Bi)):A("",!0)]),t("div",Mi,[i[25]||(i[25]=t("div",{class:"dni-header-main"},"UNIVERSIDAD NACIONAL DEL ALTIPLANO",-1)),t("div",Vi,r(((l=h.value)==null?void 0:l.nombre)||"ADMISIÓN"),1)]),t("div",Hi,[w.value?(c(),f("img",{key:0,src:w.value},null,8,Zi)):A("",!0)])]),i[34]||(i[34]=t("div",{class:"dni-gold-band"},null,-1)),t("div",Gi,[t("div",Yi,[t("div",Qi,[e.foto?(c(),f("img",{key:0,src:e.foto,onError:i[5]||(i[5]=x=>{var g;x.target.style.display="none",(g=x.target.nextElementSibling)==null||g.style.removeProperty("display")})},null,40,Xi)):A("",!0),e.foto?A("",!0):(c(),f("div",qi,"SIN FOTO"))]),t("div",Ji,[i[26]||(i[26]=t("div",{class:"dni-side-num-label"},"DNI",-1)),t("div",Ki,r(e.dni),1)])]),t("div",it,[t("div",tt,[i[27]||(i[27]=t("div",{class:"dni-label"},"Apellidos",-1)),t("div",et,r(e.paterno)+" "+r(e.materno),1)]),t("div",ot,[i[28]||(i[28]=t("div",{class:"dni-label"},"Nombres",-1)),t("div",at,r(e.nombres),1)]),i[31]||(i[31]=t("div",{class:"dni-sep"},null,-1)),t("div",nt,[i[29]||(i[29]=t("div",{class:"dni-label"},"Cargo",-1)),t("div",dt,r(e.cargo),1)]),t("div",st,[i[30]||(i[30]=t("div",{class:"dni-label"},"Tipo asignación",-1)),t("div",{class:"dni-value",style:H(e.es_manual?"color: #7c3aed !important;":"")},r(e.es_manual?"DESIGNACIÓN":"SORTEO"),5)])])]),t("div",lt,[i[32]||(i[32]=t("span",{class:"dni-foot-process"},"ADMISIÓN UNA PUNO",-1)),t("span",rt,r(((d=h.value)==null?void 0:d.anio)||"2026-II"),1)])])}),128))])):(c(),F(a,{key:2,description:"Sin resultados",style:{padding:"60px 0"}}))]),_:1},8,["spinning"])]),_:1},8,["open"])}}};export{ut as default};
