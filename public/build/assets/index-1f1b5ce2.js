import{_ as H}from"./logotiny-c4b525af.js";import{_ as Y}from"./logoDAD-c76d0a0b.js";import{_ as Z}from"./_plugin-vue_export-helper-c27b6911.js";import{f as u,o as n,s as C,w as a,a as o,d as e,t as f,L as K,p as Q,h as W,r as v,x as ee,c as r,u as E,F as N,Z as te,i as oe,z as se,b,g as _,n as ae}from"./app-ce8068d7.js";import ne from"./certificado-256340ce.js";import ie from"./dni-33fa77db.js";import"./es-9d394e7d.js";const le="/build/assets/image-b69d7d4b.png";const P=p=>(Q("data-v-05daf995"),p=p(),W(),p),ce={class:"headPre"},de=P(()=>e("div",{class:"flex justify-between mb-2 logo-container"},[e("div",{class:"logoPre"},[e("img",{src:H,class:"logo-img"}),e("div",{class:"x"},[e("div",{class:"container-pre"},[e("span",{class:"puntajex"},"DIRECCIÓN DE")]),e("h1",{class:"logoPreAD"},"ADMISIÓN")]),e("img",{src:Y,class:"logo-img"})])],-1)),re={class:"flex justify-center title-proceso-container"},_e={class:"title-proceso text-center"},ue=P(()=>e("div",{class:"flex justify-center titulo-pre"},[e("div",null,[e("div",{class:"flex justify-center",style:{height:"40px"}},[e("hr",{class:"line-pre mr-2"}),e("span",{style:{"font-size":"1.4rem","text-transform":"capitalize"}},"Puntaje obtenido"),e("hr",{class:"line-pre ml-2"})])])],-1)),me=P(()=>e("div",{style:{background:"#267ec5ea",height:"3px",width:"100%"}},null,-1)),pe={class:"container"},ge={__name:"LayoutPuntaje",props:["nombre"],setup(p){return(I,m)=>{const j=u("a-layout-content"),w=u("a-layout");return n(),C(w,{style:{"min-height":"100vh"}},{default:a(()=>[o(w,null,{default:a(()=>[e("div",ce,[de,e("div",re,[e("span",_e,"EXAMEN "+f(p.nombre),1)]),ue]),me,o(j,{class:"content flex justify-center"},{default:a(()=>[e("div",pe,[K(I.$slots,"default",{},void 0,!0)])]),_:3})]),_:3})]),_:3})}}},ve=Z(ge,[["__scopeId","data-v-05daf995"]]);const fe=e("h1",{style:{"font-size":"1.7rem"}},"Resultados del examen",-1),he={style:{"text-align":"justify","font-size":"1em"}},ye={class:"flex",style:{"min-height":"30px","align-items":"center"}},xe=e("div",{class:"mr-1",style:{width:"30px","min-width":"30px"}},[e("img",{src:le,alt:""})],-1),be={class:"flex"},we=e("div",{class:"mr-1"}," Descargar",-1),Ie={style:{"margin-top":"-2px"}},je=e("div",{class:"mt-6"},null,-1),ke=e("h1",{style:{"font-size":"1.7rem"}},"Consultar puntaje",-1),Ee={class:"mt-6",style:{}},Ne={style:{"margin-top":"-10px","text-align":"left"}},Ce={class:"ml-1 mb-2"},Pe=e("div",{class:"ml-4"},[e("div",{class:"mb-2"},"1. Ingrese su DNI en el campo de texto proporcionado."),e("div",{class:"mb-2"},"2. Ingrese el código mostrado en pantalla."),e("div",null,'3. Haga clic en el botón "Consultar Puntaje".')],-1),De={class:"flex mt-6 justify-left",style:{}},$e={style:{width:"100%"}},Se={style:{"min-width":"360px","max-width":"420px"}},Ae=e("div",{style:{"margin-bottom":"7px"}},[e("label",null,"N° Documento")],-1),Le={class:"texto-imagen"},Re=e("div",{class:"overlay"},null,-1),ze={class:"mb-4"},Ue=e("div",{class:"mt-3"},[e("label",null,"Código")],-1),Te={class:"mt-4",style:{display:"flex","justify-content":"left"}},Me={key:0,class:"mt-6"},Oe={key:0},Be={key:1},Fe={key:0},Ve={key:1},qe={class:"flex justify-center"},Xe={key:2},Je={class:"flex justify-center"},Ge={key:1,class:"mt-6"},He=e("div",{class:"mt-6"},null,-1),Ye=e("h1",{style:{"font-size":"1.7rem"}},"Subir archivos ",-1),Ze={class:"mt-2"},at={__name:"index",props:["procceso_seleccionado"],setup(p){const I=window.location.origin,m=p,j=v(""),w=v(),i=ee({dni:"",codigo_secreto:""}),A=t=>{i.dni=t.target.value.replace(/\D/g,"")},L=async()=>{y.value=0;try{const t=await axios.post("/get-puntajes-proceso/",{dni:i.dni,id_proceso:m.procceso_seleccionado.id});if(t.data.estado==!1)O("error","No se han encontrado datos en este proceso",""),i.dni="",g.value=[];else if(t.data&&t.data.datos){g.value=t.data.datos;const s=g.value.some(c=>c.condicion==="SI");y.value=s?1:0,y.value==0&&(i.dni=""),$()}else g.value=[]}catch(t){console.error("Error al obtener puntajes:",t),g.value=[]}};function R(t,s){return new Promise((c,d)=>{s?h.value!==j.value?d(new Error("El código ingresado no coincide.")):c():d(new Error("Por favor, ingresa el código secreto."))})}const z=v([]),U=async()=>{let t=await axios.get("/get-puntajes-maximos-proceso/"+m.procceso_seleccionado.id);t.data.estado==!0&&(z.value=t.data.datos)},D=v([]),T=async()=>{let t=await axios.post("/get-documentos-resultados",{id_proceso:m.procceso_seleccionado.id});t.data.estado==!0&&(D.value=t.data.datos)},M=async t=>{try{const s=await axios({url:t,method:"GET",responseType:"blob"}),c=window.URL.createObjectURL(new Blob([s.data])),d=document.createElement("a");d.href=c,d.setAttribute("download",t.split("/").pop()),document.body.appendChild(d),d.click(),d.remove()}catch(s){console.error("Error descargando archivo:",s)}};U(),T();const h=v(null),$=async()=>{let t=await axios.get("/generar-captcha");h.value=t.data.captcha},O=(t,s,c)=>{ae[t]({message:s,description:c})},g=v([]),y=v(null),B=[{title:"Nombre",dataIndex:"nombres"},{title:"Programa",dataIndex:"programa",responsive:["sm"]},{title:"Puntaje",dataIndex:"puntaje",align:"center"},{title:"Vocacional",dataIndex:"puntaje_vocacional",align:"center"},{title:"Fecha",dataIndex:"fecha",align:"center"},{title:"Condición",dataIndex:"condicion",align:"center"}];function F(t){return t.split("-").reverse().join("-")}return $(),(t,s)=>{const c=u("a-button"),d=u("a-input"),S=u("a-form-item"),V=u("a-form"),k=u("a-tag"),q=u("a-table"),X=u("a-alert"),J=u("a-card");return n(),r(N,null,[o(E(te),{title:"Resultados"}),o(ve,{nombre:m.procceso_seleccionado.nombre},{default:a(()=>[o(J,null,{default:a(()=>[e("div",null,[fe,e("p",he," Para consultar la relación de ingresantes del EXAMEN "+f(m.procceso_seleccionado.nombre)+', haga clic en el botón "Descargar" correspondiente a la fecha de su interés. El archivo se descargará automáticamente, y podrá abrirlo para visualizar el listado de ingresantes ordenado por mérito. ',1)]),(n(!0),r(N,null,oe(D.value,l=>(n(),r("div",{key:l,class:"flex justify-between p-2",style:{"border-bottom":"solid 1px #d9d9d9"}},[e("div",ye,[xe,e("div",null,[e("span",null,f(l.nombre),1)])]),e("div",null,[o(c,{onClick:G=>M(E(I)+"/"+l.url),style:{height:"36px",background:"#088dcf",color:"white",border:"none"}},{default:a(()=>[e("div",be,[we,e("div",Ie,[o(E(se))])])]),_:2},1032,["onClick"])])]))),128)),je,ke,e("div",Ee,[e("div",Ne,[e("div",Ce," Para consultar el puntaje del EXAMEN "+f(m.procceso_seleccionado.nombre)+", siga estos pasos: ",1),Pe]),e("div",De,[e("div",$e,[e("div",Se,[o(V,{ref_key:"formRef",ref:w,model:i,name:"inicio_dni"},{default:a(()=>[Ae,o(S,{name:"dni",rules:[{required:!0,message:"Por favor ingresa tu DNI",trigger:"change"},{min:8,message:"El dni debe tener 8 digitos",trigger:"blur"}]},{default:a(()=>[o(d,{value:i.dni,"onUpdate:value":s[0]||(s[0]=l=>i.dni=l),onInput:A,disabled:y.value,maxlength:8,placeholder:"N° Documento"},null,8,["value","disabled"])]),_:1}),e("div",Le,f(h.value),1),Re,e("div",ze,[Ue,o(S,{name:"codigo_secreto",rules:[{required:!0,message:"Ingresa el código del cuadro",trigger:"change"},{min:6,message:"El ubigeo debe tener 6 caracteres",trigger:"blur"},{validator:R,trigger:"change"}]},{default:a(()=>[o(d,{value:i.codigo_secreto,"onUpdate:value":s[1]||(s[1]=l=>i.codigo_secreto=l),maxlength:6,placeholder:"Ingresa el codigo"},null,8,["value"])]),_:1},8,["rules"])]),e("div",Te,[h.value!==i.codigo_secreto?(n(),C(c,{key:0,type:"primary",disabled:""},{default:a(()=>[b("CONSULTAR PUNTAJE")]),_:1})):_("",!0),i.codigo_secreto===h.value?(n(),C(c,{key:1,onClick:s[2]||(s[2]=l=>L()),style:{background:"goldenrod",border:"none",color:"white"}},{default:a(()=>[b("CONSULTAR PUNTAJE")]),_:1})):_("",!0)])]),_:1},8,["model"])])])])]),g.value.length>0?(n(),r("div",Me,[o(q,{columns:B,"data-source":g.value,pagination:!1,size:"small"},{bodyCell:a(({column:l,index:G,record:x})=>[l.dataIndex==="puntaje"?(n(),r("div",Oe,f(x.puntaje),1)):_("",!0),l.dataIndex==="fecha"?(n(),r("div",Be,f(F(x.fecha)),1)):_("",!0),l.dataIndex==="condicion"?(n(),r(N,{key:2},[x.condicion=="SI"?(n(),r("div",Fe,[o(k,{color:"blue"},{default:a(()=>[b(" Ingresó ")]),_:1})])):_("",!0),x.condicion=="NO"?(n(),r("div",Ve,[e("div",qe,[o(k,{color:"orange"},{default:a(()=>[b(" No ingresó ")]),_:1})])])):_("",!0),x.condicion=="CL"?(n(),r("div",Xe,[e("div",Je,[o(k,{color:"yellow"},{default:a(()=>[b(" Clasificado ")]),_:1})])])):_("",!0)],64)):_("",!0)]),_:1},8,["data-source"])])):_("",!0),y.value===1?(n(),r("div",Ge,[o(X,{message:"! MUY IMPORTANTE !",description:"Los postulantes que hayan alcanzado una vacante deberán subir su Certificado de Estudios y DNI en formato PDF con un peso max. de 2mb",type:"info"}),He,Ye,e("div",null,[o(ne,{id_proceso:m.procceso_seleccionado.id,dni:i.dni},null,8,["id_proceso","dni"])]),e("div",Ze,[o(ie,{id_proceso:m.procceso_seleccionado.id,dni:i.dni},null,8,["id_proceso","dni"])])])):_("",!0)]),_:1})]),_:1},8,["nombre"])],64)}}};export{at as default};