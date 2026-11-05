import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, readonly, reactive, inject, onMounted, onBeforeMount, watchEffect, onActivated } from 'vue'
// testing
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
const formErrors = ref({})
const tsId = `ts=`+(Date.parse(new Date()))
const dateArr = ref([1,2,3,4,5,6,7,8])
const dateActive = ref(1)
const showItemDate = ref(false)
const activeDate = (index) => {
  dateActive.value = index
}
const activeTabIndex = ref(0)

// ------------------------------ PERSIAPAN
const endpointApi = '/t_logbook'
onBeforeMount(()=>{
  document.title = 'LOG BOOK'
})

//  @if( $id )------------------- VALUES FORM ! PENTING JANGAN DIHAPUS


//  @else----------------------- LANDING

let initialValues = {}
let initialValues2 = reactive([]); 
const changedValues = []


const values = reactive({
  m_dir_id: store.user.data?.m_dir_id,
  tanggal: "",
  m_kary_id: store.user.data.m_kary_id,
  id: null ,
});

console.log('tes',store.user.data.m_kary_id)

function formatTgl(dateStr) {
  const [day, month, year] = dateStr.split('/'); 
  return `${year}-${month}-${day}`; 
}


onBeforeMount(async () => {
    await new Promise(resolve => setTimeout(resolve, 500));
      await dataBook({ id: values.m_kary_id });
});
async function dataBook(m_kary_id) {
  console.log('Tes',m_kary_id)
  try {
    const formattedDate = formatTgl(values.tanggal);
    const dataURL = `${store.server.url_backend}/operation${endpointApi}/group_by_proyek?date=${formattedDate}`;
    
    const res = await fetch(dataURL, {
      headers: {
        'Content-Type': 'application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`,
      },
    });

    if (!res.ok) throw new Error("Failed to retrieve project data");

    const resultJson = await res.json();
    initialValues2 = resultJson.data ;
    console.log('DATABOOK',initialValues2)
    values.id = initialValues2.id ??  null;
  

    if (initialValues2[0]?.group_data) {
        initialValues2[0].group_data.forEach(item => {

          if (Array.isArray(item.detail)) {
            item.detail.forEach(detailItem => {
              IDtsk = detailItem.t_logbook_id ?? null;
            });
          }
        });

        const groupProyek = initialValues2[0].group_data.map(item => ({
          m_proyek_id: item.proyek_id,
          proyek_nama: item.proyek_nama,
        }));

        detail.items.push(...groupProyek);
      }
    } catch (err) {
    isBadForm.value = true;
    swal.fire({
      icon: 'error',
      text: err.message || "An error occurred.",
      allowOutsideClick: true,
      confirmButtonText: 'Back',
    }).then(() => {
      router.back();
    });
    } finally {
    isRequesting.value = false;
  }
}

//  detail
let IDtsk = null;



// DETAIL SETUP
const proyekPilih = ref(null); 
let proyekNama = ref(null); 

const detail = reactive({ items: [] });
const detail2 = reactive({ items: [] });
let taskDetail = reactive({ items: {} });
let allTask = reactive({});
const activeProyekIndex = ref(null);



const onDetailAdd = (el) => {
  if (Array.isArray(el)) {
    el.forEach(row => {
      const proyek = { m_proyek_id: row.id || null };

      const isProyekExist = detail.items.find(item => item.m_proyek_id === proyek.m_proyek_id);
      
      if (isProyekExist) {
        swal.fire({
          icon: 'error',
          title: 'Oops..!',
          text: 'Proyek sudah ada!',
        });
      } else {
        detail.items.push(proyek);
      }
    });
  } 
};





const addDetail2 = async () => {
  if (!proyekPilih.value) {
    await swal.fire({
      icon: 'warning',
      title: 'Oops..!',
      text: 'Tambah Tugas Setelah Pilih Proyek!'
    });
    return;
  }
  const task = {
    m_proyek_id: proyekPilih.value, 
    t_logbook_id: IDtsk ?? null,
    status: 'TODO',
  };
  detail2.items.push(task);
  updateTugas(); 
};

const updateTugas = () => {
  const allCombinedTasks = [...detail2.items, ...Object.values(taskDetail.items).flat()];
  allTask[proyekPilih.value] = allCombinedTasks.reduce((acc, task) => {
    const existingIndex = acc.findIndex(existingTask => 
      existingTask.m_proyek_id === task.m_proyek_id && existingTask.task === task.task 
    );
    if (existingIndex !== -1) {
      acc[existingIndex] = task;
    } else {
      acc.push(task);
    }
    return acc;
  }, []);
};

const simpanTaskProyek = (proyekId) => {
  if (proyekId) {
    taskDetail.items[proyekId] = [...detail2.items];
    updateTugas();
  }
};

