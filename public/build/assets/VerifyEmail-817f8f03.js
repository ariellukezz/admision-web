import{v as p,g,c as a,a as e,u as t,w as s,F as _,o as n,X as h,k as r,f as y,d as o,n as b,b as c,e as v}from"./app-24548a49.js";import{G as x,A as k}from"./ApplicationLogo-88b3cf79.js";import{_ as w}from"./PrimaryButton-5d6a7cac.js";import"./_plugin-vue_export-helper-c27b6911.js";import"./logotiny-c4b525af.js";import"./logoDAD-c76d0a0b.js";const V=o("div",{class:"mb-4 text-sm text-gray-600"}," Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another. ",-1),B={key:0,class:"mb-4 text-sm font-medium text-green-600"},E=["onSubmit"],L={class:"mt-4 flex items-center justify-between"},T={__name:"VerifyEmail",props:{status:String},setup(l){const d=l,i=p(),m=()=>{i.post(route("verification.send"))},u=g(()=>d.status==="verification-link-sent");return(f,N)=>(n(),a(_,null,[e(t(h),{title:"Email Verification"}),e(x,null,{default:s(()=>[e(t(r),{href:"/",class:"mb-4 flex items-center justify-center"},{default:s(()=>[e(k,{class:"h-20 w-20 fill-current text-gray-500"})]),_:1}),V,t(u)?(n(),a("div",B," A new verification link has been sent to the email address you provided during registration. ")):y("",!0),o("form",{onSubmit:v(m,["prevent"])},[o("div",L,[e(w,{class:b({"opacity-25":t(i).processing}),disabled:t(i).processing},{default:s(()=>[c(" Resend Verification Email ")]),_:1},8,["class","disabled"]),e(t(r),{href:f.route("logout"),method:"post",as:"button",class:"text-sm text-gray-600 underline hover:text-gray-900"},{default:s(()=>[c(" Log Out ")]),_:1},8,["href"])])],40,E)]),_:1})],64))}};export{T as default};