import b from"./cardpuntaje-f112c72d.js";import{x as c,c as o,a as e,u as j,d as _,w as s,F as p,r as a,o as i,X as k,b as v,C as V,f as C,z as N}from"./app-2924acb4.js";const P={class:"bg-white overflow-hidden shadow-sm sm:rounded-lg",style:{background:"#f2f2f200",width:"100dvw",height:"100vh",display:"flex","justify-content":"center","align-items":"center"}},U={style:{width:"330px"}},B={style:{width:"100%",display:"flex","justify-content":"flex-end"}},D={key:0},S={__name:"index",setup(E){const d=c(null),u=c(null),n=c(!1),f=r=>{console.log(r),n.value=!1},m=async()=>{d.value=await N.get("/get-puntajes/"+u.value),n.value=!0};return(r,l)=>{const h=a("a-label"),g=a("a-input"),w=a("a-button"),x=a("a-modal"),y=a("a-card");return i(),o(p,null,[e(j(k),{title:"Puntajes Unap"}),_("div",P,[_("div",U,[e(y,{loading:r.loading,title:"Ver Puntaje",style:{width:"100%",height:"100%"}},{default:s(()=>[e(h,null,{default:s(()=>[v("Dni ")]),_:1}),e(g,{value:u.value,"onUpdate:value":l[0]||(l[0]=t=>u.value=t),placeholder:"Dni"},null,8,["value"]),_("div",B,[e(w,{style:{"margin-top":"16px"},onClick:m},{default:s(()=>[v("Ver Puntaje")]),_:1})]),e(x,{visible:n.value,"onUpdate:visible":l[1]||(l[1]=t=>n.value=t),title:"MIS PUNTAJES",onOk:f,style:{width:"340px"}},{default:s(()=>[d.value!=null?(i(),o("div",D,[(i(!0),o(p,null,V(d.value.data.datos,t=>(i(),o("div",null,[e(b,{datos:t},null,8,["datos"])]))),256))])):C("",!0)]),_:1},8,["visible"])]),_:1},8,["loading"])])])],64)}}};export{S as default};