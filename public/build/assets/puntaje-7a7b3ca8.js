import{_ as h}from"./_plugin-vue_export-helper-c27b6911.js";import{o as e,c as s,F as r,i as t,G as m,d as c}from"./app-fdc4b8b5.js";const v={data(){return{seatGroups:[{rows:4,cols:10},{rows:4,cols:10},{rows:4,cols:10},{rows:4,cols:10},{rows:4,cols:10},{rows:4,cols:10},{rows:4,cols:10},{rows:4,cols:10}],selectedSeats:[{group:2,row:1,col:2},{group:3,row:3,col:4}]}},methods:{isSeatSelected(a,l,n){return this.selectedSeats.some(o=>o.group===a&&o.row===l&&o.col===n)}}},f={class:"seat-map"},g=c("svg",{xmlns:"http://www.w3.org/2000/svg",width:"24",height:"24",viewBox:"0 0 24 24",fill:"none",stroke:"currentColor","stroke-width":"2","stroke-linecap":"round","stroke-linejoin":"round",class:"feather feather-user"},[c("path",{d:"M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"}),c("circle",{cx:"12",cy:"7",r:"4"})],-1),k=[g];function x(a,l,n,o,p,w){return e(),s("div",f,[(e(!0),s(r,null,t(p.seatGroups,(u,i)=>(e(),s("div",{key:i,class:"seat-group"},[(e(!0),s(r,null,t(u.rows,d=>(e(),s("div",{key:d,class:"seat-row"},[(e(!0),s(r,null,t(u.cols,_=>(e(),s("div",{key:_,class:m(["seat",{selected:w.isSeatSelected(i+1,d,_)}])},k,2))),128))]))),128))]))),128))])}const B=h(v,[["render",x]]);export{B as default};