<template>
  <Head title="Usuarios"/>
  <AuthenticatedLayout>
  <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-5">
  <!-- <pre>{{ permisos}}</pre> -->
  <a-button class="mb-3"  type="primary" @click="showModalRol">Nuevo</a-button>

  <a-table bordered :data-source="users" :columns="columns" size="small">

    <template #bodyCell="{ column, index }">
    
      <template v-if="column.dataIndex === 'role_name'">
        <a-button primary size="small">{{users[index].role_name }}</a-button>
       </template>

      <template v-if="column.dataIndex === 'operation'">
        <a-button type="primary" shape="" size="small">
          <template #icon><form-outlined/></template>
        </a-button>
        <a-divider type="vertical" />
        <a-button type="danger" shape="" size="small">
          <template #icon><delete-outlined /></template>
        </a-button>
      </template>
    </template>
  </a-table>

  </div>
  
  </AuthenticatedLayout>
  
  <div>
    <a-modal v-model:visible="visible" title="Nuevo Usuario" style="margin-top: -50px;" @ok="handleOk">
      <div>

        <a-form
          ref="formRef"
          name="custom-validation"
          :model="formState"
          :rules="rules"
          v-bind="layout"
          @finish="handleFinish"
          @validate="handleValidate"
          :validate-messages="validateMessages"
          @finishFailed="handleFinishFailed"
        >
          <a-form-item has-feedback label="Nombre" name="name">
            <a-input v-model:value="formState.name" type="text" autocomplete="off" />
          </a-form-item>

          <a-form-item has-feedback label="Apellido Paterno" name="paterno">
            <a-input v-model:value="formState.paterno" type="text" autocomplete="off" />
          </a-form-item>
          
          <a-form-item has-feedback label="Apellido Materno" name="materno">
            <a-input v-model:value="formState.materno" type="text" autocomplete="off" />
          </a-form-item>

          <a-form-item has-feedback label="Correo" name="email" :rules="[{ type: 'email', required: true }]">
            <a-input v-model:value="formState.email" type="text" autocomplete="off" />
          </a-form-item>

          <a-form-item has-feedback label="Contraseña" name="pass">
            <a-input v-model:value="formState.pass" type="password" autocomplete="off" />
          </a-form-item>
          <a-form-item has-feedback label="Confirmar Contraseña" name="checkPass">
            <a-input v-model:value="formState.checkPass" type="password" autocomplete="off" />
          </a-form-item>
          <a-form-item has-feedback label="Rol">
            <!-- <pre>{{ rol }}</pre> -->
            <a-auto-complete
              v-model:value="rols"
              :options="roles"
              style="width: 100%;"
              :filter-option="filterOption"
              >
              <a-input
                placeholder="input here"
                class="custom"
                v-model:value="rols"
              />
              <template #option="item" >
                <div @click="cambiar(item)">
                  <span>
                    {{ item.value }}
                  </span>
                </div>
              </template>
            </a-auto-complete>
          </a-form-item>
        </a-form>
  
      </div>

      <template #footer>
        <a-button type="primary" @click="guardar()">Submit</a-button>
        <a-button style="margin-left: 10px" @click="resetForm">Reset</a-button>
      </template>
    </a-modal>
  </div>


  
</template>
  
