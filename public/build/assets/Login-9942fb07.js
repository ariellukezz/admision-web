import{A as y,k as v,B as w,u as s,o as d,c as m,C as b,x as k,a as t,w as i,F as V,X as C,d as l,E as u,t as B,f as p,z as S,y as L,b as f,l as $,p as A,h as F}from"./app-7a7acdb7.js";import{G as I,A as E}from"./ApplicationLogo-2e48425c.js";import{_,a as h,b as x}from"./TextInput-819e0a29.js";import{_ as N}from"./PrimaryButton-670cb6f7.js";import{_ as R}from"./_plugin-vue_export-helper-c27b6911.js";import"./logotiny-c4b525af.js";import"./logoDAD-c76d0a0b.js";const U=["value"],j={__name:"Checkbox",props:{checked:{type:[Array,Boolean],default:!1},value:{default:null}},emits:["update:checked"],setup(a,{emit:e}){const c=a,n=y({get(){return c.checked},set(o){e("update:checked",o)}});return(o,r)=>v((d(),m("input",{type:"checkbox",value:a.value,"onUpdate:modelValue":r[0]||(r[0]=g=>b(n)?n.value=g:null),class:"text-indigo-600"},null,8,U)),[[w,s(n)]])}};const q=a=>(A("data-v-0fff58d4"),a=a(),F(),a),z={style:{width:"300px"}},D={key:0,class:"mb-4 text-sm font-medium text-green-600"},G=["onSubmit"],M={class:"mt-3"},P={class:"mt-2 flex justify-between"},O={class:"inline-flex items-center"},T=q(()=>l("span",{class:"mx-2 text-sm text-gray-600"},"Recuerdame",-1)),X={class:"mt-6"},H={style:{display:"flex","justify-content":"center"},class:"mt-2"},J={__name:"Login",props:{canResetPassword:Boolean,status:String},setup(a){const e=k({email:"",password:"",remember:!1}),c=()=>{e.post(route("login"),{onFinish:()=>e.reset("password")})};return(n,o)=>(d(),m(V,null,[t(s(C),{title:"Log in"}),t(I,null,{default:i(()=>[l("div",z,[t(s(u),{href:"/",class:"flex items-center justify-center"},{default:i(()=>[t(E,{class:"h-20 fill-current text-gray-500"})]),_:1}),a.status?(d(),m("div",D,B(a.status),1)):p("",!0),l("form",{onSubmit:S(c,["prevent"])},[l("div",null,[t(_,{for:"email",value:"Correo"}),t(h,{id:"email",type:"email",class:"mt-1 block w-full",style:{height:"35px"},modelValue:s(e).email,"onUpdate:modelValue":o[0]||(o[0]=r=>s(e).email=r),required:"",autofocus:"",autocomplete:"username"},null,8,["modelValue"]),t(x,{class:"mt-2",message:s(e).errors.email},null,8,["message"])]),l("div",M,[t(_,{for:"password",value:"Contraseña"}),t(h,{id:"password",type:"password",class:"mt-1 block w-full",style:{height:"35px"},modelValue:s(e).password,"onUpdate:modelValue":o[1]||(o[1]=r=>s(e).password=r),required:"",autocomplete:"current-password"},null,8,["modelValue"]),t(x,{class:"mt-2",message:s(e).errors.password},null,8,["message"])]),l("div",P,[l("label",O,[t(j,{name:"remember",checked:s(e).remember,"onUpdate:checked":o[2]||(o[2]=r=>s(e).remember=r)},null,8,["checked"]),T])]),l("div",X,[t(N,{class:L(["w-full primary",{"opacity-25":s(e).processing}]),style:{background:"linear-gradient(to right, #0079EA, #0006EB)","box-shadow":"0px 10px 20px -10px #0000FF9D"},disabled:s(e).processing},{default:i(()=>[f(" Ingresar al Sistema ")]),_:1},8,["class","disabled"]),l("div",H,[a.canResetPassword?(d(),$(s(u),{key:0,href:n.route("password.request"),style:{"font-size":".8rem","text-decoration":"none"},class:"text-sm text-gray-600 underline hover:text-gray-900"},{default:i(()=>[f(" ¿Olvidé mi contraseña? ")]),_:1},8,["href"])):p("",!0)])])],40,G)])]),_:1})],64))}},te=R(J,[["__scopeId","data-v-0fff58d4"]]);export{te as default};