import{r,e as S,i as Z,c as f,a as e,u as E,w as a,d as m,F as T,j as U,g as i,o as s,X as J,b as t,S as X,k as C,t as b,f as I,E as q,D as W,m as ee,n as ae}from"./app-45ca54b3.js";import{A as te}from"./AuthenticatedLayout-96b4700b.js";import{F as le}from"./FormOutlined-9f81d3a6.js";import"./DropdownLink-e3dd6598.js";import"./_plugin-vue_export-helper-c27b6911.js";import"./logotiny-c4b525af.js";const oe={class:"bg-white overflow-hidden shadow-sm sm:rounded-lg p-4"},ne={class:"mr-3"},ue={class:"flex justify-between",style:{position:"relative"}},de={class:"mr-2",style:{position:"absolute",left:"8px",top:"3px"}},ie={style:{"font-size":"1rem","font-weight":"bold"}},se={key:1,style:{"font-size":"0.95rem"}},ve={key:3},Ce={__name:"index",setup(re){const _=r(null),D=r(""),L=r([]),c=r(!1),N=r(1),G=r(null),y=r(20),u=r({id:null,codigo:"",nombre:"",postulante:"",tipo:"",observacion:""});S(D,(n,o)=>{A()}),S(y,(n,o)=>{A()}),S(_,(n,o)=>{n==0&&(_.value=null),N.value=1,A()}),S(c,(n,o)=>{c.value==!1&&u.value.id!=null&&(u.value.id=null,u.value.codigo=null,u.value.nombre=null,u.value.postulante=null,u.value.tipo=null,u.value.observacion=null)}),S(N,(n,o)=>{A()});const x=n=>{c.value=!0,u.value.id=n.id,u.value.codigo=n.codigo,u.value.postulante=n.postulante,u.value.tipo=n.tipo,u.value.observacion=n.observacion},A=async()=>{let n=await U.post("get-inscripciones-admin?page="+N.value,{term:D.value,paginashoja:y.value,programa:_.value});L.value=n.data.datos.data,G.value=n.data.datos.total},h=()=>{let n={id:u.value.id,codigo:u.value.codigo,nombre:u.value.nombre,observacion:u.value.observacion};U.post("save-documento",n).then(o=>{A(),k("success",o.data.titulo,o.data.mensaje),c.value=!1,u.value.codigo=null,u.value.id=null,u.value.nombre=""})},V=n=>{U.get("eliminar-modalidad/"+n.id).then(o=>{A(),k("warning",o.data.titulo,o.data.mensaje)})},B=[{title:"Codigo",dataIndex:"codigo"},{title:"DNI",dataIndex:"dni",align:"center"},{title:"Postulante",dataIndex:"postulante"},{title:"Programa",dataIndex:"programa"},{title:"Modalidad",dataIndex:"modalidad",align:"center"},{title:"Estado",dataIndex:"estado",align:"center"},{title:"Observación",dataIndex:"observacion"},{title:"Acciones",dataIndex:"acciones",width:"140px",align:"center"}],M=r([]),P=n=>{console.log("selectedRowKeys changed: ",n),M.value=n};Z(()=>({selectedRowKeys:E(M),onChange:P,hideDefaultSelections:!0}));const k=(n,o,l)=>{ae[n]({message:o,description:l})};return A(),(n,o)=>{const l=i("a-select-option"),z=i("a-select"),p=i("a-input"),j=i("row"),O=i("a-tag"),g=i("a-button"),F=i("a-divider"),Y=i("a-popconfirm"),H=i("a-table"),$=i("a-pagination"),R=i("a-form-item"),Q=i("a-form"),K=i("a-modal");return s(),f(T,null,[e(E(J),{title:"Documentos"}),e(te,null,{default:a(()=>[m("div",oe,[e(j,{class:"flex justify-between mb-4"},{default:a(()=>[m("div",ne,[e(z,{ref:"select",value:_.value,"onUpdate:value":o[0]||(o[0]=d=>_.value=d),placeholder:"Seleccionar programa",class:"selector-modalidad",style:{width:"200px"}},{default:a(()=>[e(l,{value:0},{default:a(()=>[t("TODOS")]),_:1}),e(l,{value:1},{default:a(()=>[t("ADMINISTRACIÓN")]),_:1}),e(l,{value:2},{default:a(()=>[t("ANTROPOLOGÍA")]),_:1}),e(l,{value:3},{default:a(()=>[t("ARQUITECTURA Y URBANISMO")]),_:1}),e(l,{value:4},{default:a(()=>[t("ARTE: ARTES PLÁSTICAS")]),_:1}),e(l,{value:5},{default:a(()=>[t("ARTE: DANZA")]),_:1}),e(l,{value:6},{default:a(()=>[t("ARTE: MÚSICA")]),_:1}),e(l,{value:8},{default:a(()=>[t("BIOLOGÍA: ECOLOGÍA")]),_:1}),e(l,{value:9},{default:a(()=>[t("BIOLOGÍA: MICROBIOLOGÍA Y LABORATORIO CLÍNICO")]),_:1}),e(l,{value:10},{default:a(()=>[t("BIOLOGÍA: PESQUERÍA")]),_:1}),e(l,{value:11},{default:a(()=>[t("CIENCIAS CONTABLES")]),_:1}),e(l,{value:12},{default:a(()=>[t("CIENCIAS DE LA COMUNICACIÓN SOCIAL")]),_:1}),e(l,{value:13},{default:a(()=>[t("CIENCIAS FÍSICO MATEMÁTICAS: FÍSICA")]),_:1}),e(l,{value:14},{default:a(()=>[t("CIENCIAS FÍSICO MATEMÁTICAS: MATEMÁTICAS")]),_:1}),e(l,{value:15},{default:a(()=>[t("DERECHO")]),_:1}),e(l,{value:16},{default:a(()=>[t("EDUCACIÓN FÍSICA")]),_:1}),e(l,{value:17},{default:a(()=>[t("EDUCACIÓN INICIAL")]),_:1}),e(l,{value:18},{default:a(()=>[t("EDUCACIÓN PRIMARIA")]),_:1}),e(l,{value:19},{default:a(()=>[t("EDUCACIÓN SECUNDARIA DE LA ESPECIALIDAD DE CIENCIA, TECNOLOGÍA Y AMBIENTE")]),_:1}),e(l,{value:20},{default:a(()=>[t("EDUCACIÓN SECUNDARIA DE LA ESPECIALIDAD DE CIENCIAS SOCIALES")]),_:1}),e(l,{value:21},{default:a(()=>[t("EDUCACIÓN SECUNDARIA DE LA ESPECIALIDAD DE LENGUA, LITERATURA, PSICOLOGÍA Y FILOSOFÍA")]),_:1}),e(l,{value:22},{default:a(()=>[t("EDUCACIÓN SECUNDARIA DE LA ESPECIALIDAD DE MATEMÁTICA, FÍSICA, COMPUTACIÓN E INFORMÁTICA")]),_:1}),e(l,{value:23},{default:a(()=>[t("ENFERMERÍA")]),_:1}),e(l,{value:24},{default:a(()=>[t("INGENIERÍA AGRÍCOLA")]),_:1}),e(l,{value:25},{default:a(()=>[t("INGENIERÍA AGROINDUSTRIAL")]),_:1}),e(l,{value:26},{default:a(()=>[t("INGENIERÍA AGRONÓMICA")]),_:1}),e(l,{value:27},{default:a(()=>[t("INGENIERÍA CIVIL")]),_:1}),e(l,{value:28},{default:a(()=>[t("INGENIERÍA DE MINAS")]),_:1}),e(l,{value:29},{default:a(()=>[t("INGENIERÍA DE SISTEMAS")]),_:1}),e(l,{value:30},{default:a(()=>[t("INGENIERÍA ECONÓMICA")]),_:1}),e(l,{value:31},{default:a(()=>[t("INGENIERÍA ELECTRÓNICA")]),_:1}),e(l,{value:32},{default:a(()=>[t("INGENIERÍA ESTADÍSTICA E INFORMÁTICA")]),_:1}),e(l,{value:33},{default:a(()=>[t("INGENIERÍA GEOLÓGICA")]),_:1}),e(l,{value:34},{default:a(()=>[t("INGENIERÍA MECÁNICA ELÉCTRICA")]),_:1}),e(l,{value:35},{default:a(()=>[t("INGENIERÍA METALÚRGICA")]),_:1}),e(l,{value:36},{default:a(()=>[t("INGENIERÍA QUÍMICA")]),_:1}),e(l,{value:37},{default:a(()=>[t("INGENIERÍA TOPOGRÁFICA Y AGRIMENSURA")]),_:1}),e(l,{value:38},{default:a(()=>[t("MEDICINA HUMANA")]),_:1}),e(l,{value:39},{default:a(()=>[t("MEDICINA VETERINARIA Y ZOOTECNIA")]),_:1}),e(l,{value:40},{default:a(()=>[t("NUTRICIÓN HUMANA")]),_:1}),e(l,{value:41},{default:a(()=>[t("ODONTOLOGÍA")]),_:1}),e(l,{value:42},{default:a(()=>[t("SOCIOLOGÍA")]),_:1}),e(l,{value:43},{default:a(()=>[t("TRABAJO SOCIAL")]),_:1}),e(l,{value:44},{default:a(()=>[t("TURISMO")]),_:1})]),_:1},8,["value"])]),m("div",ue,[e(p,{type:"text",placeholder:"Buscar",value:D.value,"onUpdate:value":o[1]||(o[1]=d=>D.value=d),style:{"max-width":"300px","padding-left":"30px"}},null,8,["value"]),m("div",de,[e(E(X))])])]),_:1}),e(H,{columns:B,"data-source":L.value,pagination:!1,size:"small"},{bodyCell:a(({column:d,index:Ie,record:v})=>[d.dataIndex==="codigo"?(s(),C(O,{key:0,color:"success",style:{"padding-top":"3px"}},{default:a(()=>[m("span",ie,b(v.codigo),1)]),_:2},1024)):I("",!0),d.dataIndex==="postulante"?(s(),f("span",se,b(v.paterno)+" "+b(v.materno)+", "+b(v.nombres),1)):I("",!0),d.dataIndex==="estado"?(s(),f(T,{key:2},[v.estado===0?(s(),C(O,{key:0,color:"blue"},{default:a(()=>[t("HABILITADO")]),_:1})):(s(),C(O,{key:1,color:"error"},{default:a(()=>[t("ANULADO")]),_:1}))],64)):I("",!0),d.dataIndex==="programa"?(s(),f("div",ve,b(v.programa),1)):I("",!0),d.dataIndex==="verificado"?(s(),f(T,{key:4},[v.verificado===0?(s(),C(O,{key:0,color:"error"},{default:a(()=>[t("no verificado")]),_:1})):I("",!0),v.verificado===1?(s(),C(O,{key:1,color:"success"},{default:a(()=>[t("verificado")]),_:1})):I("",!0)],64)):I("",!0),d.dataIndex==="acciones"?(s(),f(T,{key:5},[e(g,{type:"success",disabled:"",style:{background:"#52c41a",color:"white"},onClick:w=>x(v),size:"small"},{icon:a(()=>[e(E(q))]),_:2},1032,["onClick"]),e(F,{type:"vertical"}),e(g,{type:"primary",onClick:w=>x(v),size:"small"},{icon:a(()=>[e(E(le))]),_:2},1032,["onClick"]),e(F,{type:"vertical"}),L.value.length?(s(),C(Y,{key:0,title:"¿Estas seguro de eliminar?",onConfirm:w=>V(v)},{default:a(()=>[e(g,{type:"danger",shape:"",size:"small"},{icon:a(()=>[e(E(W))]),_:1})]),_:2},1032,["onConfirm"])):I("",!0)],64)):I("",!0)]),_:1},8,["data-source"]),e($,{current:N.value,"onUpdate:current":o[2]||(o[2]=d=>N.value=d),total:G.value,pageSize:y.value,"onUpdate:pageSize":o[3]||(o[3]=d=>y.value=d),"show-less-items":""},null,8,["current","total","pageSize"])])]),_:1}),m("div",null,[e(K,{visible:c.value,"onUpdate:visible":o[10]||(o[10]=d=>c.value=d),title:"Nuevo Documento",style:{"margin-top":"-40px"}},{footer:a(()=>[e(g,{style:{"margin-left":"10px"},onClick:n.resetForm},{default:a(()=>[t("Cancelar")]),_:1},8,["onClick"]),e(g,{type:"primary",onClick:o[9]||(o[9]=d=>h())},{default:a(()=>[t("Guardar")]),_:1})]),default:a(()=>[e(Q,ee({ref:"formRef",name:"custom-validation",model:n.formState},n.layout,{onFinish:n.handleFinish,onValidate:n.handleValidate,onFinishFailed:n.handleFinishFailed}),{default:a(()=>[e(R,{"has-feedback":"",label:"Codigo",name:"codigo"},{default:a(()=>[e(p,{type:"text",value:u.value.codigo,"onUpdate:value":o[4]||(o[4]=d=>u.value.codigo=d),autocomplete:"off"},null,8,["value"])]),_:1}),e(R,{"has-feedback":"",label:"Nombre",name:"nombre"},{default:a(()=>[e(p,{type:"text",value:u.value.nombre,"onUpdate:value":o[5]||(o[5]=d=>u.value.nombre=d),autocomplete:"off"},null,8,["value"])]),_:1}),e(R,{"has-feedback":"",label:"Postulante",name:"postulante"},{default:a(()=>[e(p,{disabled:"",type:"text",value:u.value.postulante,"onUpdate:value":o[6]||(o[6]=d=>u.value.postulante=d),autocomplete:"off"},null,8,["value"])]),_:1}),e(R,{"has-feedback":"",label:"Tipo",name:"tipo"},{default:a(()=>[e(p,{disabled:"",type:"text",value:u.value.tipo,"onUpdate:value":o[7]||(o[7]=d=>u.value.tipo=d),autocomplete:"off"},null,8,["value"])]),_:1}),e(R,{"has-feedback":"",label:"Observacion",name:"nombre"},{default:a(()=>[e(p,{type:"text",value:u.value.observacion,"onUpdate:value":o[8]||(o[8]=d=>u.value.observacion=d),autocomplete:"off"},null,8,["value"])]),_:1})]),_:1},16,["model","onFinish","onValidate","onFinishFailed"])]),_:1},8,["visible"])])],64)}}};export{Ce as default};