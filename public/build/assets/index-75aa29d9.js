import{r as s,e as _,i as L,f as i,o as b,c as V,a as o,u as c,w as l,d as p,F as N,j as y,Z as T,b as h,S as Z,k as H,D as J,g as P,m as Q,n as W}from"./app-8a9b4894.js";import{A as X}from"./AuthenticatedLayout-01b50765.js";import{F as Y}from"./FormOutlined-d87653e5.js";import"./DropdownLink-5159c35e.js";import"./_plugin-vue_export-helper-c27b6911.js";import"./logotiny-c4b525af.js";import"./TeamOutlined-3c415ab9.js";const ee={class:"bg-white overflow-hidden shadow-sm sm:rounded-lg p-4"},ae={class:"mr-3"},oe={class:"flex justify-between",style:{position:"relative"}},te={class:"mr-2",style:{position:"absolute",left:"8px",top:"3px"}},ve={__name:"index",setup(le){const v=s(""),u=s([]);s([]);const d=s(!1),f=s(1),w=s(null),t=s({id:null,codigo:"",nombre:""}),j=()=>{d.value=!0};_(v,(a,e)=>{m()}),_(d,(a,e)=>{d.value==!1&&t.value.id!=null&&(t.value.id=null,t.value.codigo=null,t.value.nombre=null)}),_(f,(a,e)=>{m()});const I={labelCol:{span:7},wrapperCol:{span:14}},B={nombre:[{required:!0,validator:async(a,e)=>e===""?Promise.reject("Ingrese su el nombre del filial"):Promise.resolve(),trigger:"change"}],codigo:[{required:!0,validator:async(a,e)=>e===""?Promise.reject("Ingrese la sede del filial"):Promise.resolve(),trigger:"change"}]};s([]);const S=a=>{d.value=!0,t.value.id=a.id,t.value.codigo=a.codigo,t.value.nombre=a.nombre},m=async(a="")=>{let e=await y.post("modalidad/get-modalidades?page="+f.value,{term:v.value});u.value=e.data.datos.data,w.value=e.data.datos.total},U=()=>{let a={id:t.value.id,codigo:t.value.codigo,nombre:t.value.nombre};y.post("save-modalidad",a).then(e=>{m(),x("success",e.data.titulo,e.data.mensaje),d.value=!1,t.value.codigo=null,t.value.id=null,t.value.nombre=""})},R=a=>{y.get("eliminar-modalidad/"+a.id).then(e=>{m(),x("warning",e.data.titulo,e.data.mensaje)})},z=[{title:"Cod",dataIndex:"codigo"},{title:"Nombre",dataIndex:"nombre"},{title:"Acciones",dataIndex:"acciones"}],C=s([]),A=a=>{console.log("selectedRowKeys changed: ",a),C.value=a};L(()=>({selectedRowKeys:c(C),onChange:A,hideDefaultSelections:!0}));const x=(a,e,r)=>{W[a]({message:e,description:r})};return m(),(a,e)=>{const r=i("a-button"),g=i("a-input"),D=i("row"),E=i("a-divider"),M=i("a-popconfirm"),O=i("a-table"),$=i("a-pagination"),k=i("a-form-item"),q=i("a-form"),K=i("a-modal");return b(),V(N,null,[o(c(T),{title:"Modalidades"}),o(X,null,{default:l(()=>[p("div",ee,[o(D,{class:"flex justify-between mb-4"},{default:l(()=>[p("div",ae,[o(r,{type:"primary",onClick:j},{default:l(()=>[h("Nuevo")]),_:1})]),p("div",oe,[o(g,{type:"text",placeholder:"Buscar",value:v.value,"onUpdate:value":e[0]||(e[0]=n=>v.value=n),style:{"max-width":"300px","padding-left":"30px"}},null,8,["value"]),p("div",te,[o(c(Z))])])]),_:1}),o(O,{columns:z,"data-source":u.value,pagination:!1,size:"small"},{bodyCell:l(({column:n,index:F})=>[n.dataIndex==="acciones"?(b(),V(N,{key:0},[o(r,{type:"primary",onClick:G=>S(u.value[F]),size:"small"},{icon:l(()=>[o(c(Y))]),_:2},1032,["onClick"]),o(E,{type:"vertical"}),u.value.length?(b(),H(M,{key:0,title:"¿Estas seguro de eliminar?",onConfirm:G=>R(u.value[F])},{default:l(()=>[o(r,{type:"danger",shape:"",size:"small"},{icon:l(()=>[o(c(J))]),_:1})]),_:2},1032,["onConfirm"])):P("",!0)],64)):P("",!0)]),_:1},8,["data-source"]),o($,{current:f.value,"onUpdate:current":e[1]||(e[1]=n=>f.value=n),total:w.value,"show-less-items":""},null,8,["current","total"])])]),_:1}),p("div",null,[o(K,{visible:d.value,"onUpdate:visible":e[5]||(e[5]=n=>d.value=n),title:"Nuevo Programa",style:{"margin-top":"-40px"}},{footer:l(()=>[o(r,{style:{"margin-left":"10px"},onClick:a.resetForm},{default:l(()=>[h("Cancelar")]),_:1},8,["onClick"]),o(r,{type:"primary",onClick:e[4]||(e[4]=n=>U())},{default:l(()=>[h("Guardar")]),_:1})]),default:l(()=>[o(q,Q({ref:"formRef",name:"custom-validation",model:a.formState,rules:B},I,{onFinish:a.handleFinish,onValidate:a.handleValidate,onFinishFailed:a.handleFinishFailed}),{default:l(()=>[o(k,{"has-feedback":"",label:"Codigo",name:"codigo"},{default:l(()=>[o(g,{type:"text",value:t.value.codigo,"onUpdate:value":e[2]||(e[2]=n=>t.value.codigo=n),autocomplete:"off"},null,8,["value"])]),_:1}),o(k,{"has-feedback":"",label:"Nombre",name:"nombre"},{default:l(()=>[o(g,{type:"text",value:t.value.nombre,"onUpdate:value":e[3]||(e[3]=n=>t.value.nombre=n),autocomplete:"off"},null,8,["value"])]),_:1})]),_:1},16,["model","onFinish","onValidate","onFinishFailed"])]),_:1},8,["visible"])])],64)}}};export{ve as default};