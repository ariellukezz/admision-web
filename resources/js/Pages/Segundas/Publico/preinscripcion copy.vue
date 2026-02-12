<template>
  <Head title="Preinscripción" />
  <Layout :nombre="props.procceso_seleccionado.nombre">

    <div v-if="pagina_pre === 0" class="bg-gray-50 flex items-center justify-center p-4">
      <div class="w-full max-w-md">

        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
          <div class="p-6 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
              </svg>
              Validación de Identidad
            </h2>
          </div>

          <div class="p-6">
            <a-form
              ref="formRef"
              :model="formState"
              layout="vertical"
              @finish="getDatosPersonales"
              class="space-y-6"
            >

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">
                  Tipo de Documento
                </label>
                <a-radio-group
                  v-model:value="datospersonales.tipo_doc"
                  class="grid grid-cols-2 gap-0"
                >
                  <a-radio-button :value="1" class="text-center font-bold h-10">
                    DNI
                  </a-radio-button>
                  <a-radio-button :value="3" class="text-center h-10">
                    Carnet Ext.
                  </a-radio-button>
                </a-radio-group>
              </div>

              <div>
                <a-form-item
                  label="Número de Documento"
                  name="dni"
                  :rules="[
                    { required: true, message: 'Ingrese su número de documento' },
                    { min: 8, max: 12, message: 'Documento inválido' }
                  ]"
                >
                  <a-input
                    v-model:value="formState.dni"
                    :maxlength="datospersonales.tipo_doc === 1 ? 8 : 12"
                    size="large"
                    placeholder="Ingrese su número"
                    @input="dniInput"
                    class="w-full"
                  >
                    <template #prefix>
                      <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                      </svg>
                    </template>
                  </a-input>
                </a-form-item>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">
                  Código de Verificación
                </label>
                <div class="flex items-center gap-4">
                  <div class="flex-1">
                    <div class="bg-gray-100 border border-gray-300 rounded px-6 py-4 text-center">
                      <div class="text-2xl font-mono font-bold text-gray-800 tracking-widest select-none">
                        <span class="line-through" style="font-size: 2.2rem;">{{ codigo_aleatorio }}</span>
                      </div>
                    </div>
                  </div>
                  <a-button
                    @click="getCodigoAleatorio"
                    type="text"
                    class="text-blue-600 hover:text-blue-800"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                  </a-button>
                </div>
                <a-form-item
                  label="Ingrese el código mostrado"
                  name="codigo_secreto"
                  :rules="[
                    { required: true, message: 'Ingrese el código de seguridad' },
                    { validator: validateCodigoSecreto }
                  ]"
                  class="mt-4"
                >
                  <a-input
                    v-model:value="formState.codigo_secreto"
                    :maxlength="6"
                    size="large"
                    placeholder="Ingrese el código"
                    class="w-full"
                  >
                    <template #prefix>
                      <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                      </svg>
                    </template>
                  </a-input>
                </a-form-item>
              </div>

              <div class="pt-4">
                  <a-button
                    type="primary"
                    html-type="submit"
                    size="large"
                    :disabled="!participa || formState.codigo_secreto !== codigo_aleatorio || modalcarrerasprevias"
                    block
                    class="h-12 font-medium flex items-center justify-center"
                    style="color: white;">
                    <LoginOutlined class="mr-2" />
                    Iniciar Postulación
                  </a-button>
              </div>
            </a-form>
          </div>
        </div>
      </div>
    </div>

    <div v-if="pagina_pre === 1" class="bg-gray-50 min-h-screen p-4 md:p-6">
      <div class="max-w-6xl mx-auto">
        <div class="mb-8">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h1 class="text-2xl font-bold text-gray-900">Datos Personales</h1>
              <p class="text-gray-600 mt-1">Complete todos los campos requeridos (*)</p>
            </div>
            <div class="hidden md:block">
              <span class="text-sm font-medium text-blue-600 bg-blue-50 px-3 py-1 rounded-full">
                Paso 1 de 3
              </span>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
          <div class="border-b border-gray-100">
            <div class="p-4 md:p-6">
              <div class="flex items-start bg-yellow-50 border border-yellow-100 rounded-lg p-4">
                <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.698-.833-2.464 0L4.196 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
                <div>
                  <h3 class="text-sm font-medium text-yellow-800">Declaración Jurada</h3>
                  <p class="text-sm text-yellow-700 mt-1">
                    La información proporcionada tiene carácter de declaración jurada y debe coincidir exactamente con sus documentos oficiales.
                  </p>
                </div>
              </div>
            </div>
          </div>

          <div class="p-4 md:p-6">
            <a-form
              ref="formDatosPersonales"
              :model="datospersonales"
              layout="vertical"
              class="space-y-8"
            >
              <div>
                <div class="flex items-center mb-6">
                  <div class="w-1 h-6 bg-blue-600 rounded-full mr-3"></div>
                  <h2 class="text-lg font-semibold text-gray-900">Información Personal</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                  <div>
                    <a-form-item
                      label="Primer Apellido"
                      name="primerapellido"
                      :rules="[{ required: true, message: 'Ingrese su primer apellido' }]"
                      class="mb-0"
                    >
                      <a-input
                        v-model:value="datospersonales.primerapellido"
                        @input="pimerapellidoInput"
                        placeholder="Primer apellido"
                        class="w-full"
                      />
                    </a-form-item>
                  </div>

                  <div>
                    <a-form-item
                      label="Segundo Apellido"
                      name="segundo_apellido"
                      class="mb-0"
                    >
                      <a-input
                        v-model:value="datospersonales.segundo_apellido"
                        @input="pimerapellidoInput"
                        placeholder="Primer apellido"
                        class="w-full"
                      />
                    </a-form-item>
                  </div>

                  <div>
                    <a-form-item
                      label="Nombres"
                      name="nombres"
                      :rules="[{ required: true, message: 'Ingrese sus nombres' }]"
                      class="mb-0"
                    >
                      <a-input
                        v-model:value="datospersonales.nombres"
                        @input="nombresInput"
                        placeholder="Nombres"
                        class="w-full"
                      />
                    </a-form-item>
                  </div>
                </div>
              </div>

              <div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                  <div>
                    <a-form-item
                      label="Género"
                      name="sexo"
                      :rules="[{ required: true, message: 'Seleccione su género' }]"
                      class="mb-0"
                    >
                      <a-select
                        v-model:value="datospersonales.sexo"
                        placeholder="Seleccionar"
                        size="large"
                        class="w-full"
                      >
                        <a-select-option value="1">Masculino</a-select-option>
                        <a-select-option value="2">Femenino</a-select-option>
                      </a-select>
                    </a-form-item>
                  </div>

                  <div>
                    <a-form-item
                      label="Estado Civil"
                      name="estado_civil"
                      :rules="[{ required: true, message: 'Seleccione estado civil' }]"
                      class="mb-0"
                    >
                      <a-select
                        v-model:value="datospersonales.estado_civil"
                        placeholder="Seleccionar"
                        class="w-full"
                        size="large"
                      >
                        <a-select-option value="1">Soltero</a-select-option>
                        <a-select-option value="2">Casado</a-select-option>
                        <a-select-option value="3">Viudo</a-select-option>
                        <a-select-option value="4">Divorciado</a-select-option>
                      </a-select>
                    </a-form-item>
                  </div>

                  <div>
                    <a-form-item
                      label="Fecha de Nacimiento"
                      name="fec_nacimiento"
                      :rules="[
                        { required: true, message: 'Seleccione fecha de nacimiento' },
                        { validator: validateFechaNacimiento }
                      ]"
                      class="mb-0"
                    >
                      <a-date-picker
                        v-model:value="datospersonales.fec_nacimiento"
                        format="DD/MM/YYYY"
                        placeholder="DD/MM/AAAA"
                        class="w-full"
                        size="large"
                      />
                    </a-form-item>
                  </div>
                </div>
              </div>
              <div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                  <div class="col-span-1">
                    <a-form-item
                      label="Celular"
                      name="celular"
                      :rules="[
                        { required: true, message: 'Ingrese su celular' },
                        { min: 9, message: 'Celular inválido' },
                        { validator: validateCelular }
                      ]"
                      class="mb-0"
                    >
                      <a-input
                        v-model:value="datospersonales.celular"
                        :maxlength="9"
                        placeholder="Número de celular"
                        class="w-full"
                      />
                    </a-form-item>
                  </div>

                  <div class="col-span-2">
                    <a-form-item
                      label="Correo Electrónico"
                      name="correo"
                      :rules="[
                        { required: true, message: 'Ingrese su correo' },
                        { type: 'email', message: 'Correo inválido' },
                        { validator: validateCorreo }
                      ]"
                      class="mb-0"
                    >
                      <a-input
                        v-model:value="datospersonales.correo"
                        type="email"
                        placeholder="correo@ejemplo.com"
                        @input="correoInput"
                        class="w-full"
                      />
                    </a-form-item>
                  </div>
                </div>
              </div>
              <div>

              <div class="grid grid-cols-3 gap-6">

                <div class="col-span-2">
                  <a-form-item
                    label="Dirección Actual"
                    name="direccion"
                    :rules="[{ required: true, message: 'Ingrese su dirección' }]"
                    class="mb-0"
                  >
                    <a-input
                      v-model:value="datospersonales.direccion"
                      placeholder="Dirección completa"
                      class="w-full"
                    />
                  </a-form-item>
                </div>

                <div class="col-span-1">
                  <a-form-item
                    label="Año de Egreso de la I.E. Superior"
                    name="egreso"
                    :rules="[{ required: true, message: 'Ingrese año de egreso' }]"
                    class="mb-0"
                  >
                    <a-input
                      v-model:value="datospersonales.egreso"
                      placeholder="Año de egreso"
                      class="w-full"
                    />
                  </a-form-item>
                </div>
              </div>

                <div class="grid grid-cols-2 gap-6 mt-8">
                    <div>
                      <a-form-item
                        label="Ubigeo de Nacimiento"
                        name="nacimiento"
                        :rules="[{ required: true, message: 'Seleccione ubigeo de nacimiento' }]"
                        class="mb-0"
                      >
                        <a-auto-complete
                            v-model:value="datospersonales.nacimiento"
                            :options="ubigeosNacimiento"
                            @select="onSelectNacimiento"
                            allowClear
                        >
                            <a-input
                                placeholder="Lugar"
                                v-model:value="buscarNacimiento"
                                @keypress="handleKeyPress"
                                class="h-12"
                            >
                                <template #suffix>
                                    <a-tooltip title="Extra information">
                                    <down-outlined/>
                                    </a-tooltip>
                                </template>
                            </a-input>
                        </a-auto-complete>
                      </a-form-item>
                    </div>

                    <div>
                      <a-form-item
                        label="Ubigeo de Residencia"
                        name="residencia"
                        :rules="[{ required: true, message: 'Seleccione ubigeo de residencia' }]"
                        class="mb-0"
                        >
                        <a-auto-complete
                              v-model:value="datospersonales.residencia"
                              :options="residencias"
                              @select="onSelectResidencias"
                              allowClear
                          >
                              <a-input
                                  placeholder="Lugar"
                                  v-model:value="buscarResidencia"
                                  @keypress="handleKeyPress"
                                  class="h-12"
                              >
                                  <template #suffix>
                                      <a-tooltip title="Extra information">
                                      <down-outlined/>
                                      </a-tooltip>
                                  </template>
                              </a-input>
                          </a-auto-complete>
                      </a-form-item>
                    </div>
                  </div>
              </div>
            </a-form>
          </div>
        </div>
      </div>
    </div>

    <div v-if="pagina_pre === 2" class="bg-gray-50 min-h-screen p-4 md:p-6">
      {{ condiciones_lengua }}
      <div class="max-w-6xl mx-auto">
        <div class="mb-8">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h1 class="text-2xl font-bold text-gray-900">Datos Adicionales exigidos por sunedu</h1>
              <p class="text-gray-600 mt-1">Complete todos los campos requeridos (*)</p>
            </div>
            <div class="hidden md:block">
              <span class="text-sm font-medium text-blue-600 bg-blue-50 px-3 py-1 rounded-full">
                Paso 2 de 3
              </span>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
          <div class="border-b border-gray-100">
            <div class="p-4 md:p-6">
              <div class="flex items-start bg-yellow-50 border border-yellow-100 rounded-lg p-4">
                <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.698-.833-2.464 0L4.196 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
                <div>
                  <h3 class="text-sm font-medium text-yellow-800">La información proporcionada tiene carácter de declaración jurada</h3>
                </div>
              </div>
            </div>
          </div>

          <div class="p-4 md:p-6">
            <a-form
              ref="formDatosPersonales"
              :model="datospersonales"
              layout="vertical"
              class="space-y-8"
            >
              <div>
                <div class="flex items-center mb-6">
                  <div class="w-1 h-6 bg-blue-600 rounded-full mr-3"></div>
                  <h2 class="text-lg font-semibold text-gray-900">Discapacidad</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-6">
                  <div class="col-span-1">
                    <a-form-item
                      label="¿Tiene discapacidad?"
                      name="sexo"
                      :rules="[{ required: true, message: 'Seleccione su género' }]"
                      class="mb-0"
                    >
                      <a-select
                        v-model:value="datospersonales.sexo"
                        placeholder="Seleccionar"
                        size="large"
                        class="w-full"
                      >

                      </a-select>
                    </a-form-item>
                  </div>

                  <div class="col-span-2">
                    <a-form-item
                      label="Tipo de discapacidad"
                      name="sexo"
                      :rules="[{ required: true, message: 'Seleccione su género' }]"
                      class="mb-0"
                    >
                      <a-select
                        v-model:value="datospersonales.sexo"
                        placeholder="Seleccionar"
                        size="large"
                        class="w-full"
                      >
                        <a-select-option value="1">Masculino</a-select-option>
                        <a-select-option value="2">Femenino</a-select-option>
                      </a-select>
                    </a-form-item>
                  </div>

              </div>
              <div>
              <div class="flex items-center mb-4 mt-8">
                  <div class="w-1 h-6 bg-blue-600 rounded-full mr-3"></div>
                  <h2 class="text-lg font-semibold text-gray-900">Identidad lingüística</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-6">
                  <div class="col-span-1">
                    <a-form-item
                      label="¿Se idendtifica con alguna lengua indigena?"
                      name="id_condicion_lengua"
                      :rules="[{ required: true, message: 'Seleccionar' }]"
                      class="mb-0"
                    >
                      <a-select
                        v-model:value="datos_transversales.id_condicion_lengua"
                        placeholder="Selecciona"
                        :options="condiciones_lengua"
                        :field-names="{ value: 'id', label: 'descripcion' }"
                        size="large"
                        class="w-full"
                      >
                      </a-select>
                    </a-form-item>
                  </div>
                                    <div class="col-span-2">
                    <a-form-item
                      label="Lengua indigena con la que se identifica"
                      name="sexo"
                      :rules="[{ required: true, message: 'Seleccione su género' }]"
                      class="mb-0"
                    >
                      <a-select
                        v-model:value="datospersonales.sexo"
                        placeholder="Seleccionar"
                        :options="lenguas_indigenas"
                        :field-names="{ value: 'id', label: 'descripcion' }"
                        size="large"
                        class="w-full"
                      >
                      </a-select>
                    </a-form-item>
                  </div>

                </div>


              <div class="flex items-center mb-4 mt-8">
                  <div class="w-1 h-6 bg-blue-600 rounded-full mr-3"></div>
                  <h2 class="text-lg font-semibold text-gray-900">Identidad de pertenencia</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-6">
                  <div class="col-span-1">
                    <a-form-item
                      label="¿Pertenece algún pueblo indigena?"
                      name="id_pertenencia_cultural"
                      :rules="[{ required: true, message: 'Seleccione su género' }]"
                      class="mb-0"
                    >
                      <a-select
                        v-model:value="datos_transversales.id_pertenencia_cultural"
                        placeholder="Seleccionar"
                        :options="opciones_pertenencia_cultural"
                        :field-names="{ value: 'id', label: 'descripcion' }"
                        size="large"
                        class="w-full"
                      >
                      </a-select>
                    </a-form-item>
                  </div>
                  <div class="col-span-2">
                    <a-form-item
                      label="Pueblo indigena"
                      name="sexo"
                      :rules="[{ required: true, message: 'Seleccione su género' }]"
                      class="mb-0"
                    >
                      <a-select
                        v-model:value="datospersonales.sexo"
                        placeholder="Seleccionar"
                        size="large"
                        :options="pueblos_indigenas"
                        :field-names="{ value: 'id', label: 'descripcion' }"
                        class="w-full"
                      >
                        <a-select-option value="1">Masculino</a-select-option>
                        <a-select-option value="2">Femenino</a-select-option>
                      </a-select>
                    </a-form-item>
                  </div>

                </div>


                </div>
              </div>
              <div>

              </div>
            </a-form>
          </div>
        </div>
      </div>
    </div>

    <div v-if="pagina_pre === 6" class="bg-gray-50 p-4 md:p-6">
      <div class="max-w-6xl mx-auto">
        <div class="mb-8">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h1 class="text-2xl font-bold text-gray-900">Programa y Documentación</h1>
              <p class="text-gray-600 mt-1">Seleccione el programa y suba los documentos requeridos</p>
            </div>
            <div class="hidden md:block">
              <span class="text-sm font-medium text-blue-600 bg-blue-50 px-3 py-1 rounded-full">
                Paso 2 de 3
              </span>
            </div>
          </div>

        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
          <div class="p-4 md:p-6">
            <a-form
              ref="formPreinscripcion"
              :model="datos_preinscripcion"
              layout="vertical"
              class="space-y-8"
            >
              <div>
                <div class="flex items-center mb-6">
                  <div class="w-1 h-6 bg-blue-600 rounded-full mr-3"></div>
                  <h2 class="text-lg font-semibold text-gray-900">Programa de Segunda Especialidad</h2>
                </div>

                <div class="">
                  <a-form-item
                    name="programa"
                    :rules="[{ required: true, message: 'Seleccione el programa' }]"
                    class="mb-0"
                  >
                    <a-select
                      v-model:value="datos_preinscripcion.programa"
                      placeholder="Seleccione su programa"
                      size="large"
                      class="w-full"

                    >
                      <a-select-option
                        v-for="programa in programas"
                        :key="programa.value"
                        :value="programa.value"
                      >
                        {{ programa.label }}
                      </a-select-option>
                    </a-select>
                  </a-form-item>
                </div>
              </div>

              <div>
                <div class="flex items-center mb-6">
                  <div class="w-1 h-6 bg-blue-600 rounded-full mr-3"></div>
                  <h2 class="text-lg font-semibold text-gray-900">Documentos Requeridos</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
                  <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors">
                    <Titulo :id_proceso="props.procceso_seleccionado.id" :dni="formState.dni" />
                  </div>
                </div>
              </div>

            </a-form>
          </div>
        </div>
      </div>
    </div>

    <div v-if="pagina_pre === 7 || postulante_inscrito === 1" class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
      <div class="w-full max-w-2xl">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
          <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-6">
            <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>

          <h1 class="text-2xl font-bold text-gray-900 mb-4">¡Preinscripción Exitosa!</h1>

          <p class="text-gray-600 mb-8 max-w-md mx-auto">
            Su preinscripción ha sido registrada correctamente en el sistema.
            Descargue los anexos para continuar con el proceso presencial.
          </p>

          <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 mb-8 max-w-md mx-auto">
            <div class="flex items-center justify-center text-sm text-blue-700">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <span>Transcurridas 24 horas podrá descargar su constancia oficial</span>
            </div>
          </div>

          <a-button
            type="primary"
            size="large"
            @click="descargaReglamento"
            class="h-12 px-8 font-medium"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Descargar Anexos
          </a-button>
        </div>
      </div>
    </div>

    <div v-if="pagina_pre !== 0 && pagina_pre !== 7" class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg z-50">
      <div class="max-w-6xl mx-auto px-4 py-4">
        <div class="flex items-center justify-between">
          <div>
            <a-button
              @click="pagina_pre === 1 ? prev() : pagina_pre = 1"
              class="h-12 px-6 font-medium"
            >
              <LeftOutlined/>
              Anterior
            </a-button>
          </div>

          <div>
            <a-button
              v-if="pagina_pre === 1 || pagina_pre === 2"
              type="primary"
              @click="saveDatosPersonales"
              class="h-12 px-6 font-medium"
              size="large"
            >
              Siguiente
              <RightOutlined/>
            </a-button>

            <a-button
              v-if="pagina_pre === 6"
              type="primary"
              @click="abrirModalDatos"
              size="large"
              class="h-12 px-6 font-medium"
            >
              <EyeOutlined/>
              Verificar Datos
            </a-button>
          </div>
        </div>
      </div>
    </div>

    <a-modal
      v-model:open="open"
      centered
      :width="800"
      :footer="null"
      :closable="true"
    >
      <div class="p-1">
        <div class="border-b border-gray-200 pb-6 mb-6">
          <div class="flex items-center">
            <div class="inline-flex items-center justify-center w-12 h-12 bg-blue-100 rounded-lg mr-4">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <div>
              <h2 class="text-xl font-bold text-gray-900">Verificación Final de Datos</h2>
              <p class="text-gray-600 mt-1">Revise y confirme su información antes de enviar</p>
            </div>
          </div>
        </div>

        <div class="max-h-[60vh] overflow-y-auto pr-2">
          <div class="mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
              <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              Datos Personales
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div v-for="(item, index) in [
                { label: 'Tipo Documento', value: tipo_docs[datospersonales.tipo_doc] },
                { label: 'N° Documento', value: formState.dni },
                { label: 'Primer Apellido', value: datospersonales.primerapellido },
                { label: 'Segundo Apellido', value: datospersonales.segundo_apellido },
                { label: 'Nombres', value: datospersonales.nombres },
                { label: 'Estado Civil', value: estados_civil[datospersonales.estado_civil] },
                { label: 'Sexo', value: sexos[datospersonales.sexo] },
                { label: 'Correo', value: datospersonales.correo },
                { label: 'Celular', value: datospersonales.celular },
                { label: 'Fecha Nacimiento', value: datospersonales.fec_nacimiento },
                { label: 'Ubigeo Nacimiento', value: datospersonales.ubigeo_nacimiento },
                { label: 'Ubigeo Residencia', value: datospersonales.ubigeo_residencia }
              ]" :key="index" class="bg-gray-50 rounded-lg p-4">
                <div class="text-sm font-medium text-gray-500 mb-1">{{ item.label }}</div>
                <div class="text-gray-900 font-medium">{{ item.value || 'No especificado' }}</div>
              </div>
            </div>
          </div>

          <!-- Programa Seleccionado -->
          <div class="mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
              <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
              </svg>
              Programa Seleccionado
            </h3>

            <div class="bg-gray-50 rounded-lg p-6">
              <a-select
                v-model:value="datos_preinscripcion.programa"
                placeholder="Seleccione una especialidad"
                size="large"
                class="w-full"
              >
                <a-select-option
                  v-for="item in especialidades"
                  :key="item.id"
                  :value="item.id"
                >
                  {{ item.nombre }}
                </a-select-option>
              </a-select>
            </div>
          </div>

          <!-- Declaración Jurada -->
          <div class="mb-8">
            <div class="bg-blue-50 border border-blue-100 rounded-lg p-6">
              <a-checkbox v-model:checked="checkbox1" class="w-full">
                <div class="flex items-start">
                  <div class="inline-flex items-center justify-center w-10 h-10 bg-blue-100 rounded-lg mr-4 flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                  </div>
                  <div>
                    <h4 class="font-semibold text-gray-900 mb-1">Declaración Jurada</h4>
                    <p class="text-gray-600 text-sm">
                      Certifico bajo declaración jurada que toda la información proporcionada es veraz,
                      completa y corresponde fielmente a mis documentos oficiales.
                    </p>
                  </div>
                </div>
              </a-checkbox>
            </div>
          </div>
        </div>

        <!-- Footer del Modal -->
        <div class="border-t border-gray-200 pt-6 mt-6">
          <div class="flex items-center justify-end gap-3">
            <a-button @click="open = false" class="h-10 px-6 font-medium">
              Cancelar
            </a-button>
            <a-button
              type="primary"
              @click="submit"
              :disabled="!checkbox1 || !datos_preinscripcion.programa"
              class="h-10 px-6 font-medium"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
              Confirmar y Guardar
            </a-button>
          </div>
        </div>
      </div>
    </a-modal>

    <!-- Modal de Carga -->
    <a-modal
      v-model:open="modalcarrerasprevias"
      centered
      :footer="null"
      :closable="false"
      :maskClosable="false"
    >
      <div class="py-12 text-center">
        <!-- Spinner -->
        <div class="inline-flex items-center justify-center w-16 h-16">
          <div class="w-12 h-12 border-4 border-blue-100 border-t-blue-600 rounded-full animate-spin"></div>
        </div>

        <!-- Texto -->
        <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-2">Verificando información</h3>
        <p class="text-gray-600">Estamos revisando sus datos en el sistema, tomará solo unos momentos.</p>
      </div>
    </a-modal>

  </Layout>
