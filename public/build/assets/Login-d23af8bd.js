import{i as v,z as w,I as b,o as n,c as m,T as k,a as t,u as s,w as i,F as V,Z as B,d as l,J as p,t as C,g as f,H as S,B as I,b as _,k as L,p as $,h as F}from"./app-69dc42f1.js";import{G as A,A as N}from"./ApplicationLogo-755a00da.js";import{_ as h,a as x,b as g}from"./TextInput-5a48da83.js";import{_ as U}from"./PrimaryButton-a8e60714.js";import{_ as j}from"./_plugin-vue_export-helper-c27b6911.js";import"./logotiny-c4b525af.js";import"./logoDAD-c76d0a0b.js";const q=["value"],z={__name:"Checkbox",props:{checked:{type:[Array,Boolean],default:!1},value:{default:null}},emits:["update:checked"],setup(a,{emit:e}){const d=e,c=a,o=v({get(){return c.checked},set(r){d("update:checked",r)}});return(r,u)=>w((n(),m("input",{type:"checkbox",value:a.value,"onUpdate:modelValue":u[0]||(u[0]=y=>o.value=y),class:"text-indigo-600"},null,8,q)),[[b,o.value]])}};const D=a=>($("data-v-0fff58d4"),a=a(),F(),a),E={style:{width:"300px"}},R={key:0,class:"mb-4 text-sm font-medium text-green-600"},G=["onSubmit"],M={class:"mt-3"},P={class:"mt-2 flex justify-between"},T={class:"inline-flex items-center"},H=D(()=>l("span",{class:"mx-2 text-sm text-gray-600"},"Recuerdame",-1)),J={class:"mt-6"},O={style:{display:"flex","justify-content":"center"},class:"mt-2"},Z={__name:"Login",props:{canResetPassword:Boolean,status:String},setup(a){const e=k({email:"",password:"",remember:!1}),d=()=>{e.post(route("login"),{onFinish:()=>e.reset("password")})};return(c,o)=>(n(),m(V,null,[t(s(B),{title:"Log in"}),t(A,null,{default:i(()=>[l("div",E,[t(s(p),{href:"/",class:"flex items-center justify-center"},{default:i(()=>[t(N,{class:"h-20 fill-current text-gray-500"})]),_:1}),a.status?(n(),m("div",R,C(a.status),1)):f("",!0),l("form",{onSubmit:S(d,["prevent"])},[l("div",null,[t(h,{for:"email",value:"Correo"}),t(x,{id:"email",type:"email",class:"mt-1 block w-full",style:{height:"35px"},modelValue:s(e).email,"onUpdate:modelValue":o[0]||(o[0]=r=>s(e).email=r),required:"",autofocus:"",autocomplete:"username"},null,8,["modelValue"]),t(g,{class:"mt-2",message:s(e).errors.email},null,8,["message"])]),l("div",M,[t(h,{for:"password",value:"Contraseña"}),t(x,{id:"password",type:"password",class:"mt-1 block w-full",style:{height:"35px"},modelValue:s(e).password,"onUpdate:modelValue":o[1]||(o[1]=r=>s(e).password=r),required:"",autocomplete:"current-password"},null,8,["modelValue"]),t(g,{class:"mt-2",message:s(e).errors.password},null,8,["message"])]),l("div",P,[l("label",T,[t(z,{name:"remember",checked:s(e).remember,"onUpdate:checked":o[2]||(o[2]=r=>s(e).remember=r)},null,8,["checked"]),H])]),l("div",J,[t(U,{class:I(["w-full primary",{"opacity-25":s(e).processing}]),style:{background:"linear-gradient(to right, #0079EA, #0006EB)","box-shadow":"0px 10px 20px -10px #0000FF9D"},disabled:s(e).processing},{default:i(()=>[_(" Ingresar al Sistema ")]),_:1},8,["class","disabled"]),l("div",O,[a.canResetPassword?(n(),L(s(p),{key:0,href:c.route("password.request"),style:{"font-size":".8rem","text-decoration":"none"},class:"text-sm text-gray-600 underline hover:text-gray-900"},{default:i(()=>[_(" ¿Olvidé mi contraseña? ")]),_:1},8,["href"])):f("",!0)])])],40,G)])]),_:1})],64))}},te=j(Z,[["__scopeId","data-v-0fff58d4"]]);export{te as default};