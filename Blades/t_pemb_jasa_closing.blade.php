@if(!$req->has('id'))
<div class="bg-white p-1 rounded-md min-h-[520px] border-t-5 border-red-700">
  <!-- Position Indicator -->
  <div class="flex justify-between items-center bg-[#800000] text-white px-4 py-2 rounded-t-md">
    <h3 class="text-lg font-bold">Pembelian Barang/Jasa</h3>
  </div>

  <!-- Table Header -->
  <div class="p-4">
    <!-- Filter Section -->
    <div class="mb-4 flex items-center">
      <span class="mr-2 font-semibold">Filter Status:</span>
      <!-- Active Button -->
      <button
        @click="filterShowData(true, 1)"
        :class="{'bg-green-500 text-white': activeBtn === 1, 'border-green-500 text-green-500': activeBtn !== 1}"
        class="border-1 font-semibold bg-white hover:bg-green-500 hover:text-white duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2"
      >
        Aktif
      </button>

      <!-- Inactive Button -->
      <button
        @click="filterShowData(false, 2)"
        :class="{'bg-red-500 text-white': activeBtn === 2, 'border-red-500 text-red-500': activeBtn !== 2}"
        class="border-1 font-semibold bg-white hover:bg-red-500 hover:text-white duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2 ml-2"
      >
        Nonaktif
      </button>
    </div>

    <TableApi ref="apiTable" :api="landing.api" :columns="landing.columns" :actions="landing.actions"
      class="max-h-[450px]">
      <!-- Create New Button and Filter Buttons -->
      <template #header>
        <div class="flex space-x-2">
          <!-- Create New Button -->
          <RouterLink :to="$route.path + '/create?' + (Date.parse(new Date()))"
            class="border-1 border-[#800000] font-semibold text-[#800000] bg-white hover:bg-[#800000] hover:text-white duration-300 transform hover:-translate-y-0.5 rounded-md py-1 px-2">
            Create New
          </RouterLink>
        </div>
      </template>
    </TableApi>
  </div>
</div>
@else

