import{_ as x}from"./_plugin-vue_export-helper-c27b6911.js";import{f as c,o as s,c as o,d as n,t as e,g as f,F as d,i as p,a as _,w as m,b as y}from"./app-3a079e0d.js";const N={props:["id_postulante","actualiza","dni"],data(){return{preguntas:null,respuestas:[],datos:[]}},mounted(){this.getDatos()},methods:{async getPreguntas(){const t=await axios.post("get-preguntas",{id_postulante:this.id_postulante,id_programa:this.datos[0].id_vocacional});this.preguntas=t.data.datos},async getDatos(){const t=await axios.post("get-datos-examen",{id_postulante:this.id_postulante});this.datos=t.data.datos,this.getPreguntas()},async saveVocacional(){let t=110;try{const u=await axios.post("save-vocacional",{id_postulante:this.id_postulante,id_examen:this.datos[0].id_vocacional,respuestas:this.respuestas,actualizar:this.actualiza,proceso:4,name:"Examen vocacional completado",nro:7,dni:this.datos[0].nro_doc,avance:t});return this.actualiza==="si"?1:0}catch{}}}},b={key:0,class:""};function k(t,u,w,V,a,z){const g=c("a-radio"),h=c("a-radio-group");return s(),o("div",null,[a.datos.length>0?(s(),o("div",b,[n("h1",null,"DNI: "+e(a.datos[0].nro_doc),1),n("h1",null,"Nombre: "+e(a.datos[0].nombres)+" "+e(a.datos[0].primer_apellido)+" "+e(a.datos[0].segundo_apellido),1),n("h1",null,"Programa: "+e(a.datos[0].nombre),1)])):f("",!0),(s(!0),o(d,null,p(a.preguntas,(r,i)=>(s(),o("div",{class:"mb-3",key:r.id},[n("h3",null,e(i+1)+". "+e(r.pregunta),1),(s(!0),o(d,null,p(r.respuestas,l=>(s(),o("div",{key:l.ide},[_(h,{value:a.respuestas[i],"onUpdate:value":v=>a.respuestas[i]=v},{default:m(()=>[_(g,{value:l},{default:m(()=>[y(e(l.respuesta),1)]),_:2},1032,["value"])]),_:2},1032,["value","onUpdate:value"])]))),128))]))),128))])}const C=x(N,[["render",k]]);export{C as default};