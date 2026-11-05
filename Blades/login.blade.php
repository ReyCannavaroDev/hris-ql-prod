@verbatim
<div class="min-h-screen bg-gradient-to-r from-black to-blue-900 py-6 flex flex-col justify-start sm:justify-center relative overflow-y-auto">
  <img
    style="object-fit: cover; object-position: right; width: 100%; height: 100%;"
    :src="getBasic.brand_bg"
    class="w-screen z-0 h-full absolute opacity-70 fixed top-0 left-0"
  >

  <div class="relative py-3 px-2 sm:max-w-xl sm:mx-auto z-10 mb-20 sm:mb-0">
    <div
      class="absolute inset-0 bg-gradient-to-r from-cyan-400 via-sky-500 to-purple-500 shadow-lg transform -skew-y-3 sm:skew-y-0 sm:-rotate-6 sm:rounded-3xl">
    </div>

    <div class="relative px-4 py-8 bg-white shadow-lg rounded-2xl sm:rounded-3xl sm:p-12">
      <div class="max-w-md mx-auto">
        <img :src="getBasic.brand_logo" @error="defaultImage" class="w-[80px] sm:w-[100px] my-2 m-auto">
        <h1 class="text-xl sm:text-2xl font-semibold text-center leading-tight">
          Human Resource Information System
        </h1>
        <p class="text-sm text-gray-400 text-center mt-1">
          Masuk untuk melanjutkan
        </p>

        <div class="mt-6 border-b border-gray-200 flex text-sm">
          <button
            type="button"
            class="flex-1 pb-2 text-center transition-all"
            :class="activeTab === 'login'
              ? 'font-semibold text-cyan-600 border-b-2 border-cyan-500'
              : 'text-gray-400 border-b-2 border-transparent hover:text-gray-700'"
            @click="switchTab('login')"
          >
            Login
          </button>
          <button
            type="button"
            class="flex-1 pb-2 text-center transition-all"
            :class="activeTab === 'absen'
              ? 'font-semibold text-cyan-600 border-b-2 border-cyan-500'
              : 'text-gray-400 border-b-2 border-transparent hover:text-gray-700'"
            @click="switchTab('absen')"
          >
            Absen
          </button>
        </div>

        <form v-show="activeTab === 'login'" @submit="onLogin" class="mt-4">
          <div class="space-y-4">
            <div class="relative">
              <FieldX
                :bind="{ readonly: false, style: 'padding-top:15px; padding-bottom:15px; font-size: 11pt' }"
                :value="values.email"
                @input="v=>values.email=v"
                placeholder="Username"
                fa-icon="user"
                :check="false"
              />
            </div>
            <div class="relative">
              <FieldX
                :bind="{ readonly: false, style: 'padding-top:15px; padding-bottom:15px; font-size: 11pt' }"
                :value="values.password"
                @input="v=>values.password=v"
                type="password"
                placeholder="Password"
                fa-icon="lock"
                :check="false"
              />
            </div>
            <div class="relative pt-2">
              <button
                type="submit"
                class="bg-cyan-500 w-full text-white rounded-xl px-4 py-3 font-medium hover:bg-cyan-600 transition-colors disabled:opacity-60"
                :disabled="isRequesting"
              >
                <span v-if="!isRequesting">Masuk</span>
                <span v-else>Memproses...</span>
              </button>
            </div>
          </div>
        </form>

        <div v-show="activeTab === 'absen'" class="mt-4 text-sm">
          <p class="text-[11px] text-center text-gray-500 mb-3 bg-gray-50 p-2 rounded-lg">
            Gunakan fitur ini untuk absen dengan wajah + lokasi tanpa login.
          </p>

          <div class="relative overflow-hidden rounded-xl bg-black shadow-inner">
            <video
              v-show="!isImage"
              ref="videoElement"
              autoplay
              playsinline
              muted
              style="transform: scaleX(-1);"
              class="w-full h-[180px] sm:h-[220px] object-cover"
            ></video>

            <img
              v-show="isImage"
              id="imgAbsen"
              class="w-full h-[180px] sm:h-[220px] object-cover"
            >
          </div>

          <div
            v-if="!isImage"
            class="mt-3 text-[11px] text-center font-medium"
            :class="qualityLabel === 'Bagus' ? 'text-green-600' : (qualityLabel === 'Cukup' ? 'text-yellow-600' : 'text-red-600')"
          >
            <span v-if="qualityScore !== null">
              Kualitas wajah: {{ qualityScore }}/100 – {{ qualityMessage }}
            </span>
            <span v-else class="text-gray-400">Mengukur kualitas wajah...</span>
          </div>

          <div class="mt-2 py-1 bg-gray-50 rounded-lg text-[11px] flex items-center justify-center gap-2">
            <input type="checkbox" v-model="voiceEnabled" class="border-gray-300 rounded text-cyan-600" />
            <span class="text-gray-600">Panduan suara {{ voiceEnabled ? 'aktif' : 'mati' }}</span>
          </div>

          <div v-if="faceResultText" class="mt-2 text-center text-[11px] font-bold"
            :class="faceMatch === true ? 'text-green-600' : 'text-red-600'">
            {{ faceResultText }}
          </div>

          <div class="mt-4 p-3 bg-slate-50 rounded-xl text-[11px] text-gray-600 space-y-2">
            <div class="flex justify-between items-center border-b border-gray-200 pb-1">
              <span class="font-medium text-gray-800">{{ formAbsen.time || '-' }}</span>
              <span v-if="formAbsen.lat" class="flex items-center gap-1 text-cyan-700">
                <i class="fa fa-location-arrow"></i>
                {{ formAbsen.lat }}, {{ formAbsen.long }}
              </span>
            </div>
            <div>
              <div class="font-semibold text-gray-700">Alamat :</div>
              <div class="leading-relaxed line-clamp-2">
                {{ formAbsen.address || '-' }}
              </div>
            </div>
          </div>

          <div class="mt-5 flex flex-col sm:flex-row justify-center gap-2">
            <button
              v-if="isImage"
              @click="recaptureAbsen"
              class="flex-1 px-4 py-3 rounded-xl text-xs font-bold bg-amber-500 text-white hover:bg-amber-600 transition-transform active:scale-95"
            >
              ULANGI FOTO
            </button>
            <button
              v-else
              @click="captureAbsen"
              class="flex-1 px-4 py-3 rounded-xl text-xs font-bold bg-cyan-600 text-white hover:bg-cyan-700 transition-transform active:scale-95"
            >
              AMBIL FOTO
            </button>
            
            <button
              :disabled="!isImage || isSubmittingAbsen"
              @click="submitAbsen"
              class="flex-1 px-4 py-3 rounded-xl text-xs font-bold bg-emerald-600 text-white hover:bg-emerald-700 disabled:opacity-50 transition-transform active:scale-95"
            >
              <span v-if="!isSubmittingAbsen">
                {{ absenMode === 'checkin' ? 'CHECK-IN' : 'CHECK-OUT' }}
              </span>
              <span v-else>MEMPROSES...</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="w-full sm:absolute sm:bottom-0 sm:right-0 p-6 text-center sm:text-right z-20">
    <p class="text-white text-xs sm:text-sm opacity-80">
      ©2023 Copyright
      <a class="underline font-semibold" href="https://qqltech.com" target="_blank">Quantum Leap</a>. 
      <span class="block sm:inline">All Rights Reserved</span>
    </p>
  </div>
</div>
@endverbatim
