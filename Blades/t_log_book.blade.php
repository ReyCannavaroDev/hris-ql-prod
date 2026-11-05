@verbatim
<div class="space-y-5 p-6">
  <!-- Header -->
  <div class="lg:h-[300px] 2xl:h-[400px] lg:flex flex-col lg:flex-row gap-4  rounded-xl md:space-y-10 lg:space-y-0 ">

    <!-- Bagian Kiri -->
    <div class="flex-1 space-y-4">

      <div class="h-full bg-white border-t-4 border-blue-500 py-4 rounded-lg px-3">
        <div class="flex justify-between p-2">
          <FieldX :bind="{ readonly: false }" class="!mt-0" :value="data.month"
            :errorText="formErrors.month ? 'failed' : ''" @input="v => {
            data.month = v
            changeCalendar(v)
          }" :hints="formErrors.month" placeholder="Pilih Bulan" label="" type="month" :check="false" />
        </div>

        <div class="h-[85%]">
          <table class=" text-black   w-full h-full ">
            <tr class="bg-gray-300 border border-2 ">
              <th v-for="day in daysOfWeek" :key="day" class="p-1  font-semibold">{{ day }}</th>
            </tr>

            <tr v-for="row in calendarRows" :key="row" class="rounded-2xl">
  <td v-for="cell in row" :key="cell.date"
    :class="{
      'bg-blue-500 text-white': selectedDate && selectedDate.getTime() === cell.date.getTime(),
      'bg-red-500 text-white': cell.date && cell.date.getDay() === 0 && (!selectedDate || selectedDate.getTime() !== cell.date.getTime()),
      'cursor-pointer': cell.day
    }"
    class="p-1 text-center border"
    @click="handleDateClick(cell.date)">
    {{ cell.day }}
  </td>
</tr>

          </table>
        </div>

      </div>
    </div>

    <!-- Bagian Kanan -->
    <div class="flex-1 h-full">
    </div>

  </div>

  <!-- FORM -->
  <div class=" bg-white rounded-2xl p-6">
    <!-- ACTION BUTTON START -->
    <div class="flex flex-row justify-end space-x-[20px] ">

      <button @click="onSave" class="bg-[#10B981] hover:bg-[#0ea774] text-white px-[36.5px] py-[12px] rounded-[6px] ">
            Simpan
        </button>

    </div>

    <!-- DETAIL -->
    <div class="grid xl:grid-cols-2 gap-4">
      <!-- TABLE PROYEK -->
      <div class="w-full border-2 rounded-2xl p-4 mt-10">
        <h1 class="font-semibold text-xl">PROYEK</h1>
        <div class="mt-4">

          <ButtonMultiSelect title="Add to list" @add="onDetailAdd" :api="{
              url: `${store.server.url_backend}/operation/m_proyek`,
              headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
              params: { 
                join: true,
                simplest: true,
                
               },
              onsuccess:(res)=>{
                $log(res)
                res.data = res.data.map((dt)=>({
                ...dt, is_active: dt.is_active ? 1 : 0,
                }))
                return res;
              }
            }" :columns="[{
              checkboxSelection: true,
              headerCheckboxSelection: true,
              headerName: 'No',
              valueGetter:p=>'',
              width:60,
              sortable: false, resizable: true, filter: false,
              cellClass: ['justify-center', 'bg-gray-50', '!border-gray-200']
            },
            {
              flex: 1,
              field: 'proyek_nama',
              headerName:  'Nama Proyek',
              sortable: false, resizable: true, filter: 'ColFilter',
              cellClass: ['border-r', '!border-gray-200', 'justify-start']
            },
            {
              flex: 1,
              field: 'proyek_kode',
              headerName:  'Kode Proyek',
              sortable: false, resizable: true, filter: 'ColFilter',
              cellClass: ['border-r', '!border-gray-200', 'justify-start']
            },            
            ]">
            <div
              class="bg-blue-600 text-sm text-white font-semibold hover:bg-blue-500 transition-transform duration-300 transform hover:-translate-y-0.5 rounded p-1.5">
              <icon fa="plus" size="sm mr-0.5" /> Add to list
            </div>
          </ButtonMultiSelect>
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
                <td class="text-black font-bold text-capitalize px-2 text-center w-[5%] border border-[#CACACA]">Action
                </td>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, i) in detail.items" :key="i" :class="{'bg-gray-200': proyekPilih === item.m_proyek_id}"
                class="border-t cursor-pointer " @click="clickProyek(item.m_proyek_id , i)">
                <td class="p-2 text-center pt-7 pb-4 border border-[#CACACA] ">
                  {{ i + 1 }}.
                </td>
                <td class="p-2 pt-7 pb-4 border border-[#CACACA]">
                  <FieldSelect :bind="{disabled:true ,clearable: false}" class="col-span-8 !mt-0 w-full"
                    :value="item.m_proyek_id" @input="v => item.m_proyek_id = v" valueField="id"
                    displayField="proyek_nama" :api="{
                  url: `${store.server.url_backend}/operation/m_proyek`,
                  headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}` },
                  params: { simplest: true, transform: false, join: false }
                }" placeholder="Pilih Proyek" label=" " :check="false" />
                </td>
                <td class="p-2 pt-7 pb-4 border border-[#CACACA]">
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
        <div class="mt-4">
          <button
          @click="addDetail2"
          type="button"

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
                <td class="text-black font-bold text-capitalize px-2 text-center w-[5%] border border-[#CACACA]">Action
                </td>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, i) in detail2.items" :key="i" class="border-t">
                <td class="p-2 text-center pt-7 pb-4 border border-[#CACACA]">{{ i + 1 }} .</td>
                <td class="p-2 pt-7 pb-4 border border-[#CACACA]">
                  <FieldX :bind="{ readonly: false }" @input="(v) => item.task = v" :value="item.task"
                    :error-text="formErrors.task" placeholder="Tuliskan Task" label='' class="col-span-8 !mt-0 w-full"
                    :check="false" />
                </td>
                <td class="p-2 pt-7 pb-4 border border-[#CACACA]">
                  <FieldSelect :bind="{clearable:false }" :value="item.status" @input="v=>item.status=v"
                    :errorText="formErrors.status?'failed':''" :hints="formErrors.status" valueField="key"
                    displayField="key" :options="[
                            {'key' : 'TODO'}, 
                            {'key' : 'PROGRESS'},
                            {'key' : 'DONE'},
                            ]" placeholder="Status" label="" :check="false" />
                </td>
                <td class="p-2 pt-7 pb-4 border border-[#CACACA]">
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








@endverbatim