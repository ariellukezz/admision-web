import{r as _,e as z,c,a as e,u as S,w as a,F as T,o as m,X as Y,d as u,b as s,S as Z,f as x,t as g,D as ee,n as ae,g as d,p as te,h as oe}from"./app-e1f113b2.js";import{A as le}from"./AuthenticatedLayout-e98e4649.js";import{_ as ne}from"./_plugin-vue_export-helper-c27b6911.js";import{F as se}from"./FormOutlined-07ea5ac4.js";import"./DropdownLink-84cafe64.js";import"./logotiny-c4b525af.js";const q=F=>(te("data-v-a58078c6"),F=F(),oe(),F),de={class:"m-4"},ue={class:"mb-4"},ie={class:"flex justify-end"},re={key:0},pe={key:0},_e={key:1},ce={key:2},me={key:1,style:{"text-transform":"uppercase"}},ve={key:2,style:{"text-transform":"uppercase"}},fe={class:"flex justify-end mt-1"},ge={style:{scale:".9"}},be=q(()=>u("div",{class:"custom-title"},[u("h3",{style:{"font-size":"1.2rem","margin-top":"5px","font-weight":"600"}},"Apoderado Nuevo")],-1)),xe={class:"custom-form"},ye={style:{padding:"0px 10px"}},he=q(()=>u("label",null,"Observación:",-1)),Ie={class:"flex justify-end"},ke={__name:"index",setup(F){const y=_(1),D=_(0),b=_(10),w=_(""),N=_([]),P=_(null),n=_({id:null,tipo_doc:1,nro_documento:"",paterno:"",materno:null,nombres:"",tipo_apoderado:1,observacion:null}),R={tipo_doc:[{required:!0,message:"Seleccione el tipo de documento",trigger:"change"}],nro_documento:[{required:!0,message:"Ingrese el número de documento",trigger:"blur"}],paterno:[{required:!0,message:"Ingrese el apellido paterno",trigger:"blur"}],materno:[{required:!0,message:"Ingrese el apellido materno",trigger:"blur"}],nombres:[{required:!0,message:"Ingrese los nombres",trigger:"blur"}],tipo_apoderado:[{required:!0,message:"Seleccione el tipo de apoderado",trigger:"change"}]},M={labelCol:{span:24,backgroundColor:"red"},wrapperCol:{span:20}},O=o=>{console.log("Form submitted:",o)},j=o=>{console.log("Form validation failed:",o)},B=()=>{P.value.resetFields()},E=()=>{axios.post("/admin/save-apoderados-admin",n.value).then(o=>{h(),I.value=!1,L(o.data.tipo,o.data.titulo,o.data.mensaje)}).catch(o=>{console.error(o)})},h=()=>{axios.post("/admin/get-apoderados-admin?page="+y.value,{pages:b.value,term:w.value}).then(o=>{N.value=o.data.datos.data,y.value=o.data.datos.current_page,D.value=o.data.datos.total,console.log(o.data)}).catch(o=>{console.error(o)})};h();const I=_(!1),V=()=>{I.value=!0},$=o=>{n.value.id=o.id,n.value.tipo_doc=1,n.value.nro_documento=o.dni,n.value.paterno=o.paterno,n.value.materno=o.materno,n.value.nombres=o.nombres,n.value.tipo_apoderado=o.tipo_apoderado,n.value.observacion=o.observacion,I.value=!0};z(w,(o,t)=>{h()}),z(y,(o,t)=>{h()}),z(b,(o,t)=>{h()});const G=[{title:"Tipo",dataIndex:"tipo_apoderado",width:"70px",align:"center"},{title:"DNI",dataIndex:"dni"},{title:"Apoderado",dataIndex:"nombres"},{title:"Postulante",dataIndex:"postulante"},{title:"Action",dataIndex:"action"}],L=(o,t,v)=>{ae[o]({message:t,description:v})};return(o,t)=>{const v=d("a-button"),k=d("a-input"),A=d("a-tag"),X=d("a-divider"),H=d("a-table"),J=d("a-pagination"),r=d("a-select-option"),C=d("a-select"),p=d("a-form-item"),f=d("a-col"),U=d("a-row"),K=d("a-textarea"),Q=d("a-form"),W=d("a-modal");return m(),c(T,null,[e(S(Y),{title:"Apoderado"}),e(le,null,{default:a(()=>[u("div",de,[u("div",ue,[u("div",ie,[e(v,{style:{display:"none"},type:"primary",onClick:t[0]||(t[0]=l=>V())},{default:a(()=>[s("Abrir")]),_:1}),e(k,{placeholder:"Buscar",value:w.value,"onUpdate:value":t[1]||(t[1]=l=>w.value=l),style:{"max-width":"300px"}},{suffix:a(()=>[e(S(Z))]),_:1},8,["value"])])]),u("div",null,[e(H,{columns:G,"data-source":N.value,pagination:!1,size:"small"},{bodyCell:a(({column:l,record:i})=>[l.dataIndex==="tipo_apoderado"?(m(),c("div",re,[i.tipo_apoderado==1?(m(),c("div",pe,[e(A,{color:"blue"},{default:a(()=>[s("PADRE")]),_:1})])):x("",!0),i.tipo_apoderado==2?(m(),c("div",_e,[e(A,{color:"pink"},{default:a(()=>[s("MADRE")]),_:1})])):x("",!0),i.tipo_apoderado==3?(m(),c("div",ce,[e(A,{color:"orange"},{default:a(()=>[s("TUTOR")]),_:1})])):x("",!0)])):x("",!0),l.dataIndex==="nombres"?(m(),c("span",me,g(i.nombres)+" "+g(i.paterno)+" "+g(i.materno),1)):x("",!0),l.dataIndex==="postulante"?(m(),c("span",ve,g(i.dni_postulante)+" - "+g(i.postulante)+" "+g(i.primer_apellido)+" "+g(i.segundo_apellido),1)):l.dataIndex==="action"?(m(),c(T,{key:3},[e(v,{type:"primary",onClick:Fe=>$(i),size:"small"},{icon:a(()=>[e(S(se))]),_:2},1032,["onClick"]),e(X,{type:"vertical"}),e(v,{type:"danger",shape:"",size:"small"},{icon:a(()=>[e(S(ee))]),_:1})],64)):x("",!0)]),_:1},8,["data-source"]),u("div",fe,[e(J,{current:y.value,"onUpdate:current":t[2]||(t[2]=l=>y.value=l),total:D.value,pageSize:b.value,"onUpdate:pageSize":t[3]||(t[3]=l=>b.value=l),"show-less-items":""},null,8,["current","total","pageSize"]),u("div",ge,[e(C,{value:b.value,"onUpdate:value":t[4]||(t[4]=l=>b.value=l),placeholder:"Seleccione",style:{"font-size":"0.6rem",width:"100px"}},{default:a(()=>[e(r,{value:1},{default:a(()=>[s("1/Pag")]),_:1}),e(r,{value:10},{default:a(()=>[s("10/Pag")]),_:1}),e(r,{value:20},{default:a(()=>[s("20/Pag")]),_:1}),e(r,{value:50},{default:a(()=>[s("50/Pag")]),_:1}),e(r,{value:100},{default:a(()=>[s("100/Pag")]),_:1})]),_:1},8,["value"])])])]),e(W,{visible:I.value,"onUpdate:visible":t[13]||(t[13]=l=>I.value=l),class:"card-size",style:{"margin-top":"-30px"}},{title:a(()=>[be]),footer:a(()=>[e(p,{"wrapper-col":{span:20,offset:4}},{default:a(()=>[u("div",Ie,[e(v,{type:"primary",onClick:t[12]||(t[12]=l=>E())},{default:a(()=>[s("Guardar")]),_:1}),e(v,{style:{"margin-left":"10px"},onClick:B},{default:a(()=>[s("Reset")]),_:1})])]),_:1})]),default:a(()=>[e(Q,{ref_key:"formRef",ref:P,name:"apoderadoForm",model:n.value,rules:R,layout:M,class:"",onFinish:O,onFinishFailed:j},{default:a(()=>[u("div",xe,[e(U,{gutter:[0,0]},{default:a(()=>[e(f,{span:8,class:"ant-col-xs-24 col-item"},{default:a(()=>[e(p,{label:"Tipo de Documento",name:"tipo_doc"},{default:a(()=>[e(C,{value:n.value.tipo_doc,"onUpdate:value":t[5]||(t[5]=l=>n.value.tipo_doc=l),placeholder:"Seleccione",style:{"min-width":"210px"}},{default:a(()=>[e(r,{value:1},{default:a(()=>[s("DNI")]),_:1}),e(r,{value:2},{default:a(()=>[s("Carnet Ext.")]),_:1})]),_:1},8,["value"])]),_:1})]),_:1}),e(f,{span:8,class:"ant-col-xs-24 col-item"},{default:a(()=>[e(p,{label:"Nro. Documento",name:"nro_documento"},{default:a(()=>[e(k,{value:n.value.nro_documento,"onUpdate:value":t[6]||(t[6]=l=>n.value.nro_documento=l)},null,8,["value"])]),_:1})]),_:1}),e(f,{span:8,class:"ant-col-xs-24 col-item"},{default:a(()=>[e(p,{label:"Apellido Paterno",name:"paterno"},{default:a(()=>[e(k,{value:n.value.paterno,"onUpdate:value":t[7]||(t[7]=l=>n.value.paterno=l)},null,8,["value"])]),_:1})]),_:1})]),_:1}),e(U,{gutter:[0,0]},{default:a(()=>[e(f,{span:8,class:"ant-col-xs-24 col-item"},{default:a(()=>[e(p,{label:"Tipo de Apoderado",name:"tipo_apoderado"},{default:a(()=>[e(C,{value:n.value.tipo_apoderado,"onUpdate:value":t[8]||(t[8]=l=>n.value.tipo_apoderado=l),placeholder:"Seleccione"},{default:a(()=>[e(r,{value:1},{default:a(()=>[s("Padre")]),_:1}),e(r,{value:2},{default:a(()=>[s("Madre")]),_:1}),e(r,{value:3},{default:a(()=>[s("Tutor")]),_:1})]),_:1},8,["value"])]),_:1})]),_:1}),e(f,{span:8,class:"ant-col-xs-24 col-item"},{default:a(()=>[e(p,{label:"Nombres",name:"nombres"},{default:a(()=>[e(k,{value:n.value.nombres,"onUpdate:value":t[9]||(t[9]=l=>n.value.nombres=l)},null,8,["value"])]),_:1})]),_:1}),e(f,{span:8,class:"ant-col-xs-24 col-item"},{default:a(()=>[e(p,{label:"Apellido Materno",name:"materno"},{default:a(()=>[e(k,{value:n.value.materno,"onUpdate:value":t[10]||(t[10]=l=>n.value.materno=l)},null,8,["value"])]),_:1})]),_:1})]),_:1})]),u("div",ye,[e(U,{gutter:[20,16]},{default:a(()=>[e(f,{span:20,class:"ant-col-xs-24 col-item"},{default:a(()=>[e(p,{name:"observacion"},{default:a(()=>[he,e(K,{value:n.value.observacion,"onUpdate:value":t[11]||(t[11]=l=>n.value.observacion=l)},null,8,["value"])]),_:1})]),_:1})]),_:1})])]),_:1},8,["model"])]),_:1},8,["visible"])])]),_:1})],64)}}},De=ne(ke,[["__scopeId","data-v-a58078c6"]]);export{De as default};