</template>

<script setup>
import Layout from '@/Layouts/LayoutPreinscripcionSegundas.vue'
import { defineProps, watch, reactive, ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import { format } from 'date-fns';
import {
  LoginOutlined,
  LeftOutlined,
  RightOutlined,
  EyeOutlined,
} from '@ant-design/icons-vue';
import Foto from "./components/foto-preinscripcion.vue"
import Titulo from "./components/titulo.vue"
import { message } from 'ant-design-vue';

const loading = ref(true);
const open = ref(false);
const abrirModalDatos = async () => {
  try {
    const values = await formPreinscripcion.value.validateFields();
    console.log("Valores validados:", values);

    const validDocs = await validateDocuments();
    console.log("Resultado de validDocs:", validDocs);

    if (!validDocs) {
      return;
    }

    open.value = true;
  } catch (error) {
    console.error("Error al abrir el modal de datos:", error);
    message.error("Por favor, corrige los errores del formulario antes de continuar.");
  }
};

const checkbox1 = ref(false)
const avance = ref(0)

const pagina_pre = ref(2)
const next = () => { pagina_pre.value++; }
const prev = () => { pagina_pre.value--; }

const formRef = ref();
const formState = reactive({ dni: '', codigo_secreto: '', });

const formDatosPersonales = ref();
const datospersonales = reactive({
    id: null,
    tipo_doc:1,
    primerapellido: "",
    segundo_apellido: "",
    nombres:"",
    estado_civil:null,
    sexo:null,
    correo:"",
    celular:'',
    fec_nacimiento:"",
    egreso:"",
    direccion:"",
    ubigeo_nacimiento:"",
    ubigeo_residencia:"",
    residencia:"",
    nacimiento:"",
});

const datos_transversales = reactive({
  discapacidad: null,
  tipo_discapacidad: "",
  id_postulante : 3,
  id_proceso : 1,
  id_pertenencia_cultural : "47de46ba-be45-41a6-a542-6f88fcd4653c",
  id_prueblo_indigena : "b57c729c-2c2e-4b07-9e01-d63ecf94ccc1",
  id_condicion_lengua : "820ddc3b-dad9-4fce-8d58-942d1c46c4ab",
  id_lengua_indigena : "7a6dbcce-7bf8-4894-8ce3-2526b6c83217"

})

const formDatosResidencia = ref();
const datosresidencia = reactive({
    dep: null,
    prov: null,
    dist: null,
    direccion:''
});
const formDatosColegio = ref();
const datoscolegio = reactive({
    egreso: null,
    pais: 125,
    dep: null,
    prov: null,
    dist: null,
    colegio:'',
});


const rules = {
  ubigeo_nacimiento: [{ required: true, message: "El lugar de nacimiento es obligatorio" }],
  ubigeo_residencia: [{ required: true, message: "El lugar de residencia es obligatorio" }],
};

const formPreinscripcion = ref();
const datos_preinscripcion = reactive({
  modalidad: null,
  programa:null,
  tipo_certificado:null,
  codigo_medico: null,
  codigo_certificado:null,
})

const dniInput = (event) => { formState.dni = event.target.value.replace(/\D/g, ''); };
const ubigeoInput = (event) => { formState.ubigeo = event.target.value.replace(/\D/g, ''); };
const nombresInput = (event) => { datospersonales.nombres = event.target.value.replace(/[^A-Za-z\s]/g, '');};
const pimerapellidoInput = (event) => { datospersonales.primerapellido = event.target.value.replace(/[^A-Za-z]/g, '');};
const celularInput = (event) => { datospersonales.celular = event.target.value.replace(/\D/g, ''); };

const departamentos = ref([])
const departamentoscolegio = ref([])


const residencia = ref(null)
const buscarResidencia = ref(null)
const residencias = ref([])

const getUbigeosResidencia = async () => {
    axios.post("/get-ubigeo",{"term": buscarResidencia.value})
    .then((response) => {
        residencias.value = response.data.datos.data;
    })
    .catch((error) => {
        if (error.response) {
            console.error('Error de servidor:', error.response.data);
        } else if (error.request) {
            console.error('Error de red:', error.request);
            } else { console.error('Error de configuración:', error.message); }
  });
}
watch(buscarResidencia, ( newValue, oldValue ) => {  if(newValue.length >= 3){ getUbigeosResidencia() }})

const nacimiento = ref(null)
const buscarNacimiento = ref(null)
const ubigeosNacimiento = ref([])

const getUbigeosNacimiento = async () => {
    axios.post("/get-ubigeo",{"term": buscarNacimiento.value})
    .then((response) => {
        ubigeosNacimiento.value = response.data.datos.data;
    })
    .catch((error) => {
        if (error.response) {
            console.error('Error de servidor:', error.response.data);
        } else if (error.request) {
            console.error('Error de red:', error.request);
            } else { console.error('Error de configuración:', error.message); }
  });
}
watch(buscarNacimiento, ( newValue, oldValue ) => {  if(newValue.length >= 3){ getUbigeosNacimiento() }});
const onSelectNacimiento = (value, option) => { datospersonales.ubigeo_nacimiento = option.key; };
const onSelectResidencias = (value, option) => { datospersonales.ubigeo_residencia = option.key; };

const codigo_secreto = ref(null);
watch(() => formState.dni, (newValue, oldValue) => {
if(newValue.length == 8){
    getPasoRegistrado();
    selectedItems.value = [];
}
});

const participa = ref(0);
const datareniec = ref(null)

const getDatosPersonales2 = async () => {

let res = await axios.post( "/get-postulante-datos-personales2", {nro_doc: formState.dni});
    if(res.data.datos.length > 0) {
    datospersonales.id = res.data.datos[0].id
    datospersonales.primerapellido = res.data.datos[0].primer_apellido
    datospersonales.segundo_apellido = res.data.datos[0].segundo_apellido
    datospersonales.nombres = res.data.datos[0].nombres
    datospersonales.estado_civil = res.data.datos[0].estado_civil
    datospersonales.sexo = res.data.datos[0].sexo
    datospersonales.direccion = res.data.datos[0].direccion
    datospersonales.correo = res.data.datos[0].correo
    datospersonales.celular = res.data.datos[0].celular
    datospersonales.egreso = res.data.datos[0].egreso
    datospersonales.residencia = res.data.datos[0].residencia
    datospersonales.nacimiento = res.data.datos[0].nacimiento
    if (res.data.datos[0].fec_nacimiento) {
        const fechaStr = res.data.datos[0].fec_nacimiento.trim();
        const fechaParsed = dayjs(fechaStr, 'YYYY-MM-DD', true);
        if (!fechaParsed.isValid()) {
            console.error("Fecha inválida:", fechaStr);
        } else {
            datospersonales.fec_nacimiento = fechaParsed;
        }
    }
    formState.ubigeo = res.data.datos[0].ubigeo
    datospersonales.ubigeo_nacimiento = res.data.datos[0].ubigeo
    datospersonales.ubigeo_residencia = res.data.datos[0].ubigeo_residencia
    datosresidencia.direccion = res.data.datos[0].direccion
    }
}

function cambiarFormato(fecha) {

  const d = dayjs.isDayjs(fecha) ? fecha : dayjs(fecha, 'YYYY-MM-DD', true);
  if (!d.isValid()) {
    console.error("Fecha inválida en cambiarFormato:", fecha);
    return "";
  }
  return d.format('DD/MM/YYYY');
}

const ultimopaso = ref(null);
const getPasoRegistrado = async () => {
    ultimopaso.value = null;
    let res = await axios.get( "/get-paso-registrado/"+props.procceso_seleccionado.id+"/"+formState.dni);
    if(res.data.estado == true){
        getDatosPersonales2()
        if(res.data.datos.nro == 6){
            consultaInscripcion2()
        }else{
            pagina_pre.value = res.data.datos.nro + 1;
            console.log("PASO::::", res.data.datos);
        }
    }
    else{
        modalcarrerasprevias.value = true;
        loading.value = true;
        getSancionado();
    }
}


const savePasos =  async (namex, num, avan ) => {
let res = await axios.post(
    "/save-pasos-preinscripcion",
    {
    id: id_pasos.value,
    nombre: namex,
    nro: num,
    avance: avan,
    postulante: datospersonales.id,
    proceso: props.procceso_seleccionado.id
    }
);
getPasos()
}

const saveDNI =  async () => {

let res = await axios.post( "/save-postulante-dni",
    {
        tipo_doc: datospersonales.tipo_doc,
        nro_doc: formState.dni,
        ubigeo_nacimiento: formState.ubigeo_nacimiento,
        paterno: datospersonales.primerapellido,
        materno:datospersonales.segundo_apellido,
        nombres: datospersonales.nombres,
    });
    getDatosPersonales2()
}

const saveDatosPersonales =  async () => {

    await formDatosPersonales.value.validateFields(['ubigeo_nacimiento']);
    const values = await formDatosPersonales.value.validateFields();

    let res = await axios.post(
        "/save-postulante-segundas",
        {
        tipo_doc: datospersonales.tipo_doc,
        nro_doc: formState.dni,
        id: datospersonales.id,
        primer_apellido: datospersonales.primerapellido,
        segundo_apellido: datospersonales.segundo_apellido,
        nombres: datospersonales.nombres,
        egreso: datospersonales.egreso,
        correo: datospersonales.correo,
        celular: datospersonales.celular,
        sexo: datospersonales.sexo,
        estado_civil: datospersonales.estado_civil,
        fec_nacimiento: format(new Date(datospersonales.fec_nacimiento), 'yyyy-MM-dd'),
        ubigeo_nacimiento: datospersonales.ubigeo_nacimiento,
        ubigeo_residencia: datospersonales.ubigeo_residencia,
        direccion: datospersonales.direccion,
        proceso: props.procceso_seleccionado.id
        }
    );
    pagina_pre.value = 6;
    if(res.data.estado === true ){
        notificacion(res.data.tipo, res.data.titulo, res.data.mensaje);
        pagina_pre.value = 6;
    }
}

watch(pagina_pre, ( newValue, oldValue ) => {

if(pagina_pre === 1 ){
  getDatosPersonales();
  getDepartamentos();
}
if(newValue === 3 ){
    getDepartamentosColegio();
    getUbigeo();
}
if(newValue === 4 ){
  getApoderado();

}
if(newValue === 5 ){
  if( datospersonales.id ){
    getApoderadoM();
  }
}
if(newValue === 6){
    getProgramas();
}
})

function validateFechaNacimiento(rule, value) {
return new Promise((resolve, reject) => {
    if (!value) {
    reject(new Error('Por favor, selecciona tu fecha de nacimiento.'));
    } else {
    const fechaNacimiento = new Date(value);
    const fechaMinima = new Date();
    fechaMinima.setFullYear(fechaMinima.getFullYear() - 20);

    if (fechaNacimiento > fechaMinima) {
        reject(new Error('Debes tener al menos 20 años.'));
    } else {
        resolve();
    }
    }
});
}

function validateCodigoSecreto(rule, value) {
return new Promise((resolve, reject) => {
    if (!value) {
    reject(new Error('Por favor, ingresa el código secreto.'));
    } else if (codigo_aleatorio.value !== formState.codigo_secreto) {
    reject(new Error('El código ingresado no coincide.'));
    } else {
    resolve();
    }
});
}

const id_pasos = ref(null)
const avance_current = ref(null)

const getPasos = async ( ) => {
    let res = await axios.post( "/get-pasos-proceso",
    { postulante: datospersonales.id,
        proceso: props.procceso_seleccionado.id
    });
    if (res.data.datos.length > 0){
        avance_current.value = res.data.datos[0].avance
        id_pasos.value = null
        pagina_pre.value = res.data.datos[0].nro + 1
        avance.value = res.data.datos[0].avance
        modalcarrerasprevias.value = false;
        loading.value = false;
    }

}

const notificacion = (type, titulo, mensaje) => {  notification[type]({ message: titulo, description: mensaje, });};
const imagen = ref(null);

const submit = async () => {
let fd = new FormData();
fd.append('dni', formState.dni)
fd.append('modalidad', 1)
fd.append('programa', datos_preinscripcion.programa)
fd.append('tipo_certificado', datos_preinscripcion.tipo_certificado)
fd.append('codigo_certificado', datos_preinscripcion.codigo_certificado)
fd.append('codigo_medico', datos_preinscripcion.codigo_medico)
fd.append('id_postulante', datospersonales.id)
fd.append('id_proceso', props.procceso_seleccionado.id)
await axios.post("/save-pre-inscripcion", fd).then(res=>{
    if( avance_current.value < 100){
        savePasos("Registro de datos preinscripcion", 6, 110)
    } else {
        next()
    }
    showToast("success","2",res.data.menssje);
}).catch(err=>console.log(err))
open.value = false
}


const presionado = ref(0);
const getDocs = async () => {
    window.open("/pdf-solicitud/"+props.procceso_seleccionado.id+"/"+formState.dni, '_blank');
}

const tipo_docs = { 1: 'DNI', 2: 'PASAPORTE' }
const estados_civil = { 1: 'SOLTERO', 2: 'CASADO', 3: 'VIUDO' }
const sexos = { 1: 'MASCULINO', 2: 'FEMENINO' }
const props = defineProps(['procceso_seleccionado']);

const sancionado = ref(null)
const modalSancionado = ref(false);

const getSancionado =  async () => {
    participa.value = 0;
    try {
        let res = await axios.get("/get-sancionado/" + formState.dni + "/" + props.procceso_seleccionado.id);
        sancionado.value = res.data.datos;
        if(sancionado.value != null){
            loading.value = false;
            modalSancionado.value = true;
            return;
        }else{
            consultaInscripcion()
        }
    } catch (error) {
        console.error("Error al obtener datos de sancionado", error);
    }
}

const codigo_aleatorio = ref(null);

const getCodigoAleatorio = async ( ) => {
    let res = await axios.get("/generar-captcha");
    codigo_aleatorio.value = res.data.captcha;
}
const postulante_inscrito = ref(0);

const consultaInscripcion = async () => {
    postulante_inscrito.value = 0;
    try {
        let res = await axios.get("/participa-proceso/"+props.procceso_seleccionado.id+"/"+formState.dni);
        if (res.data.estado === true) {
            postulante_inscrito.value = 1;
            modalcarrerasprevias.value = false;
            loading.value = false;
            pagina_pre.value = 7;
        } else {
            getDataPrisma();
            participa.value = 1;
        }
    } catch (error) {
        console.error("Error al obtener datos del participante", error);
    }
}

const consultaInscripcion2 = async () => {
postulante_inscrito.value = 0;
  try {
      let res = await axios.get("/participa-proceso/"+props.procceso_seleccionado.id+"/"+formState.dni);
      if (res.data.estado === true) {
          pagina_pre.value = 7;
      }else{
          pagina_pre.value = 6;
      }
  } catch (error) {
      console.error("Error al obtener datos del participante", error);
  }
}

getCodigoAleatorio();
const modalcarrerasprevias = ref(false);
const participante = ref(null);
const anteriores = ref([]);

const getDataPrisma = async () => {
    participante.value = null;
    const response = await axios.get('/get-data-prisma/'+formState.dni);
    if(response.data.estado === true){
        participante.value = response.data.datos;
        formState.dni = response.data.datos.dni;
        datospersonales.primerapellido = response.data.datos.paterno;
        datospersonales.segundo_apellido = response.data.datos.materno;
        datospersonales.nombres = response.data.datos.nombre;
    }
    loading.value = false;
    modalSancionado.value = false;
    confirmacion.value = false;
}

const confirmacion = ref(null);
function validateCelular(rule, value) {
  return new Promise((resolve, reject) => {
    if (!value) {
      reject(new Error('Por favor, ingresa tu número de celular.'));
    } else {
      axios.post('/existe-celular',{ celular: value, dni:formState.dni})
        .then(response => {
          if (response.data == true) {
            reject(new Error('Este número de celular ya está registrado.'));
          } else {
            resolve();
          }
        })
        .catch(error => {
          console.error('Error al verificar el número de celular:', error);
          reject(new Error('Error al verificar el número de celular.'));
        });
    }
  });
}

function validateCorreo(rule, value) {
  return new Promise((resolve, reject) => {
    if (!value) {
      reject(new Error('Por favor, ingresa tu correo.'));
    } else {
      axios.post('/existe-correo',{ email: value, dni:formState.dni})
        .then(response => {
          if (response.data == true) {
            reject(new Error('Este correo ya está registrado.'));
          } else {
            resolve();
          }
        })
        .catch(error => {
          console.error('Error al verificar el correo:', error);
          reject(new Error('Error al verificar correo.'));
        });
    }
  });
}

const programas = ref([]);
const getProgramas =  async () => {
  let res = await axios.post( "/get-select-programas-proceso-segundas",{ "id_modalidad":1, "id_proceso": props.procceso_seleccionado.id });
  programas.value = res.data.datos;
}

const validationMessage = ref('');
const validateDocuments = async () => {
  try {
    const response = await axios.get('/verificar-documentos-preinscripcion/'+formState.dni+'/'+props.procceso_seleccionado.id);
    const data = response.data;

    if (data.estado) {
      validationMessage.value = 'Todos los documentos están completos.';
      return true;  // Se retorna true si está todo bien
    } else {
      if (data.missing.length === 2) {
        validationMessage.value = 'Faltan ambos documentos: foto y título.';
        message.error('Faltan 2 documentos.');
        return false;
      } else if (data.missing.includes(8)) {
        validationMessage.value = 'Falta subir foto.';
        message.error('Falta subir foto.');
        return false;
      } else if (data.missing.includes(7)) {
        validationMessage.value = 'Falta subir título.';
        message.error('Falta subir título.');
        return false;
      }
    }
  } catch (error) {
    console.error('Error en la validación:', error);
    validationMessage.value = 'Error al validar documentos.';
    message.error('Error al validar documentos.');
    return false;
  }
};



const descargaReglamento = async () => {
  try {
        const response = await axios.get('/descargar-reglamento/'+props.procceso_seleccionado.id, {
          responseType: 'blob',
        });
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', 'reglamento.pdf');
        document.body.appendChild(link);
        link.click();
      } catch (error) {
        console.error('Error al descargar el PDF', error);
      }

}
const condiciones_lengua = ref([]);
const getCondicionesLengua = async () => {
    const response = await axios.get('/get-condiciones-lengua-segundas')
    if(response.data){
      condiciones_lengua.value = response.data
    }

}

const opciones_pertenencia_cultural = ref([]);
const getOptionsPertenenciaCultural = async () => {
    const response = await axios.get('/get-pertenencia-cultural-segundas')
    if(response.data){
      opciones_pertenencia_cultural.value = response.data
    }

}
const lenguas_indigenas = ref([]);
const getLenguasIndigenas = async () => {
    const response = await axios.get('/get-lengua-segundas')
    if(response.data){
      lenguas_indigenas.value = response.data
    }

}
const pueblos_indigenas = ref([]);
const getPueblosIndigenas = async () => {
    const response = await axios.get('/get-pueblos-indigenes-segundas')
    if(response.data){
      pueblos_indigenas.value = response.data
    }

}

getCondicionesLengua()
getOptionsPertenenciaCultural()
getLenguasIndigenas()
getPueblosIndigenas()

</script>


<style scoped>
.modal-content::-webkit-scrollbar {
  width: 6px;
}
.modal-content::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}
.modal-content::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}
.modal-content::-webkit-scrollbar-thumb:hover {
  background: #a1a1a1;
}
.step-enter-active,
.step-leave-active {
  transition: all 0.3s ease;
}
.step-enter-from,
.step-leave-to {
  opacity: 0;
  transform: translateY(20px);
}
:deep(.ant-input),
:deep(.ant-select-selector),
:deep(.ant-picker) {
  border-radius: 6px !important;
}