<script setup>
  import { Head } from '@inertiajs/vue3';
  import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
  import { computed, ref, reactive } from 'vue';
  import { FormOutlined, DeleteOutlined } from '@ant-design/icons-vue';
  import { notification } from 'ant-design-vue';

  const props = defineProps(['usuarios'])
  
  const roles = ref([]);
  const rols = ref("");
  const rol = ref("");
  const users = ref([]);
  const permisos = ref([]);

  const visible = ref(false)
  const showModalRol = () => {
      visible.value = true;
  };
  const handleOk = e => {
      console.log(e)
      visible.value = false
  };

  const columns = [{
      title: 'Nombre',
      dataIndex: 'name',
      width: '30%',
    }, {
      title: 'Correo',
      dataIndex: 'email',
    }, {
      title: 'Rol',
      dataIndex: 'role_name',
    }, {
      title: 'operation',
      dataIndex: 'operation',
    }];


    const formRef = ref();
    const formState = reactive({
      name:'',
      paterno:'',
      materno:'',
      email:'',
      pass: '',
      checkPass: '',
      rol: ref(null),
    });

    let validateCorreo = async (_rule, value) => {
      if (value === '') {
        return Promise.reject('Ingrese su correo electrónico');
      } else {
        return Promise.resolve();
      }
    };

    let validateNombre = async (_rule, value) => {
      if (value === '') {
        return Promise.reject('Ingrese su nombre');
      } else {
        return Promise.resolve();
      }
    };

    let validatePass = async (_rule, value) => {
      if (value === '') {
        return Promise.reject('Ingrese la contraseña');
      } else {
        if (formState.checkPass !== '') {
          formRef.value.validateFields('checkPass');
        }
        return Promise.resolve();
      }
    };

    let validatePass2 = async (_rule, value) => {
      if (value === '') {
        return Promise.reject('Ingrese la contraseña nuevamente');
      } else if (value !== formState.pass) {
        return Promise.reject("Las contraseñas no coenciden");
      } else {
        return Promise.resolve();
      }
    };

    const rules = {
      pass: [{
        required: true,
        validator: validatePass,
        trigger: 'change',
      }],
      checkPass: [{
        required: true,
        validator: validatePass2,
        trigger: 'change',
      }],
      name: [{
        required: true,
        validator: validateNombre,
        trigger: 'change',
      }],
      email: [{
        validator: validateCorreo,
        trigger: 'change',
      }],


    };

    const layout = {
      labelCol: {
        span: 8,
      },
      wrapperCol: {
        span: 18,
      },
    };
    const handleFinish = values => {
      console.log(values, formState);
    };
    const handleFinishFailed = errors => {
      console.log(errors);
    };
    const resetForm = () => {
      formRef.value.resetFields();
    };
    const handleValidate = (...args) => {
      console.log(args);
    };

    const validateMessages = {
      required: 'Ingrese ${label}',
      types: {
        email: '${label} no valido',
      },
    };

    
    const value =  ref('')

    const options =  ref([{
      value: 'Burns Bay Road',
    }, {
      value: 'Downing Street',
    }, {
      value: 'Wall Street',
    }]);

    const filterOption = (input, option) => {
      return option.value.toUpperCase().indexOf(input.toUpperCase()) >= 0;
    };

    const getUsuarios = async () => {  
      let res = await axios.get(`get-usuarios`);
      users.value = res.data.usuarios;
    }

    const getRoles = async () => {  
      let res = await axios.get(`get-roles-u`);
      roles.value = res.data.datos;
    }

    const getPermisos = async () => {
      let res = await axios.get(`get-permisos`);
      permisos.value = res.data.permisos;
    }

    getRoles();
    getUsuarios();
    getPermisos()

    const cambiar = val =>
    { 
       rol.value = val.id;
    }

    const guardar = () => {
      let post = {
          name:formState.name,
          paterno: formState.paterno,
          materno: formState.materno,
          email:formState.email,
          password:formState.pass,
          rol:rol.value
      };
      axios.post("save-user", post).then((result) => {
        console.log(result.data.user);
        visible.value = false;
        getUsuarios();
        notificacion('success','Nuevo Usuario Agregado', result.data.user.name );
      });
    }

    const notificacion = (type, titulo, mensaje) => {
      notification[type]({
        message: titulo,
        description: mensaje,
      });
    };


    const buscar = () => {
      if (permisos.value.find(e => e.id === 8)){return true}
      else{ return false};
    }
    
    console.log(buscar()) 




</script>