const kembalikanTask = (proyekId) => {
  if (proyekId && taskDetail.items[proyekId]) {
    detail2.items = [...taskDetail.items[proyekId]];
  } else {
    detail2.items = [];
  }
  updateTugas();
};

const removeDetail2 = async (index) => {
  try {
    const itemToDelete = detail2.items[index];
    if (!itemToDelete || !itemToDelete.id) {
      console.error("Invalid item or missing ID.");
      return;
    }

    const confirmation = await swal.fire({
  icon: 'warning',
  title: 'Apakah Anda Yakin?',
  text: 'Tindakan ini akan menghapus item yang dipilih secara permanen.',
  showCancelButton: true,
  confirmButtonText: 'Ya, hapus!',
  cancelButtonText: 'Batal',
  });

    if (!confirmation.isConfirmed) {
      return;
    }

    const dataURL = `${store.server.url_backend}/operation/t_logbook_d/${itemToDelete.id}`;
    const response = await fetch(dataURL, {
      method: 'DELETE',
      headers: {
        'Content-Type': 'Application/json',
        'Authorization': `${store.user.token_type} ${store.user.token}`,
      },
    });

    if (!response.ok) {
      throw new Error(`Failed to delete item on server: ${response.statusText}`);
    }
    detail2.items.splice(index, 1);
    updateTugas();
    await swal.fire({
      icon: 'success',
      title: 'Hapus Berhasil!',
      text: 'Item berhasil dihapus.',
    });

  } catch (error) {
    console.error("Error deleting item:", error);
    await swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Failed to delete the item. Please try again later.',
    });
  }
};


