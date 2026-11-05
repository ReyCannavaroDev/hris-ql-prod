import { useRouter, useRoute } from 'vue-router'
import {
  ref,
  readonly,
  reactive,
  inject,
  onMounted,
  onBeforeMount,
  onBeforeUnmount,
  watch,
  computed,
} from 'vue'

const router = useRouter()
const route = useRoute()
const store = inject('store')
const swal = inject('swal')

const isRequesting = ref(false)
const showPass = ref(true)

// ---------- LOGIN STATE ----------
const values = reactive({
  email: null,
  password: null,
})
const valuesRo = readonly(values)

const getBasic = ref({})

// ---------- TAB & ABSEN STATE ----------
const activeTab = ref('login') // 'login' | 'absen'

// tetap ada, tapi tidak dipilih manual lagi (mode akan otomatis)
const absenMode = ref('checkin') // 'checkin' | 'checkout'

// label untuk ditampilkan di UI (Mode Absen: ...)
const absenModeLabel = computed(() =>
  absenMode.value === 'checkin' ? 'Check-in' : 'Check-out',
)

// hint UI untuk mode otomatis
const autoModeHint = ref('Ambil foto untuk menentukan mode absen otomatis.')
const lastAttendanceStatus = ref('')

const videoElement = ref(null)
const isImage = ref(false)
const capturedImageBlob = ref(null)

const formAbsen = reactive({
  time: '',
  lat: null,
  long: null,
  address: '',
})

const isSubmittingAbsen = ref(false)
let clockInterval = null

// ---------- LIMIT 3x GAGAL ABSEN PUBLIC ----------
const publicAbsenProblemCount = ref(0)
const MAX_PUBLIC_ABSEN_PROBLEM = 3

function resetPublicAbsenProblem() {
  publicAbsenProblemCount.value = 0
}

function maybeShowLoginSuggestion() {
  if (publicAbsenProblemCount.value < MAX_PUBLIC_ABSEN_PROBLEM) return

  swal
    .fire({
      icon: 'warning',
      text:
        'Sistem sudah 3x kesulitan memproses absen tanpa login. ' +
        'Silakan login dan lakukan absen dari dalam aplikasi agar lebih akurat.',
      confirmButtonColor: '#1469AE',
    })
    .then(() => {
      activeTab.value = 'login'
    })
}

// ---------- FACE QUALITY COACH ----------
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

//FACE RECOGNITION
const FACE_API_BASE = 'https://hris.qqltech.com:7089'

//STATUS ABSENSI (AUTO MODE)
const STATUS_API_BASE = store.server.url_backend

async function refreshAutoAbsenMode(employeeId) {
  if (!employeeId) {
    autoModeHint.value = 'Employee ID belum ada (wajah belum dikenali).'
    return
  }

  autoModeHint.value = 'Mengecek status absensi...'

  try {
    const url =
      `${STATUS_API_BASE}/public/presensi_absensi/status?employee_id=` +
      encodeURIComponent(employeeId)

    const res = await fetch(url, { method: 'GET' })
    const json = await res.json().catch(() => null)

    if (!res.ok) {
      throw new Error(json?.message || 'Gagal membaca status absensi.')
    }

    const st = String(json?.data?.status || '').toUpperCase()
    lastAttendanceStatus.value = st

    if (st === 'WORKING') {
      absenMode.value = 'checkout'
      autoModeHint.value = `Status: ${st}. Mode otomatis: CHECK-OUT.`
    } else {
      absenMode.value = 'checkin'
      autoModeHint.value = `Status: ${st || '-'}. Mode otomatis: CHECK-IN.`
    }
  } catch (e) {
    absenMode.value = 'checkin'
    autoModeHint.value = `Gagal cek status, default CHECK-IN. (${e?.message || e})`
  }
}

const faceResultText = ref('')
const faceMatch = ref(null)
const recognizedEmployeeId = ref('')
const recognizedName = ref('')

// BASIC COMPANY / BRAND
onBeforeMount(async () => {
  store.isUsingLayout = false
  isRequesting.value = true
  try {
    const result = await fetch(store.server.url_backend + `/public/m_general/get_basic`, {
      method: 'GET',
      headers: {
        'Content-Type': 'Application/json',
      },
    })
    if (!result.ok) {
      throw new Error(await result.text())
    }
    const data = await result.json()
    getBasic.value = data?.data
    document.title = getBasic.value?.brand_title
  } catch (err) {
    console.log(err)
  } finally {
    isRequesting.value = false
  }
})

// LOGIN
async function onLogin(e = null) {
  e?.preventDefault()
  try {
    isRequesting.value = true
    const result = await fetch(store.server.url_backend + '/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'Application/json',
      },
      body: JSON.stringify(valuesRo),
    })

    if (!result.ok) {
      throw new Error(await result.text())
    }

    const userValue = await result.json()
    window.localStorage.user = JSON.stringify(userValue)
    store.commit('set', ['user', userValue])
  } catch (err) {
    isRequesting.value = false
    swal.fire({
      icon: 'warning',
      text: 'Email atau Password Salah!',
    })
    return
  }
  isRequesting.value = false
  await router.push('/dashboard')
}

