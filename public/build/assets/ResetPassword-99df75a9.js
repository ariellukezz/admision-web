import{v as f,c as w,a as s,u as o,w as r,F as _,o as g,X as V,d as l,i as b,b as k,j as v,g as y}from"./app-c81d9251.js";import{G as x,A as P}from"./ApplicationLogo-88c0ef92.js";import{_ as i,a as m,b as n}from"./TextInput-8acda008.js";import{_ as h}from"./PrimaryButton-b9ff15e8.js";import"./_plugin-vue_export-helper-c27b6911.js";import"./logotiny-c4b525af.js";import"./logoDAD-c76d0a0b.js";const C=["onSubmit"],L={class:"mt-3"},S={class:"mt-3"},$={class:"mt-4 flex items-center justify-end"},E={__name:"ResetPassword",props:{email:String,token:String},setup(u){const d=u,e=f({token:d.token,email:d.email,password:"",password_confirmation:""}),p=()=>{e.post(route("password.update"),{onFinish:()=>e.reset("password","password_confirmation")})};return(j,a)=>{const c=y("Link");return g(),w(_,null,[s(o(V),{title:"Reset Password"}),s(x,null,{default:r(()=>[s(c,{href:"/",class:"mb-4 flex items-center justify-center"},{default:r(()=>[s(P,{class:"h-20 w-20 fill-current text-gray-500"})]),_:1}),l("form",{onSubmit:v(p,["prevent"])},[l("div",null,[s(i,{for:"email",value:"Email"}),s(m,{id:"email",type:"email",class:"mt-1 block w-full",modelValue:o(e).email,"onUpdate:modelValue":a[0]||(a[0]=t=>o(e).email=t),required:"",autofocus:"",autocomplete:"username"},null,8,["modelValue"]),s(n,{class:"mt-2",message:o(e).errors.email},null,8,["message"])]),l("div",L,[s(i,{for:"password",value:"Password"}),s(m,{id:"password",type:"password",class:"mt-1 block w-full",modelValue:o(e).password,"onUpdate:modelValue":a[1]||(a[1]=t=>o(e).password=t),required:"",autocomplete:"new-password"},null,8,["modelValue"]),s(n,{class:"mt-2",message:o(e).errors.password},null,8,["message"])]),l("div",S,[s(i,{for:"password_confirmation",value:"Confirm Password"}),s(m,{id:"password_confirmation",type:"password",class:"mt-1 block w-full",modelValue:o(e).password_confirmation,"onUpdate:modelValue":a[2]||(a[2]=t=>o(e).password_confirmation=t),required:"",autocomplete:"new-password"},null,8,["modelValue"]),s(n,{class:"mt-2",message:o(e).errors.password_confirmation},null,8,["message"])]),l("div",$,[s(h,{class:b(["w-full",{"opacity-25":o(e).processing}]),disabled:o(e).processing},{default:r(()=>[k(" Reset Password ")]),_:1},8,["class","disabled"])])],40,C)]),_:1})],64)}}};export{E as default};