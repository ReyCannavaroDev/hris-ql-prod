
  import {useRouter, useRoute} from 'vue-router'
  import {ref, reactive, inject, onBeforeMount, watchEffect} from 'vue'

  const router = useRouter()
  const route = useRoute()
  const store = inject('store')
  const swal = inject('swal')

  // STATE
  const isRequesting = ref(false)
  const isBadForm = ref(false)
  const formErrors = ref({})
  let initialValues = {}
  let resultValues = reactive({items: [] })
  const values = reactive({group_data: [] })   // default array kosong
  const availableDates = ref([])

  // versi single-page
  const showForm = ref(false)
  const selectedMenu = ref(null)

  const endpointApi = '/presensi_maksi'

// =================== INIT ===================
onBeforeMount(() => {
    document.title = 'Form Pesan Makan Siang'
  loadInitalData()
})

// =================== LOAD DATA ===================
const loadInitalData = async () => {
  try {
    isRequesting.value = true
    const dataURL = `${store.server.url_backend}/operation${endpointApi}/get_maksi_3hari`
  const res = await fetch(dataURL, {
    headers: {
    'Content-Type': 'application/json',
  Authorization: `${store.user.token_type} ${store.user.token}`
      },
    })
  if (!res.ok) throw new Error("Gagal mengambil data")

  const resultJson = await res.json()

  if (!showForm.value) {
    availableDates.value = resultJson.data || []
  } else {
      const allMenus = resultJson.data || []
      const selected = allMenus.find(m => m.id === selectedMenu.value.id)

  if (!selected) {
    values.group_data = []
        return
      }

  initialValues = selected
  Object.assign(values, JSON.parse(JSON.stringify(selected)))
  loadDet()
    }
  } catch (err) {
    isBadForm.value = true
    swal.fire({
    icon: 'error',
  text: err.message || err
    })
  } finally {
    isRequesting.value = false
  }
}

// =================== DETAIL LAUK ===================
const checkItemDetail = (idx, i) => {
  const tipe = values.group_data[idx]['detail'][i]['tipe_lauk.value_2']?.toLowerCase()
  values.group_data[idx]['not_check'] = false

  if (tipe === 'single') {
    values.group_data[idx]['detail'].forEach((d, di) => {
      d.check = di === i ? !d.check : false
    })
  } else {
    values.group_data[idx]['detail'][i]['check'] = !values.group_data[idx]['detail'][i]['check']
  }
  loadDet()
}

const checkItemDetailNotIn = (idx) => {
    values.group_data[idx]['not_check'] = true
  values.group_data[idx]['detail'].forEach(d => d.check = false)
  loadDet()
}

