import{r as l,f as c,o as C,c as N,a as o,u as P,w as p,F as R,Z as A,d as a,j as F,b as h,l as B}from"./app-32563e7d.js";import{L as M}from"./LayoutCalificador-8c1c9d1b.js";import{r as D,u as U}from"./xlsx-4f9172c7.js";import{_ as V}from"./_plugin-vue_export-helper-c27b6911.js";import"./DropdownLink-b76990c1.js";import"./logotiny-c4b525af.js";/* empty css                                                                          */import"./TopMenu-8daf9d7f.js";const E={class:"p-4",style:{width:"100%","max-width":"1300px",background:"white","border-radius":"8px"}},L={class:"flex justify-between"},O={style:{display:"flex","justify-content":"flex-end"}},G={style:{display:"flex"}},T={__name:"SubirResultado",setup(Z){const d=l([]),s=l(0),m=l(""),_=async()=>{s.value=0;try{const e=await B.post("subir-excel-simulacro",{data:d.value},{onUploadProgress:t=>{t.lengthComputable&&(s.value=Math.round(t.loaded/t.total*100))}});console.log("Respuesta del servidor:",e.data),s.value=100}catch(e){s.value=70,m.value="exception",console.error("Error:",e)}},x=e=>{const t=e.target.files[0],r=new FileReader;r.onload=i=>{const u=new Uint8Array(i.target.result),n=D(u,{type:"array"}),v=n.SheetNames[0],g=n.Sheets[v],f=U.sheet_to_json(g,{header:1}),w=f[0],I=f.slice(1).map(S=>{const b={};return w.forEach((j,k)=>{b[j]=S[k]}),b});d.value=I},r.readAsArrayBuffer(t)},y=l([{title:"DNI",dataIndex:"dni"},{title:"Ap. Paterno",dataIndex:"paterno"},{title:"Ap. Materno",dataIndex:"materno"},{title:"Mombres",dataIndex:"nombres"},{title:"Puntaje",dataIndex:"puntaje"},{title:"Fecha",dataIndex:"fecha"},{title:"Puesto Programa",dataIndex:"puesto_programa"},{title:"Puesto General",dataIndex:"puesto_general"}]);return(e,t)=>{const r=c("a-button"),i=c("a-progress"),u=c("a-table");return C(),N(R,null,[o(P(A),{title:"Inicio"}),o(M,null,{default:p(()=>[a("div",E,[a("form",{onSubmit:t[0]||(t[0]=F((...n)=>e.submit&&e.submit(...n),["prevent"]))},[a("div",L,[a("input",{type:"file",onChange:x},null,32),a("div",O,[o(r,{class:"mr-2",style:{"border-radius":"5px",height:"38px",border:"solid 1px var(--primary-color)",color:"var(--primary-color)"},onClick:_},{default:p(()=>[h("Ejemplo")]),_:1}),o(r,{style:{height:"38px","border-radius":"5px",border:"none",color:"white",background:"var(--primary-color)"},onClick:_},{default:p(()=>[h(" Subir resultado")]),_:1})])])],32),a("div",G,[o(i,{percent:s.value,status:m.value},null,8,["percent","status"])]),a("div",null,[o(u,{dataSource:d.value,columns:y.value},null,8,["dataSource","columns"])])])]),_:1})],64)}}},Y=V(T,[["__scopeId","data-v-84322550"]]);export{Y as default};