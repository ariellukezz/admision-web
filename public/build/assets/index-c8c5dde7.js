import{a,A as re,r as c,e as ie,x as de,f as d,o as x,c as h,u as w,w as s,d as n,F as z,Z as ue,b,S as ce,t as F,g as P,s as pe,D as me,v as ve,k as fe,n as _e}from"./app-9aed8e38.js";import{A as ge}from"./AuthenticatedLayout-fcda8a7f.js";import{F as xe}from"./FormOutlined-7869783b.js";import"./DropdownLink-2489b3f9.js";import"./_plugin-vue_export-helper-c27b6911.js";import"./logotiny-c4b525af.js";/* empty css                                                                            */import"./TeamOutlined-87e6ce6d.js";var ye={icon:{tag:"svg",attrs:{viewBox:"64 64 896 896",focusable:"false"},children:[{tag:"path",attrs:{d:"M832 464h-68V240c0-70.7-57.3-128-128-128H388c-70.7 0-128 57.3-128 128v224h-68c-17.7 0-32 14.3-32 32v384c0 17.7 14.3 32 32 32h640c17.7 0 32-14.3 32-32V496c0-17.7-14.3-32-32-32zM332 240c0-30.9 25.1-56 56-56h248c30.9 0 56 25.1 56 56v224H332V240zm460 600H232V536h560v304zM484 701v53c0 4.4 3.6 8 8 8h40c4.4 0 8-3.6 8-8v-53a48.01 48.01 0 10-56 0z"}}]},name:"lock",theme:"outlined"};const he=ye;function L(u){for(var r=1;r<arguments.length;r++){var i=arguments[r]!=null?Object(arguments[r]):{},g=Object.keys(i);typeof Object.getOwnPropertySymbols=="function"&&(g=g.concat(Object.getOwnPropertySymbols(i).filter(function(p){return Object.getOwnPropertyDescriptor(i,p).enumerable}))),g.forEach(function(p){be(u,p,i[p])})}return u}function be(u,r,i){return r in u?Object.defineProperty(u,r,{value:i,enumerable:!0,configurable:!0,writable:!0}):u[r]=i,u}var U=function(r,i){var g=L({},r,i.attrs);return a(re,L({},g,{icon:he}),null)};U.displayName="LockOutlined";U.inheritAttrs=!1;const ke=U,we={class:"bg-white overflow-hidden shadow-sm sm:rounded-lg p-5"},Pe={class:"flex justify-between mb-2"},Ce={class:"mr-3"},Oe={class:"flex justify-between",style:{position:"relative"}},Ie={class:"mr-2",style:{position:"absolute",left:"10px",top:"6px"}},ze={key:0,style:{"text-transform":"capitalize"},size:"small"},Fe={key:0,style:{"text-align":"center"}},Ue={key:1,style:{"text-align":"center"}},je={class:"flex justify-end"},Ve=n("div",{class:"mr-2"},"Estado",-1),Se={style:{"margin-top":"-2px"}},Le=n("div",null,"Nombres",-1),Ne=n("div",null,"Ap. paterno",-1),Ae=n("div",null,"Ap. materno",-1),Re=n("div",null,"Correo",-1),$e=n("div",null,"Contraseña",-1),qe=n("div",null,"Confirmar contraseña",-1),Be=n("div",null,"Rol",-1),Ee=n("div",null,"Examen",-1),We={__name:"index",props:["usuarios"],setup(u){const r=c(""),i=c([]),g=c(""),p=c([]),j=c([]);let V;ie(r,(t,o)=>{clearTimeout(V),V=setTimeout(()=>{O()},500)});const y=c(!1),N=()=>{y.value=!0},A=t=>{console.log(t),y.value=!1},S=t=>{e.id=t.id,e.name=t.name,e.paterno=t.paterno,e.materno=t.materno,e.email=t.email,e.rol=t.id_rol,e.id_proceso=t.id_proceso,e.proceso=t.proceso,e.estado=t.estado==1,g.value=t.id_rol,y.value=!0},C=c(),e=de({id:null,name:"",paterno:"",materno:"",email:"",pass:"",checkPass:"",rol:c(null),estado:!0,id_proceso:null,proceso:9});let R=async(t,o)=>o===""?Promise.reject("Ingrese su correo electrónico"):Promise.resolve(),$=async(t,o)=>o===""?Promise.reject("Ingrese su nombre"):Promise.resolve(),q=async(t,o)=>o===""?Promise.reject("Ingrese la contraseña"):(e.checkPass!==""&&C.value.validateFields("checkPass"),Promise.resolve()),B=async(t,o)=>o===""?Promise.reject("Ingrese la contraseña nuevamente"):o!==e.pass?Promise.reject("Las contraseñas no coenciden"):Promise.resolve();const E={pageSize:20},M=t=>{console.log(t,e)},D=t=>{console.log(t)},H=()=>{C.value.resetFields()},T=(...t)=>{console.log(t)},G={required:"Ingrese ${label}",types:{email:"${label} no valido"}},O=async()=>{let t=await axios.post("get-usuarios",{term:r.value});p.value=t.data.usuarios},Z=async()=>{let t=await axios.get("get-roles-u");i.value=t.data.datos},J=async()=>{let t=await axios.get("get-select-procesos");j.value=t.data.datos};Z(),O(),J();const Q=()=>{let t={id:e.id,name:e.name,paterno:e.paterno,materno:e.materno,email:e.email,estado:e.estado,rol:2,id_proceso:e.id_proceso};e.pass&&e.pass.trim()!==""&&(t.password=e.pass),axios.post("save-user",t).then(o=>{console.log(o.data.user),y.value=!1,e.id=null,e.name="",e.paterno="",e.materno="",e.email="",e.pass="",e.checkPass="",e.rol=c(null),e.id_proceso=null,e.proceso=9,O(),W("success","Nuevo Usuario Agregado",o.data.user.name)})},W=(t,o,m)=>{_e[t]({message:o,description:m})},X={pass:[{required:!0,validator:q,trigger:"change"}],checkPass:[{required:!0,validator:B,trigger:"change"}],name:[{required:!0,validator:$,trigger:"change"}],email:[{validator:R,trigger:"change"}]},Y=[{title:"Nombre",dataIndex:"name"},{title:"Correo",dataIndex:"email"},{title:"Rol",dataIndex:"role_name",align:"center"},{title:"Proceso",dataIndex:"proceso",align:"center"},{title:"Estado",dataIndex:"estado",align:"center"},{title:"operation",dataIndex:"operation"}];return(t,o)=>{const m=d("a-button"),v=d("a-input"),I=d("a-tag"),K=d("a-table"),ee=d("a-switch"),f=d("a-form-item"),_=d("a-col"),ae=d("a-select"),te=d("a-row"),oe=d("a-form"),se=d("a-modal");return x(),h(z,null,[a(w(ue),{title:"Usuarios"}),a(ge,null,{default:s(()=>[n("div",we,[n("div",Pe,[n("div",Ce,[a(m,{class:"mb-3",style:{"border-radius":"5px",border:"none",background:"#476175"},type:"primary",onClick:N},{default:s(()=>[b("Usuario nuevo")]),_:1})]),n("div",Oe,[a(v,{type:"text",placeholder:"Buscar",value:r.value,"onUpdate:value":o[0]||(o[0]=l=>r.value=l),style:{height:"36px","max-width":"300px","border-radius":"6px","padding-left":"30px"}},null,8,["value"]),n("div",Ie,[a(w(ce))])])]),a(K,{bordered:"","data-source":p.value,columns:Y,size:"small",pagination:E},{bodyCell:s(({column:l,index:le,record:k})=>[l.dataIndex==="name"?(x(),h("span",ze,F(k.name.toLowerCase())+" "+F(k.paterno.toLowerCase()),1)):P("",!0),l.dataIndex==="role_name"?(x(),pe(I,{key:1,color:"cyan",size:"small"},{default:s(()=>[b(F(p.value[le].role_name),1)]),_:2},1024)):P("",!0),l.dataIndex==="estado"?(x(),h(z,{key:2},[k.estado==1?(x(),h("div",Fe,[a(I,{color:"#476175",size:"small"},{default:s(()=>[b(" activo ")]),_:1})])):(x(),h("div",Ue,[a(I,{color:"gray",size:"small"},{default:s(()=>[b(" inactivo ")]),_:1})]))],64)):P("",!0),l.dataIndex==="operation"?(x(),h(z,{key:3},[a(m,{class:"mr-1",type:"primary",onClick:ne=>S(k),style:{"border-radius":"4px",background:"none",border:"1px solid gray",color:"gray",width:"20px"},size:"small"},{icon:s(()=>[a(w(ke))]),_:2},1032,["onClick"]),a(m,{class:"mr-1",type:"primary",onClick:ne=>S(k),style:{"border-radius":"4px",background:"none",color:"blue",width:"20px"},size:"small"},{icon:s(()=>[a(w(xe))]),_:2},1032,["onClick"]),a(m,{type:"danger",style:{"border-radius":"4px",background:"none",color:"red",border:"red 1px solid",width:"20px"},size:"small"},{icon:s(()=>[a(w(me))]),_:1})],64)):P("",!0)]),_:1},8,["data-source"])])]),_:1}),n("div",null,[a(se,{visible:y.value,"onUpdate:visible":o[10]||(o[10]=l=>y.value=l),title:"Nuevo Usuario",style:{"margin-top":"-50px"},onOk:A},{footer:s(()=>[a(m,{style:{"border-radius":"5px"},onClick:H},{default:s(()=>[b("Cancelar")]),_:1}),a(m,{type:"primary",onClick:o[9]||(o[9]=l=>Q()),style:{"border-radius":"5px",border:"none",background:"#476175"}},{default:s(()=>[b("Guardar")]),_:1})]),default:s(()=>[n("div",null,[a(oe,ve({ref_key:"formRef",ref:C,name:"custom-validation",model:e,rules:X},t.layout,{onFinish:M,onValidate:T,"validate-messages":G,onFinishFailed:D}),{default:s(()=>[n("div",null,[n("div",je,[Ve,n("div",Se,[a(ee,{style:fe(e.estado?{backgroundColor:"#476175"}:{backgroundColor:"grey"}),checked:e.estado,"onUpdate:checked":o[1]||(o[1]=l=>e.estado=l)},null,8,["style","checked"])])])]),a(te,{gutter:[16,0],class:"form-row mb-0"},{default:s(()=>[a(_,{span:24,md:24,lg:12,xl:24,xxl:24},{default:s(()=>[Le,a(f,{"has-feedback":"",name:"name"},{default:s(()=>[a(v,{value:e.name,"onUpdate:value":o[2]||(o[2]=l=>e.name=l),type:"text",autocomplete:"off"},null,8,["value"])]),_:1})]),_:1}),a(_,{span:24,md:24,lg:12,xl:12,xxl:12},{default:s(()=>[Ne,a(f,{"has-feedback":"",name:"paterno"},{default:s(()=>[a(v,{value:e.paterno,"onUpdate:value":o[3]||(o[3]=l=>e.paterno=l),type:"text",autocomplete:"off"},null,8,["value"])]),_:1})]),_:1}),a(_,{span:24,md:24,lg:12,xl:12,xxl:12},{default:s(()=>[Ae,a(f,{"has-feedback":"",name:"materno"},{default:s(()=>[a(v,{value:e.materno,"onUpdate:value":o[4]||(o[4]=l=>e.materno=l),type:"text",autocomplete:"off"},null,8,["value"])]),_:1})]),_:1}),a(_,{span:24,md:24,lg:12,xl:24,xxl:24},{default:s(()=>[Re,a(f,{"has-feedback":"",name:"email",rules:[{type:"email",required:!0}]},{default:s(()=>[a(v,{value:e.email,"onUpdate:value":o[5]||(o[5]=l=>e.email=l),type:"text",autocomplete:"off"},null,8,["value"])]),_:1})]),_:1}),a(_,{span:24,md:24,lg:12,xl:12,xxl:12},{default:s(()=>[$e,a(f,{"has-feedback":"",name:"pass"},{default:s(()=>[a(v,{value:e.pass,"onUpdate:value":o[6]||(o[6]=l=>e.pass=l),type:"password",autocomplete:"off"},null,8,["value"])]),_:1})]),_:1}),a(_,{span:24,md:24,lg:12,xl:12,xxl:12},{default:s(()=>[qe,a(f,{"has-feedback":"",name:"checkPass"},{default:s(()=>[a(v,{value:e.checkPass,"onUpdate:value":o[7]||(o[7]=l=>e.checkPass=l),type:"password",autocomplete:"off"},null,8,["value"])]),_:1})]),_:1}),a(_,{span:24,md:24,lg:12,xl:12,xxl:12},{default:s(()=>[Be,a(f,{"has-feedback":""},{default:s(()=>[a(v,{value:"Revisor",disabled:""})]),_:1})]),_:1}),a(_,{span:24,md:24,lg:12,xl:12,xxl:12},{default:s(()=>[Ee,a(f,{name:"proceso",rules:[{required:!0,message:"Seleccine el rol",trigger:"change"}]},{default:s(()=>[a(ae,{ref:"select",value:e.id_proceso,"onUpdate:value":o[8]||(o[8]=l=>e.id_proceso=l),style:{width:"100%"},options:j.value,onFocus:t.focus,onChange:t.handleChange},null,8,["value","options","onFocus","onChange"])]),_:1})]),_:1})]),_:1})]),_:1},16,["model"])])]),_:1},8,["visible"])])],64)}}};export{We as default};