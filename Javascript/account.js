import { useRouter, useRoute } from 'vue-router'
import { ref, reactive, inject, onBeforeMount, watchEffect } from 'vue'

const router = useRouter()
const route = useRoute()
const store = inject('store')
const swal = inject('swal')

const isBadForm = ref(false)
const isRequesting = ref(false)
const modulPath = route.params.modul
const formErrors = ref({})
const endpointApi = '/default_users'
const userId = ref(null)
const actionText = ref(route.params.id === 'create' ? 'Tambah' : route.query.action)
let initialValues = {}
const values = reactive({
  is_active: true
})

onBeforeMount(async () => {
  document.title = 'Master Pengguna'
  try {
    userId.value = store.user.data?.id
    if (!userId.value) {
      throw 'User ID tidak ditemukan'
    }
    values.direktorat = store.user.data?.direktorat
    const dataURL = `${store.server.url_backend}/operation${endpointApi}/${userId.value}?withKary=true`
    isRequesting.value = true
    const params = new URLSearchParams({ join: true, transform: false })
    const res = await fetch(dataURL + '?' + params, {
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      },
    })

    if (!res.ok) throw new Error("Failed when trying to read data")

    const resultJson = await res.json()
    initialValues = resultJson.data
    initialValues.name = initialValues.nama_lengkap
    for (const key in initialValues) {
      values[key] = initialValues[key]
    }

  } catch (err) {
    isBadForm.value = true

    swal.fire({
      icon: 'error',
      text: err,
      allowOutsideClick: false,
      confirmButtonText: 'Kembali',
    }).then(() => {
      router.back()
    })
  } finally {
    isRequesting.value = false
  }
})

function onBack() {
  let isChanged = false

  for (const key in initialValues) {
    if (values[key] !== initialValues[key]) {
      isChanged = true
      break
    }
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

function redirectKary() {
  const tsId = `ts=` + Date.now()
  router.replace('/m_karyawan/' + values.m_kary_id + '?profile=true&' + tsId)
}

async function onSave() {
  let dataURL = ''

  try {
    const isCreating = ['Create', 'Copy', 'Tambah'].includes(actionText.value)

    if (!isCreating && !userId.value) {
      throw 'User ID tidak ditemukan, tidak bisa update'
    }

    if (isCreating) {
      dataURL = `${store.server.url_backend}/operation${endpointApi}`
    } else {
      dataURL = `${store.server.url_backend}/operation${endpointApi}/reset_password?id_user=${userId.value}`
    }
    isRequesting.value = true
    delete values.atasan
    values.is_active = values.is_active ? 1 : 0

    const res = await fetch(dataURL, {
      method: 'POST',
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      },
      body: JSON.stringify(values)
    })

    if (!res.ok) {
      if ([400, 422].includes(res.status)) {
        const responseJson = await res.json()
        formErrors.value = responseJson.errors || {}

        throw (responseJson.errors?.length
          ? responseJson.errors[0]
          : responseJson.message || "Failed when trying to post data")
      } else {
        throw ("Failed when trying to post data")
      }
    }
    await swal.fire({
      icon: 'success',
      title: 'Berhasil',
      text: isCreating
        ? 'Data berhasil ditambahkan'
        : 'Password berhasil diubah',
      timer: 1500,
      showConfirmButton: false
    })

    if (!isCreating) {
      window.location.href = `${store.server.url_backend}/logout`
    } else {
      router.replace('/' + modulPath + '?reload=' + Date.now())
    }

  } catch (err) {
    isBadForm.value = true

    swal.fire({
      icon: 'error',
      text: err
    })
  } finally {
    isRequesting.value = false
  }
}

// global loading
watchEffect(() => {
  store.commit('set', ['isRequesting', isRequesting.value])
})