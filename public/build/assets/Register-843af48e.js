import{v as f,c as w,a as e,u as s,w as r,F as _,o as g,X as V,G as d,d as t,x as v,b as u,y as x}from"./app-e1f113b2.js";import{G as y,A as b}from"./ApplicationLogo-e9a92bd7.js";import{_ as m,a as i,b as n}from"./TextInput-8d2539dd.js";import{_ as h}from"./PrimaryButton-cdeb779f.js";import"./_plugin-vue_export-helper-c27b6911.js";import"./logotiny-c4b525af.js";import"./logoDAD-c76d0a0b.js";const k=["onSubmit"],q={class:"mt-3"},N={class:"mt-3"},U={class:"mt-3"},$={class:"mt-4 flex flex-col items-end"},P={__name:"Register",setup(A){const a=f({name:"",email:"",password:"",password_confirmation:"",terms:!1}),p=()=>{a.post(route("register"),{onFinish:()=>a.reset("password","password_confirmation")})};return(c,o)=>(g(),w(_,null,[e(s(V),{title:"Register"}),e(y,null,{default:r(()=>[e(s(d),{href:"/",class:"flex items-center justify-center"},{default:r(()=>[e(b,{class:"h-20 w-20 fill-current text-gray-500"})]),_:1}),t("form",{onSubmit:x(p,["prevent"])},[t("div",null,[e(m,{for:"name",value:"Name"}),e(i,{id:"name",type:"text",class:"mt-1 block w-full",modelValue:s(a).name,"onUpdate:modelValue":o[0]||(o[0]=l=>s(a).name=l),required:"",autofocus:"",autocomplete:"name"},null,8,["modelValue"]),e(n,{class:"mt-2",message:s(a).errors.name},null,8,["message"])]),t("div",q,[e(m,{for:"email",value:"Email"}),e(i,{id:"email",type:"email",class:"mt-1 block w-full",modelValue:s(a).email,"onUpdate:modelValue":o[1]||(o[1]=l=>s(a).email=l),required:"",autocomplete:"username"},null,8,["modelValue"]),e(n,{class:"mt-2",message:s(a).errors.email},null,8,["message"])]),t("div",N,[e(m,{for:"password",value:"Password"}),e(i,{id:"password",type:"password",class:"mt-1 block w-full",modelValue:s(a).password,"onUpdate:modelValue":o[2]||(o[2]=l=>s(a).password=l),required:"",autocomplete:"new-password"},null,8,["modelValue"]),e(n,{class:"mt-2",message:s(a).errors.password},null,8,["message"])]),t("div",U,[e(m,{for:"password_confirmation",value:"Confirm Password"}),e(i,{id:"password_confirmation",type:"password",class:"mt-1 block w-full",modelValue:s(a).password_confirmation,"onUpdate:modelValue":o[3]||(o[3]=l=>s(a).password_confirmation=l),required:"",autocomplete:"new-password"},null,8,["modelValue"]),e(n,{class:"mt-2",message:s(a).errors.password_confirmation},null,8,["message"])]),t("div",$,[e(h,{class:v(["w-full",{"opacity-25":s(a).processing}]),disabled:s(a).processing},{default:r(()=>[u(" Register ")]),_:1},8,["class","disabled"]),e(s(d),{href:c.route("login"),class:"mt-4 text-sm text-gray-600 underline hover:text-gray-900"},{default:r(()=>[u(" Already registered? ")]),_:1},8,["href"])])],40,k)]),_:1})],64))}};export{P as default};