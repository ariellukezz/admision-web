import{_ as b}from"./_plugin-vue_export-helper-c27b6911.js";import{l as g,w as n,r as s,o as y,d as o,t as d,a as t,b as _}from"./app-2924acb4.js";const x={props:["titulo","tipo_apoderado"],setup(a,{emit:l}){return{emitirEvento:()=>{l("miEventoPersonalizado")}}},data(){return{tipo_doc:1,dni:"",primer_apellido:"",segundo_apellido:"",t_apoderado:this.tipo_apoderado,nombres:"",res:null}},methods:{async guardar(){try{const a=await axios.post("/guardar-apoderado",{tipo_doc:this.tipo_doc,dni:this.dni,tipo_apoderado:this.tipo_apoderado,primer_apellido:this.primer_apellido,segundo_apellido:this.segundo_apellido,nombres:this.nombres,observacion:this.observacion});this.res=a.data}catch{}},ejecutarFuncion(){this.guardar();const a="Datos del hijo";this.$emit("hijo-clicado",a),console.log("Hijo Clicado")}}},h={class:"flex",style:{"justify-content":"space-between"}},j={style:{"font-size":"1rem"}},C={style:{"margin-top":"0px"}},w={style:{"margin-top":"-10px"}},k={style:{"margin-top":"-10px"}},E={style:{"margin-top":"-10px","margin-bottom":"-10px"}},U={style:{display:"flex","justify-content":"flex-end","margin-top":"16px"}};function D(a,l,u,N,e,B){const m=s("a-radio"),c=s("a-radio-group"),r=s("a-input"),p=s("a-form-item"),v=s("a-button"),f=s("a-card");return y(),g(f,{style:{"border-bottom":"1px #418dff solid","border-radius":"6px"}},{default:n(()=>[o("div",h,[o("div",j,[o("h1",null,d(u.titulo),1)]),t(c,{value:e.tipo_doc,"onUpdate:value":l[0]||(l[0]=i=>e.tipo_doc=i),class:"flex justify-end",style:{display:"flex",width:"yellow"},name:"radioGroup"},{default:n(()=>[t(m,{value:1},{default:n(()=>[_("Dni")]),_:1}),t(m,{value:2},{default:n(()=>[_("Carnet Ext.")]),_:1})]),_:1},8,["value"])]),o("div",null,[o("div",C,[t(p,null,{default:n(()=>[o("div",null,[o("label",null,"Dni:"+d(e.dni),1)]),t(r,{value:e.dni,"onUpdate:value":l[1]||(l[1]=i=>e.dni=i)},null,8,["value"])]),_:1})]),o("div",w,[t(p,null,{default:n(()=>[o("div",null,[o("label",null,"Primer apellido:"+d(e.primer_apellido),1)]),t(r,{value:e.primer_apellido,"onUpdate:value":l[2]||(l[2]=i=>e.primer_apellido=i)},null,8,["value"])]),_:1})]),o("div",k,[t(p,null,{default:n(()=>[o("div",null,[o("label",null,"segundo apellido: "+d(e.segundo_apellido),1)]),t(r,{value:e.segundo_apellido,"onUpdate:value":l[3]||(l[3]=i=>e.segundo_apellido=i)},null,8,["value"])]),_:1})]),o("div",E,[t(p,null,{default:n(()=>[o("div",null,[o("label",null,"Nombres: "+d(e.nombres),1)]),t(r,{value:e.nombres,"onUpdate:value":l[4]||(l[4]=i=>e.nombres=i)},null,8,["value"])]),_:1})]),o("div",U,[t(v,{type:"primary",onClick:a.ejecutarEnHijo,block:"",style:{height:"40px"}},{default:n(()=>[_(" Confirmar "+d(u.titulo),1)]),_:1},8,["onClick"])])])]),_:1})}const H=b(x,[["render",D]]);export{H as default};