import{x as r,O as J,c as C,a as t,u as _,w as s,d as m,F as w,r as i,o as b,X as K,b as v,l as Q,t as U,f as F,D as Y,A as Z,B as ee}from"./app-2924acb4.js";import{A as ae}from"./AuthenticatedLayout-c3ebb936.js";import{F as te}from"./FormOutlined-8100f063.js";import"./DropdownLink-d6d860f1.js";import"./_plugin-vue_export-helper-c27b6911.js";import"./logotiny-c4b525af.js";import"./logoDAD-c76d0a0b.js";const oe={class:"bg-white overflow-hidden shadow-sm sm:rounded-lg"},le=m("h1",null," Usuarios ",-1),se=["onClick"],ye={__name:"index",props:["usuarios"],setup(re){const y=r([]),c=r(""),k=r(""),f=r([]),h=r([]),p=r(!1),I=()=>{p.value=!0},N=a=>{console.log(a),p.value=!1},R=[{title:"Nombre",dataIndex:"name",width:"30%"},{title:"Correo",dataIndex:"email"},{title:"Rol",dataIndex:"role_name"},{title:"operation",dataIndex:"operation"}],g=r(),l=J({name:"",paterno:"",materno:"",email:"",pass:"",checkPass:"",rol:r(null)}),B={pass:[{required:!0,validator:async(a,e)=>e===""?Promise.reject("Ingrese la contraseña"):(l.checkPass!==""&&g.value.validateFields("checkPass"),Promise.resolve()),trigger:"change"}],checkPass:[{required:!0,validator:async(a,e)=>e===""?Promise.reject("Ingrese la contraseña nuevamente"):e!==l.pass?Promise.reject("Las contraseñas no coenciden"):Promise.resolve(),trigger:"change"}],name:[{required:!0,validator:async(a,e)=>e===""?Promise.reject("Ingrese su nombre"):Promise.resolve(),trigger:"change"}],email:[{validator:async(a,e)=>e===""?Promise.reject("Ingrese su correo electrónico"):Promise.resolve(),trigger:"change"}]},O={labelCol:{span:8},wrapperCol:{span:18}},A=a=>{console.log(a,l)},V=a=>{console.log(a)},j=()=>{g.value.resetFields()},q=(...a)=>{console.log(a)},S={required:"Ingrese ${label}",types:{email:"${label} no valido"}};r(""),r([{value:"Burns Bay Road"},{value:"Downing Street"},{value:"Wall Street"}]);const z=(a,e)=>e.value.toUpperCase().indexOf(a.toUpperCase())>=0,x=async()=>{let a=await axios.get("get-usuarios");f.value=a.data.usuarios},D=async()=>{let a=await axios.get("get-roles-u");y.value=a.data.datos},$=async()=>{let a=await axios.get("get-permisos");h.value=a.data.permisos};D(),x(),$();const M=a=>{k.value=a.id},L=()=>{let a={name:l.name,paterno:l.paterno,materno:l.materno,email:l.email,password:l.pass,rol:k.value};axios.post("save-user",a).then(e=>{console.log(e.data.user),p.value=!1,x(),E("success","Nuevo Usuario Agregado",e.data.user.name)})},E=(a,e,n)=>{ee[a]({message:e,description:n})};return console.log((()=>!!h.value.find(a=>a.id===8))()),(a,e)=>{const n=i("a-button"),T=i("a-divider"),W=i("a-table"),u=i("a-input"),d=i("a-form-item"),X=i("a-auto-complete"),G=i("a-form"),H=i("a-modal");return b(),C(w,null,[t(_(K),{title:"Usuarios"}),t(ae,null,{default:s(()=>[m("div",oe,[le,t(n,{type:"primary",onClick:I},{default:s(()=>[v("Nuevo")]),_:1}),t(W,{bordered:"","data-source":f.value,columns:R,size:"small"},{bodyCell:s(({column:o,index:P})=>[o.dataIndex==="role_name"?(b(),Q(n,{key:0,primary:"",size:"small"},{default:s(()=>[v(U(f.value[P].role_name),1)]),_:2},1024)):F("",!0),o.dataIndex==="operation"?(b(),C(w,{key:1},[t(n,{type:"primary",shape:"",size:"small"},{icon:s(()=>[t(_(te))]),_:1}),t(T,{type:"vertical"}),t(n,{type:"danger",shape:"",size:"small"},{icon:s(()=>[t(_(Y))]),_:1})],64)):F("",!0)]),_:1},8,["data-source"])])]),_:1}),m("div",null,[t(H,{visible:p.value,"onUpdate:visible":e[9]||(e[9]=o=>p.value=o),title:"Nuevo Usuario",style:{"margin-top":"-50px"},onOk:N},{footer:s(()=>[t(n,{type:"primary",onClick:e[8]||(e[8]=o=>L())},{default:s(()=>[v("Submit")]),_:1}),t(n,{style:{"margin-left":"10px"},onClick:j},{default:s(()=>[v("Reset")]),_:1})]),default:s(()=>[m("div",null,[t(G,Z({ref_key:"formRef",ref:g,name:"custom-validation",model:l,rules:B},O,{onFinish:A,onValidate:q,"validate-messages":S,onFinishFailed:V}),{default:s(()=>[t(d,{"has-feedback":"",label:"Nombre",name:"name"},{default:s(()=>[t(u,{value:l.name,"onUpdate:value":e[0]||(e[0]=o=>l.name=o),type:"text",autocomplete:"off"},null,8,["value"])]),_:1}),t(d,{"has-feedback":"",label:"Apellido Paterno",name:"paterno"},{default:s(()=>[t(u,{value:l.paterno,"onUpdate:value":e[1]||(e[1]=o=>l.paterno=o),type:"text",autocomplete:"off"},null,8,["value"])]),_:1}),t(d,{"has-feedback":"",label:"Apellido Materno",name:"materno"},{default:s(()=>[t(u,{value:l.materno,"onUpdate:value":e[2]||(e[2]=o=>l.materno=o),type:"text",autocomplete:"off"},null,8,["value"])]),_:1}),t(d,{"has-feedback":"",label:"Correo",name:"email",rules:[{type:"email",required:!0}]},{default:s(()=>[t(u,{value:l.email,"onUpdate:value":e[3]||(e[3]=o=>l.email=o),type:"text",autocomplete:"off"},null,8,["value"])]),_:1}),t(d,{"has-feedback":"",label:"Contraseña",name:"pass"},{default:s(()=>[t(u,{value:l.pass,"onUpdate:value":e[4]||(e[4]=o=>l.pass=o),type:"password",autocomplete:"off"},null,8,["value"])]),_:1}),t(d,{"has-feedback":"",label:"Confirmar Contraseña",name:"checkPass"},{default:s(()=>[t(u,{value:l.checkPass,"onUpdate:value":e[5]||(e[5]=o=>l.checkPass=o),type:"password",autocomplete:"off"},null,8,["value"])]),_:1}),t(d,{"has-feedback":"",label:"Rol"},{default:s(()=>[t(X,{value:c.value,"onUpdate:value":e[7]||(e[7]=o=>c.value=o),options:y.value,style:{width:"100%"},"filter-option":z},{option:s(o=>[m("div",{onClick:P=>M(o)},[m("span",null,U(o.value),1)],8,se)]),default:s(()=>[t(u,{placeholder:"input here",class:"custom",value:c.value,"onUpdate:value":e[6]||(e[6]=o=>c.value=o)},null,8,["value"])]),_:1},8,["value","options"])]),_:1})]),_:1},16,["model"])])]),_:1},8,["visible"])])],64)}}};export{ye as default};