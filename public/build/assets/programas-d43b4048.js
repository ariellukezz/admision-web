import{r as c,e as x,q as X,f as s,o as i,c as p,a as t,u as y,w as l,d as v,F as P,l as A,Z as Y,b as d,S as ee,t as h,g as u,s as E,E as ae,D as te,v as oe,n as le}from"./app-32563e7d.js";import{A as ne}from"./AuthenticatedLayout-d8b51e85.js";import{F as se}from"./FormOutlined-82af32ee.js";import"./DropdownLink-b76990c1.js";import"./_plugin-vue_export-helper-c27b6911.js";import"./logotiny-c4b525af.js";/* empty css                                                                            */import"./TeamOutlined-4d59c187.js";const ie={class:"bg-white overflow-hidden shadow-sm sm:rounded-lg p-4",style:{height:"calc(100vh - 98px)"}},de={class:"mr-3"},ue={class:"flex justify-between",style:{position:"relative"}},re={class:"mr-2",style:{position:"absolute",left:"8px",top:"3px"}},ce={style:{}},ve={key:0},pe={style:{"font-size":".9rem"}},me={key:1},_e={style:{"font-size":".9rem"}},fe={key:2},ge={style:{"font-size":".9rem"}},ye={key:3,class:"flex",style:{"justify-content":"center"}},he={key:4,class:"flex",style:{"justify-content":"center"}},be={key:0},ke={key:1},Ce={class:"flex",style:{"justify-content":"flex-end"}},ze={__name:"programas",setup(Ie){const I=c(""),w=c(1),O=c(null);c("PUNO");const N=c([]),f=c(!1),B=c(""),r=c([]),a=c({id:null,codigo:"",nombre:"",nivel_academico:"CARRERA PROFESIONAL",tipo_autorizacion:"RECONOCIDO POR LIC.",id_facultad:1,estado:!0,area:"BIOMEDICAS"}),U=()=>{f.value=!0};x(I,(e,o)=>{b()}),x(B,(e,o)=>{getDepartamentos()}),x(f,(e,o)=>{f.value==!1&&a.value.id!=null&&(a.value.id=null,a.value.codigo=null,a.value.nombre=null,a.value.estado=!0)}),x(w,(e,o)=>{b()});const j=e=>{f.value=!0,a.value.id=e.id,a.value.codigo=e.codigo,a.value.nombre=e.nombre,a.value.nivel_academico=e.nivel_academico,a.value.tipo_autorizacion=e.tipo_autorizacion,a.value.id_facultad=e.id_fac,e.estado==1?a.value.estado=!0:a.value.estado=!1,a.value.area=e.area},L=async()=>{let e=await A.get("get-facultades");N.value=e.data.datos},b=async(e="")=>{let o=await A.post("programas/get-programas?page="+w.value,{term:I.value});r.value=o.data.datos.data,O.value=o.data.datos.total},M=()=>{let e={id:a.value.id,codigo:a.value.codigo,nombre:a.value.nombre,nivel_academico:a.value.nivel_academico,tipo_autorizacion:a.value.tipo_autorizacion,estado:a.value.estado,id_facultad:a.value.id_facultad,area:a.value.area};A.post("save-programa",e).then(o=>{b(),V("success",o.data.titulo,o.data.mensaje),f.value=!1,a.value.codigo=null,a.value.id=null,a.value.nombre=""})},G=e=>{A.get("eliminar-programa/"+e.id).then(o=>{b(),V("warning","PROGRAMA ELIMINADO",o.data.mensaje)})},$=[{title:"Cod",dataIndex:"codigo",width:"60px",align:"center",responsive:["md"]},{title:"Nombre",dataIndex:"nombre"},{title:"Area",dataIndex:"area",align:"center",width:"100px",responsive:["md"]},{title:"Fun.",dataIndex:"estado",align:"center",width:"60px",responsive:["md"]},{title:"Acciones",dataIndex:"acciones",width:"90px",align:"center"}],z=c([]),K=e=>{console.log("selectedRowKeys changed: ",e),z.value=e};X(()=>({selectedRowKeys:y(z),onChange:K,hideDefaultSelections:!0}));const V=(e,o,m)=>{le[e]({message:o,description:m})},T=e=>{console.log("Detalle:",e)};return L(),b(),(e,o)=>{const m=s("a-button"),F=s("a-input"),q=s("row"),k=s("a-tag"),Z=s("a-table"),H=s("a-pagination"),C=s("a-form-item"),D=s("a-select"),S=s("a-select-option"),J=s("a-switch"),Q=s("a-form"),W=s("a-modal");return i(),p(P,null,[t(y(Y),{title:"Procesos"}),t(ne,null,{default:l(()=>[v("div",ie,[t(q,{class:"flex justify-between mb-4"},{default:l(()=>[v("div",de,[t(m,{type:"primary",style:{"border-radius":"5px",background:"#476175"},onClick:U},{default:l(()=>[d("Agregar")]),_:1})]),v("div",ue,[t(F,{type:"text",placeholder:"Buscar",value:I.value,"onUpdate:value":o[0]||(o[0]=n=>I.value=n),style:{"max-width":"300px","padding-left":"30px"}},null,8,["value"]),v("div",re,[t(y(ee))])])]),_:1}),v("div",ce,[t(Z,{columns:$,"data-source":r.value,pagination:!1,size:"small",scroll:{x:380,y:"calc(100vh - 240px)"}},{bodyCell:l(({column:n,index:_,record:g})=>[n.dataIndex==="codigo"?(i(),p("div",ve,[v("span",pe,h(g.codigo),1)])):u("",!0),n.dataIndex==="nombre"?(i(),p("div",me,[v("span",_e,h(g.nombre),1)])):u("",!0),n.dataIndex==="facultad"?(i(),p("div",fe,[v("span",ge,h(g.facultad),1)])):u("",!0),n.dataIndex==="area"?(i(),p("div",ye,[r.value[_].area=="BIOMÉDICAS"?(i(),E(k,{key:0,style:{"font-size":".8rem"},color:"cyan"},{default:l(()=>[d(h(r.value[_].area),1)]),_:2},1024)):u("",!0),r.value[_].area=="SOCIALES"?(i(),E(k,{key:1,style:{"font-size":".8rem"},color:"purple"},{default:l(()=>[d(h(r.value[_].area),1)]),_:2},1024)):u("",!0),r.value[_].area=="INGENIERÍAS"?(i(),E(k,{key:2,style:{"font-size":".8rem"},color:"blue"},{default:l(()=>[d(h(r.value[_].area),1)]),_:2},1024)):u("",!0)])):u("",!0),n.dataIndex==="estado"?(i(),p("div",he,[r.value[_].estado==1?(i(),p("div",be,[t(k,{color:"green"},{default:l(()=>[d("Si")]),_:1})])):u("",!0),r.value[_].estado==0?(i(),p("div",ke,[t(k,{color:"red"},{default:l(()=>[d("No")]),_:1})])):u("",!0)])):u("",!0),n.dataIndex==="acciones"?(i(),p(P,{key:5},[t(m,{type:"",onClick:R=>T(g),style:{"border-radius":"4px",background:"none",color:"green"},size:"small"},{icon:l(()=>[t(y(ae))]),_:2},1032,["onClick"]),t(m,{type:"",onClick:R=>j(g),style:{"border-radius":"4px",background:"none",color:"gray"},size:"small"},{icon:l(()=>[t(y(se))]),_:2},1032,["onClick"]),t(m,{class:"",onClick:R=>G(g),style:{"border-radius":"4px",background:"none",color:"red"},shape:"",size:"small"},{icon:l(()=>[t(y(te))]),_:2},1032,["onClick"])],64)):u("",!0)]),_:1},8,["data-source","scroll"])]),v("div",Ce,[t(H,{current:w.value,"onUpdate:current":o[1]||(o[1]=n=>w.value=n),simple:"","page-size":"50",total:O.value},null,8,["current","total"])])])]),_:1}),v("div",null,[t(W,{visible:f.value,"onUpdate:visible":o[8]||(o[8]=n=>f.value=n),title:a.value.id==null?"Nuevo Programa":"Editar Programa",style:{"margin-top":"-40px"}},{footer:l(()=>[t(m,{style:{"margin-left":"10px"},onClick:e.resetForm},{default:l(()=>[d("Cancelar")]),_:1},8,["onClick"]),t(m,{type:"primary",onClick:o[7]||(o[7]=n=>M())},{default:l(()=>[d("Guardar")]),_:1})]),default:l(()=>[t(Q,oe({ref:"formRef",name:"custom-validation",model:e.formState,rules:e.rules},e.layout,{onFinish:e.handleFinish,onValidate:e.handleValidate,onFinishFailed:e.handleFinishFailed}),{default:l(()=>[t(C,{"has-feedback":"",label:"Codigo",name:"codigo"},{default:l(()=>[t(F,{type:"text",value:a.value.codigo,"onUpdate:value":o[2]||(o[2]=n=>a.value.codigo=n),autocomplete:"off"},null,8,["value"])]),_:1}),t(C,{"has-feedback":"",label:"Nombre",name:"nombre"},{default:l(()=>[t(F,{type:"text",value:a.value.nombre,"onUpdate:value":o[3]||(o[3]=n=>a.value.nombre=n),autocomplete:"off"},null,8,["value"])]),_:1}),t(C,{"has-feedback":"",label:"Facultad",name:"facultad"},{default:l(()=>[t(D,{options:N.value,ref:"Tipo",style:{width:"100%"},onFocus:e.focus,onChange:e.handleChange,value:a.value.id_facultad,"onUpdate:value":o[4]||(o[4]=n=>a.value.id_facultad=n)},null,8,["options","onFocus","onChange","value"])]),_:1}),t(C,{"has-feedback":"",label:"Area",name:"area"},{default:l(()=>[t(D,{style:{width:"100%"},onFocus:e.focus,onChange:e.handleChange,value:a.value.area,"onUpdate:value":o[5]||(o[5]=n=>a.value.area=n)},{default:l(()=>[t(S,{value:"BIOMÉDICAS"},{default:l(()=>[d(" BIOMEDICAS ")]),_:1}),t(S,{value:"INGENIERÍAS"},{default:l(()=>[d(" INGENIERÍAS ")]),_:1}),t(S,{value:"SOCIALES"},{default:l(()=>[d(" SOCIALES ")]),_:1})]),_:1},8,["onFocus","onChange","value"])]),_:1}),t(C,{"has-feedback":"",label:"Vigente",name:"estado"},{default:l(()=>[t(J,{checked:a.value.estado,"onUpdate:checked":o[6]||(o[6]=n=>a.value.estado=n)},null,8,["checked"])]),_:1})]),_:1},16,["model","rules","onFinish","onValidate","onFinishFailed"])]),_:1},8,["visible","title"])])],64)}}};export{ze as default};