//AUTO LOGIN JIKA SUDAH LOGIN 
onMounted(async () => {
  // timer untuk jam di tab absen
  updateTime()
  clockInterval = setInterval(updateTime, 1000)

  if (localStorage.user) {
    const tempJson = JSON.parse(localStorage.user)
    values.email = tempJson.data?.username

    try {
      isRequesting.value = true
      const result = await fetch(
        store.server.url_backend + `${store.server.url_user_check}`,
        {
          method: 'GET',
          headers: {
            'Content-Type': 'Application/json',
            Authorization: `${tempJson.token_type} ${tempJson.token}`,
          },
        },
      )
      if (!result.ok) {
        throw new Error(await result.text())
      }
      const refreshedUser = await result.json()
      Object.assign(tempJson, { data: refreshedUser })
      store.commit('set', ['user', tempJson])
      store.commit('set', ['userWasRefreshed', true])
      window.localStorage.user = JSON.stringify(tempJson)
      router.replace('/dashboard')
    } catch (err) {
      // kalau gagal refresh, ya tetap di halaman login
    }
    isRequesting.value = false
  }
})

onBeforeUnmount(() => {
  stopCameraStream()
  stopQualityMonitor()
  if (clockInterval) clearInterval(clockInterval)
  try {
    if (typeof window !== 'undefined' && window.speechSynthesis) {
      window.speechSynthesis.cancel()
    }
  } catch (e) {
    console.log(e)
  }
})

// ---------- TAB SWITCH ----------
function switchTab(tab) {
  activeTab.value = tab
  if (tab === 'absen') {
    startAbsenTab()
  } else {
    stopAbsenTab()
  }
}

async function startAbsenTab() {
  isImage.value = false
  capturedImageBlob.value = null
  faceResultText.value = ''
  faceMatch.value = null
  recognizedEmployeeId.value = ''
  recognizedName.value = ''

  // reset mode auto
  absenMode.value = 'checkin'
  autoModeHint.value = 'Ambil foto untuk menentukan mode absen otomatis.'
  lastAttendanceStatus.value = ''

  resetPublicAbsenProblem()
  await mountCam()
  getLocation()
}

function stopAbsenTab() {
  stopCameraStream()
  stopQualityMonitor()
}

// ---------- WAKTU ----------
function updateTime() {
  const now = new Date()
  const h = String(now.getHours()).padStart(2, '0')
  const m = String(now.getMinutes()).padStart(2, '0')
  const s = String(now.getSeconds()).padStart(2, '0')
  formAbsen.time = `${h}:${m}:${s}`
}

// ---------- LOKASI ----------
function getLocation() {
  if (!navigator.geolocation) {
    swal.fire({
      icon: 'error',
      text: 'Browser tidak mendukung geolocation.',
    })
    return
  }

  navigator.geolocation.getCurrentPosition(setPosition, showLocationError)
}

async function setPosition(position) {
  formAbsen.lat = position.coords.latitude
  formAbsen.long = position.coords.longitude

  try {
    const res = await fetch(
      `https://nominatim.openstreetmap.org/reverse?format=json&lat=${formAbsen.lat}&lon=${formAbsen.long}`,
    )
    const resultJson = await res.json()
    formAbsen.address = resultJson?.display_name || ''
  } catch (err) {
    console.log(err)
  }
}

function showLocationError(error) {
  let msg = 'Gagal mendapatkan lokasi.'
  switch (error.code) {
    case error.PERMISSION_DENIED:
      msg = 'Izinkan akses lokasi untuk menggunakan fitur absen.'
      break
    case error.POSITION_UNAVAILABLE:
      msg = 'Informasi lokasi tidak tersedia.'
      break
    case error.TIMEOUT:
      msg = 'Request lokasi timeout.'
      break
  }
  swal.fire({ icon: 'error', text: msg })
}

// ---------- KAMERA ----------
async function mountCam() {
  try {
    const stream = await navigator.mediaDevices.getUserMedia({
      video: true,
      audio: false,
    })
    if (videoElement.value) {
      videoElement.value.srcObject = stream
    }
    startQualityMonitor()
  } catch (error) {
    alert('Oh maaf, sepertinya kami tidak mendapatkan akses kamera anda')
  }
}

function stopCameraStream() {
  if (videoElement.value && videoElement.value.srcObject) {
    const stream = videoElement.value.srcObject
    stream.getTracks()?.forEach((t) => t.stop())
    videoElement.value.srcObject = null
  }
}