<!-- CONTENT -->
@verbatim
<div class="flex flex-col border rounded-md shadow-md md:w-full w-full p-0 bg-[#800000] border-none text-white">
  <!-- Header -->
  <div class="rounded-t-md py-2 px-4 mt-5">
    <div class="flex items-center gap-x-2">
      <!-- Back Button -->
      <button
        class="py-1 px-2 rounded transition-all text-[#800000] bg-[#FFEBEE] border border-[#FFEBEE] duration-300 hover:text-white hover:bg-[#800000]"
        @click="onBack"
      >
        <icon fa="arrow-left" size="sm" />
      </button>
      <!-- Title and Subtitle -->
      <div>
        <h1 class="text-2xl font-bold">Form Pembelian Barang Jasa</h1>
        <p v-if="actionText" class="text-[#FFEBEE]">
          {{ actionText === 'Edit' ? 'Edit' : (actionText === 'Tambah' ? 'New' : '') }} Data
        </p>
      </div>
    </div>
  </div>

  <!-- Content -->
  <div class="p-4 grid <md:grid-cols-1 grid-cols-2 gap-2 bg-white  text-gray-700">

    <!-- No. Pembelian B/J -->
    <div>
      <label class="text-sm font-bold">No. Pembelian B/J</label>
      <FieldX :bind="{ readonly: !actionText }" class="w-full !mt-3" :value="values.no_pembelian_barang"
        :errorText="formErrors.no_pembelian_barang ? 'failed' : ''" @input="v => values.no_pembelian_barang = v"
        :hints="formErrors.no_pembelian_barang" label="" placeholder="Masukkan No. Pembelian B/J" :check="false" />
    </div>

    <!-- STATUS -->
    <div>
      <label class="text-sm font-bold">Status</label>
      <FieldX :bind="{ readonly: true }" class="w-full !mt-3" :value="values.status"
        :errorText="formErrors.status ? 'failed' : ''" @input="v => values.status = v" :hints="formErrors.status"
        label="" placeholder="DRAFT" :check="false" />
    </div>

    <!-- Tipe Item -->
    <div>
      <label class="text-sm font-bold">Tipe Item</label>
      <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="w-full !mt-3" :value="values.tipe" @input="v=>{
                  values.tipe=v
                  }" :errorText="formErrors.tipe?'failed':''" displayField="tipe_item" valueField="id"
        :hints="formErrors.tipe" :api="{
                      url: `${store.server.url_backend}/operation/m_item`,
                      headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                      params: {
                        where: `this.is_active = true`,
                      }
                }" :check="false" label="" placeholder="Pilih Tipe Item" />
    </div>

    <!-- Tanggal -->
    <div>
      <label class="font-semibold">Tanggal Buat</label>
      <FieldX :bind="{ readonly: !actionText , required: true}" class="w-full !mt-3" :value="values.tgl_buat"
        :errorText="formErrors.tgl_buat?'failed':''" @input="v=>values.tgl_buat=v" :hints="formErrors.tgl_buat"
        :check="false" type="date" label="" placeholder="DD/MM/YYYY" />
    </div>

    <div>
      <label class="text-sm font-bold">Supplier <span class="text-red-600 font-bold">*</span></label>
      <FieldPopup :bind="{ readonly: !actionText }" class="w-full !mt-3" :value="values.supplier_id"
        @input="(v)=>values.supplier_id=v" :errorText="formErrors.supplier_id?'failed':''"
        :hints="formErrors.supplier_id" valueField="id" displayField="nama" :api="{
                url: `${store.server.url_backend}/operation/m_supplier`,
                headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}`},
                params: {
                  simplest:true,
                  where: `this.is_active = true`,
                  searchfield: 'this.nama'
                }
              }" placeholder="Cari Supplier" label="" :check="false" :columns="[{
                headerName: 'No',
                valueGetter:(p)=>p.node.rowIndex + 1,
                width: 60,
                sortable: false, resizable: false, filter: false,
                cellClass: ['justify-center', 'bg-gray-50']
              },
              {
                flex: 1,
                field: 'nama',
                headerName:'Nama',
                wrapText:true,
                sortable: false, resizable: true, filter: 'ColFilter',
                cellClass: ['border-r', '!border-gray-200', 'justify-end']
              },
            ]" />
    </div>

    <!-- PPN -->
    <div class="!mt-3">
      <label class="text-sm font-bold">PPN</label>
      <div class="flex items-center space-x-3">
        <!-- Checkbox -->
        <input
      type="checkbox"
      v-model="values.is_ppn"
      class="w-5 h-5 cursor-pointer accent-[#800000]"
    />

        <!-- Conditional Fields -->
        <div v-if="values.is_ppn" class="flex items-center space-x-2 w-full">
          <!-- FieldSelect for PPN Type -->
          <FieldSelect :bind="{ disabled: !actionText, clearable: true }" class="w-1/3" :value="values.ppn_type"
            @input="v => values.ppn_type = v" :errorText="formErrors.ppn_type ? 'failed' : ''"
            :hints="formErrors.ppn_type" valueField="key" displayField="key" :options="[
          { key: 'INCLUDE' },
          { key: 'EXCLUDE' }
        ]" placeholder="Pilih Tipe PPN" label="" fa-icon="caret-down" :check="false" />

          <!-- Separator -->
          <span class="font-semibold text-gray-700">-</span>

          <!-- FieldX for PPN Amount -->
          <FieldX type="number" :bind="{ readonly: !actionText }" class="w-1/4" :value="values.jumlah_ppn"
            :errorText="formErrors.jumlah_ppn ? 'failed' : ''" @input="v => values.jumlah_ppn = v"
            :hints="formErrors.jumlah_ppn" placeholder="Masukkan PPN" label="" :check="false" />

          <!-- Percentage Label -->
          <div class="bg-[#800000] text-white font-bold rounded-md px-2 py-1">
            <span>%</span>
          </div>
        </div>
      </div>
    </div>


    <!-- ToP -->
    <div>
      <label class="text-sm font-bold">ToP </label><label class="text-red-500">*</label>
      <div class="flex items-center space-x-1">
        <!-- Input ToP -->
        <FieldNumber type="number" :bind="{ readonly: !actionText }" class="w-1/3 !mt-3" :value="values.top"
          :errorText="formErrors.top ? 'failed' : ''" @input="v => values.top = v" :hints="formErrors.top"
          placeholder="Masukkan ToP" label="" :check="false" />
        <!-- Label Hari -->
        <div class="text-white bg-[#800000] font-bold  p-1 rounded-md mt-3">
          <span>HARI</span>

        </div>
      </div>
    </div>

    <!-- Due Date Delivery -->
    <div>
      <label class="font-semibold">Due Date Delivery <span class="text-red-500 space-x-0 pl-0">*</span></label>
      <FieldX :bind="{ readonly: !actionText , required: true}" class="w-full !mt-3" :value="values.due_date"
        :errorText="formErrors.due_date?'failed':''" @input="v=>values.due_date=v" :hints="formErrors.due_date"
        :check="false" type="date" label="" placeholder="DD/MM/YYYY" />
    </div>


    <!-- Catatan -->
    <div>
      <label class="text-sm font-bold">Catatan</label>
      <FieldX :bind="{ readonly: !actionText }" class="w-full !mt-3" :value="values.catatan"
        :errorText="formErrors.catatan ? 'failed' : ''" @input="v => values.catatan = v" :hints="formErrors.catatan"
        label="" type="textarea" placeholder="Catatan | Optional" :check="false" />
    </div>

    <div></div>

    <!-- STATUS TOGGLE -->
    <div class="grid grid-cols-12 items-start gap-y-2">
      <label class="col-span-12 font-bold">PPH<label class="text-red-500 space-x-0 pl-0">*</label></label>
      <div class="col-span-12 flex items-center space-x-2">
        <input
          class="mr-2 h-3.5 !-mt-0 w-8 appearance-none rounded-[0.4375rem] bg-gray-300 before:pointer-events-none before:absolute before:h-3.5 before:w-3.5 before:rounded-full before:bg-transparent before:content-[''] after:absolute after:z-[2] after:-mt-[0.1875rem] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-400 after:bg-white after:shadow-[0_0px_3px_0_rgb(0_0_0_/_7%),_0_2px_2px_0_rgb(0_0_0_/_4%)] after:transition-[background-color_0.2s,transform_0.2s] after:content-[''] checked:bg-[#800000] checked:after:absolute checked:after:z-[2] checked:after:-mt-[3px] checked:after:ml-[1.0625rem] checked:after:h-5 checked:after:w-5 checked:after:rounded-full checked:after:border checked:after:border-gray-400 checked:after:bg-white checked:after:shadow-[0_3px_1px_-2px_rgba(0,0,0,0.2),_0_2px_2px_0_rgba(0,0,0,0.14),_0_1px_5px_0_rgba(0,0,0,0.12)] checked:after:transition-[background-color_0.2s,transform_0.2s] checked:after:content-[''] hover:cursor-pointer focus:outline-none focus:ring-0 focus:before:scale-100 focus:before:opacity-[0.12] focus:before:shadow-[3px_-1px_0px_13px_rgba(0,0,0,0.6)] focus:before:transition-[box-shadow_0.2s,transform_0.2s] focus:after:absolute focus:after:z-[1] focus:after:block focus:after:h-5 focus:after:w-5 focus:after:rounded-full focus:after:content-[''] checked:focus:border-[#800000] checked:focus:bg-[#800000] checked:focus:before:ml-[1.0625rem] checked:focus:before:scale-100 checked:focus:before:shadow-[3px_-1px_0px_13px_#800000] checked:focus:before:transition-[box-shadow_0.2s,transform_0.2s] dark:bg-gray-600 dark:after:bg-gray-400 dark:checked:bg-[#800000] dark:checked:after:bg-white dark:focus:before:shadow-[3px_-1px_0px_13px_rgba(255,255,255,0.4)] dark:checked:focus:before:shadow-[3px_-1px_0px_13px_#800000]"
          type="checkbox"
          role="switch"
          id="is_pph"
          :disabled="!actionText"
          v-model="values.is_pph"
        />
        <label for="is_pph" class="text-sm font-medium text-gray-700">
          {{ values.is_pph ? 'IYA' : 'TIDAK' }}
        </label>
      </div>
    </div>


  </div>
</div>

<!-- TABLE DETAIL -->

<div class="bg-white p-4">
  <h1 class="text-2xl font-semibold mb-5"> PEMBELIAN BARANG / JASA</h1>
  <div class="<md:col-span-1 col-span-3">
    <!-- Button Multi Select -->
    <ButtonMultiSelect title="Add To List" @add="onDetailAdd" :api="{
        url: `${store.server.url_backend}/operation/m_item`,
        headers: { 'Content-Type': 'Application/json', Authorization: `${store.user.token_type} ${store.user.token}` },
        params: {
          simplest: true,
          where: 'this.is_active=true',
          searchfield: 'this.code, this.name, this.id',
          notin: detailArr.length > 0 ? `m_role.id:${detailArr.map(dt => dt.m_role_id).join(',')}` : null
        },
        onsuccess: (response) => {
          response.data = [...response.data].map((dt) => {
            return dt;
          });
          response.page = response.current_page;
          response.hasNext = response.has_next;
          return response;
        }
      }" :columns="[
        {
          checkboxSelection: true,
          headerCheckboxSelection: true,
          headerName: 'No',
          valueGetter: p => '',
          width: 60,
          sortable: false,
          resizable: true,
          filter: false,
          cellClass: ['justify-center', 'bg-gray-50', '!border-gray-200']
        },
        {
          flex: 1,
          headerName: 'KODE ITEM',
          sortable: false,
          resizable: true,
          filter: 'ColFilter',
          field: 'kode_item',
          cellClass: ['justify-center', '!border-gray-200']
        },
        {
          flex: 1,
          headerName: 'KATEGORI 1',
          sortable: false,
          resizable: true,
          filter: 'ColFilter',
          field: 'kategori_1',
          cellClass: ['justify-center', '!border-gray-200']
        },
        {
          flex: 1,
          headerName: 'TIPE ITEM',
          sortable: false,
          resizable: true,
          filter: 'ColFilter',
          field: 'tipe_item',
          cellClass: ['justify-center', '!border-gray-200']
        },
        {
          flex: 1,
          headerName: 'NAMA ITEM',
          sortable: false,
          resizable: true,
          filter: 'ColFilter',
          field: 'nama_item',
          cellClass: ['justify-center', '!border-gray-200']
        },
        {
          flex: 1,
          headerName: 'Catatan',
          sortable: false,
          resizable: true,
          filter: 'ColFilter',
          field: 'catatan',
          cellClass: ['justify-center', '!border-gray-200']
        }
      ]">
      <div v-show="actionText"
        class=" p-2  font-semibold bg-[#800000] hover:bg-[#690000] text-white py-[12px] px-[19.5px] flex items-center justify-center space-x-2 rounded">
        <icon fa="plus" />
        Pilih Kendaraan
      </div>
    </ButtonMultiSelect>

    <!-- TABLE -->
    <div class="mt-4">
      <table class="w-full overflow-x-auto table-auto border border-[#CACACA]">
        <thead>
          <tr class="bg-[#800000] text-white font-semibold text-[14px] border border-[#CACACA]">
            <td class=" font-semibold text-[14px] text-capitalize p-2 text-center w-[5%] border  border-[#CACACA]">
              No.</td>
            <td class=" font-semibold text-[14px] text-capitalize px-2 text-center border  border-[#CACACA]">Kode</td>
            <td class=" font-semibold text-[14px] text-capitalize px-2 text-center border  border-[#CACACA]">Nama Item
            </td>
            <td class=" font-semibold text-[14px] text-capitalize px-2 text-center border  border-[#CACACA]">Qty
              Pembelian</td>
            <td class=" font-semibold text-[14px] text-capitalize px-2 text-center border  border-[#CACACA]">UoM</td>
            <td class=" font-semibold text-[14px] text-capitalize px-2 text-center border  border-[#CACACA]">Harga</td>
            <td class=" font-semibold text-[14px] text-capitalize px-2 text-center border  border-[#CACACA]">Diskon %
            </td>
            <td class=" font-semibold text-[14px] text-capitalize px-2 text-center border  border-[#CACACA]">Diskon Amt
            </td>
            <td class=" font-semibold text-[14px] text-capitalize px-2 text-center border  border-[#CACACA]">Total
            </td>

            <td class=" font-semibold text-[14px] text-capitalize px-2 text-center border  border-[#CACACA]">Catatan
            </td>
            <td class=" font-semibold text-[14px] text-capitalize px-2 text-center border  border-[#CACACA] w-[5%]">
              Action</td>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, i) in detailArr" :key="i" class="border-t" v-if="detailArr.length > 0">
            <td class="p-2 text-center border border-[#CACACA]">{{ i + 1 }}.</td>
            <!-- KODE -->
            <td class="p-2 border border-[#CACACA]">
              <FieldX :bind="{ readonly: true }" class="w-full !mt-0" :value="item.kode"
                @input="v => item.kode = v" placeholder="KODE" label="" :check="false" />
            </td>
            <!-- NAMA ITEM -->
            <td class="p-2 border border-[#CACACA]">
              <FieldX :bind="{ readonly: true }" class="w-full !mt-0" :value="item.nama_item"
                @input="v => item.nama_item = v" placeholder="BERAS" label="" :check="false" />
            </td>
            <!-- QTY PEMBELIAN -->
            <td class="p-2 border border-[#CACACA]">
              <FieldX :bind="{ readonly: !actionText }" class="w-full !mt-0" :value="item.qty"
                @input="v => item.qty = v" placeholder="masukan qty" label="" :check="false" />
            </td>
            <!-- UoM -->
            <td class="p-2 border border-[#CACACA]">
              {{item.uom}}
            </td>
            <!-- HARGA -->
            <td class="p-2 border border-[#CACACA]">
              <FieldX :bind="{ readonly: !actionText }" class="w-full !mt-0" :value="item.harga"
                @input="v => item.harga = v" placeholder="masukan harga" label="" :check="false" />
            </td>
            <!-- DISKON-->
            <td class="p-2 border border-[#CACACA]">
              <FieldX :bind="{ readonly: !actionText }" class="w-full !mt-0" :value="item.diskon"
                @input="v => item.diskon = v" placeholder="masukan diskon" label="" :check="false" />
            </td>
            <!-- DISKON-->
            <td class="p-2 border border-[#CACACA]">
              <FieldX :bind="{ readonly: !actionText }" class="w-full !mt-0" :value="item.diskon_amt"
                @input="v => item.diskon_amt = v" placeholder="masukan diskon_amt" label="" :check="false" />
            </td>
            <!-- TONASE -->
            <td class="p-2 border border-[#CACACA]">
              <FieldX :bind="{ readonly: true }" class="w-full !mt-0" :value="item.total"
                @input="v => item.total = v" placeholder="Tuliskan total" label="" :check="false" />
            </td>
            <!-- CATATAN -->
            <td class="p-2 border border-[#CACACA]">
              <FieldX :bind="{ readonly: !actionText }" class="w-full !mt-0" :value="item.catatan"
                @input="v => item.catatan = v" placeholder="Tuliskan Catatan" type="textarea" label="" :check="false" />
            </td>
            <!-- STATUS -->
            <td class="p-2 border border-[#CACACA] text-center">
              <input
    type="checkbox"
    v-model="item.is_active"
    :true-value="true"
    :false-value="false"
    class="w-5 h-5 cursor-pointer"
  />
            </td>
            <!-- ACTION -->
            <td class="p-2 border border-[#CACACA]">
              <div class="flex justify-center">
                <button
                  type="button"
                  @click="removeDetail(i)"
                  :disabled="!actionText"
                  class="text-[#F24E1E] hover:text-[#b33a15]"
                >
                  <svg width="14" height="14" viewBox="0 0 14 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                      d="M14 1H10.5L9.5 0H4.5L3.5 1H0V3H14M1 16C1 16.5304 1.21071 17.0391 1.58579 17.4142C1.96086 17.7893 2.46957 18 3 18H11C11.5304 18 12.0391 17.7893 12.4142 17.4142C12.7893 17.0391 13 16.5304 13 16V4H1V16Z"
                      fill="#F24E1E"
                    />
                  </svg>
                </button>
              </div>
            </td>
          </tr>
          <tr v-else class="text-center">
            <td colspan="11" class="py-[20px]">No data to show</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

<!-- RINGKASAN TOTAL -->
<div class="mt-8 border rounded-md p-4 w-1/2 bg-white">
  <table class="w-full text-sm text-left text-gray-500">
    <tbody>
      <!-- Total -->
      <tr class="border-b border-[#CACACA]">
        <td class="py-2 font-bold">Total</td>
        <td class="py-2">{{ total }}</td>
        <td class="py-2 font-bold">Total Diskon</td>
        <td class="py-2">{{ totalDiskon }}</td>
      </tr>

      <!-- DPP -->
      <tr class="border-b border-[#CACACA]">
        <td class="py-2 font-bold">DPP</td>
        <td class="py-2">{{ dpp }}</td>
        <td class="py-2 font-bold">DP</td>
        <td class="py-2">
          <FieldX :bind="{ readonly: !actionText }" class="w-full !mt-0" :value="values.dp"
            @input="v => values.dp = v" placeholder="Masukkan DP" label="" :check="false" />
        </td>
      </tr>

      <!-- Pajak (PPN) -->
      <tr class="border-b border-[#CACACA]">
        <td class="py-2 font-bold">Pajak (PPN)</td>
        <td class="py-2 flex items-center">
          <FieldX type="number" :bind="{ readonly: !actionText }" class="w-16 !mt-0" :value="values.ppn_percentage"
            @input="v => values.ppn_percentage = v" placeholder="%" label="" :check="false" />
          <span class="ml-2">%</span>
        </td>
        <td class="py-2 font-bold">Ongkos Kirim</td>
        <td class="py-2">
          <FieldX :bind="{ readonly: !actionText }" class="w-full !mt-0" :value="values.ongkos_kirim"
            @input="v => values.ongkos_kirim = v" placeholder="Masukkan Ongkos Kirim" label="" :check="false" />
        </td>
      </tr>

      <!-- Grand Total -->
      <tr>
        <td class="py-2 font-bold">Grand Total</td>
        <td class="py-2">{{ grandTotal }}</td>
      </tr>
    </tbody>
  </table>
</div>
</div>





<!-- Divider -->
<hr v-show="actionText" class="border-gray-300">

<!-- Action Buttons -->
<div v-show="actionText" class="flex flex-row items-center justify-end space-x-2 p-2 bg-white rounded-b-md">
  <i class="text-gray-500 text-[12px]">Tekan CTRL + S untuk shortcut Save Data</i>
  <button
      class="bg-green-600 text-white font-semibold hover:bg-green-500 transition-transform duration-300 transform hover:-translate-y-0.5 rounded-md p-2"
      v-show="actionText"
      @click="onSave"
    >
      <icon fa="save" />
      Simpan
    </button>
</div>
</div>
@endverbatim
@endif