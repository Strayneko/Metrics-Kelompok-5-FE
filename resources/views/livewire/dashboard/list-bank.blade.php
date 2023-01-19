<script>
  Alpine.data('listBankDashboard', () => ({
    showSidebar: false,
    showMessage: 'Please wait...',
    isShow: false,
    token: localStorage.getItem('token'),
    isLoading: true,
    resData: [],
    roleId: 0,
    // redirect to login page if user is not logged in
    checkLogin() {
      /**
       * Redirect to login page if there is no token in localstorage
       */
      if (!this.token) {
        window.location.href = `{{ route('login') }}`
        // console.log('hello')
      }

      const reqTime = Date.now()
      const path = '/api/user/me'
      const apiKey = generateKey(path, reqTime)

      /**
       * Get profile
       */
      fetch(`{{ env('API_URL') }}${path}`, {
        method: 'GET',
        headers: {
          'Content-type': 'application/json;charset=UTF-8',
          'Authorization': this.token,
          'Request-Time': reqTime,
          'D-App-Key': apiKey
        }
      }).then(async res => {
        this.resData = await res.json()
        /**
         * Redirect to login if user not found
         */
        if (this.resData.status == false) {
          localStorage.removeItem('token')
          window.location.href = `{{ route('login') }}`
        }

        // console.log(this.resData)
        this.roleId = this.resData.data.role_id

        /**
         * Redirect to home if user role is not 2 (admin)
         */
        if (this.roleId != 2) {
          window.location.replace(`{{ route('home') }}`)
        }

        this.isLoading = false
      }).catch(err => {
        Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: 'Internal Server Error! Please Try Again Later.',
        })
      })
    }
  }))

  Alpine.data('listBank', () => ({
    banks: [],
    updatedbank: {
      name: "",
      loaning_percentage: "",
      max_age: "",
      min_age: "",
      marital_status: 0,
      nationality: 0,
      employment: 0
    },
    isupdate: false,
    message: "",
    isSubmit: false,


    // consume api for update bank data to database
    updatedBank(id) {
      /**
       * Create form data
       */
      const data = new FormData(this.$refs.updateBankForm)

      this.isSubmit = true

      const reqTime = Date.now()
      const path = `/api/bank/edit/${id}`
      const apiKey = generateKey(path, reqTime)

      /**
       * Fetch api to update data bank
       */
      fetch(`{{ env('API_URL') }}${path}`, {
        method: "POST",
        body: data,
        headers: {
          'Authorization': localStorage.getItem('token'),
          'Request-Time': reqTime,
          'D-App-Key': apiKey
        }
      }).then(async (response) => {
        this.isSubmit = false

        let responsdata = await response.json()
        let status = responsdata.status
        this.message = responsdata.message

        let msg = ``
        for (m of this.message) {
          msg += `<p>${m}</p>`
        }

        if (status == false) {
          Swal.fire({
            icon: 'error',
            title: 'Oops...',
            html: msg
          })
          // window.location.replace('')
          return
        }
        Swal.fire({
          icon: 'success',
          title: 'Success!',
          text: 'Update Bank Success!'
        }).then(res => {
          window.location.replace(`{{ env('APP_URL') }}/dashboard/bank`)
        })
      }).catch(err => {
        Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: 'Internal Server Error! Please Try Again Later.',
        })
      })
    },

    deleteBank(id) {

      const reqTime = Date.now()
      const path = `/api/bank/delete/${id}`
      const apiKey = generateKey(path, reqTime)

      Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
      }).then((result) => {
        if (result.isConfirmed) {
          /**
           * Fetch api to delete data bank
           */
          fetch(`{{ env('API_URL') }}${path}`, {
              method: "POST",
              headers: {
                'Content-type': 'application/json;charset=UTF-8',
                'Authorization': localStorage.getItem('token'),
                'Request-Time': reqTime,
                'D-App-Key': apiKey
              }
            })
            .then(async (response) => {
              let data = await response.json()
              let status = data.status
              this.message = data.message

              let msg = ``
              for (m of this.message) {
                msg += `<p>${m}</p>`
              }

              if (status == false) {
                Swal.fire({
                  icon: 'error',
                  title: 'Oops...',
                  html: msg
                })
                // window.location.replace('')
                return
              }
              Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Delete Bank Success!'
              }).then(res => {
                window.location.replace(`{{ env('APP_URL') }}/dashboard/bank`)
              })
            }).catch(err => {
              Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Internal Server Error! Please Try Again Later.',
              })
            })
        }
      })
    },


    // redirect to login page if user is not logged in for modal
    pageNumber: 0,
    size: 5,
    total: '',
    bankLists: [],
    startAt: 0,
    pages: [],
    checkLogged() {
      if (!this.token) {
        window.location.href(`{{ route('home') }}`)
      }
    },
    // fetch api for get bank list from database
    getBanks() {
      const reqTime = Date.now()
      const path = '/api/bank'
      const apiKey = generateKey(path, reqTime)

      /**
       * Get data banks
       */
      fetch(`{{ env('API_URL') }}${path}`, {
        method: 'GET',
        headers: {
          'Content-type': 'application/json;charset=UTF-8',
          'Authorization': localStorage.getItem('token'),
          'Request-Time': reqTime,
          'D-App-Key': apiKey
        }
      }).then(async res => {
        this.banks = await res.json()
        // console.log(this.banks)

        // console.log(this.pageNumber)
        const start = this.pageNumber * this.size
        const end = start + this.size
        // console.log(start, end)

        this.total = this.banks.data.length
        // console.log(this.total)

        this.bankLists = this.banks.data.slice(start, end)
        // console.log(this.bankLists)

        this.pages = Array.from({
          length: Math.ceil(this.total / this.size)
        }, (val, i) => i)
        // console.log(this.pages)

      }).catch(err => {
        Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: 'Internal Server Error! Please Try Again Later.',
        })
      })
    },
    async viewPage(index) {
      this.bankLists = []
      this.pageNumber = index

      const start = this.pageNumber * this.size
      const end = start + this.size
      // console.log(start, end)

      this.startAt = start
      this.total = this.banks.data.length
      // console.log(this.total)

      this.bankLists = await this.banks.data.slice(start, end)
      // console.log(this.bankLists)

      this.pages = Array.from({
        length: Math.ceil(this.total / this.size)
      }, (val, i) => i)
      // console.log(this.pages)
    }
  }))

  Alpine.data('createBank', () => ({
    newBank: {
      name: "",
      loaning_percentage: "",
      max_age: "",
      min_age: "",
      marital_status: 0,
      nationality: 0,
      employment: 0
    },
    message: "",
    isSubmit: false,
    // consume api for add new bank data to database
    createNewBank() {
      /**
       * Create form data
       */
      const data = new FormData()
      data.append('name', this.newBank.name)
      data.append('loaning_percentage', this.newBank.loaning_percentage)
      data.append('max_age', this.newBank.max_age)
      data.append('min_age', this.newBank.min_age)
      data.append('marital_status', this.newBank.marital_status)
      data.append('nationality', this.newBank.nationality)
      data.append('employment', this.newBank.employment)

      this.isSubmit = true

      const reqTime = Date.now()
      const path = '/api/bank/create'
      const apiKey = generateKey(path, reqTime)

      /**
       * Fetch api to create new data bank
       */
      fetch(`{{ env('API_URL') }}${path}`, {
        method: "POST",
        body: data,
        headers: {
          'Authorization': localStorage.getItem('token'),
          'Request-Time': reqTime,
          'D-App-Key': apiKey
        }
      }).then(async (response) => {
        this.isSubmit = false

        let data = await response.json()
        let status = data.status
        this.message = data.message

        let msg = ``
        for (m of this.message) {
          msg += `<p>${m}</p>`
        }

        if (status == false) {
          Swal.fire({
            icon: 'error',
            title: 'Oops...',
            html: msg
          })
          // window.location.replace('')
          return
        }
        Swal.fire({
          icon: 'success',
          title: 'Success!',
          text: 'Add Bank Success!'
        }).then(res => {
          window.location.replace(`{{ env('APP_URL') }}/dashboard/bank`)
        })
      }).catch(err => {
        Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: 'Internal Server Error! Please Try Again Later.',
        })
      })
    },

    // redirect to login page if user is not logged in for modal
    checkLogged() {
      if (!this.token) {
        window.location.href(`{{ route('home') }}`)
      }
    }
  }))
