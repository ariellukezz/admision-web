import{q as v,C as w,J as b,o as i,c as m,T as k,a as t,u as s,w as n,F as V,Z as C,d as o,K as p,t as B,g as _,j as S,G as L,b as f,s as $,p as F,h as I}from"./app-bc7c33ea.js";import{G as j,A as q}from"./ApplicationLogo-f65f4962.js";import{_ as h,a as g,b as x}from"./TextInput-440581be.js";import{_ as A}from"./PrimaryButton-a75085e5.js";import{_ as N}from"./_plugin-vue_export-helper-c27b6911.js";import"./logotiny-c4b525af.js";import"./logoDAD-c76d0a0b.js";const U=["value"],D={__name:"Checkbox",props:{checked:{type:[Array,Boolean],default:!1},value:{default:null}},emits:["update:checked"],setup(a,{emit:e}){const c=e,d=a,l=v({get(){return d.checked},set(r){c("update:checked",r)}});return(r,u)=>w((i(),m("input",{type:"checkbox",value:a.value,"onUpdate:modelValue":u[0]||(u[0]=y=>l.value=y),class:"text-indigo-600"},null,8,U)),[[b,l.value]])}};const E=a=>(F("data-v-878aced6"),a=a(),I(),a),G={class:"container-login"},R={style:{width:"100%"}},z={key:0,class:"mb-4 text-sm font-medium text-green-600"},M={class:"mt-3"},P={class:"mt-2 flex justify-between"},T={class:"inline-flex items-center"},J=E(()=>o("span",{class:"mx-2 text-sm text-gray-600"},"Recuerdame",-1)),K={class:"mt-8"},O={style:{display:"flex","justify-content":"center"},class:"mt-4"},Z={__name:"Login",props:{canResetPassword:Boolean,status:String},setup(a){const e=k({email:"",password:"",remember:!1}),c=()=>{e.post(route("login"),{onFinish:()=>e.reset("password")})};return(d,l)=>(i(),m(V,null,[t(s(C),{title:"Log in"}),t(j,null,{default:n(()=>[o("div",G,[o("div",R,[t(s(p),{href:"/",class:"flex items-center justify-center"},{default:n(()=>[t(q,{class:"fill-current text-gray-500",style:{background:"none"}})]),_:1}),a.status?(i(),m("div",z,B(a.status),1)):_("",!0),o("form",{onSubmit:S(c,["prevent"])},[o("div",null,[t(h,{for:"email",value:"Correo"}),t(g,{id:"email",type:"email",class:"mt-1 block w-full",style:{height:"40px"},modelValue:s(e).email,"onUpdate:modelValue":l[0]||(l[0]=r=>s(e).email=r),required:"",autofocus:"",autocomplete:"username"},null,8,["modelValue"]),t(x,{class:"mt-2",message:s(e).errors.email},null,8,["message"])]),o("div",M,[t(h,{for:"password",value:"Contraseña"}),t(g,{id:"password",type:"password",class:"mt-1 block w-full",style:{height:"40px"},modelValue:s(e).password,"onUpdate:modelValue":l[1]||(l[1]=r=>s(e).password=r),required:"",autocomplete:"current-password"},null,8,["modelValue"]),t(x,{class:"mt-2",message:s(e).errors.password},null,8,["message"])]),o("div",P,[o("label",T,[t(D,{name:"remember",checked:s(e).remember,"onUpdate:checked":l[2]||(l[2]=r=>s(e).remember=r)},null,8,["checked"]),J])]),o("div",K,[t(A,{class:L(["w-full primary",{"opacity-25":s(e).processing}]),style:{height:"40px",background:"linear-gradient(to right, #0079EA, #0006EB)","box-shadow":"0px 10px 20px -10px #0000FF9D"},disabled:s(e).processing},{default:n(()=>[f(" Ingresar al Sistema ")]),_:1},8,["class","disabled"]),o("div",O,[a.canResetPassword?(i(),$(s(p),{key:0,href:d.route("password.request"),style:{"font-size":".8rem","text-decoration":"none"},class:"text-sm text-gray-600 underline hover:text-gray-900"},{default:n(()=>[f(" ¿Olvidé mi contraseña? ")]),_:1},8,["href"])):_("",!0)])])],32)])])]),_:1})],64))}},te=N(Z,[["__scopeId","data-v-878aced6"]]);export{te as default};