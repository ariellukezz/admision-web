import{r,e as U,c as v,a as e,u as h,w as o,F as O,j as y,o as m,X as W,d as l,t as V,b as E,f as b,g as d,p as J,h as M}from"./app-41695359.js";import{A as T}from"./LayoutDocente-0a533bab.js";import X from"./voucher-4604289e.js";import{_ as $}from"./_plugin-vue_export-helper-c27b6911.js";import{C as G}from"./CreditCardOutlined-23136118.js";import"./DropdownLink-820fb3a2.js";import"./logotiny-c4b525af.js";const p=x=>(J("data-v-03fef4ce"),x=x(),M(),x),H=p(()=>l("label",{style:{"margin-right":"10px"}}," Buscar:",-1)),Q={style:{height:"34px"}},Y={style:{"font-weight":"700",color:"black","font-size":".7rem"}},Z={style:{"margin-top":"-10px"}},ee={style:{"font-size":".8rem","text-transform":"uppercase"}},te={style:{"margin-right":"-8px","margin-left":"-8px","min-width":"600px"}},le=p(()=>l("label",null,"DNI",-1)),oe=p(()=>l("label",null,"Ap. paterno",-1)),ae=p(()=>l("label",null,"Proceso",-1)),se=p(()=>l("label",null,"Nombres",-1)),ne=p(()=>l("label",null,"Ap. Materno",-1)),ie=p(()=>l("label",null,"Puntaje",-1)),ue=p(()=>l("label",null,"Programa",-1)),re={class:"flex justify-end mb-4"},de={class:"",style:{width:"100%",height:"380px"}},pe={key:0},ce={style:{width:"100%",height:"380px",position:"relative",overflow:"hidden"}},_e={key:0},ve=["src"],me={style:{height:"380px"}},fe={style:{width:"100%",height:"380px",position:"relative",overflow:"hidden"}},ge={key:0},he=["src"],ye={style:{width:"100%",height:"380px",position:"relative",overflow:"hidden"}},be={key:0},xe=["src"],we={style:{width:"100%",height:"380px",position:"relative",overflow:"hidden"}},ke={key:0},Ie=p(()=>l("div",null,null,-1)),qe={class:"mt-4 flex justify-end",style:{"margin-right":"-10px"}},Se={__name:"imprimir",setup(x){const w=window.location.origin,f=r(null),s=r(null),I=r([]);r();const q=r([]);r(!1);const K=r([]),P=async()=>{let n=await y.get("get-requisitos");K.value=n.data.datos},j=r(null),i=r(null),N=async()=>{let n=await y.get("get-ingresante/"+f.value);i.value=n.data.datos},B=async(n="",t=1)=>{let u=await y.post("get-postulantes?page="+t,{term:f.value});I.value=u.data.datos.data},S=async()=>{q.value=[];let n=await y.post("get-postulante-requisitos",{dni:s.value});n.data.estado===!0&&(q.value=JSON.parse(n.data.datos.requisitos))};S(),U(f,(n,t)=>{B()}),U(s,(n,t)=>{S(),N()});const D=async()=>{let n=await y.post("control-biometrico",{dni:s.value});A(n.data.datos)},A=n=>{var t=document.createElement("iframe");t.style.display="none",t.src=w+"/documentos/cepre2023-II/"+n+"/control-biometrico-unido.pdf",document.body.appendChild(t),t.contentWindow.focus(),t.contentWindow.print()};return P(),(n,t)=>{const u=d("a-input"),z=d("a-auto-complete"),g=d("a-col"),k=d("a-row"),c=d("a-form-item"),C=d("a-button"),_=d("a-tab-pane"),F=d("a-tabs"),R=d("a-card");return m(),v(O,null,[e(h(W),{title:"Revisión de documentos"}),e(T,null,{default:o(()=>[l("div",null,[e(R,{style:{background:"white",height:"calc(100vh - 90px)",overflow:"hidden"},class:"mb-0 p-0"},{default:o(()=>[e(k,{gutter:16,class:"mb-3"},{default:o(()=>[e(g,{span:24,sm:24,md:24,lg:24,style:{display:"flex","justify-content":"end","align-items":"end"}},{default:o(()=>[l("div",null,[H,e(z,{value:s.value,"onUpdate:value":t[1]||(t[1]=a=>s.value=a),options:I.value,style:{width:"300px"},onSelect:n.onSelect,onSearch:n.onSearch},{suffix:o(()=>[e(h(G))]),option:o(({value:a,label:L})=>[l("div",Q,[l("div",null,[l("span",Y,V(a),1)]),l("div",Z,[l("span",ee,V(L),1)])])]),default:o(()=>[e(u,{ref_key:"dniInput",ref:j,placeholder:"Buscar",value:f.value,"onUpdate:value":t[0]||(t[0]=a=>f.value=a),onKeypress:n.handleKeyPress},null,8,["value","onKeypress"])]),_:1},8,["value","options","onSelect","onSearch"])])]),_:1})]),_:1}),e(k,{gutter:16},{default:o(()=>[e(g,{span:24,sm:24,md:24,lg:24,style:{border:"1px solid #d9d9d9","min-width":"600px"},class:"m-0 p-0"},{default:o(()=>[l("div",te,[e(F,{activeKey:n.activeKey,"onUpdate:activeKey":t[9]||(t[9]=a=>n.activeKey=a),type:"card",style:{}},{default:o(()=>[e(_,{key:"7",tab:"Datos Personales",class:"pl-2 pr-2"},{default:o(()=>[l("div",null,[e(k,{gutter:16},{default:o(()=>[e(g,{xs:24,sm:12,md:12,lg:12,xl:12},{default:o(()=>[e(c,{rules:[{required:!0,message:"El nombre es obligatorio"}]},{default:o(()=>[le,e(u,{disabled:"",value:i.value.nro_doc,"onUpdate:value":t[2]||(t[2]=a=>i.value.nro_doc=a)},null,8,["value"])]),_:1}),e(c,{rules:[{required:!0,message:"El nombre es obligatorio"}]},{default:o(()=>[oe,e(u,{value:i.value.primer_apellido,"onUpdate:value":t[3]||(t[3]=a=>i.value.primer_apellido=a)},null,8,["value"])]),_:1}),e(c,{rules:[{required:!0,message:"El nombre es obligatorio"}]},{default:o(()=>[ae,e(u,{value:i.value.proceso,"onUpdate:value":t[4]||(t[4]=a=>i.value.proceso=a)},null,8,["value"])]),_:1})]),_:1}),e(g,{xs:24,sm:12,md:12,lg:12,xl:12},{default:o(()=>[e(c,{rules:[{required:!0,message:"El nombre es obligatorio"}]},{default:o(()=>[se,e(u,{value:i.value.nombres,"onUpdate:value":t[5]||(t[5]=a=>i.value.nombres=a)},null,8,["value"])]),_:1}),e(c,{rules:[{required:!0,message:"El nombre es obligatorio"}]},{default:o(()=>[ne,e(u,{value:i.value.segundo_apellido,"onUpdate:value":t[6]||(t[6]=a=>i.value.segundo_apellido=a)},null,8,["value"])]),_:1}),e(c,{rules:[{required:!0,message:"El nombre es obligatorio"}]},{default:o(()=>[ie,e(u,{value:i.value.puntaje,"onUpdate:value":t[7]||(t[7]=a=>i.value.puntaje=a)},null,8,["value"])]),_:1})]),_:1}),e(g,{xs:24,sm:24,md:24,lg:24,xl:24},{default:o(()=>[e(c,{rules:[{required:!0,message:"El nombre es obligatorio"}]},{default:o(()=>[ue,e(u,{value:i.value.programa,"onUpdate:value":t[8]||(t[8]=a=>i.value.programa=a)},null,8,["value"])]),_:1})]),_:1})]),_:1}),l("div",re,[e(C,null,{default:o(()=>[E(" Actualizar Datos")]),_:1})])])]),_:1}),e(_,{key:"2",tab:"Voucher",class:"pl-2 pr-2"},{default:o(()=>[l("div",de,[s.value!==null&&s.value.length===8?(m(),v("div",pe,[e(X,{dni:s.value},null,8,["dni"])])):b("",!0)])]),_:1}),e(_,{key:"1",tab:"Solicitud",class:"pl-2 pr-2"},{default:o(()=>[l("div",null,[l("div",ce,[s.value!==null&&s.value.length===8?(m(),v("div",_e,[l("iframe",{src:h(w)+"/documentos/cepre2023-II/"+s.value+"/solicitud-1.pdf",style:{top:"-54px",position:"absolute"},width:"100%",height:"100%",scrolling:"yes",frameborder:"1"},null,8,ve)])):b("",!0)])])]),_:1}),e(_,{key:"3",tab:"Certificado"},{default:o(()=>[l("div",me,[l("div",fe,[s.value!==null&&s.value.length===8?(m(),v("div",ge,[l("iframe",{src:h(w)+"/documentos/cepre2023-II/"+s.value+"/certificado-1.pdf",style:{top:"-54px",position:"absolute"},width:"100%",height:"470px",scrolling:"yes",frameborder:"1"},null,8,he)])):b("",!0)])])]),_:1}),e(_,{key:"4",tab:"Ex vocacional"},{default:o(()=>[l("div",null,[l("div",ye,[s.value!==null&&s.value.length===8?(m(),v("div",be,[l("iframe",{src:h(w)+"/documentos/cepre2023-II/"+s.value+"/constancia%20vocacional-1.pdf",style:{top:"-54px",position:"absolute"},width:"100%",height:"470px",scrolling:"yes",frameborder:"1"},null,8,xe)])):b("",!0)])])]),_:1}),e(_,{key:"5",tab:"Cert Cepre"},{default:o(()=>[l("div",null,[l("div",we,[s.value!==null&&s.value.length===8?(m(),v("div",ke)):b("",!0)])])]),_:1}),e(_,{key:"6",tab:"D. Biométricos"},{default:o(()=>[Ie]),_:1})]),_:1},8,["activeKey"])])]),_:1})]),_:1}),l("div",qe,[e(C,{type:"primary",onClick:t[10]||(t[10]=a=>D())},{default:o(()=>[E("Imprimir")]),_:1})])]),_:1})])]),_:1})],64)}}},Ne=$(Se,[["__scopeId","data-v-03fef4ce"]]);export{Ne as default};