// ---------- CAPTURE / RECAPTURE ----------
async function captureAbsen() {
  try {
    if (!videoElement.value) return

    const canvas = document.createElement('canvas')
    canvas.width = videoElement.value.videoWidth
    canvas.height = videoElement.value.videoHeight

    const ctx = canvas.getContext('2d')
    ctx.translate(canvas.width, 0)
    ctx.scale(-1, 1)
    ctx.drawImage(videoElement.value, 0, 0, canvas.width, canvas.height)

    const dataUrl = canvas.toDataURL('image/jpeg')
    const imgEl = document.getElementById('imgAbsen')
    if (imgEl) imgEl.setAttribute('src', dataUrl)

    const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg'))
    capturedImageBlob.value = blob
    isImage.value = true

    // panggil Face Recognition (info + untuk menentukan mode auto)
    // (tidak wajib ditunggu, tapi submit akan memastikan mode ter-set)
    recognizeFacePublic(blob)

    // stop kamera & quality monitor setelah foto diambil
    stopCameraStream()
    stopQualityMonitor()
    try {
      if (typeof window !== 'undefined' && window.speechSynthesis) {
        window.speechSynthesis.cancel()
      }
    } catch (e) {
      console.log(e)
    }
  } catch (err) {
    console.log(err)
    alert('Gagal mengambil gambar dari kamera.')
  }
}

async function recaptureAbsen() {
  isImage.value = false
  capturedImageBlob.value = null
  faceResultText.value = ''
  faceMatch.value = null
  recognizedEmployeeId.value = ''
  recognizedName.value = ''

  // reset mode auto
  absenMode.value = 'checkin'
  autoModeHint.value = 'Ambil foto untuk menentukan mode absen otomatis.'
  lastAttendanceStatus.value = ''

  await mountCam()
}

// ---------- SUBMIT ABSEN (PUBLIC CHECKIN/CHECKOUT) ----------
async function submitAbsen() {
  try {
    if (!capturedImageBlob.value) {
      swal.fire({
        icon: 'warning',
        text: 'Ambil foto dulu sebelum absen.',
      })
      return
    }

    isSubmittingAbsen.value = true

    // Pastikan mode sudah AUTO sesuai status backend (kalau sudah ada id/nama)
    const empKey = recognizedEmployeeId.value || recognizedName.value
    if (empKey) {
      await refreshAutoAbsenMode(empKey)
    }

    const fd = new FormData()
    fd.append('foto', capturedImageBlob.value, 'captured_image.jpg')
    if (formAbsen.lat != null) fd.append('lat', String(formAbsen.lat))
    if (formAbsen.long != null) fd.append('long', String(formAbsen.long))
    if (formAbsen.address) fd.append('address', formAbsen.address)

    const endpoint = absenMode.value === 'checkin' ? 'checkin' : 'checkout'

    const res = await fetch(
      `${store.server.url_backend}/public/presensi_absensi/${endpoint}`,
      {
        method: 'POST',
        body: fd,
      },
    )

    const responseJson = await res.json().catch(() => null)

    if (!res.ok) {
      // gagal / ditolak → hitung percobaan
      publicAbsenProblemCount.value += 1

      const backendMsg =
        responseJson?.message ||
        `Absen ${absenMode.value === 'checkin' ? 'check-in' : 'check-out'} gagal, coba kembali nanti.`

      // kalau sudah 3x → paksa arahkan ke login
      if (publicAbsenProblemCount.value >= MAX_PUBLIC_ABSEN_PROBLEM) {
        maybeShowLoginSuggestion()
      } else {
        swal.fire({
          icon: 'error',
          text: backendMsg,
        })
      }
      return
    }

    // sukses → reset counter gagal
    resetPublicAbsenProblem()

    swal
      .fire({
        icon: 'success',
        text:
          responseJson?.message ||
          `Berhasil ${absenMode.value === 'checkin' ? 'check-in' : 'check-out'}.`,
        iconColor: '#1469AE',
        confirmButtonColor: '#1469AE',
      })
      .then(() => {
        recaptureAbsen()
      })
  } catch (err) {
    publicAbsenProblemCount.value += 1
    swal.fire({
      icon: 'error',
      text: err?.message || String(err),
    })
    maybeShowLoginSuggestion()
  } finally {
    isSubmittingAbsen.value = false
  }
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

// ---------- QUALITY METRICS ----------
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

      publicAbsenProblemCount.value += 1
      maybeShowLoginSuggestion()
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
        await refreshAutoAbsenMode(recognizedEmployeeId.value || recognizedName.value)
      } else {
        faceMatch.value = false
        const reason = String(info.reason || '').toLowerCase()
        if (reason === 'face_not_detected') {
          faceResultText.value =
            'Wajah tidak terdeteksi. Pastikan wajah menghadap kamera dan cukup terang.'
        } else {
          faceResultText.value = 'Wajah tidak dikenali di sistem.'
        }

        publicAbsenProblemCount.value += 1
        maybeShowLoginSuggestion()
      }
    } else {
      faceResultText.value = 'Response Face API tidak sesuai format.'
      faceMatch.value = null

      publicAbsenProblemCount.value += 1
      maybeShowLoginSuggestion()
    }
  } catch (err) {
    console.error('Error call face API:', err)
    faceResultText.value = 'Gagal menghubungi Face Recognition.'
    faceMatch.value = null

    publicAbsenProblemCount.value += 1
    maybeShowLoginSuggestion()
  }
}

// ---------- LOGO FALLBACK ----------
const defaultImage = () => {
  return store.server.url_backend + '/logo.png'
}