const loadDet = () => {
  const filteredArrResult = values?.group_data
    ?.map(item => ({...item, detail: item.detail.filter(det => det.check) }))
    .filter(item => !item?.not_check)

  resultValues.items = filteredArrResult
  filteredArrResult?.forEach(v => {
    v.detail_text = v?.detail?.map(det => det.lauk).join(', ')
  })
}

  // =================== SAVE ===================
  async function onSave() {
  if (!resultValues.items.length) {
    return swal.fire({icon: 'warning', text: 'Harap tambahkan setidaknya satu pilihan lauk' })
  }

  if (resultValues.items.some(item => !item.detail_text || item.detail_text.trim() === '')) {
    return swal.fire({icon: 'warning', text: 'Harap pilih lauk terlebih dahulu.' })
  }

  const konfirm = await swal.fire({
    icon: 'warning',
  text: 'Kamu yakin sudah memilih menu dengan benar?',
  showDenyButton: true
  })

  if (!konfirm.isConfirmed) return

  try {
    isRequesting.value = true
    const dataURL = `${store.server.url_backend}/operation/presensi_maksi_det/pesan_maksi_3hari`

  const payload = {
    presensi: [
  {
    presensi_maksi_id: selectedMenu.value.id,
  tanggal: selectedMenu.value.tanggal,
  pesan: resultValues.items
        }
  ]
    }

  const res = await fetch(dataURL, {
    method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  Authorization: `${store.user.token_type} ${store.user.token}`
      },
  body: JSON.stringify(payload)
    })

  if (!res.ok) {
      const responseJson = await res.json()
  formErrors.value = responseJson.errors || {}
  throw (responseJson.message || "Terjadi kesalahan saat menyimpan data.")
    }

  swal.fire({icon: 'success', text: 'Pesanan berhasil disimpan' })
  showForm.value = false
  loadInitalData()
  } catch (err) {
    isBadForm.value = true
    swal.fire({icon: 'error', text: err })
  } finally {
    isRequesting.value = false
  }
}

  // =================== CANCEL ===================
  async function onCancel() {
  const konfirm = await swal.fire({
    icon: 'warning',
  text: `Yakin ingin membatalkan pesanan tanggal ${formatDate(selectedMenu.value.tanggal)}?`,
  showCancelButton: true,
  confirmButtonText: 'Ya, batalkan',
  cancelButtonText: 'Batal'
  });

  if (!konfirm.isConfirmed) return;

  try {
    isRequesting.value = true;
  const dataURL = `${store.server.url_backend}/operation/presensi_maksi_det/cancel_3hari`;

  const res = await fetch(dataURL, {
    method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  Authorization: `${store.user.token_type} ${store.user.token}`
      },
  body: JSON.stringify({
    tanggal: selectedMenu.value.tanggal
      })
    });

  const responseJson = await res.json();
  if (!res.ok) {
      throw (responseJson.message || 'Gagal membatalkan pesanan.');
    }

  swal.fire({icon: 'success', text: responseJson.message });
  showForm.value = false;
  loadInitalData();
  } catch (err) {
    swal.fire({ icon: 'error', text: err });
  } finally {
    isRequesting.value = false;
  }
}

// =================== OPEN FORM ===================
const openForm = async (menu) => {
    selectedMenu.value = menu
  showForm.value = true

  // isi reactive values dengan menu
  Object.assign(values, JSON.parse(JSON.stringify(menu)))

  if (menu.sudah_pesan) {
    try {
      const url = `${store.server.url_backend}/operation/presensi_maksi_det?presensi_m_menu_maksi_id=${menu.presensi_m_menu_maksi_id}&where=this.m_kary_id='${store.user.data.m_kary_id}'`
  const res = await fetch(url, {
    method: "GET",
  headers: {
    "Authorization": "Bearer " + store.user.token,
  "Content-Type": "application/json"
        }
      });

  const detailData = await res.json()
      const laukList = detailData.data?.flatMap(d => d.lauk || []) || []

      laukList.forEach(l => {
        const groupIndex = values.group_data.findIndex(g => g.tipe_lauk_id === l.tipe_lauk_id);
  if (groupIndex !== -1) {
    values.group_data[groupIndex].detail.forEach(d => {
      if (l.detail.some(ld => ld.id === d.id)) {
        d.check = true
      }
    })
  }
      })
  loadDet()
    } catch (err) {
    console.error("Gagal fetch detail pesanan:", err)
  }
  } else {
    loadDet()
  }
}

  // =================== UTIL ===================
  function onBack() {
    showForm.value = false
  selectedMenu.value = null
  values.group_data = []
}

  function dayName(iso) {
  const d = new Date(iso)
  return ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][d.getDay()]
}

  function formatDate(iso) {
  const d = new Date(iso)
  const m = [
  "Januari", "Februari", "Maret", "April", "Mei", "Juni",
  "Juli", "Agustus", "September", "Oktober", "November", "Desember"
  ][d.getMonth()]
  return `${d.getDate()} ${m} ${d.getFullYear()}`
}

watchEffect(() => store.commit('set', ['isRequesting', isRequesting.value]))
