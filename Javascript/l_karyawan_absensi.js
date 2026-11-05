  import { useRouter, useRoute, RouterLink } from 'vue-router'
  import { ref, readonly, reactive, inject, onMounted, onBeforeMount, watchEffect, onActivated } from 'vue'

  const router = useRouter()
  const route = useRoute()
  const store = inject('store')
  const swal = inject('swal')

  const isRead = route.params.id && route.params.id !== 'create'
  const actionText = ref(route.params.id === 'create' ? 'Tambah' : route.query.action)
  const isBadForm = ref(false)
  const isRequesting = ref(false)
  const modulPath = route.params.modul
  const currentMenu = store.currentMenu
  const apiTable = ref(null)
  const exportHtml = ref(false)
  const formErrors = ref({})
  const activeTabIndex = ref(0)
  const tsId = `ts=`+(Date.parse(new Date()))

  const file_report = ref('')

  // ------------------------------ PERSIAPAN
  onBeforeMount(()=>{
    document.title = 'Laporan Absensi'
  })

  //  @if( $id )------------------- VALUES FORM ! PENTING JANGAN DIHAPUS
  
  //  @else----------------------- LANDING
  let initialValues = {}
  const changedValues = []
  const checkedState = ref()
  
  //let thisMonth = new Date().toISOString().split('T')[0]
  //let tempYear = thisMonth.split('-')[0]
  //let tempMonth = thisMonth.split('-')[1]
  //const openDateSelected = ref(null)
  //const dataByDate = ref([])
  //const dataByDateDetail = ref([])

  //const values = reactive({
  //  tipe: 'HTML',
  //  tipe_report : 'Laporan Absensi Karyawan Rekap',
  //  periode : tempYear+'-'+tempMonth,
  //})

  //const resetValuesPeriode = () => {
  //  values.periode = tempYear+'-'+tempMonth
  //}

  let todayDate = new Date().toISOString().split('T')[0] 
  let tempYear = todayDate.split('-')[0]
  let tempMonth = todayDate.split('-')[1]

  let firstDayOfMonth = `${tempYear}-${tempMonth}-01`


  const openDateSelected = ref(null)
  const dataByDate = ref([])
  const dataByDateDetail = ref([])

  const values = reactive({
    tipe: 'HTML',
    tipe_report : 'Laporan Absensi Karyawan Rekap',
    periode_awal : firstDayOfMonth,
    periode_akhir : todayDate,
  })

  const resetValuesPeriode = () => {
    values.periode_awal = firstDayOfMonth
    values.periode_akhir = todayDate
  }

  const onGenerate = async () => {

    if(values.tipe_report == 'Laporan Absensi Karyawan Rekap'){
      file_report.value = 'report_karyawan_absensi_rekap'
    }
    if(values.tipe_report == 'Laporan Absensi Karyawan Detail'){
      file_report.value = 'report_karyawan_absensi_detail'
    }
    if(values.tipe_report == 'Laporan Sisa Cuti Karyawan'){
      file_report.value = 'report_sisa_cuti_karyawan'
    }
    
    if(values.tipe_report == 'Laporan Absensi Karyawan Detail' && !values.m_kary_id){
      swal.fire({
        icon: 'error',
        text: 'Harap Memilih Karyawan Terlebih Dahulu!',
      })
      return
    }
    if(values.tipe === null || values.tipe_report === null){
      swal.fire({
        icon: 'error',
        text: 'Harap Memilih Tipe Export dan Tipe Report Dahulu!',
      })
      return
    }
    if(values.tipe_report === 'Laporan Absensi Karyawan Detail' && (values.periode === null)){
      swal.fire({
        icon: 'error',
        text: 'Harap Memilih Periode Dahulu!',
      })
      return
    }
    const tempGet = []
    isRequesting.value = true
    if(values.tipe){
      if(values.tipe?.toLowerCase() === 'excel'){
        tempGet.push(`export=xls`)
      }else if(values.tipe?.toLowerCase() === 'pdf'){
        tempGet.push(`export=pdf`)
      }
    }
  
    if(values.periode){
      let tempDay = values.periode.split('/')[0]
      tempGet.push(`periode=${tempDay}`)
    }

    if(values.periode_awal){
      let tempDay = values.periode_awal
      tempGet.push(`periode_awal=${tempDay}`)
    }

    if(values.periode_akhir){
      let tempDay = values.periode_akhir
      tempGet.push(`periode_akhir=${tempDay}`)
    }
    
    if(values.tipe_report){
      tempGet.push(`tipe_report=${values.tipe_report}`)
    }
    if(values.m_kary_id){
      tempGet.push(`kary_id=${values.m_kary_id}`)
    }
    if(values.m_divisi_id){
      tempGet.push(`m_divisi_id=${values.m_divisi_id}`)
    }
    if(values.m_dept_id){
      tempGet.push(`m_dept_id=${values.m_dept_id}`)
    }

    const paramsGet = tempGet.join("&")
    if(values.tipe?.toLowerCase() !== 'html'){
      exportHtml.value = false
      window.open(`${store.server.url_backend}/web/${file_report.value}` + '?' + paramsGet)
    }else{
      await fetch(`${store.server.url_backend}/web/${file_report.value}` + '?' + paramsGet, {
        headers: {
            'Content-Type': 'html',
          },
      })
      .then(response => response.text())
      .then(html => {
        exportHtml.value = true
        const tempDiv = document.createElement('div')
        tempDiv.innerHTML = html
        const targetDiv = document.getElementById('exportTable')
        targetDiv.innerHTML = ''
        targetDiv.appendChild(tempDiv)
      })
      .catch(error => {
        swal.fire({
          icon: 'error',
          text: error,
        })
      })
    }
    
    isRequesting.value = false
  }

  //  @endif -------------------------------------------------END
  watchEffect(()=>store.commit('set', ['isRequesting', isRequesting.value]))