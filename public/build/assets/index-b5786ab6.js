import{r as o,k as c,w as s,j as d,o as p,d as l,a as u,b as _,t as m,g as v}from"./app-03b39463.js";import{A as g}from"./AuthenticatedLayout-9771aac3.js";import{x}from"./index-c5e79823.js";import"./DropdownLink-f48c26d2.js";import"./_plugin-vue_export-helper-c27b6911.js";import"./logotiny-c4b525af.js";import"./xlsx-dd522480.js";const b={class:"p-5"},f={style:{display:"none"}},C={__name:"index",setup(h){const a=o([]),n=()=>{let e=[{sheet:"Participantes",columns:[{label:"dni",value:"dni"},{label:"Nombre",value:"nombres"},{label:"Paterno",value:"primer_apellido"},{label:"Materno",value:"segundo_apellido"},{label:"Modalidad",value:"modalidad"},{label:"Programa",value:"programa"},{label:"Nota Ex Vocacional",value:"nota"}],content:a.value}];x(e,{fileName:"Lista",extraLength:3,writeMode:"writeFile",writeOptions:{},RTL:!1})},t=o("");return(async()=>{const e=await d.get("resultados-vocacional");t.value=e.data.datos})(),(e,i)=>{const r=v("a-button");return p(),c(g,null,{default:s(()=>[l("div",b,[u(r,{onClick:n},{default:s(()=>[_("Descargar XLSX")]),_:1})]),l("div",f,m(a.value=t.value),1)]),_:1})}}};export{C as default};