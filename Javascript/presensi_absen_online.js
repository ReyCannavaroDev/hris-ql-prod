import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, readonly, reactive, inject, onMounted, onBeforeMount,onBeforeUnmount, watchEffect, onActivated, watch } from 'vue'

const router = useRouter()
const route = useRoute()
const store = inject('store')
const swal = inject('swal')

const isRead = route.params.id && route.params.id !== 'create'
const actionText = ref(route.params.id === 'create' ? 'Tambah' : route.query.action)
const isProfile = ref(route.query.profile ? true : false)
const isBadForm = ref(false)
const isRequesting = ref(false)
const modulPath = route.params.modul
const currentMenu = store.currentMenu
const apiTable = ref(null)
const formErrors = ref({})
const formErrorsPend = ref({})
const formErrorsKel = ref({})
const formErrorsPel = ref({})
const formErrorsPres = ref({})
const formErrorsOrg = ref({})
const formErrorsBhs = ref({})
const formErrorsPK = ref({})
const activeTabIndex = ref(0)
const content = ref()

//facereg
const faceResultText = ref('')
const faceMatch = ref(null)
const recognizedEmployeeId = ref('')
const recognizedName = ref('')
const FACE_API_BASE = 'https://hris.qqltech.com:7089'

const qualityScore = ref(null)
const qualityLabel = ref('')
const qualityMessage = ref('')
const voiceEnabled = ref(true)

let qualityTimer = null
let lastSpokenKey = null
let lastSpeakAt = 0
let lastSpokenScore = null
let lastSpeakStartedAt = 0

const MIN_DELTA_SCORE = 15 // minimal beda skor utk ngomong lagi
const MIN_INTERVAL_MS = 10000 // minimal 10 detik antar suara

// kalau user matikan panduan suara, langsung hentikan semua suara
watch(voiceEnabled, (val) => {
  if (typeof window === 'undefined') return
  const synth = window.speechSynthesis
  if (!synth) return

  try {
    synth.cancel()
    console.log('Voice helper: cancel semua suara, voiceEnabled =', val)
  } catch (e) {
    console.log(e)
  }
})


const tsId = `ts=`+(Date.parse(new Date()))

// ------------------------------ PERSIAPAN
const endpointApi = '/m_kary'
onBeforeMount(()=>{
  document.title = 'Absen Online'
})

//  @if( $id )------------------- VALUES FORM ! PENTING JANGAN DIHAPUS
let initialValues = {}
const changedValues = []
const thisYear = new Date().getFullYear()
let tempKTP = ''
let tempBPJS = ''
let tempNPWP = ''
let tempKK = ''
let tempPasfoto = ''


const values = reactive({
  is_active: 1,
  direktorat: store.user.data?.direktorat,
  cuti_p24: 0,
  cuti_reguler: 0,
  cuti_masa_kerja:0,
  cuti_p24_terpakai:0,
  sisa_cuti_reguler:0,
  sisa_cuti_masa_kerja:0,

})

function onBack() {
  let isChanged = false
  for (const key in initialValues) {
    if (values[key] !== initialValues[key]) {
      isChanged = true
      break;
    }
  }

  if (!isChanged) {
    router.replace('/' + modulPath)
    return
  }

      router.replace('/' + modulPath)
}

function onReset() {
  swal.fire({
    icon: 'warning',
    text: 'Reset this form data?',
    showDenyButton: true
  }).then((res) => {
    if (res.isConfirmed) {
      for (const key in initialValues) {
        values[key] = initialValues[key]
      }
    }
  })
}

