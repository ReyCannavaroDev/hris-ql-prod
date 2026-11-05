@verbatim
<div id="maksiApp" class="bg-white p-6 rounded-xl min-h-[570px]">

  <!-- LIST HARI & TANGGAL -->
  <div v-if="!showForm">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-xl font-bold">Pemesanan Makan (3 Hari)</h2>
      <button @click="loadInitalData()" class="bg-slate-100 hover:bg-slate-200 text-slate-700 rounded px-3 py-1 text-sm">
        Refresh
      </button>
    </div>

    <div v-if="isRequesting" class="flex items-center justify-center h-[480px] text-gray-500">
      Memuat data menu...
    </div>

    <div v-else>
      <template v-if="Array.isArray(availableDates) && availableDates.length">
        <div class="space-y-3">
          <div v-for="menu in availableDates" :key="menu.id"
            class="border rounded-xl p-4 flex items-center justify-between">
            <div class="flex flex-col">
              <span class="text-lg font-semibold">{{ dayName(menu.tanggal) }}</span>
              <span class="text-sm text-gray-500">{{ formatDate(menu.tanggal) }}</span>
            </div>

            <div class="flex items-center gap-3">
              <span
                v-if="menu.sudah_pesan"
                class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700 border border-green-300">
                Sudah dipesan
              </span>

              <button
                @click="openForm(menu)"
                class="bg-green-600 hover:bg-green-700 text-white rounded px-4 py-2 disabled:opacity-50"
              >
                {{ menu.sudah_pesan ? 'Lihat Pesanan' : 'Pesan' }}
              </button>
            </div>
          </div>
        </div>
      </template>

      <div v-else class="flex items-center justify-center h-[480px] text-gray-500">
        Tidak ada menu tersedia untuk dipesan.
      </div>
    </div>
  </div>

  <!-- FORM PEMESANAN -->
  <div v-else>
    <div class="flex flex-col gap-y-3">
      <div class="flex gap-x-4 px-2">
        <div class="flex flex-col border rounded shadow-sm px-6 py-6 w-full bg-white">

          <!-- HEADER -->
          <div class="flex flex-col items-start mb-4 pb-2 border-b">
            <h1 class="text-[24px] mb-1 font-bold">Form Transaksi Makan Siang</h1>
            <p class="text-sm text-gray-600">
              Tanggal: <span class="font-medium">{{ formatDate(selectedMenu.tanggal) }}</span>
            </p>
          </div>

          <div v-if="!values?.group_data || !values.group_data.length"
            class="bg-yellow-50 border border-yellow-300 text-yellow-800 px-4 py-3 rounded mb-4">
            Data menu untuk tanggal yang dipilih belum tersedia. Silakan kembali ke daftar.
          </div>

          <!-- PILIHAN LAUK -->
          <div v-if="values?.group_data && values.group_data.length" class="space-y-6">
            <div v-for="(grp, idx) in values.group_data" :key="grp.tipe_lauk_id" class="border rounded-lg">
              <div class="flex items-center justify-between px-4 py-3 bg-gray-50 rounded-t-lg">
                <div class="font-semibold">{{ grp.tipe_lauk }}</div>
                <button
                  v-if="!selectedMenu.sudah_pesan"
                  type="button"
                  class="text-xs underline text-gray-600 hover:text-gray-800"
                  @click="checkItemDetailNotIn(idx)"
                >
                  Tidak ambil {{ grp.tipe_lauk }}
                </button>
              </div>

              <div class="p-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                <label
                  v-for="(d, i) in grp.detail"
                  :key="d.__id || i"
                  class="flex items-center gap-2 border rounded p-2 cursor-pointer"
                  :class="{ 'bg-gray-100 cursor-not-allowed': selectedMenu.sudah_pesan }"
                >
                  <input
                    :type="(d['tipe_lauk.value_2'] || grp['tipe_lauk.value_2']) === 'single' ? 'radio' : 'checkbox'"
                    :name="`lauk-${grp.tipe_lauk_id}`"
                    :checked="!!d.check"
                    :disabled="selectedMenu.sudah_pesan"
                    @change="checkItemDetail(idx, i)"
                  />
                  <span class="text-sm">{{ d.lauk }}</span>
                </label>
              </div>
            </div>

            <!-- RINGKASAN PILIHAN -->
            <div class="mt-2">
              <h3 class="font-semibold mb-2">Ringkasan Pilihan</h3>
              <table class="w-full table-auto border border-[#CACACA] text-sm">
                <thead>
                  <tr class="bg-[#f8f8f8] text-gray-700">
                    <th class="border p-2 w-[5%]">No</th>
                    <th class="border p-2 w-[30%]">Tipe Lauk</th>
                    <th class="border p-2">Detail</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="!resultValues.items || !resultValues.items.length">
                    <td colspan="3" class="text-center py-3 text-gray-500">Belum ada pilihan.</td>
                  </tr>
                  <tr v-else v-for="(it, ii) in resultValues.items" :key="`${it.tipe_lauk_id}-${ii}`">
                    <td class="border p-2 text-center">{{ ii + 1 }}</td>
                    <td class="border p-2">{{ it.tipe_lauk }}</td>
                    <td class="border p-2">{{ it.detail_text }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- BUTTONS -->
          <div class="flex flex-row justify-end gap-3 mt-8">
            <button
              @click="onBack"
              class="bg-[#EF4444] hover:bg-[#ed3232] text-white px-[24px] py-[10px] rounded-[6px]">
              Kembali
            </button>
            <button
              v-if="!selectedMenu.sudah_pesan"
              @click="onSave"
              class="bg-[#10B981] hover:bg-[#0ea774] text-white px-[24px] py-[10px] rounded-[6px]"
            >
              Simpan
            </button>
                <button
                  v-if="selectedMenu.sudah_pesan"
                  @click="onCancel"
                  class="bg-yellow-500 hover:bg-yellow-600 text-white px-[24px] py-[10px] rounded-[6px]">
                  Batalkan Pesanan
                </button>
          </div>

        </div>
      </div>
    </div>
  </div>

</div>
@endverbatim