import{r as i,x as Ee,e as h,f as c,o as g,c as C,a as e,u as y,w as l,F as oe,l as D,I as te,Z as Ue,d as n,S as Ce,s as S,b as u,t as j,g as k,E as De,D as Se,y as z,n as ke,p as Ne,h as Ae}from"./app-e9ff0a7a.js";import{L as Re}from"./LayoutSimulacro-840a1709.js";import{_ as Oe}from"./_plugin-vue_export-helper-c27b6911.js";import{F as Ve}from"./FormOutlined-6da24715.js";import"./DropdownLink-202beeb1.js";import"./logotiny-c4b525af.js";import"./TopMenu-d6b8aac6.js";const r=N=>(Ne("data-v-ca4f1f14"),N=N(),Ae(),N),qe={class:"mb-4",style:{width:"100%"}},Fe={style:{background:"white","border-radius":"12px",overflow:"hidden","box-shadow":"rgba(0, 0, 0, 0.1) 0px 20px 25px -5px, rgba(0, 0, 0, 0.03) 0px 10px 10px -5px"}},Pe={class:"flex justify-end mt-5 mb-4 mr-4"},je={class:"flex justify-between",style:{position:"relative"}},Ke={class:"mr-2",style:{position:"absolute",left:"8px",top:"3px"}},Le={key:1},Be=r(()=>n("span",null,"SEC TERMINADA",-1)),Me=r(()=>n("span",null,"5TO AÑO",-1)),Te=r(()=>n("span",null,"4TO AÑO",-1)),Ye=r(()=>n("span",null,"3ER AÑO",-1)),ze=r(()=>n("span",{style:{color:"blue"}},"M",-1)),Ge=r(()=>n("span",{style:{color:"red"}},"F",-1)),$e={class:"flex justify-between mb-6 mt-2 pr-4"},Ze={clas:"",style:{scale:"0.9"}},He={class:"flex justify-center"},Je=r(()=>n("div",{style:{"margin-bottom":"-10px"}},[n("h1",{class:"titulo-form"},"Datos Personales")],-1)),Qe={class:"flex justify-end",style:{}},We=r(()=>n("label",null,"Sexo",-1)),Xe=r(()=>n("label",null,"Tipo Doc",-1)),el={key:0},ll=r(()=>n("span",{style:{color:"red"}},"*",-1)),al={key:1},ol=r(()=>n("label",null,[u("Nombres"),n("span",{style:{color:"red"}},"*")],-1)),tl=r(()=>n("label",null,[u("Primer apellido"),n("span",{style:{color:"red"}},"*")],-1)),sl=r(()=>n("label",null,[u("Segundo apellido "),n("span",{style:{color:"red"}},"*")],-1)),nl=r(()=>n("label",null,"Celular",-1)),ul=r(()=>n("label",null,"Fec. Nacimiento",-1)),dl=r(()=>n("label",null,"Correo electrónico",-1)),rl=r(()=>n("div",{style:{"margin-top":"-10px"}},[n("h1",{class:"titulo-form"},"Datos Residencia")],-1)),il=r(()=>n("label",null,"País",-1)),cl=r(()=>n("label",null,[u("Dep / Prov / Dist "),n("span",{style:{color:"red"}},"*")],-1)),pl=r(()=>n("div",{style:{"margin-top":"-10px"}},[n("h1",{class:"titulo-form"},"Datos de estudios")],-1)),_l=r(()=>n("label",null,[u("Grado de instrucción"),n("span",{style:{color:"red"}},"*")],-1)),vl=r(()=>n("label",null,[u("Ubic. colegio (dep/prov/dist)"),n("span",{style:{color:"red"}},"*")],-1)),fl=r(()=>n("label",null,"Nombre del colegio",-1)),ml={class:"flex justify-end"},gl={__name:"index",setup(N){const A=i(""),R=i(1),G=i(0),I=i(10),$=i(!1),O=i(!1),V=i(null),K=i(null),w=i(null),Z=i([]),L=i(null),q=i(null),F=i(null),H=i([]),B=i(null);i(null);const M=i(null),J=i([]),Q=i(),t=Ee({id:null,tipo_doc:1,nro_doc:"",paterno:"",materno:"",nombres:"",sexo:null,fec_nac:"",celular:"",correo:"",pais:1,ubigeo_residencia:"",grado:1,ubigeo_colegio:null,id_colegio:null,terminos:!1,id_pago:null}),W=a=>{t.nro_doc=a.target.value.replace(/\D/g,"")},se=a=>{t.celular=a.target.value.replace(/\D/g,"")},ne=(a,o)=>{K.value=o.key,t.ubigeo_residencia=o.key},ue=(a,o)=>{q.value=o.key},de=(a,o)=>{q.value=o.key,t.id_colegio=o.key};function re(a,o){return new Promise((p,P)=>{if(!o)P(new Error(""));else{const d=new Date(o),f=new Date,x=new Date;f.setFullYear(f.getFullYear()-41),x.setFullYear(x.getFullYear()-13),d>x||d<f?P(new Error("Debes tener entre 13 y 30 años")):p()}})}const ie=async()=>{$.value=!0;try{const a=await Q.value.validateFields(),o=await D.post("save-simulacro-participante",t);o.status===202?console.log(o.data.errors):(O.value=!1,o.data.estado===!0&&(_e("success",o.data.titulo,""),E(),ve()))}catch(a){console.error(a)}$.value=!1},X=i([]),E=async()=>{let a=await D.post("get-participantes-simulacro?page="+R.value,{term:A.value,paginashoja:I.value});G.value=a.data.datos.total,X.value=a.data.datos.data,a.data.datos.data[0].fec_nac&&(t.fec_nac=te(a.data.datos.data[0].fec_nac))};E(),h(R,(a,o)=>{E()}),h(I,(a,o)=>{E()}),h(A,(a,o)=>{E()}),h(q,(a,o)=>{pe()}),h(w,(a,o)=>{a.length>=3&&ee()}),h(F,(a,o)=>{a.length>=3&&le()});const ce=a=>{O.value=!0,t.id=a.id_participante,t.tipo_doc=1,t.nro_doc=a.nro_doc,t.nombres=a.nombres,t.paterno=a.paterno,t.materno=a.materno,t.sexo=a.sexo,t.celular=a.celular,t.correo=a.correo,t.fec_nac=te(a.fec_nac),t.grado=a.instruccion,t.ubigeo_residencia=a.ubigeo,t.ubigeo_colegio=a.ubigeocolegio,t.id_colegio=a.idcolegio,V.value=a.lugar,L.value=a.lugarcolegio,B.value=a.colegio},ee=async()=>{D.post("/get-ubigeo",{term:w.value}).then(a=>{Z.value=a.data.datos.data}).catch(a=>{a.response?console.error("Error de servidor:",a.response.data):a.request?console.error("Error de red:",a.request):console.error("Error de configuración:",a.message)})},le=async()=>{D.post("/get-ubigeo",{term:F.value}).then(a=>{H.value=a.data.datos.data}).catch(a=>{a.response?console.error("Error de servidor:",a.response.data):a.request?console.error("Error de red:",a.request):console.error("Error de configuración:",a.message)})},pe=async()=>{D.post("/get-colegios-ubigeo",{term:M.value,ubigeo:q.value}).then(a=>{J.value=a.data.datos.data}).catch(a=>{a.response?console.error("Error de servidor:",a.response.data):a.request?console.error("Error de red:",a.request):console.error("Error de configuración:",a.message)})},_e=(a,o,p)=>{ke[a]({message:o,description:p})},ve=()=>{t.tipo_doc=1,t.paterno="",t.materno="",t.nombres="",t.sexo=null,t.fec_nac="",t.celular="",t.correo="",t.pais=1,t.ubigeo_residencia="",t.grado=1,t.ubigeo_colegio=null,t.id_colegio=null,t.terminos=!1,t.id_pago=null,K.valu=!0,V.value=null,K.value=null,w.value=null},fe=i([{title:"Nro_Doc",dataIndex:"nro_doc"},{title:"Nombres",dataIndex:"nombres",responsive:["xs","sm","md","lg"]},{title:"Sexo",dataIndex:"sexo",responsive:["md"]},{title:"F. Nac",dataIndex:"fec_nacimiento",responsive:["lg"]},{title:"Instruccion",dataIndex:"instruccion",width:"120px",align:"center"},{title:"Lugar",dataIndex:"lugar",responsive:["lg"]},{title:"Acciones",dataIndex:"acciones",width:"120px",align:"center"}]);return ee(),le(),(a,o)=>{const p=c("a-input"),P=c("a-tag"),d=c("a-select-option"),f=c("a-select"),x=c("a-button"),me=c("a-popconfirm"),ge=c("a-table"),xe=c("a-pagination"),b=c("a-row"),ae=c("a-radio"),be=c("a-radio-group"),v=c("a-form-item"),ye=c("a-divider"),_=c("a-col"),he=c("a-date-picker"),T=c("a-tooltip"),Y=c("a-auto-complete"),Ie=c("a-form"),we=c("a-modal");return g(),C(oe,null,[e(y(Ue),{title:"participantes"}),e(Re,null,{default:l(()=>[n("div",qe,[n("div",Fe,[n("div",Pe,[n("div",je,[e(p,{type:"text",placeholder:"Buscar",value:A.value,"onUpdate:value":o[0]||(o[0]=s=>A.value=s),style:{"max-width":"340px","padding-left":"30px","border-radius":"6px"}},null,8,["value"]),n("div",Ke,[e(y(Ce))])])]),n("div",null,[e(ge,{dataSource:X.value,columns:fe.value,pagination:!1},{bodyCell:l(({column:s,index:xl,record:m})=>[s.dataIndex==="nro_doc"?(g(),S(P,{key:0,color:"#4f4f4f",style:{width:"80px"}},{default:l(()=>[u(j(m.nro_doc),1)]),_:2},1024)):k("",!0),s.dataIndex==="nombres"?(g(),C("div",Le,[n("span",null,j(m.nombres)+" "+j(m.paterno)+" "+j(m.materno),1)])):k("",!0),s.dataIndex==="instruccion"?(g(),S(f,{key:2,ref:"select",value:m.instruccion,"onUpdate:value":U=>m.instruccion=U,placeholder:"Seleccionar",style:{width:"100px"}},{default:l(()=>[e(d,{value:1},{default:l(()=>[Be]),_:1}),e(d,{value:2},{default:l(()=>[Me]),_:1}),e(d,{value:3},{default:l(()=>[Te]),_:1}),e(d,{value:4},{default:l(()=>[Ye]),_:1})]),_:2},1032,["value","onUpdate:value"])):k("",!0),s.dataIndex==="sexo"?(g(),S(f,{key:3,ref:"select",value:m.sexo,"onUpdate:value":U=>m.sexo=U,placeholder:"Seleccionar",style:{width:"60px"}},{default:l(()=>[e(d,{value:"1"},{default:l(()=>[ze]),_:1}),e(d,{value:"2"},{default:l(()=>[Ge]),_:1})]),_:2},1032,["value","onUpdate:value"])):k("",!0),s.dataIndex==="acciones"?(g(),C(oe,{key:4},[e(x,{type:"success",class:"mr-1",style:{color:"#476175"},size:"small",disabled:""},{icon:l(()=>[e(y(De))]),_:1}),e(x,{class:"mr-1",onClick:U=>ce(m),style:{color:"blue"},size:"small"},{icon:l(()=>[e(y(Ve))]),_:2},1032,["onClick"]),e(me,{title:"¿Estas seguro de eliminar?",onConfirm:U=>a.eliminar(m),disabled:""},{default:l(()=>[e(x,{size:"small",style:{color:"crimson"},disabled:""},{icon:l(()=>[e(y(Se),{disabled:""})]),_:1})]),_:2},1032,["onConfirm"])],64)):k("",!0)]),_:1},8,["dataSource","columns"])]),n("div",$e,[n("div",null,[e(xe,{current:R.value,"onUpdate:current":o[1]||(o[1]=s=>R.value=s),"page-size":I.value,simple:"",total:G.value,"show-less-items":""},null,8,["current","page-size","total"])]),n("div",Ze,[e(f,{value:I.value,"onUpdate:value":o[2]||(o[2]=s=>I.value=s),style:{width:"90px"}},{default:l(()=>[e(d,{value:3},{default:l(()=>[u("3 Reg.")]),_:1}),e(d,{value:10},{default:l(()=>[u("10 Reg.")]),_:1}),e(d,{value:20},{default:l(()=>[u("20 Reg.")]),_:1}),e(d,{value:50},{default:l(()=>[u("50 Reg.")]),_:1}),e(d,{value:100},{default:l(()=>[u("100 Reg.")]),_:1})]),_:1},8,["value"])])])])]),e(we,{visible:O.value,"onUpdate:visible":o[23]||(o[23]=s=>O.value=s),footer:!1,centered:"",style:{width:"100%","max-width":"900px"}},{default:l(()=>[n("div",He,[e(b,{style:{display:"flex","justify-content":"center"}},{default:l(()=>[e(_,{span:24},{default:l(()=>[e(Ie,{ref_key:"formDatos",ref:Q,name:"form",model:t,rules:a.formRules},{default:l(()=>[e(b,null,{default:l(()=>[Je]),_:1}),e(b,{gutter:16},{default:l(()=>[e(_,{xs:24,sm:12,md:24,lg:24},{default:l(()=>[n("div",Qe,[n("div",null,[We,e(v,{name:"sexo",rules:[{required:!0,message:"Seleccione el sexo"}]},{default:l(()=>[n("div",null,[e(be,{value:t.sexo,"onUpdate:value":o[3]||(o[3]=s=>t.sexo=s),name:"radioGroup"},{default:l(()=>[e(ae,{value:"1"},{default:l(()=>[u("M")]),_:1}),e(ae,{value:"2"},{default:l(()=>[u("F")]),_:1})]),_:1},8,["value"])])]),_:1})]),e(ye,{type:"vertical",style:{height:"60px"}}),n("div",null,[Xe,e(v,{name:"tipo_doc",rules:[{required:!0,message:"Escoja el tipo",trigger:"change"}]},{default:l(()=>[n("div",null,[e(f,{value:t.tipo_doc,"onUpdate:value":o[4]||(o[4]=s=>t.tipo_doc=s),style:{width:"120px"}},{default:l(()=>[e(d,{value:1},{default:l(()=>[u("DNI")]),_:1}),e(d,{value:2},{default:l(()=>[u("Carnet. Ext")]),_:1})]),_:1},8,["value"])])]),_:1})])])]),_:1}),e(_,{xs:24,sm:12,md:8,lg:8},{default:l(()=>[t.tipo_doc===1?(g(),C("label",el,[u("DNI "),ll])):(g(),C("label",al,"N° Carnet Ext.")),e(v,{name:"nro_doc",rules:[{required:!0,message:"Ingrese el N° de documento"},{min:8,message:"El dni debe tener 8 digitos",trigger:"blur"}]},{default:l(()=>[t.tipo_doc===1?(g(),S(p,{key:0,value:t.nro_doc,"onUpdate:value":o[5]||(o[5]=s=>t.nro_doc=s),onInput:W,maxlength:8,disabled:t.terminos},null,8,["value","disabled"])):(g(),S(p,{key:1,value:t.nro_doc,"onUpdate:value":o[6]||(o[6]=s=>t.nro_doc=s),onInput:W,maxlength:12,disabled:t.terminos},null,8,["value","disabled"]))]),_:1})]),_:1}),e(_,{xs:24,sm:12,md:8,lg:8},{default:l(()=>[ol,e(v,{name:"nombres",rules:[{required:!0,message:"Ingrese los nombres"}]},{default:l(()=>[e(p,{value:t.nombres,"onUpdate:value":o[7]||(o[7]=s=>t.nombres=s)},null,8,["value"])]),_:1})]),_:1}),e(_,{xs:24,sm:12,md:8,lg:8},{default:l(()=>[tl,e(v,{name:"paterno",rules:[{required:!0,message:"Ingrese su primer apellido"}]},{default:l(()=>[e(p,{value:t.paterno,"onUpdate:value":o[8]||(o[8]=s=>t.paterno=s)},null,8,["value"])]),_:1})]),_:1}),e(_,{xs:24,sm:12,md:8,lg:8},{default:l(()=>[sl,e(v,{name:"materno",rules:[{required:!0,message:"Ingrese su segundo apellido"}]},{default:l(()=>[e(p,{value:t.materno,"onUpdate:value":o[9]||(o[9]=s=>t.materno=s)},null,8,["value"])]),_:1})]),_:1}),e(_,{xs:24,sm:12,md:8,lg:8},{default:l(()=>[nl,e(v,{name:"celular",rules:[{required:!0,message:"Ingrese el N° de celular"},{min:8,message:"El numero debe tener almenos 9 digitos",trigger:"blur"}]},{default:l(()=>[e(p,{value:t.celular,"onUpdate:value":o[10]||(o[10]=s=>t.celular=s),onInput:se,maxlength:9},null,8,["value"])]),_:1})]),_:1}),e(_,{xs:24,sm:12,md:8,lg:8},{default:l(()=>[ul,e(v,{name:"fec_nac",rules:[{required:!0,message:"Ingresa tu fecha de nacimiento",trigger:"change"},{validator:re,trigger:"change"}]},{default:l(()=>[e(he,{style:{width:"100%"},placeholder:"Seleccionar fec. nacimiento",value:t.fec_nac,"onUpdate:value":o[11]||(o[11]=s=>t.fec_nac=s),format:"DD/MM/YYYY"},null,8,["value"])]),_:1},8,["rules"])]),_:1}),e(_,{xs:24,sm:24,md:24,lg:24},{default:l(()=>[dl,e(v,{name:"correo",rules:[{required:!0,message:"Ingresa un correo valido",trigger:"change"},{type:"email",message:"Ingresa un correo valido"}]},{default:l(()=>[e(p,{value:t.correo,"onUpdate:value":o[12]||(o[12]=s=>t.correo=s)},null,8,["value"])]),_:1})]),_:1})]),_:1}),e(b,null,{default:l(()=>[rl]),_:1}),e(b,{gutter:16},{default:l(()=>[e(_,{xs:24,sm:12,md:8,lg:8},{default:l(()=>[il,e(v,{name:"pais"},{default:l(()=>[e(f,{value:t.pais,"onUpdate:value":o[13]||(o[13]=s=>t.pais=s),placeholder:"Seleccione el país"},{default:l(()=>[e(d,{value:1},{default:l(()=>[u("PERÚ")]),_:1}),e(d,{value:2},{default:l(()=>[u("BOLIVIA")]),_:1}),e(d,{value:3},{default:l(()=>[u("CHILE")]),_:1}),e(d,{value:4},{default:l(()=>[u("VENEZUELA")]),_:1}),e(d,{value:5},{default:l(()=>[u("COLOMBIA")]),_:1}),e(d,{value:6},{default:l(()=>[u("ECUADOR")]),_:1}),e(d,{value:7},{default:l(()=>[u("ARGENTINA")]),_:1}),e(d,{value:8},{default:l(()=>[u("BRASIL")]),_:1})]),_:1},8,["value"])]),_:1})]),_:1}),e(_,{xs:24,sm:24,md:24,lg:16},{default:l(()=>[cl,e(v,{name:"ubigeo_residencia"},{default:l(()=>[e(Y,{value:V.value,"onUpdate:value":o[15]||(o[15]=s=>V.value=s),options:Z.value,onSelect:ne},{default:l(()=>[e(p,{placeholder:"Departamento",value:w.value,"onUpdate:value":o[14]||(o[14]=s=>w.value=s),onKeypress:a.handleKeyPress},{suffix:l(()=>[e(T,{title:"Extra information"},{default:l(()=>[e(y(z))]),_:1})]),_:1},8,["value","onKeypress"])]),_:1},8,["value","options"])]),_:1})]),_:1})]),_:1}),e(b,null,{default:l(()=>[pl]),_:1}),e(b,{gutter:16},{default:l(()=>[e(_,{xs:24,sm:12,md:8,lg:8},{default:l(()=>[_l,e(v,{name:"grado"},{default:l(()=>[e(f,{value:t.grado,"onUpdate:value":o[16]||(o[16]=s=>t.grado=s),style:{width:"100%"}},{default:l(()=>[e(d,{value:1},{default:l(()=>[u("SECUNDARIA CONCLUIDA")]),_:1}),e(d,{value:2},{default:l(()=>[u("SECUNDARIA 5TO AÑO")]),_:1}),e(d,{value:3},{default:l(()=>[u("SECUNDARIA 4T0 AÑO")]),_:1}),e(d,{value:4},{default:l(()=>[u("SECUNDARIA 3ER AÑO")]),_:1})]),_:1},8,["value"])]),_:1})]),_:1}),e(_,{xs:24,sm:24,md:24,lg:16},{default:l(()=>[vl,e(v,{name:"ubigeo_colegio"},{default:l(()=>[e(Y,{value:L.value,"onUpdate:value":o[18]||(o[18]=s=>L.value=s),options:H.value,onSelect:ue},{default:l(()=>[e(p,{placeholder:"Procedencia del Colegio",value:F.value,"onUpdate:value":o[17]||(o[17]=s=>F.value=s),onKeypress:a.handleKeyPress},{suffix:l(()=>[e(T,{title:"Extra information"},{default:l(()=>[e(y(z))]),_:1})]),_:1},8,["value","onKeypress"])]),_:1},8,["value","options"])]),_:1})]),_:1}),e(_,{xs:24,sm:24,md:24,lg:24},{default:l(()=>[fl,e(v,null,{default:l(()=>[e(Y,{value:B.value,"onUpdate:value":o[20]||(o[20]=s=>B.value=s),options:J.value,onSelect:de},{default:l(()=>[e(p,{placeholder:"Procedencia del Colegio",value:M.value,"onUpdate:value":o[19]||(o[19]=s=>M.value=s),onKeypress:a.handleKeyPress},{suffix:l(()=>[e(T,{title:"Extra information"},{default:l(()=>[e(y(z))]),_:1})]),_:1},8,["value","onKeypress"])]),_:1},8,["value","options"])]),_:1})]),_:1})]),_:1}),e(b,null,{default:l(()=>[e(_,{span:24},{default:l(()=>[n("div",ml,[e(x,{class:"mr-4",onClick:o[21]||(o[21]=s=>a.Cancelar())},{default:l(()=>[u(" Cancelar ")]),_:1}),e(x,{type:"primary",style:{width:"90px",background:"#340691","border-radius":"4px",border:"none"},onClick:o[22]||(o[22]=s=>ie())},{default:l(()=>[u("Guardar")]),_:1})])]),_:1})]),_:1})]),_:1},8,["model","rules"])]),_:1})]),_:1})])]),_:1},8,["visible"])]),_:1})],64)}}},Cl=Oe(gl,[["__scopeId","data-v-ca4f1f14"]]);export{Cl as default};