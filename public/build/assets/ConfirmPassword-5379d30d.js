import{v as m,c,a as s,u as o,w as t,F as d,o as u,X as p,d as a,x as f,b as _,y as w,g as b}from"./app-45ca54b3.js";import{G as g,A as x}from"./ApplicationLogo-116037e6.js";import{_ as h,a as y,b as v}from"./TextInput-35a924c8.js";import{_ as C}from"./PrimaryButton-2b4e5065.js";import"./_plugin-vue_export-helper-c27b6911.js";import"./logotiny-c4b525af.js";import"./logoDAD-c76d0a0b.js";const V=a("div",{class:"mb-4 text-sm text-gray-600"}," This is a secure area of the application. Please confirm your password before continuing. ",-1),k=["onSubmit"],L={class:"mt-4 flex justify-end"},T={__name:"ConfirmPassword",setup(P){const e=m({password:""}),i=()=>{e.post(route("password.confirm"),{onFinish:()=>e.reset()})};return($,r)=>{const n=b("Link");return u(),c(d,null,[s(o(p),{title:"Confirm Password"}),s(g,null,{default:t(()=>[s(n,{href:"/",class:"mb-4 flex items-center justify-center"},{default:t(()=>[s(x,{class:"h-10 w-10 fill-current text-gray-500"})]),_:1}),V,a("form",{onSubmit:w(i,["prevent"])},[a("div",null,[s(h,{for:"password",value:"Password"}),s(y,{id:"password",type:"password",class:"mt-1 block w-full",modelValue:o(e).password,"onUpdate:modelValue":r[0]||(r[0]=l=>o(e).password=l),required:"",autocomplete:"current-password",autofocus:""},null,8,["modelValue"]),s(v,{class:"mt-2",message:o(e).errors.password},null,8,["message"])]),a("div",L,[s(C,{class:f(["w-full",{"opacity-25":o(e).processing}]),disabled:o(e).processing},{default:t(()=>[_(" Confirm ")]),_:1},8,["class","disabled"])])],40,k)]),_:1})],64)}}};export{T as default};