</script>
<main class="container relative flex justify-end font-poppins" x-data="listBankDashboard" x-init="checkLogin()">
  <template x-if="isLoading">
    @livewire('components.loading')
  </template>
  @livewire('partials.nav-mobile')

  @livewire('partials.sidebar')

  <!-- list bank section -->
  <section class="mt-20 w-full py-10 lg:mt-0 lg:w-[80%]">
    <div class="relative mx-auto w-11/12 rounded-xl pb-6 lg:mx-0 lg:w-full lg:bg-gray-1 lg:p-6 lg:pt-12"
      x-data="{ isAddActive: false, isUpdate: false }">
      <div class="relative mx-auto mb-12 flex w-max flex-col items-center justify-center gap-3">
        <h1 class="text-3xl font-bold text-orange-2">List <span class="text-navy">Bank</span></h1>
        <div
          class="relative h-2 w-40 rounded-lg bg-orange-1 after:absolute after:inset-0 after:m-auto after:h-5 after:w-16 after:rounded-xl after:bg-navy">
        </div>
      </div>
      <div x-show="isAddActive" x-transition.duration.200ms x-data="createBank" x-init="checkLogged()">
        @livewire('components.modal.add-bank')
      </div>

      <div class="mb-6">
        <a x-on:click="isAddActive = !isAddActive"
          class="inline-block cursor-pointer rounded-md bg-white px-6 py-2 font-semibold text-navy shadow-md shadow-navy/30 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-navy/40">Create
          Bank</a>
      </div>

      <div class="overflow-hidden rounded-xl bg-gray-1/30 lg:bg-white" x-data="listBank" x-init="getBanks()">
        <ul class="flex gap-3 bg-orange-1 px-3 py-4 font-semibold text-navy">
          <li class="w-10 text-center">No</li>
          <li class="w-96">Name Bank</li>
          <li class="hidden w-64 lg:block">Max Loan</li>
          <li class="hidden w-48 lg:block">Action</li>
        </ul>
        <!-- Loading -->
        <template x-if="banks.length == 0">
          <div class="my-10 pb-10 text-center text-2xl font-bold text-navy">
            <h1 x-text="showMessage"></h1>
          </div>
        </template>

        <!-- call table list template -->
        <template x-for="(bank, i) of bankLists">
          @livewire('components.list-bank')
        </template>

        {{-- Start pagination --}}
        <div class="flex justify-end py-8 px-10">
          <ul class="flex w-max items-center rounded-md font-semibold text-orange-1 outline outline-2 outline-orange-1">
            <li>
              <button class="group py-1 pl-3 disabled:cursor-not-allowed" type="button" x-on:click="viewPage(0)"
                :disabled="pageNumber == 0">
                <svg width="22" height="22" viewBox="0 0 22 22" fill="none"
                  xmlns="http://www.w3.org/2000/svg">
                  <path d="M11 4L3 11L11 19" stroke="#FF5927" stroke-width="3.77953" stroke-linejoin="round"
                    class="group-hover:animate-arrowColor1" />
                  <path d="M20 4L12 11L20 19" stroke="#FCC997" stroke-width="3.77953" stroke-linejoin="round"
                    class="group-hover:animate-arrowColor2" />
                </svg>
              </button>
            </li>

            <li>
              <button class="group py-1 pr-4 disabled:cursor-not-allowed" type="button"
                x-on:click="viewPage(pageNumber - 1)" :disabled="pageNumber == 0">
                <svg width="22" height="22" viewBox="0 0 22 22" fill="none"
                  xmlns="http://www.w3.org/2000/svg">
                  <path d="M20 4L12 11L20 19" stroke="#FCC997" stroke-width="3.77953" stroke-linejoin="round"
                    class="transition-all duration-200 group-hover:stroke-orange-2/60" />
                </svg>
              </button>
            </li>

            <li class="-mr-[.25px]">
              <button type="button" x-on:click="viewPage(0)"
                class="flex h-9 w-6 cursor-pointer items-center justify-center outline outline-1 outline-orange-1"
                :class="pageNumber == 0 ? 'bg-orange-1 text-white' : ''">1</button>
            </li>

            <template x-for="num in pages">
              <li class="-mr-[.25px]" :hidden="pageNumber != num || pageNumber == 0 || pageNumber == pages.length - 1">
                <button type="button" x-on:click="viewPage(num)"
                  class="flex h-9 w-6 cursor-pointer items-center justify-center outline outline-1 outline-orange-1"
                  x-text="num + 1" :class="pageNumber == num ? 'bg-orange-1 text-white' : ''"></button>
              </li>
            </template>

            <li class="-mr-[.25px]" :hidden="pages.length - 1 == 0">
              <button type="button" x-on:click="viewPage(pages.length - 1)"
                class="flex h-9 w-6 cursor-pointer items-center justify-center outline outline-1 outline-orange-1"
                x-text="pages.length" :class="pageNumber == pages.length - 1 ? 'bg-orange-1 text-white' : ''"></button>
            </li>

            <li>
              <button class="group py-1 pl-4 disabled:cursor-not-allowed" type="button"
                x-on:click="viewPage(pageNumber + 1)" :disabled="pageNumber == (pages.length - 1)">
                <svg width="22" height="22" viewBox="0 0 22 22" fill="none"
                  xmlns="http://www.w3.org/2000/svg">
                  <path d="M2.44653 4L10.4465 11L2.44653 19" stroke="#FCC997" stroke-width="3.77953"
                    stroke-linejoin="round" class="transition-all duration-200 group-hover:stroke-orange-2/60" />
                </svg>
              </button>
            </li>

            <li>
              <button class="group py-1 pr-3 disabled:cursor-not-allowed" type="button"
                x-on:click="viewPage(pages.length - 1)" :disabled="pageNumber == (pages.length - 1)">
                <svg width="22" height="22" viewBox="0 0 22 22" fill="none"
                  xmlns="http://www.w3.org/2000/svg">
                  <path d="M11.4465 4L19.4465 11L11.4465 19" stroke="#FF5927" stroke-width="3.77953"
                    stroke-linejoin="round" class="group-hover:animate-arrowColor1" />
                  <path d="M2.44653 4L10.4465 11L2.44653 19" stroke="#FCC997" stroke-width="3.77953"
                    stroke-linejoin="round" class="group-hover:animate-arrowColor2" />
                </svg>
              </button>
            </li>
          </ul>
        </div>
        {{-- End Pagination --}}

      </div>
    </div>
  </section>
</main>
