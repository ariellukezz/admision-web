import{r as a,e as o,o as u,k as d,w as _,j as p,d as f}from"./app-8a9b4894.js";import{A as m}from"./LayoutSimulacro-0a3496f3.js";import"./DropdownLink-5159c35e.js";import"./logotiny-c4b525af.js";import"./_plugin-vue_export-helper-c27b6911.js";const v=f("div",{style:{}}," HELLO WORD ",-1),k={__name:"index",setup(g){const l=a([]);a(!1),a("");const i=a(""),r=a(0),n=a(1),c=a(2),t=async()=>{let e=await p.post("get-certificados-revision?page="+n.value,{term:i.value,paginasize:c.value});l.value=e.data.datos.data,r.value=e.data.datos.total};return o(i,(e,s)=>{t()}),o(n,(e,s)=>{t()}),o(c,(e,s)=>{t()}),t(),(e,s)=>(u(),d(m,null,{default:_(()=>[v]),_:1}))}};export{k as default};