const removeDetail = async (index) => {
  const proyekId = detail.items[index].m_proyek_id;  
  const tLogbookId = IDtsk;
  const payload = proyekId && tLogbookId ? {
    m_proyek_id: proyekId,
    t_logbook_id: tLogbookId,
  } : null;

  try {
    detail.items.splice(index, 1);
    await swal.fire({
      icon: 'success',
      text: `Item berhasil dihapus!`,
    });
    if (payload) {
      const url = `${store.server.url_backend}/operation${endpointApi}/delete_group`;

      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${store.user.token}`,
        },
        body: JSON.stringify(payload),
      });
      
      const responseData = await response.json();

      if (!response.ok || responseData.data[0] !== "Success") {
        throw new Error(`Failed to delete data from the server. Status: ${response.status}, Message: ${responseData.message || 'Unknown error'}`);
      }
      await swal.fire({
        icon: 'success',
        text: `Delete PROYEK Melalui Database berhasil!`,
      });
    }

    // Reset related state
    proyekPilih.value = null;
    activeProyekIndex.value = null;
    proyekNama.value = '';  
    detail2.items = [];

  } catch (error) {
    // Show error only if there's an API failure or other issues
    await swal.fire({
      icon: 'error',
      title: 'Error',
      text: error.message || 'Terjadi kesalahan saat menghapus data.',
    });
  }
};


// click proyek / index
const clickProyek = async (proyekId, index) => {
  try {
    if (!proyekId) {
      await swal.fire({
        icon: 'warning',
        title: 'Oops..!',
        text: 'Anda belum memilih proyek. Silakan pilih proyek untuk melanjutkan.',
      });
      return;
    }

    if (proyekPilih.value) {
      simpanTaskProyek(proyekPilih.value);
    }


    proyekPilih.value = proyekId;
    activeProyekIndex.value = index;
    detail2.items = [];
    kembalikanTask(proyekId);


    if (Array.isArray(initialValues2) && initialValues2.length > 0) {
      initialValues2.forEach(group => {
        if (Array.isArray(group.group_data)) {
          const proyekItems = group.group_data.filter(item => item.proyek_id === proyekId);
          if (proyekItems.length > 0) {
            proyekNama.value = proyekItems[0].proyek_nama;
            proyekItems.forEach(item => {
              if (item.proyek_id === proyekId) {
                item.detail.forEach(detailItem => {
                  if (!detail2.items.some(existingItem => existingItem.id === detailItem.id)) {
                    detail2.items.push(detailItem);
                  }
                });
              }
            });
          }
        }
      });
    }

    const response = await fetch(`${store.server.url_backend}/operation/m_proyek/${proyekId}`, {
      method: 'GET',
      headers: {
        'Content-Type': 'Application/json',
        'Authorization': `${store.user.token_type} ${store.user.token}`,
      },
    });

    if (!response.ok) {
      throw new Error(`Error fetching project details: ${response.statusText}`);
    }

    const data = await response.json();
    const dt = data.data;
    proyekNama.value = dt.proyek_nama || proyekNama.value; 
    
    const initialTasks = data.tasks || [];
    initialTasks.forEach((task) => {
      if (!detail2.items.some(existingTask => existingTask.id === task.id)) {
        detail2.items.push(task);
      }
    });
  } catch (error) {
    console.error(error);
    await swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Failed to load project details.',
    });
  }
};


// watchEffect(() => {
//   if (detail.items.length > 0 && !proyekPilih.value) {
//     clickProyek(detail.items[0].m_proyek_id, 0);
//   }
// });

//  end detail


// SAVE DATA
async function onSave() {
  if (!detail.items || detail.items.length === 0) {
    await swal.fire({
      icon: 'warning',
      text: 'Harap Masukan Task Terlebih Dahulu!'
    });
    return;
  }

  if (!proyekPilih.value) {
    await swal.fire({
      icon: 'warning',
      title: 'Oops..! ',
      text: 'Untuk Simpan Data, Pilih Proyek dahulu!'
    });
    return;
  }

  const detail_lengkap = allTask[proyekPilih.value] || [];
  values.t_logbook_d = detail_lengkap;
  console.log(values.t_logbook_d);

  // Show confirmation dialog before saving
  const confirmSave = await swal.fire({
    icon: 'question',
    title: 'Konfirmasi Simpan Data',
    text: 'Apakah data yang ingin anda simpan sudah benar?',
    showCancelButton: true,  // Shows a cancel button
    confirmButtonText: 'Ya, Simpan Data',
    cancelButtonText: 'Tidak, Batalkan',
    reverseButtons: true,  // Reverses the order of the buttons (Yes comes first)
  });

  // If the user clicks 'Yes', proceed with saving the data
  if (!confirmSave.isConfirmed) {
    return;  // Exit the function if the user cancels
  }

  try {
    const dataURL = `${store.server.url_backend}/operation${endpointApi}/save`;
    const saveRes = await fetch(dataURL, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      },
      body: JSON.stringify(values)
    });

    if (!saveRes.ok) {
      if ([400, 422].includes(saveRes.status)) {
        const responseJson = await saveRes.json();
        formErrors.value = responseJson.errors || {};
        throw responseJson.errors?.[0] || responseJson.message || "Failed when trying to post data";
      } else {
        throw "Failed when trying to post data";
      }
    }

    await swal.fire({
      icon: 'success',
      text: 'Simpan Data berhasil!'
    });
     await new Promise(resolve => setTimeout(resolve, 500));
      await dataBook({ id: values.m_kary_id });

  } catch (err) {
    isBadForm.value = true;
    await swal.fire({
      icon: 'error',
      text: err
    });
  } finally {
    isRequesting.value = false;
  }
}
// END SAVE




const data = reactive({

});

const currentYear = ref(new Date().getFullYear());
const currentMonth = ref('');
const currentDate = ref(new Date().getDate());
const daysOfWeek = ['Mng', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
const calendarRows = ref([]);
const dateNow = ref();
var months = [
  "Januari", "Februari", "Maret", "April", "Mei", "Juni",
  "Juli", "Agustus", "September", "Oktober", "November", "Desember"
];

const initializeCalendar = async () => {
  const currentDateObj = new Date();
  currentMonth.value = currentDateObj.toLocaleString('default', { month: 'long' });
  data.month = `${currentYear.value}-${(currentDateObj.getMonth() + 1).toString().padStart(2, '0')}`;
  
  const daysInMonth = new Date(currentYear.value, currentDateObj.getMonth() + 1, 0).getDate();
  const firstDay = new Date(currentYear.value, currentDateObj.getMonth(), 1).getDay();
  
  let tempCalendarRows = [];
  let dayCounter = 1;

  for (let i = 0; i < 6; i++) {
    let row = [];
    for (let j = 0; j < 7; j++) {
      if (i === 0 && j < firstDay) {
        row.push({ day: '', date: '' });
      } else if (dayCounter > daysInMonth) {
        break;
      } else {
        const date = new Date(currentYear.value, currentDateObj.getMonth(), dayCounter);
        
        if (isToday(date)) {
          dateNow.value = formatDate(date);
          data.date = formatDate(date);
          await getlabelDate(data.date); 
        }

        row.push({ day: dayCounter, date: date, isToday: isToday(date) });
        dayCounter++;
      }
    }
    tempCalendarRows.push(row);
    if (dayCounter > daysInMonth) break;
  }

  calendarRows.value = tempCalendarRows;
  handleDateClick(new Date());  
};

const changeCalendar = async () => {
  let tempBulan = data.month?.split('-');
  const currentDateObj = new Date(tempBulan[0], tempBulan[1] - 1);
  const daysInMonth = new Date(currentYear.value, currentDateObj.getMonth() + 1, 0).getDate();
  const firstDay = new Date(currentYear.value, currentDateObj.getMonth(), 1).getDay();
  let tempCalendarRows = [];
  let dayCounter = 1;
  for (let i = 0; i < 6; i++) {
    let row = [];
    for (let j = 0; j < 7; j++) {
      if (i === 0 && j < firstDay) {
        row.push({ day: '', date: '' });
      } else if (dayCounter > daysInMonth) {
        break;
      } else {
        const date = new Date(currentYear.value, currentDateObj.getMonth(), dayCounter);
        if (dayCounter === 1) {
          data.date = formatDate(date);
          await getlabelDate(data.date);
        }

        row.push({
          day: dayCounter,
          date: date,
          isToday: isToday(date)
        });
        dayCounter++;
      }
    }
    tempCalendarRows.push(row);
    if (dayCounter > daysInMonth) break;
  }

  calendarRows.value = tempCalendarRows;
  handleDateClick(new Date(tempBulan[0], tempBulan[1] - 1, 1)); 
};

const isToday = (date) => {
  const today = new Date();
  return date.getFullYear() === today.getFullYear() && date.getMonth() === today.getMonth() && date.getDate() === today.getDate();
};

const formatDate = (date) => {
  const day = date.getDate().toString().padStart(2, '0'); 
  const month = (date.getMonth() + 1).toString().padStart(2, '0');
  const year = date.getFullYear();
  return `${year}-${month}-${day}`;
};

const selectedDate = ref(null);

const handleDateClick = async (date) => {
  if (date) {
    calendarRows.value.forEach(row => {
      row.forEach(cell => {
        cell.isToday = cell.date && cell.date.toDateString() === date.toDateString();
      });
    });
    data.date = formatDate(date);
    await getlabelDate(data.date); 
    landing.api.params.date = `${data.date}`;
    apiTable.value.reload();
  }
};

const localDate = (tanggal) => {
  const date = new Date(tanggal); 
  const day = date.getDate().toString().padStart(2, '0');
  const month = (date.getMonth() + 1).toString().padStart(2, '0');
  const year = date.getFullYear();  
  return `${day}/${month}/${year}`;
};

const getlabelDate = async (tanggal) => {
  const formattedDate = localDate(tanggal);
  const storedDate = localStorage.getItem('tanggal_lcl');
  if (storedDate && storedDate !== formattedDate) {
    localStorage.removeItem('tanggal_lcl');
  }
  localStorage.setItem('tanggal_lcl', formattedDate);
};

onMounted(() => {
  initializeCalendar();

  watchEffect(async () => {
    if (data.date) {
      values.tanggal = localDate(data.date);

      detail.items = [];
      detail2.items = [];
      taskDetail.items = {};
      allTask = reactive({});

      if (values.m_kary_id) {
        try {
          const loadingAlert = swal.fire({
            title: 'Memuat Data',
            text: 'Harap tunggu...',
            allowOutsideClick: false,
            didOpen: () => {
              swal.showLoading();
            },
          });

          await dataBook(values.m_kary_id);

          swal.close();

          await swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Data proyek berhasil dimuat.',
          });
        } catch (error) {
          swal.close();

          await swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: 'Terjadi kesalahan saat memuat data proyek. Coba lagi nanti.',
          });
        }
      }
    }
  });
});





let bukuId = null;  

const landing = reactive({
  api: {
    url: `${store.server.url_backend}/operation/t_logbook/group_by_proyek`,
    headers: {
      'Content-Type': 'application/json',
      authorization: `${store.user.token_type} ${store.user.token}`,
    },
    params: {
      join: true,
      simplest: true,
      searchfield: 'm_proyek.proyek_nama, this.task',
    },
  onsuccess(response) {
    const pisahData = response.data.flatMap(item => {

      return item.group_data.map(group => ({
        ...item, 
        proyek_nama: group.proyek_nama, 
      }));
    });

    response.data = pisahData; 
    response.page = response.current_page;
    response.hasNext = response.has_next;

    return response;
  },

  },

  columns: [
    {
      headerName: 'No',
      valueGetter: (params) => params.node.rowIndex + 1,
      width: 60,
      sortable: true,
      resizable: true,
      filter: true,
      cellClass: ['justify-center', 'bg-gray-50', 'border-r', '!border-gray-200']
    },
    {
      headerName: 'NAMA PROYEK',
      field: 'proyek_nama',
      filter: 'ColFilter',
      sortable: true,
      flex: 1,
      resizable: true,
      cellClass: ['border-r', '!border-gray-200', ' justify-center']
    },
  ]
});


onMounted(() => {
  if (apiTable.value) {
    setTimeout(() => {
      apiTable.value.reload();
    }, 500);
  }
});

onActivated(() => {
  if (apiTable.value && route.query.reload) {
    apiTable.value.reload();
  }
});
//  @endif -------------------------------------------------END
watchEffect(()=>store.commit('set', ['isRequesting', isRequesting.value]))