import{T as m,f as c,o as d,c as u,a as s,u as o,w as t,F as p,Z as f,d as a,B as _,b as w,H as b}from"./app-8a9b4894.js";import{G as g,A as h}from"./ApplicationLogo-1b6d409e.js";import{_ as x,a as y,b as v}from"./TextInput-598f82fe.js";import{_ as C}from"./PrimaryButton-98d8babb.js";import"./_plugin-vue_export-helper-c27b6911.js";import"./logotiny-c4b525af.js";import"./logoDAD-c76d0a0b.js";const V=a("div",{class:"mb-4 text-sm text-gray-600"}," This is a secure area of the application. Please confirm your password before continuing. ",-1),k=["onSubmit"],B={class:"mt-4 flex justify-end"},S={__name:"ConfirmPassword",setup(L){const e=m({password:""}),i=()=>{e.post(route("password.confirm"),{onFinish:()=>e.reset()})};return(P,r)=>{const n=c("Link");return d(),u(p,null,[s(o(f),{title:"Confirm Password"}),s(g,null,{default:t(()=>[s(n,{href:"/",class:"mb-4 flex items-center justify-center"},{default:t(()=>[s(h,{class:"h-10 w-10 fill-current text-gray-500"})]),_:1}),V,a("form",{onSubmit:b(i,["prevent"])},[a("div",null,[s(x,{for:"password",value:"Password"}),s(y,{id:"password",type:"password",class:"mt-1 block w-full",modelValue:o(e).password,"onUpdate:modelValue":r[0]||(r[0]=l=>o(e).password=l),required:"",autocomplete:"current-password",autofocus:""},null,8,["modelValue"]),s(v,{class:"mt-2",message:o(e).errors.password},null,8,["message"])]),a("div",B,[s(C,{class:_(["w-full",{"opacity-25":o(e).processing}]),disabled:o(e).processing},{default:t(()=>[w(" Confirm ")]),_:1},8,["class","disabled"])])],40,k)]),_:1})],64)}}};export{S as default};