async function onSave() {
  //values.tags = JSON.stringify(values.tags)
      try {
        const dataURL = `${store.server.url_backend}/operation${endpointApi}${isCreating ? '' : ('/' + route.params.id)}`
        isRequesting.value = true
        const res = await fetch(dataURL, {
          method: isCreating ? 'POST' : 'PUT',
          headers: {
            'Content-Type': 'Application/json',
            Authorization: `${store.user.token_type} ${store.user.token}`
          },
          body: JSON.stringify(values)
        })
        if (!res.ok) {
          // values.m_kary_det_kartu = []
          if ([400, 422].includes(res.status)) {
            const responseJson = await res.json()
            formErrors.value = responseJson.errors || {}
            throw (responseJson.errors.length ? responseJson.errors[0] : responseJson.message || "Failed when trying to post data")
          } else {
            throw ("Failed when trying to post data")
          }
        }
        router.replace('/' + modulPath + '?reload='+(Date.parse(new Date())))
      } catch (err) {
        isBadForm.value = true
        swal.fire({
          icon: 'error',
          text: err
        })
      }
      isRequesting.value = false
}

//  @else----------------------- LANDING

//  @endif -------------------------------------------------END
watchEffect(()=>store.commit('set', ['isRequesting', isRequesting.value]))


const tempDate = new Date()
const listTahun = []
const tempmonth = tempDate.getMonth() + 1
const tempyear = tempDate.getFullYear()
const form = reactive({
  month: tempmonth,
  year: tempyear 
})
for(let i = form.year; i >= 2010; i-- ){
    listTahun.push(i)
  }

onBeforeUnmount(()=>{
  const stream = videoElement.value.srcObject
  stream.getTracks()?.forEach((track)=>{
      track.stop()
    })
  
})

const videoElement = ref(null)
const capturedImage = ref(null)
const coordsLocation = ref()
const listDetail = ref([])
const isImage = ref(false)
const formData = new FormData()
const dataDetail = ref()
const showModal = ref(false)

async function tampilkanModal(data){
  showModal.value = true
  dataDetail.value = data
}

async function capture() {
  try {
    const canvas = document.createElement('canvas');
    canvas.width = videoElement.value.videoWidth;
    canvas.height = videoElement.value.videoHeight;
    canvas.getContext('2d').translate(canvas.width, 0);
    canvas.getContext('2d').scale(-1, 1);
    canvas.getContext('2d').drawImage(videoElement.value, 0, 0, canvas.width, canvas.height);
    const captredImageSrc = canvas.toDataURL('image/jpeg');
    let imgElem = document.getElementById('imgElem');
    imgElem.setAttribute('src', captredImageSrc);
    isImage.value=true
    const stream = videoElement.value.srcObject;
    const capturedImageBlob = await new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg'));
    formData.append('foto', capturedImageBlob, 'captured_image.jpg');

    recognizeFacePublic(capturedImageBlob)

    // formData.forEach((value, key) => {
    //   if (value instanceof File) {
    //     console.log(`${key}: ${value.name} (${value.type}), ${value.size} bytes`);
    //   } else {
    //     console.log(`${key}: ${value}`);
    //   }
    // });
    // capturedImage.value = formData
    stream.getTracks()?.forEach((track)=>{
      track.stop()
    })
    getLocation()
  } catch (error) {
    console.log(error)
    alert("Oh maaf, sepertinyßa kami tidak mendapatkan akses kamera anda");
  }
}

async function recapture(){
  faceResultText.value = ''
  faceMatch.value = null
  recognizedEmployeeId.value = ''
  recognizedName.value = ''
  
  mountCam()
  isImage.value = false

}

function updateTime() {
    var now = new Date();
    var hours = now.getHours();
    var minutes = now.getMinutes();
    var seconds = now.getSeconds();
    
    hours = (hours < 10) ? "0" + hours : hours;
    minutes = (minutes < 10) ? "0" + minutes : minutes;
    seconds = (seconds < 10) ? "0" + seconds : seconds;
    
    var currentTime = hours + ":" + minutes + ":" + seconds;
    
    form.currentTime = currentTime;
}

setInterval(updateTime, 1000);

