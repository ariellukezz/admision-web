import{r as _,f as l,o as r,s as u,w as t,a as s,u as m,Z as D,d as e,c as y,b as c,g as d,t as k,a8 as b}from"./app-fdc4b8b5.js";import{L as z}from"./LayoutCalificador-a74ec634.js";import{I as $}from"./InboxOutlined-23d3831f.js";import"./DropdownLink-7a00f3bf.js";import"./logotiny-c4b525af.js";/* empty css                                                                          */import"./_plugin-vue_export-helper-c27b6911.js";import"./TopMenu-d9db87d5.js";const F={class:"p-4",style:{background:"white",width:"100%","min-height":"calc(100vh - 90px)","border-radius":"12px"}},H={class:"mt-4",style:{height:"100%","min-height":"calc(100vh - 130px)",position:"relative"}},O=e("div",{class:"mt-1"},[e("svg",{xmlns:"http://www.w3.org/2000/svg",width:"24",height:"24",viewBox:"0 0 24 24",fill:"none",stroke:"blue","stroke-width":"2","stroke-linecap":"round","stroke-linejoin":"round",class:"feather feather-archive"},[e("polyline",{points:"21 8 21 21 3 21 3 8"}),e("rect",{x:"1",y:"3",width:"22",height:"5"}),e("line",{x1:"10",y1:"12",x2:"14",y2:"12"})])],-1),T=e("span",{style:{color:"green"}},"Proceso",-1),A={class:"steps-content mt-4"},E={key:0,style:{display:"flex",height:"calc(100vh - 210px)","border-radius":"12px","align-items":"center","justify-content":"center"}},M=e("div",{class:"flex pb-4",style:{"margin-top":"-10px","margin-bottom":"0px"}},[e("svg",{xmlns:"http://www.w3.org/2000/svg",width:"21",height:"21",viewBox:"0 0 24 24",fill:"none",stroke:"#476175","stroke-width":"2","stroke-linecap":"round","stroke-linejoin":"round",class:"feather feather-folder"},[e("path",{d:"M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"})]),e("span",{class:"ml-2",style:{"font-size":"1rem","font-weight":"bold",color:"#476175"}},"Nuevo Proceso")],-1),Z=e("div",null,[e("label",{class:""},"Nombre Proceso")],-1),q={class:"mt-2"},G={class:"mt-5",style:{width:"100%"}},J={key:1},K={key:2},Q={class:"steps-action flex justify-end",style:{position:"absolute",bottom:"0px",width:"100%"}},R={class:"flex",style:{width:"199%","justify-content":"space-between"}},W={class:"justify-end"},X={class:"ant-upload-drag-icon"},Y=e("p",{class:"ant-upload-text",style:{width:"100%"}},"Haz clic o arrastra archivos a esta área para cargar",-1),ee=e("p",{class:"ant-upload-hint"}," Soporte para carga única o múltiple. Prohibido subir datos de la empresa u otros archivos prohibidos. ",-1),de={__name:"lecturas copy",setup(te){const C=window.location.origin,p=_([]),x=_(!1),L=n=>{const o=n.file.status;o!=="uploading"&&console.log(n.file,p.value),o==="done"?b.success(`${n.file.name} archivo(s) subido(s) exitosamente.`):o==="error"&&b.error(`${n.file.name} falló al subir.`)},S=()=>{p.value=null},a=_(0),B=()=>{a.value++},w=()=>{a.value--},v=_([{title:"First",content:"First-content"},{title:"Second",content:"Second-content"},{title:"Last",content:"Last-content"}]);return(n,o)=>{const f=l("a-step"),N=l("a-steps"),j=l("a-input"),h=l("a-button"),I=l("a-card"),g=l("a-select-option"),P=l("a-select"),U=l("a-upload-dragger"),V=l("a-modal");return r(),u(z,null,{default:t(()=>[s(m(D),{name:"Subir Ides"}),e("div",F,[e("div",H,[e("div",null,[s(N,{current:a.value},{default:t(()=>[s(f,{key:"uno",status:"finish"},{icon:t(()=>[O]),title:t(()=>[T]),_:1}),s(f,{key:"dos",title:"segundo",status:""}),s(f,{key:"tres",title:"tercero",status:""})]),_:1},8,["current"])]),e("div",A,[a.value===0?(r(),y("div",E,[s(I,{class:"",style:{"min-width":"320px","border-radius":"12px"}},{default:t(()=>[M,e("div",null,[Z,e("div",q,[s(j,{value:n.value,"onUpdate:value":o[0]||(o[0]=i=>n.value=i),placeholder:"Nombre del proceso"},null,8,["value"])])]),e("div",G,[a.value===0?(r(),u(h,{key:0,onClick:w,style:{color:"white",width:"100%",background:"#466175","border-radius":"5px"}},{default:t(()=>[c("Continuar")]),_:1})):d("",!0)])]),_:1})])):d("",!0),a.value===1?(r(),y("div",J," uno "+k(v.value[a.value].content),1)):d("",!0),a.value===2?(r(),y("div",K," dos "+k(v.value[a.value].content),1)):d("",!0)]),e("div",Q,[e("div",R,[a.value>0?(r(),u(h,{key:0,onClick:w,style:{color:"#476175",border:"1px solid #466175","border-radius":"5px",width:"100px"}},{default:t(()=>[c("Anterior")]),_:1})):d("",!0),a.value<v.value.length-1?(r(),u(h,{key:1,onClick:B,type:"primary",style:{background:"#476175",border:"none","border-radius":"5px",width:"100px"}},{default:t(()=>[c("Siguiente")]),_:1})):d("",!0),a.value==v.value.length-1?(r(),u(h,{key:2,type:"primary",style:{background:"#476175",border:"none","border-radius":"5px",width:"100px"},onClick:o[1]||(o[1]=i=>m(b).success("Processing complete!"))},{default:t(()=>[c(" Terminar ")]),_:1})):d("",!0)])])]),s(V,{visible:x.value,"onUpdate:visible":o[4]||(o[4]=i=>x.value=i),title:"Subir Ides",onOk:S,centered:!0,style:{"max-height":"calc(100vh - 100px)","overflow-x":"scroll",cursor:"pointer"}},{default:t(()=>[e("div",W,[s(P,{value:n.area,"onUpdate:value":o[2]||(o[2]=i=>n.area=i)},{default:t(()=>[s(g,{value:1},{default:t(()=>[c("Biomédicas")]),_:1}),s(g,{value:2},{default:t(()=>[c("Ingenierías")]),_:1}),s(g,{value:3},{default:t(()=>[c("Sociales")]),_:1})]),_:1},8,["value"])]),s(U,{fileList:p.value,"onUpdate:fileList":o[3]||(o[3]=i=>p.value=i),name:"file",multiple:!0,action:m(C)+"/calificacion/carga-ide/",onChange:L,onDrop:n.handleDrop,"list-type":"picture"},{default:t(()=>[e("p",X,[s(m($))]),Y,ee]),_:1},8,["fileList","action","onDrop"])]),_:1},8,["visible"])])]),_:1})}}};export{de as default};