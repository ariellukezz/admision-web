import{_ as j}from"./logotiny-c4b525af.js";import{_ as V}from"./logoDAD-c76d0a0b.js";import{r as p,e as P,f as r,o as m,c as g,d as l,t as h,a as e,w as t,F as U,i as C,M as N,l as R,b as s,k as E}from"./app-9aed8e38.js";const T={class:"flex justify-center mt-6 mb-4",style:{}},z={class:"flex justify-center p-3",style:{width:"820px",background:"white",border:"solid #d9d9d9 1px","border-radius":"10px"}},I={style:{width:"300px","margin-right":"10px"}},Q=N('<div class="flex justify-center mb-2"><img src="'+j+'" width="60"></div><div class="flex justify-center" style="height:60px;width:300px;"><div class="pl-1 pr-1" style="font-size:1.0rem;text-align:center;"><div>Universidad Nacional del Altiplano</div><div style="margin-top:-5px;">Vicerrectorado Académico</div><div style="margin-top:-5px;">Dirección de Admisión</div></div></div><div class="flex justify-center" style="height:30px;width:300px;font-weight:bold;"> HOJA DE IDENTIFICACIÓN </div>',3),F={class:"flex justify-center mb-3"},H={style:{"border-radius":"5px",border:"solid 1px #d9d9d9",margin:"6px",width:"90px","text-align":"center"}},J={class:"flex justify-center"},L={class:"ml-3 pr-4",style:{}},O={class:"mt-3"},M=l("label",null,"N° Documento",-1),q={class:"mt-3"},G=l("label",null,"Nombres",-1),K={class:"mt-3"},W=l("label",null,"Apellido Paterno",-1),X={class:"mt-3"},Y=l("label",null,"Apellido Materno",-1),Z=l("hr",{style:{border:"solid 1px #00000007",height:"600px","margin-right":"20px"}},null,-1),$=N('<div><div class="flex justify-center" style="height:60px;width:460px;"><div><img src="'+j+'" width="50"></div><div class="pl-3 pr-3" style="text-align:center;"><div>Universidad Nacional del Altiplano</div><div style="margin-top:-5px;">Vicerrectorado Académico</div><div style="margin-top:-5px;">Dirección de Admisión</div></div><div><img src="'+V+'" width="60"></div></div><div class="flex justify-center" style="height:30px;width:100%;font-weight:bold;"> HOJA DE RESPUESTAS </div></div>',1),ee={class:"flex justify-center mb-4"},te={class:"flex justify-center",style:{"margin-top":"0px","z-index":"2"}},le={style:{"border-radius":"5px",border:"solid 1px #d9d9d9",margin:"6px",width:"90px","text-align":"center"}},ae={style:{"margin-top":"-10px"}},se={class:"flex justify-center"},ie={style:{"margin-right":"30px"}},de={style:{padding:"0px",height:"20px","margin-bottom":"-20px",display:"flex","align-items":"center",overflow:"hidden"}},oe={style:{width:"20px","font-size":".7rem"}},ne={class:"ml-6 pb-4"},ue={style:{padding:"0px",height:"20px","margin-bottom":"-20px",display:"flex","align-items":"center",overflow:"hidden"}},ce={style:{width:"20px","font-size":".8rem"}},ve={__name:"ficha",props:["id_resp"],setup(S){const y=S,x=p("P");p("70757838"),p("JHON ARIEL"),p("LUQUE"),p("CUSACANI"),p(4);const d=p({dni:null,nombre:null,paterno:null,materno:null,tipo_id:null,respuestas:null}),b=async()=>{let n=await R.get("/get-ficha-respuesta/"+y.id_resp);d.value=n.data.datos,A.value=n.data.datos.respuestas,x.value=n.data.datos.res_tipo,B()},_=p([]),A=p("DDDBBAA   DD A DD AA"),B=()=>{let n=[];for(let i of A.value)i!==" "&&i!=="*"?n.push([i]):i===" "?n.push([]):n.push(["A","B","C","D","E"]);_.value=n};return P(y,(n,i)=>{b()}),p(!1),b(),(n,i)=>{const u=r("a-radio"),w=r("a-radio-group"),k=r("a-card"),v=r("a-input"),o=r("a-col"),c=r("a-checkbox"),D=r("a-checkbox-group");return m(),g("div",null,[l("div",T,[l("div",z,[l("div",I,[Q,l("div",F,[l("div",H,[l("span",null,"Aula: "+h(d.value.id_aula),1)])]),l("div",J,[e(k,{class:"pl-4",style:{width:"300px"}},{default:t(()=>[e(w,{value:d.value.ide_tipo,"onUpdate:value":i[0]||(i[0]=a=>d.value.ide_tipo=a)},{default:t(()=>[e(u,{value:"P"},{default:t(()=>[s("P")]),_:1}),e(u,{value:"Q"},{default:t(()=>[s("Q")]),_:1}),e(u,{value:"R"},{default:t(()=>[s("R")]),_:1}),e(u,{value:"S"},{default:t(()=>[s("S")]),_:1}),e(u,{value:"T"},{default:t(()=>[s("T")]),_:1})]),_:1},8,["value"])]),_:1})]),l("div",L,[l("div",O,[M,e(v,{value:d.value.dni,"onUpdate:value":i[1]||(i[1]=a=>d.value.dni=a)},null,8,["value"])]),l("div",q,[G,e(v,{value:d.value.nombres,"onUpdate:value":i[2]||(i[2]=a=>d.value.nombres=a)},null,8,["value"])]),l("div",K,[W,e(v,{value:d.value.paterno,"onUpdate:value":i[3]||(i[3]=a=>d.value.paterno=a)},null,8,["value"])]),l("div",X,[Y,e(v,{value:d.value.materno,"onUpdate:value":i[4]||(i[4]=a=>d.value.materno=a)},null,8,["value"])])])]),Z,l("div",null,[$,l("div",ee,[l("div",null,[l("div",te,[l("div",le,[l("span",null,"Aula: "+h(d.value.res_aula),1)])]),e(k,{style:{width:"300px",height:"50px","z-index":"1"}},{default:t(()=>[l("div",ae,[e(w,{value:x.value,"onUpdate:value":i[5]||(i[5]=a=>x.value=a)},{default:t(()=>[e(u,{value:"P"},{default:t(()=>[s("P")]),_:1}),e(u,{value:"Q"},{default:t(()=>[s("Q")]),_:1}),e(u,{value:"R"},{default:t(()=>[s("R")]),_:1}),e(u,{value:"S"},{default:t(()=>[s("S")]),_:1}),e(u,{value:"T"},{default:t(()=>[s("T")]),_:1})]),_:1},8,["value"])])]),_:1})])]),l("div",se,[l("div",ie,[(m(),g(U,null,C(30,a=>l("div",{key:a,style:{padding:"0px",height:"17px"}},[e(D,{value:_.value[a-1],"onUpdate:value":f=>_.value[a-1]=f,style:E([{width:"200px",height:"20px"},a%2==0?{backgroundColor:"#00000007"}:""])},{default:t(()=>[l("div",de,[e(o,{span:4,style:{"margin-right":"-10px"}},{default:t(()=>[l("div",oe,h(a),1)]),_:2},1024),e(o,{span:4,class:""},{default:t(()=>[e(c,{value:"A",class:"small-checkbox"},{default:t(()=>[s("A")]),_:1})]),_:1}),e(o,{span:4},{default:t(()=>[e(c,{value:"B",class:"small-checkbox"},{default:t(()=>[s("B")]),_:1})]),_:1}),e(o,{span:4},{default:t(()=>[e(c,{value:"C",class:"small-checkbox"},{default:t(()=>[s("C")]),_:1})]),_:1}),e(o,{span:4},{default:t(()=>[e(c,{value:"D",class:"small-checkbox"},{default:t(()=>[s("D")]),_:1})]),_:1}),e(o,{span:4},{default:t(()=>[e(c,{value:"E",class:"small-checkbox"},{default:t(()=>[s("E")]),_:1})]),_:1})])]),_:2},1032,["value","onUpdate:value","style"])])),64))]),l("div",ne,[(m(),g(U,null,C(30,a=>l("div",{key:a,style:{padding:"0px",height:"17px"}},[e(D,{value:_.value[a+29],"onUpdate:value":f=>_.value[a+29]=f,style:E([{width:"200px",height:"18px"},a%2==0?{backgroundColor:"#00000007"}:""])},{default:t(()=>[l("div",ue,[e(o,{span:4,style:{"margin-right":"-10px"}},{default:t(()=>[l("div",ce,h(a+30),1)]),_:2},1024),e(o,{span:4},{default:t(()=>[e(c,{value:"A",class:"small-checkbox"},{default:t(()=>[s("A")]),_:1})]),_:1}),e(o,{span:4},{default:t(()=>[e(c,{value:"B",class:"small-checkbox"},{default:t(()=>[s("B")]),_:1})]),_:1}),e(o,{span:4},{default:t(()=>[e(c,{value:"C",class:"small-checkbox"},{default:t(()=>[s("C")]),_:1})]),_:1}),e(o,{span:4},{default:t(()=>[e(c,{value:"D",class:"small-checkbox"},{default:t(()=>[s("D")]),_:1})]),_:1}),e(o,{span:4},{default:t(()=>[e(c,{value:"E",class:"small-checkbox"},{default:t(()=>[s("E")]),_:1})]),_:1})])]),_:2},1032,["value","onUpdate:value","style"])])),64))])])])])])])}}};export{ve as default};