var listMonths = [
    { id: 1, name: "Januari" },
    { id: 2, name: "Februari" },
    { id: 3, name: "Maret" },
    { id: 4, name: "April" },
    { id: 5, name: "Mei" },
    { id: 6, name: "Juni" },
    { id: 7, name: "Juli" },
    { id: 8, name: "Agustus" },
    { id: 9, name: "September" },
    { id: 10, name: "Oktober" },
    { id: 11, name: "November" },
    { id: 12, name: "Desember" }
];



onMounted(async()=>{
  isRequesting.value = true
  // mountCam() 
  await getLocation()
  await checkLastStatus()
  await getDetailAbsen(form.year,form.month)
  const tempDate = new Date()
  const day = tempDate.getDate() 
  const monthName = ["Januari", "Februari", "Maret", "April","Mei", "Juni", "Juli", "Agustus","September", "Oktober", "November", "Desemeber"][tempDate.getMonth()]
  const year = tempDate.getFullYear() 
  form.tanggal = `${day} ${monthName} ${year}`  
  form.day = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][tempDate.getDay()]
  isRequesting.value = false
})

function getLocation() {
  console.log(navigator.geolocation)
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(setPosition, showError);
  } else { 
    swal.fire({
      icon: 'error',
      text: "Geolocation is not supported by this browser."
    })
  }
}

function showError(error) {
  switch(error.code) {
    case error.PERMISSION_DENIED:
      // if (navigator.userAgent.includes('Safari') && !navigator.userAgent.includes('Chrome')) {
      //   swal.fire({
      //     icon: 'error',
      //     text: "Geolocation is not supported or denied by this browser. Please check your browser settings."
      //   });
      // } else {
        swal.fire({
          icon: 'error',
          text: "Please enable location permissions to access your current location.",
        });
      // }
      break;
    case error.POSITION_UNAVAILABLE:
      swal.fire({
        icon: 'error',
        text: "Location information is unavailable."
      });
      break;
    case error.TIMEOUT:
      swal.fire({
        icon: 'error',
        text: "The request to get user location timed out."
      });
      break;
    case error.UNKNOWN_ERROR:
      swal.fire({
        icon: 'error',
        text: "An unknown error occurred."
      });
      break;
  }
}

function removeStrip(data){
  const tempTest = data?.split('-')
  const monthName = ["Januari", "Februari", "Maret", "April","Mei", "Juni", "Juli", "Agustus","September", "Oktober", "November", "Desemeber"][tempTest[1]-1]
  return `${tempTest[0]} ${monthName} ${tempTest[2]}`
  // console.log(data)
  // return data?.replace(/-/g,' ')
}

async function getDetailAbsen(year,month){
  try{
      const params = `${year}-${month}`
      const res = await fetch(`${store.server.url_backend}/operation/presensi_absensi/get_absen?periode=${params}`, {
        headers: {
          'Content-Type': 'Application/json',
          Authorization: `${store.user.token_type} ${store.user.token}`
        },
      })  
      if (!res.ok) {
        if ([400, 422].includes(res.status)) {
          const responseJson = await res.json()
          throw (responseJson.message || "Failed when trying to post data")
        } else {
          throw ("Failed when trying to post data")
        }
      }
      const resultJson = await res?.json()
      const data = resultJson.data
      listDetail.value = data
    }catch(err){
      swal.fire({
        icon: 'error',
        text: err
      })
    }
}

async function checkLastStatus(){
  try{
    const res = await fetch(`${store.server.url_backend}/operation/presensi_absensi/status`, {
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      },
    })  
    if (!res.ok) {
      if ([400, 422].includes(res.status)) {
        const responseJson = await res.json()
        throw (responseJson.message || "Failed when trying to post data")
      } else {
        throw ("Failed when trying to post data")
      }
    }
    const resultJson = await res?.json()
    const data = resultJson.data
    form.attending = data.status
    if(form.attending?.toLowerCase() === 'attend'){
      // const stream = videoElement.value.srcObject
      // stream?.getTracks()?.forEach((track)=>{
      //   track.stop()
      // })
    }else{
      mountCam()
    }
  }catch(err){
    swal.fire({
      icon: 'error',
      text: err
    })
  }
}

