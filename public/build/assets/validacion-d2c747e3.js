import{r as i,e as x,x as _,w as s,M as V,o as n,d as l,a as u,u as z,S as $,K as D,f as d,c as b,t as v,b as k,F as K,n as L,g as c}from"./app-c81d9251.js";import{A as M}from"./LayoutDocente-c2ff9d8d.js";import"./DropdownLink-f568c492.js";import"./logotiny-c4b525af.js";import"./_plugin-vue_export-helper-c27b6911.js";const P={style:{}},q={class:"mb-2 flex justify-end"},G={key:1},H={style:{"font-weight":"bold","font-size":"0.9rem"}},J={key:2,class:"flex"},Q={style:{"text-transform":"uppercase","font-size":".9rem"}},R={class:"flex justify-end"},W=["src"],se={__name:"validacion",setup(X){const h=i([]),y=i(!1),w=i(""),m=i(""),C=i(0),f=i(1),g=i(2),S=t=>{console.log("http://admision-web.test/"+t.url),w.value="http://admision-web.test/"+t.url,y.value=!0},p=async()=>{let t=await V.post("get-certificados-revision?page="+f.value,{term:m.value,paginasize:g.value});h.value=t.data.datos.data,C.value=t.data.datos.total},B=async t=>{let e=0;t.verificado===0?e=1:e=0;let r=await V.post("cambiar-estado",{id:t.id,estado:e});O("success",r.data.titulo,r.data.mensaje),p()};x(m,(t,e)=>{p()}),x(f,(t,e)=>{p()}),x(g,(t,e)=>{p()});const O=(t,e,r)=>{L[t]({message:e,description:r})};p();const E=[{title:"ver",dataIndex:"ver",align:"center"},{title:"Codigo",dataIndex:"cod",key:"codigo",align:"center"},{title:"Tipo",dataIndex:"tipo",key:"tipo",align:"center"},{title:"Postulante",dataIndex:"nombres",key:"nombres"},{title:"Estado",dataIndex:"verificado",key:"verificado",align:"center"},{title:"operation",dataIndex:"operation"}];return(t,e)=>{const r=c("a-input"),I=c("a-tag"),N=c("a-button"),U=c("a-table"),j=c("a-pagination"),A=c("a-card"),F=c("a-modal");return n(),_(M,null,{default:s(()=>[l("div",P,[u(A,{style:{background:"white"},class:"m-0 p-0"},{default:s(()=>[l("div",q,[u(r,{placeholder:"Buscar",value:m.value,"onUpdate:value":e[0]||(e[0]=a=>m.value=a),style:{"max-width":"300px"}},{suffix:s(()=>[u(z($))]),_:1},8,["value"])]),l("div",null,[u(U,{size:"small",dataSource:h.value,columns:E,pagination:!1},{bodyCell:s(({column:a,text:Y,record:o})=>[a.dataIndex==="ver"?(n(),_(z(D),{key:0,onClick:T=>S(o),class:"custom-icon"},null,8,["onClick"])):d("",!0),a.dataIndex==="cod"?(n(),b("div",G,[l("span",H,v(o.cod),1)])):d("",!0),a.dataIndex==="nombres"?(n(),b("div",J,[l("span",Q,v(o.dni)+" "+v(o.nombres)+" "+v(o.paterno)+" "+v(o.materno),1)])):d("",!0),a.dataIndex==="verificado"?(n(),b(K,{key:3},[o.verificado===0?(n(),_(I,{key:0,color:"error"},{default:s(()=>[k("no verificado")]),_:1})):d("",!0),o.verificado===1?(n(),_(I,{key:1,color:"success"},{default:s(()=>[k("verificado")]),_:1})):d("",!0)],64)):d("",!0),a.dataIndex==="operation"?(n(),_(N,{key:4,class:"custom-button",onClick:T=>B(o),type:"primary"},{default:s(()=>[k("Validar")]),_:2},1032,["onClick"])):d("",!0)]),_:1},8,["dataSource"]),l("div",R,[u(j,{current:f.value,"onUpdate:current":e[1]||(e[1]=a=>f.value=a),pageSize:g.value,"onUpdate:pageSize":e[2]||(e[2]=a=>g.value=a),total:C.value,"show-size-changer":"","show-less-items":""},null,8,["current","pageSize","total"])])])]),_:1})]),u(F,{width:"80%",height:"90%",visible:y.value,"onUpdate:visible":e[3]||(e[3]=a=>y.value=a),title:"Certificado",style:{"margin-top":"-70px"},onOk:t.handleOk},{default:s(()=>[l("iframe",{src:w.value,width:"100%",height:"450px"},null,8,W)]),_:1},8,["visible","onOk"])]),_:1})}}};export{se as default};