import{_ as p}from"./_plugin-vue_export-helper-c27b6911.js";import{f as l,o as s,s as c,w as n,d as t,b as e,t as o,g as i,a as u,c as f}from"./app-3a079e0d.js";const m={style:{"margin-top":"-35px"}},y={style:{display:"flex","justify-content":"flex-end","margin-top":"-10px"}},x={style:{display:"flex","justify-content":"center"}},g={style:{"font-size":"40pt","font-weight":"bold"}},v={style:{display:"flex","justify-content":"center","margin-top":"-10px"}},h={key:0},E={__name:"cardpuntaje",props:["datos"],setup(a){return(N,j)=>{const _=l("a-tag"),d=l("a-label"),r=l("a-card");return s(),c(r,{style:{"margin-bottom":"20px"}},{default:n(()=>[t("div",m,[a.datos.modalidad=="CEPREUNA"?(s(),c(_,{key:0,color:"cyan",class:"tagExamen",style:{padding:"4px"}},{default:n(()=>[e("EXAMEN "+o(a.datos.modalidad),1)]),_:1})):i("",!0),a.datos.modalidad=="GENERAL"?(s(),c(_,{key:1,color:"orange",class:"tagExamen",style:{padding:"4px"}},{default:n(()=>[e("EXAMEN "+o(a.datos.modalidad),1)]),_:1})):i("",!0)]),t("div",null,[t("div",y,[t("div",null,[u(d,null,{default:n(()=>[e("Fecha: ")]),_:1}),e(),t("span",null,o(a.datos.fecha),1)])]),t("div",x,[t("div",null,[t("span",g,o(a.datos.puntaje),1)])]),t("div",v,[a.datos.apto=="CLASIFICADO"?(s(),f("div",h,[u(d),e(),t("span",null,o(a.datos.apto),1)])):i("",!0),t("div",null,[u(d,null,{default:n(()=>[e("Ingreso: ")]),_:1}),e(),t("span",null,o(a.datos.apto),1)])])])]),_:1})}}},A=p(E,[["__scopeId","data-v-ef34f3a9"]]);export{A as default};