async function postAttend(){
  try{
    formData.append('lat', coordsLocation.value?.latitude)
    formData.append('long', coordsLocation.value?.longitude)
    formData.append('address', form.address)
    let postData = {
      foto: capturedImage.value,
      lat: coordsLocation.value?.latitude,
      long: coordsLocation.value?.longitude,
      address: form.address
    }

    // if (!validateFaceWithLoginUser()) {
    //   return
    // }

    const res = await fetch(`${store.server.url_backend}/operation/presensi_absensi/${form.attending?.toLowerCase() === 'not attend' ? 'checkin' : 'checkout'}`, {
      method: 'POST',
        headers: {
          Authorization: `${store.user.token_type} ${store.user.token}`
        },
        body: formData
    })  
    if (!res.ok) {
      if ([400, 422].includes(res.status)) {
        const responseJson = await res.json()
        throw (responseJson.message || "Failed when trying to post data")
      } else {
        throw ("Failed when trying to post data")
      }
    }
    const resultJson = await res?.json()
    swal.fire({
      icon: 'success',
      text: resultJson.message,
      iconColor: '#1469AE',
      confirmButtonColor: '#1469AE',
    }).then(async (res) => {
      if (res.isConfirmed){
        await getLocation()
        await checkLastStatus()
        await getDetailAbsen(form.year,form.month)
        isImage.value=false
        faceResultText.value = ''
        faceMatch.value = null
        recognizedEmployeeId.value = ''
        recognizedName.value = ''
        
      }
    })
  }catch(err){
    swal.fire({
      icon: 'error',
      text: err
    })
  }
}