:deep(.ant-input):focus,
:deep(.ant-select-selector):focus,
:deep(.ant-picker):focus {
  border-color: #3b82f6 !important;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1) !important;
}

:deep(.ant-btn-primary) {
  background-color: #2563eb !important;
  border-color: #2563eb !important;
}

:deep(.ant-btn-primary:hover) {
  background-color: #1d4ed8 !important;
  border-color: #1d4ed8 !important;
}
:deep(.ant-radio-button-wrapper-checked) {
  background-color: #2563eb !important;
  border-color: #2563eb !important;
  color: white !important;
}
:deep(.ant-alert-warning) {
  background-color: #fffbeb !important;
  border-color: #fde68a !important;
}
:deep(.ant-alert-warning .ant-alert-icon) {
  color: #f59e0b !important;
}
:deep(.ant-alert-warning .ant-alert-message) {
  color: #92400e !important;
  font-weight: 500;
}
:deep(.ant-alert-warning .ant-alert-description) {
  color: #92400e !important;
}

.fixed {
  box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
}

@media (max-width: 768px) {
  .step-indicator {
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
  }
  .step-line {
    width: 2px !important;
    height: 40px !important;
    margin: 0 auto;
  }
}

@media (prefers-color-scheme: dark) {
  .bg-gray-50 {
    background-color: #1a202c !important;
  }
  .bg-white {
    background-color: #2d3748 !important;
  }
  .text-gray-900 {
    color: #e2e8f0 !important;
  }
  .text-gray-600 {
    color: #a0aec0 !important;
  }
  .border-gray-200 {
    border-color: #4a5568 !important;
  }
  .bg-gray-100 {
    background-color: #684a4a !important;
  }
}
</style>
