import{r as i,i as E,c as b,a as e,u as c,w as a,d as w,F as C,j as p,g as l,o as x,X as O,b as d,D as P,f as T}from"./app-41695359.js";import{A as U}from"./AuthenticatedLayout-6ee94669.js";import{F as $}from"./FormOutlined-6cddd565.js";import"./DropdownLink-820fb3a2.js";import"./_plugin-vue_export-helper-c27b6911.js";import"./logotiny-c4b525af.js";const j={class:"bg-white overflow-hidden shadow-sm sm:rounded-lg p-5"},W={__name:"index",setup(G){const u=i(""),m=i([]),_=i([]),n=i(!1),R=()=>{k(),n.value=!0},k=async()=>{let t=await p.get("get-permission");_.value=t.data.permisos},v=async()=>{let t=await p.get("get-roles");m.value=t.data.datos.data},h=t=>{n.value=!0,u.value=t.name},z=()=>{let t={name:u.value,permisos:g.value.selectedRowKeys};p.post("save-rol",t).then(o=>{console.log(o),v(),n.value=!1})},I=[{title:"Rol",dataIndex:"name",sorter:!0},{title:"Tipo permisos",dataIndex:"guard_name"},{title:"Acciones",dataIndex:"acciones"}],N=[{title:"Nombre",dataIndex:"name",sorter:!0},{title:"Permisos",dataIndex:"guard_name"}],f=i([]),F=t=>{console.log("selectedRowKeys changed: ",t),f.value=t},g=E(()=>({selectedRowKeys:c(f),onChange:F,hideDefaultSelections:!0}));return v(),(t,o)=>{const r=l("a-button"),S=l("a-divider"),y=l("a-table"),V=l("a-label"),A=l("a-input"),B=l("a-form-item"),D=l("a-modal");return x(),b(C,null,[e(c(O),{title:"Roles"}),e(U,null,{default:a(()=>[w("div",j,[e(r,{class:"mb-3",type:"primary",onClick:R},{default:a(()=>[d("Nuevo")]),_:1}),e(y,{columns:I,"data-source":m.value,pagination:{pageSize:5},size:"small"},{bodyCell:a(({column:s,index:K})=>[s.dataIndex==="acciones"?(x(),b(C,{key:0},[e(r,{type:"primary",onClick:L=>h(m.value[K]),size:"small"},{icon:a(()=>[e(c($))]),_:2},1032,["onClick"]),e(S,{type:"vertical"}),e(r,{type:"danger",shape:"",size:"small"},{icon:a(()=>[e(c(P))]),_:1})],64)):T("",!0)]),_:1},8,["data-source"])])]),_:1}),w("div",null,[e(D,{visible:n.value,"onUpdate:visible":o[2]||(o[2]=s=>n.value=s),title:"Crear Roles"},{footer:a(()=>[e(r,{style:{"margin-left":"10px"},onClick:t.resetForm},{default:a(()=>[d("Cancelar")]),_:1},8,["onClick"]),e(r,{type:"primary",onClick:o[1]||(o[1]=s=>z())},{default:a(()=>[d("Guardar")]),_:1})]),default:a(()=>[e(B,{name:"email"},{default:a(()=>[e(V,null,{default:a(()=>[d("Rol")]),_:1}),e(A,{value:u.value,"onUpdate:value":o[0]||(o[0]=s=>u.value=s),type:"text",autocomplete:"off"},null,8,["value"])]),_:1}),e(y,{"row-selection":c(g),columns:N,"data-source":_.value,pagination:{pageSize:10},size:"small"},null,8,["row-selection","data-source"])]),_:1},8,["visible"])])],64)}}};export{W as default};