async function setPosition(position) {
  coordsLocation.value = position?.coords
  try{
    const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${position.coords.latitude}&lon=${position.coords.longitude}`)  
    const resultJson = await res?.json()
    form.address = resultJson?.display_name
    await getDistance(position.coords.latitude, position.coords.longitude)
  }catch(err){
    swal.fire({
      icon: 'error',
      text: err
    })
  }
  
}

async function getDistance(lat,long){
  try{
    const res = await fetch(`${store.server.url_backend}/operation/presensi_absensi/distance_check?lat=${lat}&long=${long}`, {
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      },
    })  
    if (!res.ok) {
      if ([400, 422].includes(res.status)) {
        const responseJson = await res.json()
        throw (responseJson.message || "Failed when trying to post data")
      } else {
        throw ("Failed when trying to post data")
      }
    }
    const resultJson = await res?.json()
    const data = resultJson.data
    form.distance_check = data?.on_scope
  }catch(err){
    swal.fire({
      icon: 'error',
      text: err
    })
  }
}

async function mountCam() {
  try {
    const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
    videoElement.value.srcObject = stream;
    startQualityMonitor()
  } catch (error) {
    alert("Oh maaf, sepertinyßa kami tidak mendapatkan akses kamera anda");
  }
}

//validator akun dan image
function validateFaceWithLoginUser() {
  const loginEmployeeId = String(store.user.data?.username || '')
  const faceEmployeeId = String(recognizedEmployeeId.value || '')

  // wajah belum dikenali
  if (!faceMatch.value) {
    swal.fire({
      icon: 'error',
      title: 'Verifikasi Wajah Gagal',
      text: 'Wajah belum dikenali. Silakan ambil ulang foto.'
    })
    return false
  }

  // hasil facereg kosong
  if (!faceEmployeeId) {
    swal.fire({
      icon: 'error',
      title: 'Verifikasi Wajah Gagal',
      text: 'Data wajah tidak valid.'
    })
    return false
  }

  // TIDAK SAMA
  if (loginEmployeeId !== faceEmployeeId) {
    swal.fire({
      icon: 'error',
      title: 'Akun Tidak Sesuai',
      html: `
        Akun login <b>${store.user.data?.username}</b><br>
        tidak sesuai dengan wajah yang dikenali <b>${recognizedName.value}</b>.
      `,
    })
    return false
  }

  return true
}


// ---------- VOICE HELPER ----------
function speak(text) {
  try {
    if (typeof window === 'undefined') return
    if (!voiceEnabled.value) {
      const synth0 = window.speechSynthesis
      synth0 && synth0.cancel()
      return
    }

    const synth = window.speechSynthesis
    if (!synth) return

    const now = Date.now()

    if (synth.speaking && now - lastSpeakStartedAt < 1500) return

    if (synth.speaking || synth.pending) {
      synth.cancel()
    }

    const u = new SpeechSynthesisUtterance(text)
    u.lang = 'id-ID'
    u.rate = 1.05
    u.pitch = 1.05

    u.onstart = () => {
      lastSpeakStartedAt = Date.now()
    }

    synth.speak(u)
  } catch (e) {
    console.log('Error speak():', e)
  }
}

//quality monitor

function computeFrameMetricsFromVideo() {
  if (!videoElement.value) return null
  if (!videoElement.value.videoWidth || !videoElement.value.videoHeight) return null

  const w = videoElement.value.videoWidth
  const h = videoElement.value.videoHeight
  if (!w || !h) return null

  const canvas = document.createElement('canvas')
  canvas.width = w
  canvas.height = h
  const ctx = canvas.getContext('2d')
  ctx.drawImage(videoElement.value, 0, 0, w, h)

  const imgData = ctx.getImageData(0, 0, w, h)
  const d = imgData.data

  let sumB = 0
  let sumLap = 0
  let sumLap2 = 0
  let cnt = 0
  const step = 4

  const lumAt = (x, y) => {
    const i = (y * w + x) * 4
    const r = d[i]
    const g = d[i + 1]
    const b = d[i + 2]
    return 0.299 * r + 0.587 * g + 0.114 * b
  }

  for (let y = 2; y < h - 2; y += step) {
    for (let x = 2; x < w - 2; x += step) {
      const c = lumAt(x, y)
      const l = lumAt(x - 1, y)
      const r = lumAt(x + 1, y)
      const u = lumAt(x, y - 1)
      const dn = lumAt(x, y + 1)
      const lap = 4 * c - l - r - u - dn

      sumB += c
      sumLap += lap
      sumLap2 += lap * lap
      cnt++
    }
  }

  const bright = sumB / Math.max(1, cnt)
  const meanLap = sumLap / Math.max(1, cnt)
  const blurVar = sumLap2 / Math.max(1, cnt) - meanLap * meanLap

  return { bright, blurVar }
}

function updateQualityFromMetrics(metrics) {
  if (!metrics) {
    qualityScore.value = null
    qualityLabel.value = ''
    qualityMessage.value = ''
    return
  }

  const { bright, blurVar } = metrics

  let brightScore
  if (bright < 40) brightScore = 30
  else if (bright > 220) brightScore = 30
  else if (bright < 80) brightScore = 60
  else if (bright > 190) brightScore = 60
  else brightScore = 90

  let blurScore
  if (blurVar < 10) blurScore = 20
  else if (blurVar < 30) blurScore = 50
  else if (blurVar < 80) blurScore = 75
  else blurScore = 95

  const score = Math.round(brightScore * 0.4 + blurScore * 0.6)
  qualityScore.value = score

  let label
  let msg
  if (score >= 80) {
    label = 'Bagus'
    msg = 'Kualitas wajah bagus, siap untuk absen.'
  } else if (score >= 55) {
    label = 'Cukup'
    if (bright < 60) msg = 'Sedikit gelap, coba hadap ke arah lampu.'
    else if (bright > 200) msg = 'Agak terlalu terang, kurangi backlight.'
    else if (blurVar < 40) msg = 'Sedikit blur, coba stabilkan kamera atau wajah.'
    else msg = 'Sudah cukup oke, bisa sedikit diperbaiki posisi atau pencahayaannya.'
  } else {
    label = 'Buruk'
    if (bright < 60) msg = 'Terlalu gelap, coba mendekat ke sumber cahaya.'
    else if (bright > 200)
      msg = 'Terlalu terang, hindari lampu atau jendela tepat di belakang.'
    else if (blurVar < 20)
      msg = 'Terlalu blur, pastikan kamera fokus dan tidak goyang.'
    else msg = 'Kualitas kurang, coba atur ulang posisi kamera dan wajah.'
  }

  qualityLabel.value = label
  qualityMessage.value = msg

  const now = Date.now()
  const key = label

  if (voiceEnabled.value && qualityMessage.value) {
    const bigChange =
      lastSpokenScore === null ? true : Math.abs(score - lastSpokenScore) >= MIN_DELTA_SCORE
    const timeOk = now - lastSpeakAt > MIN_INTERVAL_MS
    const labelChanged = key !== lastSpokenKey

    if ((labelChanged || bigChange) && timeOk) {
      speak(`Kualitas wajah ${label.toLowerCase()}. ${msg}`)
      lastSpokenKey = key
      lastSpokenScore = score
      lastSpeakAt = now
    }
  }
}

function startQualityMonitor() {
  stopQualityMonitor()
  qualityTimer = setInterval(() => {
    if (!videoElement.value || isImage.value) return
    const metrics = computeFrameMetricsFromVideo()
    updateQualityFromMetrics(metrics)
  }, 1200)
}

function stopQualityMonitor() {
  if (qualityTimer) {
    clearInterval(qualityTimer)
    qualityTimer = null
  }
}


// ---------- FACE RECOGNITION CALL ----------
async function recognizeFacePublic(imageBlob) {
  try {
    faceResultText.value = 'Mengirim foto ke Face Recognition...'
    faceMatch.value = null
    recognizedEmployeeId.value = ''
    recognizedName.value = ''

    const fd = new FormData()
    fd.append('image', imageBlob, 'captured_image.jpg')

    const res = await fetch(`${FACE_API_BASE}/recognize`, {
      method: 'POST',
      body: fd,
    })

    if (!res.ok) {
      const errJson = await res.json().catch(() => null)
      console.error('Face API error:', errJson)
      faceResultText.value = 'Gagal menghubungi Face Recognition.'
      faceMatch.value = null

      // publicAbsenProblemCount.value += 1
      // maybeShowLoginSuggestion()
      return
    }

    const data = await res.json()
    if (data.status === 'success' && data.data) {
      const info = data.data
      if (info.match === true) {
        faceMatch.value = true
        recognizedEmployeeId.value = String(info.employee_id || '')
        recognizedName.value = String(info.name || '')
        faceResultText.value =
          `Wajah dikenali sebagai: ${recognizedName.value}` +
          (recognizedEmployeeId.value ? ` (ID: ${recognizedEmployeeId.value}).` : '.')

        // AUTO: tentukan mode checkin/checkout dari status backend
        //await refreshAutoAbsenMode(recognizedEmployeeId.value || recognizedName.value)
      } else {
        faceMatch.value = false
        const reason = String(info.reason || '').toLowerCase()
        if (reason === 'face_not_detected') {
          faceResultText.value =
            'Wajah tidak terdeteksi. Pastikan wajah menghadap kamera dan cukup terang.'
        } else {
          faceResultText.value = 'Wajah tidak dikenali di sistem.'
        }

        // publicAbsenProblemCount.value += 1
        // maybeShowLoginSuggestion()
      }
    } else {
      faceResultText.value = 'Response Face API tidak sesuai format.'
      faceMatch.value = null

      // publicAbsenProblemCount.value += 1
      // maybeShowLoginSuggestion()
    }
  } catch (err) {
    console.error('Error call face API:', err)
    faceResultText.value = 'Gagal menghubungi Face Recognition.'
    faceMatch.value = null

    // publicAbsenProblemCount.value += 1
    // maybeShowLoginSuggestion()
  }
}