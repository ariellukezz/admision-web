import{r as l,e as H,f as d,o as c,c as v,d as n,a,w as o,l as b,b as i,t as D,g as N,F as J,u as K,y as Q}from"./app-32563e7d.js";const W={class:"mt-0 mb-4"},X={class:"class flex justify-between"},Y={class:"mt-0 mb-4"},Z={key:0,class:"mt-0 mb-4"},ee={key:1,class:"mt-0 mb-4"},ae={key:0},te={key:0},oe={class:"mt-4"},ne=n("label",null,"Area",-1),le=n("label",null,"Correctas",-1),se=n("label",null,"Incorrectas",-1),de=n("label",null,"En Blanco",-1),ue={class:"mt-3"},ie=n("label",null,"Ponderacion",-1),re={class:"flex justify-end"},ce={class:"mr-2"},pe={__name:"calificacion",props:["proceso"],setup(U){const g=U,x=l(1),_=l(null),m=l(""),y=l([]),A=(s,e)=>{_.value=e};H(m,(s,e)=>{E()});const P=l([]),V=l(0),B=l(1),F=l(10);l("");const w=l(1),k=l(0),C=l(0),E=async()=>{let s=await b.post("get-ponderaciones-select?page="+B.value,{term:m.value,paginasize:F.value});P.value=s.data.datos.data,V.value=s.data.datos.total},$=async()=>{await b.post("/calificar-examen",{id_area:x.value,id_simulacro:g.proceso,id_ponderacion:_.value.key,correctas:w.value,incorrectas:k.value,blanco:C.value}),p.value=!1,S()},S=async()=>{let s=await b.post("/get-puntajes-examen",{id_simulacro:g.proceso});y.value=s.data.datos};S();const p=l(!1);E();const z=s=>Number(s).toFixed(2),O=l([{title:"N°",dataIndex:"nro",align:"center"},{title:"DNI",dataIndex:"dni"},{title:"Ap. Paterno",dataIndex:"paterno"},{title:"Ap. Materno",dataIndex:"materno"},{title:"Nombres",dataIndex:"nombres"},{title:"Puntaje",dataIndex:"puntaje",align:"center"},{title:"Condicion",dataIndex:"condicion",align:"center"}]),M=async()=>{b.get("/get-pdf-resultados/"+g.proceso).then(s=>{s.data.archivos.forEach(u=>{const r=document.createElement("a");r.href=u,r.download=u.split("/").pop(),document.body.appendChild(r),r.click(),document.body.removeChild(r)})}).catch(s=>{console.error("Error al generar los PDFs:",s)})};return(s,e)=>{const u=d("a-button"),r=d("a-table"),I=d("a-select-option"),R=d("a-select"),f=d("a-input"),h=d("a-col"),G=d("a-row"),L=d("a-auto-complete"),T=d("a-modal");return c(),v("div",null,[n("div",W,[n("div",X,[n("div",Y,[a(u,{onClick:e[0]||(e[0]=t=>p.value=!0)},{default:o(()=>[i("Calificar")]),_:1})]),y.value!=[]?(c(),v("div",Z,[a(u,{style:{width:"140px",background:"green",border:"none",color:"white"},onClick:e[1]||(e[1]=t=>M())},{default:o(()=>[i("Descargar")]),_:1})])):(c(),v("div",ee,[a(u,{style:{width:"140px",background:"green",border:"none",color:"white"},onClick:e[2]||(e[2]=t=>p.value=!0),disabled:""},{default:o(()=>[i("Descargar")]),_:1})]))])]),a(r,{dataSource:y.value,columns:O.value,size:"small",pagination:!1},{bodyCell:o(({column:t,index:q,record:j})=>[t.dataIndex==="nro"?(c(),v("span",ae,D(q+1),1)):N("",!0),t.dataIndex==="puntaje"?(c(),v(J,{key:1},[j.puntaje!==null&&j.puntaje!==void 0?(c(),v("div",te,[n("span",null,D(z(j.puntaje)),1)])):N("",!0)],64)):N("",!0)]),_:1},8,["dataSource","columns"]),a(T,{visible:p.value,"onUpdate:visible":e[10]||(e[10]=t=>p.value=t),footer:!1},{default:o(()=>[n("div",oe,[ne,a(R,{ref:"select",value:x.value,"onUpdate:value":e[3]||(e[3]=t=>x.value=t),style:{width:"100%"}},{default:o(()=>[a(I,{value:1},{default:o(()=>[i("BIOMEDICAS")]),_:1}),a(I,{value:2},{default:o(()=>[i("INGENIERIAS")]),_:1}),a(I,{value:3},{default:o(()=>[i("SOCIALES")]),_:1})]),_:1},8,["value"])]),a(G,{gutter:"[16,16]"},{default:o(()=>[a(h,{span:8,xs:"{24}",sm:"{12}",md:"{8}"},{default:o(()=>[le,a(f,{ref:"select",value:w.value,"onUpdate:value":e[4]||(e[4]=t=>w.value=t),style:{width:"100%"}},null,8,["value"])]),_:1}),a(h,{span:8,xs:"{24}",sm:"{12}",md:"{8}"},{default:o(()=>[se,a(f,{ref:"select",value:k.value,"onUpdate:value":e[5]||(e[5]=t=>k.value=t),style:{width:"100%"}},null,8,["value"])]),_:1}),a(h,{span:8,xs:"{24}",sm:"{12}",md:"{8}"},{default:o(()=>[de,a(f,{ref:"select",value:C.value,"onUpdate:value":e[6]||(e[6]=t=>C.value=t),style:{width:"100%"}},null,8,["value"])]),_:1})]),_:1}),n("div",ue,[ie,n("div",null,[a(L,{value:_.value,"onUpdate:value":e[8]||(e[8]=t=>_.value=t),options:P.value,onSelect:A,style:{width:"100%"}},{default:o(()=>[a(f,{placeholder:"Ponderación ...",value:m.value,"onUpdate:value":e[7]||(e[7]=t=>m.value=t)},{suffix:o(()=>[a(K(Q))]),_:1},8,["value"])]),_:1},8,["value","options"])])]),n("div",re,[n("div",ce,[a(u,{style:{"margin-left":"6px","border-radius":"4px"}},{default:o(()=>[i("Cancelar s")]),_:1})]),n("div",null,[a(u,{type:"primary",onClick:e[9]||(e[9]=t=>$()),style:{background:"#476175",border:"none","border-radius":"4px"}},{default:o(()=>[i("Calificar")]),_:1})])])]),_:1},8,["visible"])])}}};export{pe as default};