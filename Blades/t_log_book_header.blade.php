@if(!$req->has('id'))
<div class="bg-white p-6 rounded-xl h-[570px]">
  <TableApi ref='apiTable' :api="landing.api" :columns="landing.columns" :actions="landing.actions">
    <template #header>
      <RouterLink v-if="currentMenu?.can_create||true||store.user.data.username==='developer'"
        :to="$route.path+'/create?'+(Date.parse(new Date()))"
        class="bg-green-500 text-white hover:bg-green-600 rounded-[6px] py-2 px-[12.5px]">
        <icon fa="plus" />
        Tambah Data
      </RouterLink>
    </template>
  </TableApi>
</div>
@else

@verbatim
<div class="flex flex-col gap-y-3">
  <div class="flex gap-x-4 px-2">
    <div class="flex flex-col border rounded shadow-sm px-6 py-6 <md:w-full w-full bg-white">
      <div class="mb-4">
        <h1 class="text-[24px] mb-4 font-bold">
          Form Proyek
        </h1>
        <hr>
      </div>
      <div class="grid <md:grid-cols-1 grid-cols-2 gap-2">
        <!-- START COLUMN -->

        <div>
          <label class="font-semibold">Tanggal<span class="text-red-500 space-x-0 pl-0">*</span></label>
          <FieldX :bind="{ readonly: true ,  }" class="w-full py-2 !mt-0" :value="values.tanggal"
            :errorText="formErrors.tanggal?'failed':''" @input="v=>values.tanggal=v" :hints="formErrors.tanggal"
            placeholder="Pilih Tanggal" :check="false" label="" />
        </div>

        <div>
          <label  class="font-semibold select-all">Karyawan<span class="text-red-500 space-x-0 pl-0"></span></label>
          <FieldPopup :bind="{ readonly: true , clearable:true }" class="w-full py-2 !mt-0" :value="values.m_kary_id"
            @input="(v)=>values.m_kary_id=v" :errorText="formErrors.m_kary_id?'failed':''" :hints="formErrors.m_kary_id"
            valueField="id" displayField="nama_lengkap" @update:valueFull="dataBook" :api="{
                  url: `${store.server.url_backend}/operation/m_kary`,
                  headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                  params: {
                    simplest:true,
                    searchfield:'this.id, this.nomor, this.keterangan',
                  },
                  onsuccess:(response) => {
                response.page = response.current_page
                response.hasNext = response.has_next
                return response
              }
                }" placeholder="Pilih Karyawan" label="" :check="false" :columns="[{
                  headerName: 'No',
                  valueGetter:(p)=>p.node.rowIndex + 1,
                  width: 60,
                  sortable: false, resizable: false, filter: false,
                  cellClass: ['justify-center', 'bg-gray-50']
                },
                
                {
                  flex: 1,
                  field: 'm_dept.nama',
                  headerName:'Departemen',
                  sortable: false, resizable: true, filter: 'ColFilter', wrapText: true,
                  cellClass: ['border-r', '!border-gray-200', 'justify-center']
                },
                {
                  flex: 1,
                  field: 'm_divisi.nama',
                  headerName:'Divisi',
                  sortable: false, resizable: true, filter: 'ColFilter', wrapText: true,
                  cellClass: ['border-r', '!border-gray-200', 'justify-center']
                },
                {
                  flex: 1,
                  field: 'nama_lengkap',
                  headerName: 'Nama',
                  sortable: false, resizable: true, filter: 'ColFilter',
                  cellClass: ['border-r', '!border-gray-200', 'justify-center']
                },
                ]" />
        </div>


      </div>
      <!-- END COLUMN -->

      <!-- ACTION BUTTON START -->
      <div class="flex flex-row justify-end space-x-[20px] mt-[5em]">

        <button @click="onBack" class="bg-[#EF4444] hover:bg-[#ed3232] text-white px-[36.5px] py-[12px] rounded-[6px] ">
            Batal
          </button>

        <button v-show="actionText" @click="onSave" class="bg-[#10B981] hover:bg-[#0ea774] text-white px-[36.5px] py-[12px] rounded-[6px] ">
            Simpan
        </button>

      </div>

      <!-- DETAIL -->
      <div class="grid grid-cols-2 gap-4">
        <!-- TABLE PROYEK -->
        <div class="w-full border-2 rounded-2xl p-4 mt-10">
          <h1 class="font-semibold text-xl">PROYEK</h1>
          <div v-show="actionText" class="mt-4">
            <button @click="addDetail" type="button" :disabled="!actionText" class="bg-blue-600 hover:bg-blue-500 text-white p-2 px-4 flex items-center justify-center rounded">
              + Tambah Proyek
         </button>
          </div>
          <div class="mt-4">
            <table class="w-full overflow-x-auto">
              <thead>
                <tr class="border-y">
                  <td class="text-black font-bold text-capitalize px-2 text-center w-[5%] border border-[#CACACA]">No.
                  </td>
                  <td class="text-black font-bold text-capitalize px-2 text-center w-[25%] border border-[#CACACA]">
                    PROYEK
                    <label class="text-red-500">*</label>
                  </td>
                  <td v-show="actionText"
                    class="text-black font-bold text-capitalize px-2 text-center w-[5%] border border-[#CACACA]">Action
                  </td>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, i) in detail.items" :key="i" 
                  :class="{'bg-gray-200': proyekPilih === item.m_proyek_id}" class="border-t ">
                  <td class="p-2 text-center pt-7 pb-4 border border-[#CACACA] cursor-pointer" @click="clickProyek(item.m_proyek_id , i)">
                    {{ i + 1 }}.
                  </td>
                  <td class="p-2 pt-7 pb-4 border border-[#CACACA]" >
                    <FieldSelect :bind="{ disabled: !actionText, clearable: true }" class="col-span-8 !mt-0 w-full"
                      :value="item.m_proyek_id" @input="v => item.m_proyek_id = v" valueField="id"
                      displayField="proyek_nama" :api="{
      url: `${store.server.url_backend}/operation/m_proyek`,
      headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}` },
      params: { simplest: true, transform: false, join: false }
    }" placeholder="Pilih Proyek" label=" " fa-icon="sort-desc" :check="false" />
                  </td>
                  <td class="p-2 pt-7 pb-4 border border-[#CACACA]" v-show="actionText">
                    <div class="flex justify-center">
                      <button type="button" @click.stop="removeDetail(i)">
        <svg width="14" height="18" viewBox="0 0 14 18" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M14 1H10.5L9.5 0H4.5L3.5 1H0V3H14M1 16C1 16.5304 1.21071 17.0391 1.58579 17.4142C1.96086 17.7893 2.46957 18 3 18H11C11.5304 18 12.0391 17.7893 12.4142 17.4142C12.7893 17.0391 13 16.5304 13 16V4H1V16Z" fill="#F24E1E"/>
        </svg>
      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="detail.items.length === 0" class="text-center">
                  <td colspan="5" class="py-[20px] border border-[#CACACA]">No data to show</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- TASK TABLE -->
        <div class="w-full border-2 rounded-2xl p-4 mt-10">
          <h1 class="font-semibold text-xl">TUGAS {{ proyekNama || 'PILIH PROYEK' }}</h1>
          <div v-show="actionText" class="mt-4">
            <button
          @click="addDetail2"
          type="button"
          :disabled="!actionText"
            class="bg-blue-600 hover:bg-blue-500 text-white p-2 px-4 flex items-center justify-center rounded">
              + Tambah Tugas
      </button>
          </div>
          <div class="mt-4">
            <table class="w-full overflow-x-auto">
              <thead>
                <tr class="border-y">
                  <td class="text-black font-bold text-capitalize px-2 text-center w-[5%] border border-[#CACACA]">No.
                  </td>

                  <td class="text-black font-bold text-capitalize px-2 text-center w-[25%] border border-[#CACACA]">TASK
                    <label class="text-red-500">*</label>
                  </td>
                  <td class="text-black font-bold text-capitalize px-0 text-center w-[25%] border border-[#CACACA]">
                    STATUS<label class="text-red-500">*</label></td>
                  <td v-show="actionText"
                    class="text-black font-bold text-capitalize px-2 text-center w-[5%] border border-[#CACACA]">Action
                  </td>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, i) in detail2.items" :key="i" class="border-t">
                  <td class="p-2 text-center pt-7 pb-4 border border-[#CACACA]">{{ i + 1 }} .</td>
                  <td class="p-2 pt-7 pb-4 border border-[#CACACA]">
                    <FieldX :bind="{ readonly: !actionText }" @input="(v) => item.task = v" :value="item.task"
                      :error-text="formErrors.task" :disabled="!actionText" placeholder="Tuliskan Task" label=''
                      class="col-span-8 !mt-0 w-full" :check="false" />
                  </td>
                  <td class="p-2 pt-7 pb-4 border border-[#CACACA]">
                    <FieldSelect :bind="{ disabled: !actionText, clearable:false }" :value="item.status"
                      @input="v=>item.status=v" :errorText="formErrors.status?'failed':''" :hints="formErrors.status"
                      valueField="key" displayField="key" :options="[
                            {'key' : 'TODO'}, 
                            {'key' : 'PROGRESS'},
                            {'key' : 'DONE'},
                            ]" placeholder="Status" label="" :check="false" />
                  </td>
                  <td class="p-2 pt-7 pb-4 border border-[#CACACA]" v-show="actionText">
                    <div class="flex justify-center">
                      <button type="button" @click="removeDetail2(i)">
          <svg width="14" height="18" viewBox="0 0 14 18" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M14 1H10.5L9.5 0H4.5L3.5 1H0V3H14M1 16C1 16.5304 1.21071 17.0391 1.58579 17.4142C1.96086 17.7893 2.46957 18 3 18H11C11.5304 18 12.0391 17.7893 12.4142 17.4142C12.7893 17.0391 13 16.5304 13 16V4H1V16Z" fill="#F24E1E"/>
          </svg>
        </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="detail2.items.length === 0" class="text-center ">
                  <td colspan="5" class="py-[20px] border border-[#CACACA]">Pilih Proyek Dulu !</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <!-- END DETAIL -->


    </div>

  </div>
</div>
@endverbatim
@endif