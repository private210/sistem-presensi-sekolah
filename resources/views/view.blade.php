<!doctype html>
<html lang="id" class="!scroll-smooth">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    @vite('resources/css/app.css')
    <title>Home | SDN Banjarejo</title>
  </head>
  <body>
    <header class="bg-white sticky top-0 z-50" id="header">
      <div class="mx-auto max-w-(--breakpoint-xl) px-4 sm:px-6 lg:px-8">
        <div class="flex h-20 items-center justify-between">
          <div class="md:flex md:items-center md:gap-12 ">
            <a class="flex title-font font-medium items-center mb-4 md:mb-0 mt-3">
                <img src="{{ asset('images/LogoSD.png') }}" alt="SDN Banjarejo Logo" class="h-10 w-10 ml-2 mr-2">
                <span class="text-xl font-bold text-teal-600">SDN BANJAREJO</span>
            </a>
          </div>

          <div class="hidden md:block">
            <nav aria-label="Global">
              <ul class="flex items-center gap-6 text-sm">
                <li>
                  <a class="text-black transition hover:text-gray-500/75 " href="#"> Home </a>
                </li>
                <li>
                  <a class="text-black transition hover:text-gray-500/75" href="#tentang"> Tentang </a>
                </li>
                <li>
                  <a class="text-black transition hover:text-gray-500/75" href="#visi-misi"> Visi & Misi </a>
                </li>

                <li>
                  <a class="text-black transition hover:text-gray-500/75" href="#ekstra"> Ekstrakulikuler </a>
                </li>

                <li>
                  <a class="text-black transition hover:text-gray-500/75" href="#prestasi"> Prestasi </a>
                </li>
                <li>
                  <a class="text-black transition hover:text-gray-500/75" href="#galeri"> Galeri </a>
                </li>
                <li>
                  <a class="text-black transition hover:text-gray-500/75" href="#kontak"> Kontak </a>
                </li>
              </ul>
            </nav>
          </div>

          <div class="flex items-center gap-4">
            <div class="sm:flex sm:gap-4">
              <a class="rounded-md bg-teal-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm" href="/admin/login "> Login </a>
            </div>

            <div class="block md:hidden">
              <button class="rounded-sm bg-gray-100 p-2 text-gray-600 transition hover:text-gray-600/75">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </header>
    <section id="hero" class="mb-4">
      <div class="min-h-[700px] w-full bg-cover bg-center" style="background-image: url('{{ asset('images/cover.jpg') }}') ;">
        <div class="flex min-h-[700px] w-full items-end justify-center bg-gray-900/40">
          <div class="text-center mb-20">
            <h1 class="text-3xl font-semibold text-white lg:text-4xl">Selamat Datang di <span class="auto_type text-teal-200"></span> SDN Banjarejo</h1>
            <h5 class="text-md mt-6 font-bold text-white sm:text-xl">SDN Banjarejo Merupakan Sekolah Dasar Negeri yang berlokasi di Ds. Banjarejo Kec. Barat Kab. Magetan</h5>

            <p class=" text-white">Ini merupakan bentuk implementasi dari visi kami yang mengedepankan IMTAQ dan IPTEK serta Berakhlak Mulia untuk membangun siswa yang Beriman dan Berilmu</p>
          </div>
        </div>
      </div>
    </section>

    <main>
        <section class="body-font text-gray-600" id="tentang">
            <div class="container mx-auto px-5 py-20 mt-16">
            <div class="mb-20 flex w-full flex-col flex-wrap items-center text-center">
                <h1 class="title-font mb-2 text-2xl font-medium text-gray-900 sm:text-3xl">Tentang Kami</h1>
                <p class="w-full leading-relaxed text-gray-500 lg:w-1/2">SDN Banjarejo merupakan Sekolah Dasar Negeri yang berlokasi di Desa Banjarejo, Kecamatan Barat, Kabupaten Magetan.</p>
            </div>
            <div class="flex flex-col sm:flex-row mt-10 justify-center ml-20 shadow-lg rounded-3xl border-t border-gray-200">
                <div class="sm:w-1/3 text-center sm:pr-8 sm:py-8">
                    <div class="w-20 h-20 rounded-full inline-flex items-center justify-center bg-gray-800 text-gray-600">
                        <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" class="w-10 h-10" viewBox="0 0 24 24">
                            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </div>
                    <div class="flex flex-col items-center text-center justify-center">
                        <h2 class="font-medium title-font mt-4 text-black text-lg">Jumiati Tri Handayani, S.Pd</h2>
                        <p class="text-sm text-gray-400">Kepala Sekolah | SDN Banjarejo</p>
                        <div class="w-12 h-1 bg-indigo-500 rounded mt-2 mb-4"></div>
                            <p class="text-base text-gray-400">Ibu Jumiati merupakan kepala sekolah di sdn banjarejo sejak tahun 2023, beliau merupakan sosok yang memiliki karakter yang baik dan berakhlak mulia</p>
                        </div>
                    </div>
                    <div class="sm:w-1/2 sm:pl-8 sm:py-8 sm:border-l border-gray-800 sm:border-t-0 border-t sm:mt-0 text-center sm:text-justify">
                        <p>Di SDN Banjarejo kepala sekolah dibantu oleh tenaga pendidik yang profesional dan berpengalaman dalam bidangnya, siap membimbing siswa untuk mencapai prestasi terbaik.</br> <br>
                            Dan Memiliki beberapa fasilitas yang dapat memudahkan siswa untuk belajar. Diantarnya :</p>
                        <div class=" columns-2 gap-y-2 list-decimal list-inside mt-4 text-gray-600">
                            <li>Ruang Kelas Yang Nyaman</li>
                            <li>Perpustakaan</li>
                            <li>Ruang UKS</li>
                            <li>Lapangan Olahraga</li>
                            <li>Mushola</li>
                            <li>Koperasi Sekolah</li>
                            <li>Kantin Sekolah</li>
                            <li>Tempat Parkir & Toilet Bersih</li>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </section>

        <section class="body-font text-gray-600" id="visi-misi">
            <div class="container mx-auto px-5 py-20 mt-16">
            <div class="mb-14 flex w-full flex-col flex-wrap items-center text-center">
                <h1 class="title-font mb-2 text-2xl font-medium text-gray-900 sm:text-3xl">Visi dan Misi</h1>
                <p class="w-full leading-relaxed text-gray-500 lg:w-1/2">SDN Banjarejo berjalan berdasarkan visi dan misi yang telah ditetapkan.</p>
            </div>
            <div class="flex flex-wrap justify-center">
                <div class="p-4 md:w-1/2 lg:w-1/3">
                    <div class="rounded-lg border border-gray-200 p-6">
                        <div class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 text-indigo-500">
                            <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" class="h-6 w-6" viewBox="0 0 24 24">
                                <path d="M12 2a10 10 0 100 20 10 10 0 000-20zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"></path>
                            </svg>
                        </div>
                        <h2 class="title-font mb-2 text-lg font-medium text-gray-900">Visi</h2>
                        <p class="text-base leading-relaxed">Unggul Dalam Prestasi, Budaya, IMTAQ, IPTEK,, serta Berakhlak Mulia</p>
                    </div>
                </div>
                <div class="p-4 md:w-1/2 lg:w-1/3">
                    <div class="rounded-lg border border-gray-200 p-6">
                        <div class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 text-indigo-500">
                            <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" class="h-6 w-6" viewBox="0 0 24 24">
                                <path d="M12 2a10 10 0 100 20 10 10 0 000-20zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"></path>
                            </svg>
                        </div>
                        <h2 class="title-font mb-2 text-lg font-medium text-gray-900">Misi</h2>
                        <div class="list-decimal list-inside mt-4 text-gray-600">
                            <li>Meningkatkan Kualitas Keimanan dan Ketaqwaan</li>
                            <li>Unggul dalam bidan akademik dan non-akademik</li>
                            <li>Melaksanakan Pembelajaran yang Inovatif yang Bernuansa PAKEM</li>
                            <li>Mengintegrasikan Pendidikan Budaya</li>
                            <li>Membiasakan / Menanamkan Nilai-Nilai Pancasila melalui Pembiasaan Sehari-hari</li>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="body-font text-gray-600" id="ekstra">
            <div class="container mx-auto px-5 py-24">
              <div class="mb-20 flex w-full flex-col flex-wrap items-center text-center">
                <h1 class="title-font mb-2 text-2xl font-medium text-gray-900 sm:text-3xl">Ekstrakulikuler</h1>
                <p class="w-full leading-relaxed text-gray-500 lg:w-1/2">SDN Banjarejo memiliki beberapa Ekstrakulikuler yang dapat diikuti oleh siswa sebagai sebuah wadah untuk mengembangkan potensi dan minat mereka.</p>
              </div>
              <div class="-m-4 flex flex-wrap">
                <div class="p-4 md:w-1/2 xl:w-1/3">
                  <div class="rounded-3xl shadow-lg border border-gray-200 p-6">
                    <div class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 text-indigo-500">
                      <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" class="h-6 w-6" viewBox="0 0 24 24">
                        <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
                      </svg>
                    </div>
                    <h2 class="title-font mb-2 text-lg font-medium text-gray-900">Pramuka</h2>
                    <p class="text-base leading-relaxed">Pramuka merupakan kegiatan untuk mengembangkan potensi dan minat siswa di bidang kebersihan, kesehatan, dan kebersamaan.</p>
                  </div>
                </div>
                <div class="p-4 md:w-1/2 xl:w-1/3">
                  <div class="rounded-3xl shadow-lg border border-gray-200 p-6">
                    <div class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 text-indigo-500">
                      <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" class="h-6 w-6" viewBox="0 0 24 24">
                        <circle cx="6" cy="6" r="3"></circle>
                        <circle cx="6" cy="18" r="3"></circle>
                        <path d="M20 4L8.12 15.88M14.47 14.48L20 20M8.12 8.12L12 12"></path>
                      </svg>
                    </div>
                    <h2 class="title-font mb-2 text-lg font-medium text-gray-900">Drumband</h2>
                    <p class="text-base leading-relaxed">Drumband merupakan kegiatan untuk mengembangkan potensi dan minat siswa di bidang musik.</p>
                  </div>
                </div>
                <div class="p-4 md:w-1/2 xl:w-1/3">
                  <div class="rounded-3xl shadow-lg border border-gray-200 p-6">
                    <div class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 text-indigo-500">
                      <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" class="h-6 w-6" viewBox="0 0 24 24">
                        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                      </svg>
                    </div>
                    <h2 class="title-font mb-2 text-lg font-medium text-gray-900">Olahraga</h2>
                    <p class="text-base leading-relaxed">Olahraga merupakan kegiatan untuk mengembangkan potensi dan minat siswa di bidang olahraga.</p>
                  </div>
                </div>
                <div class="p-4 md:w-1/2 xl:w-1/3">
                  <div class="rounded-3xl shadow-lg border border-gray-200 p-6">
                    <div class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 text-indigo-500">
                      <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" class="h-6 w-6" viewBox="0 0 24 24">
                        <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1zM4 22v-7"></path>
                      </svg>
                    </div>
                    <h2 class="title-font mb-2 text-lg font-medium text-gray-900">Banjari / Hadroh</h2>
                    <p class="text-base leading-relaxed">Banjari merupakan kegiatan untuk mengembangkan potensi dan minat siswa di bidang musik bernuasa keagamaan.</p>
                  </div>
                </div>
                <div class="p-4 md:w-1/2 xl:w-1/3">
                  <div class="rounded-3xl shadow-lg border border-gray-200 p-6">
                    <div class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 text-indigo-500">
                      <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" class="h-6 w-6" viewBox="0 0 24 24">
                        <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"></path>
                      </svg>
                    </div>
                    <h2 class="title-font mb-2 text-lg font-medium text-gray-900">Madin</h2>
                    <p class="text-base leading-relaxed">Madin merupakan kegiatan untuk mengembangkan potensi dan minat siswa di bidang keagamaan.</p>
                  </div>
                </div>
                <div class="p-4 md:w-1/2 xl:w-1/3">
                  <div class="rounded-3xl shadow-lg border border-gray-200 p-6">
                    <div class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 text-indigo-500">
                      <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" class="h-6 w-6" viewBox="0 0 24 24">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                      </svg>
                    </div>
                    <h2 class="title-font mb-2 text-lg font-medium text-gray-900">Kesenian</h2>
                    <p class="text-base leading-relaxed">Kesenian merupakan kegiatan untuk mengembangkan potensi dan minat siswa di bidang kesenian baik seni tari dll.</p>
                  </div>
                </div>
              </div>
            </div>
          </section>

          <section class="body-font text-gray-600" id="prestasi">
            <div class="container mx-auto px-5 py-20 mt-16">
              <div class="mb-20 flex w-full flex-col flex-wrap items-center text-center">
                  <h1 class="title-font mb-2 text-3xl font-bold text-gray-900">Prestasi</h1>
                  <p>Berikut merupakan Prestasi-prestasi yang didapatkan oleh SDN Banjarejo</p>
              </div>
              <div class="-m-4 flex flex-wrap shadow-lg rounded-3xl border-t border-gray-200">
                <div class="p-4 md:w-1/3">
                  <div class="border-opacity-60 h-full overflow-hidden rounded-lg border-2 border-gray-200">
                    <img class="w-full object-cover object-center md:h-36 lg:h-48" src="https://dummyimage.com/720x400" alt="blog" />
                    <div class="p-6">
                      <h1 class="title-font mb-3 text-lg font-medium text-gray-900"> Juara 3 Tapak Tenda</h1>
                      <p class="mb-3 leading-relaxed"> Keiatan Hari Pramuka ke-63 Kwaran Barat tahun 2025 </p>
                    </div>
                  </div>
                </div>
                <div class="p-4 md:w-1/3">
                  <div class="border-opacity-60 h-full overflow-hidden rounded-lg border-2 border-gray-200">
                    <img class="w-full object-cover object-center md:h-36 lg:h-48" src="https://dummyimage.com/721x401" alt="blog" />
                    <div class="p-6">
                      <h1 class="title-font mb-3 text-lg font-medium text-gray-900">Juara 3 Bola Voli</h1>
                      <p class="mb-3 leading-relaxed"> Mendapatkan juara 3 lomba bola voli di tingkat kecamatan dan kabupaten tahun 2025</p>
                    </div>
                  </div>
                </div>
                <div class="p-4 md:w-1/3">
                  <div class="border-opacity-60 h-full overflow-hidden rounded-lg border-2 border-gray-200">
                    <img class="w-full object-cover object-center md:h-36 lg:h-48" src="https://dummyimage.com/722x402" alt="blog" />
                    <div class="p-6">
                      <h1 class="title-font mb-3 text-lg font-medium text-gray-900">Juara Umum 2 Drumband</h1>
                      <p class="mb-3 leading-relaxed"> Lomba Drumband Walikota Cup tahun 2025</p>
                    </div>
                  </div>
                </div>
                <div class="p-4 md:w-1/3">
                  <div class="border-opacity-60 h-full overflow-hidden rounded-lg border-2 border-gray-200">
                    <img class="w-full object-cover object-center md:h-36 lg:h-48" src="https://dummyimage.com/722x402" alt="blog" />
                    <div class="p-6">
                      <h1 class="title-font mb-3 text-lg font-medium text-gray-900">Juara 1 Bola Voli O2SN</h1>
                      <p class="mb-3 leading-relaxed"> Mendapatkan juara 1 lomba bola voli di tingkat kecamatan dalam rangka O2SN</p>
                    </div>
                  </div>
                </div>
                <div class="p-4 md:w-1/3">
                  <div class="border-opacity-60 h-full overflow-hidden rounded-lg border-2 border-gray-200">
                    <img class="w-full object-cover object-center md:h-36 lg:h-48" src="https://dummyimage.com/722x402" alt="blog" />
                    <div class="p-6">
                      <h1 class="title-font mb-3 text-lg font-medium text-gray-900">Juara 3 Lomba Cipta Baca Puisi FLS2N</h1>
                      <p class="mb-3 leading-relaxed"> Mendapatkan juara 3 lomba puisi FLS2N di tingkat kecamatan tahun 2025</p>
                    </div>
                  </div>
                </div>
                <div class="p-4 md:w-1/3">
                  <div class="border-opacity-60 h-full overflow-hidden rounded-lg border-2 border-gray-200">
                    <img class="w-full object-cover object-center md:h-36 lg:h-48" src="https://dummyimage.com/722x402" alt="blog" />
                    <div class="p-6">
                      <h1 class="title-font mb-3 text-lg font-medium text-gray-900">Juara Harapan 1 Pantomim FLS2N</h1>
                      <p class="mb-3 leading-relaxed"> Mendapatkan juara harapan 1 lomba pantomim FLS2N di tingkat kecamatan tahun 2025</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </section>

          <section class="body-font text-gray-600" id="galeri">
            <div class="container mx-auto px-5 py-20 mt-16 shadow-md rounded-3xl">
              <div class="mb-20 flex w-full flex-col flex-wrap items-center text-center">
                  <h1 class="title-font mb-2 text-3xl font-bold text-gray-900">Galeri Kami</h1>
                  <p>Berikut merupakan beberapa Kegiatan yang dijalankan oleh SDN Banjarejo</p>
              </div>
              <div class="flex flex-wrap -m-4">
                <div class="lg:w-1/3 sm:w-1/2 p-4">
                  <div class="flex relative">
                    <img alt="gallery" class="absolute inset-0 w-full h-full object-cover object-center" src="https://dummyimage.com/600x360">
                    <div class="px-8 py-10 relative z-10 w-full border-4 border-gray-200 bg-white opacity-0 hover:opacity-100">
                      <h2 class="tracking-widest text-sm title-font font-medium text-indigo-500 mb-1">THE SUBTITLE</h2>
                      <h1 class="title-font text-lg font-medium text-gray-900 mb-3">Shooting Stars</h1>
                      <p class="leading-relaxed">Photo booth fam kinfolk cold-pressed sriracha leggings jianbing microdosing tousled waistcoat.</p>
                    </div>
                  </div>
                </div>
                <div class="lg:w-1/3 sm:w-1/2 p-4">
                  <div class="flex relative">
                    <img alt="gallery" class="absolute inset-0 w-full h-full object-cover object-center" src="https://dummyimage.com/601x361">
                    <div class="px-8 py-10 relative z-10 w-full border-4 border-gray-200 bg-white opacity-0 hover:opacity-100">
                      <h2 class="tracking-widest text-sm title-font font-medium text-indigo-500 mb-1">THE SUBTITLE</h2>
                      <h1 class="title-font text-lg font-medium text-gray-900 mb-3">The Catalyzer</h1>
                      <p class="leading-relaxed">Photo booth fam kinfolk cold-pressed sriracha leggings jianbing microdosing tousled waistcoat.</p>
                    </div>
                  </div>
                </div>
                <div class="lg:w-1/3 sm:w-1/2 p-4">
                  <div class="flex relative">
                    <img alt="gallery" class="absolute inset-0 w-full h-full object-cover object-center" src="https://dummyimage.com/603x363">
                    <div class="px-8 py-10 relative z-10 w-full border-4 border-gray-200 bg-white opacity-0 hover:opacity-100">
                      <h2 class="tracking-widest text-sm title-font font-medium text-indigo-500 mb-1">THE SUBTITLE</h2>
                      <h1 class="title-font text-lg font-medium text-gray-900 mb-3">The 400 Blows</h1>
                      <p class="leading-relaxed">Photo booth fam kinfolk cold-pressed sriracha leggings jianbing microdosing tousled waistcoat.</p>
                    </div>
                  </div>
                </div>
                <div class="lg:w-1/3 sm:w-1/2 p-4">
                  <div class="flex relative">
                    <img alt="gallery" class="absolute inset-0 w-full h-full object-cover object-center" src="https://dummyimage.com/602x362">
                    <div class="px-8 py-10 relative z-10 w-full border-4 border-gray-200 bg-white opacity-0 hover:opacity-100">
                      <h2 class="tracking-widest text-sm title-font font-medium text-indigo-500 mb-1">THE SUBTITLE</h2>
                      <h1 class="title-font text-lg font-medium text-gray-900 mb-3">Neptune</h1>
                      <p class="leading-relaxed">Photo booth fam kinfolk cold-pressed sriracha leggings jianbing microdosing tousled waistcoat.</p>
                    </div>
                  </div>
                </div>
                <div class="lg:w-1/3 sm:w-1/2 p-4">
                  <div class="flex relative">
                    <img alt="gallery" class="absolute inset-0 w-full h-full object-cover object-center" src="https://dummyimage.com/605x365">
                    <div class="px-8 py-10 relative z-10 w-full border-4 border-gray-200 bg-white opacity-0 hover:opacity-100">
                      <h2 class="tracking-widest text-sm title-font font-medium text-indigo-500 mb-1">THE SUBTITLE</h2>
                      <h1 class="title-font text-lg font-medium text-gray-900 mb-3">Holden Caulfield</h1>
                      <p class="leading-relaxed">Photo booth fam kinfolk cold-pressed sriracha leggings jianbing microdosing tousled waistcoat.</p>
                    </div>
                  </div>
                </div>
                <div class="lg:w-1/3 sm:w-1/2 p-4">
                  <div class="flex relative">
                    <img alt="gallery" class="absolute inset-0 w-full h-full object-cover object-center" src="https://dummyimage.com/606x366">
                    <div class="px-8 py-10 relative z-10 w-full border-4 border-gray-200 bg-white opacity-0 hover:opacity-100">
                      <h2 class="tracking-widest text-sm title-font font-medium text-indigo-500 mb-1">THE SUBTITLE</h2>
                      <h1 class="title-font text-lg font-medium text-gray-900 mb-3">Alper Kamu</h1>
                      <p class="leading-relaxed">Photo booth fam kinfolk cold-pressed sriracha leggings jianbing microdosing tousled waistcoat.</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </section>

          <section class="body-font relative text-gray-600 py-10" id="kontak">
            <div class="container mx-auto mt-16 text-center">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Hubungi Kami</h1>
                <p class="text-gray-600">Silakan hubungi kami melalui form di bawah ini untuk pertanyaan, saran, atau informasi lebih lanjut.</p>
            </div>
            <div class="container mx-auto flex flex-wrap px-5 py-24 sm:flex-nowrap">
                <div class="relative flex items-end justify-start overflow-hidden rounded-lg bg-gray-300 p-10 sm:mr-10 md:w-1/2 lg:w-2/3 min-h-[400px]">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3955.0777537705653!2d111.43802457518557!3d-7.566501992447542!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e79eacf73335089%3A0x137ca5955b880904!2sSDN%20Banjarejo!5e0!3m2!1sid!2sid!4v1751361337943!5m2!1sid!2sid"width="100%" height="100%" class="absolute inset-0" frameborder="0" title="map" marginheight="0" marginwidth="0" scrolling="no"></iframe>
                </div>
              <div class="mt-8 flex w-full flex-col bg-white md:mt-0 md:ml-auto md:w-1/2 md:py-8 lg:w-1/3">
                <h1 class="title-font mb-1 text-lg font-medium text-gray-900">Sosial Media</h1>
                <p class="mb-5 leading-relaxed text-gray-600">Anda dapat menghubungi kami melalui sosial media berikut</p>
                <div class="mb-4 grid grid-cols-2 gap-4">
                    <a class="mr-4 inline-flex items-center shadow-md rounded-md px-3 py-2 text-black hover:bg-sky-400 hover:text-white" href="https://www.facebook.com/sdn.banjarejo">
                        <svg xmlns='http://www.w3.org/2000/svg' width='35' height='35' viewBox='0 0 24 24' class="rounded-full bg-white shadow-lg "><title>facebook_fill</title><g id="facebook_fill" fill='none'><path d='M24 0v24H0V0zM12.593 23.258l-.011.002-.071.035-.02.004-.014-.004-.071-.035c-.01-.004-.019-.001-.024.005l-.004.01-.017.428.005.02.01.013.104.074.015.004.012-.004.104-.074.012-.016.004-.017-.017-.427c-.002-.01-.009-.017-.017-.018m.265-.113-.013.002-.185.093-.01.01-.003.011.018.43.005.012.008.007.201.093c.012.004.023 0 .029-.008l.004-.014-.034-.614c-.003-.012-.01-.02-.02-.022m-.715.002a.023.023 0 0 0-.027.006l-.006.014-.034.614c0 .012.007.02.017.024l.015-.002.201-.093.01-.008.004-.011.017-.43-.003-.012-.01-.01z'/><path fill='#007AFFFF' d='M13.5 21.888C18.311 21.164 22 17.013 22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 5.013 3.689 9.165 8.5 9.888V15H9a1.5 1.5 0 0 1 0-3h1.5v-2A3.5 3.5 0 0 1 14 6.5h.5a1.5 1.5 0 0 1 0 3H14a.5.5 0 0 0-.5.5v2H15a1.5 1.5 0 0 1 0 3h-1.5z'/></g></svg>
                        <h1 class="mr px-3 py-2">Facebook</h1>
                    </a>

                    <a class="mr-4 inline-flex items-center shadow-md rounded-md px-3 py-2 text-black hover:bg-fuchsia-400 hover:text-white" href="https://www.instagram.com/sdn_banjarejo/">
                        <svg width="35" height="35" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="rounded-md bg-white shadow-lg">
                            <path d="M13.8233 12.234C13.8071 12.5838 13.6922 12.9219 13.4918 13.209C13.2904 13.5057 13.0003 13.731 12.663 13.8525C12.3292 13.9886 11.9617 14.0192 11.61 13.9403C11.2565 13.8647 10.9337 13.6847 10.6838 13.4235C10.4362 13.151 10.2735 12.8121 10.2158 12.4485C10.1505 12.096 10.1947 11.7319 10.3425 11.4053C10.4866 11.0775 10.724 10.7995 11.025 10.6058C11.3325 10.4152 11.687 10.3139 12.0488 10.3133C12.5256 10.3394 12.9749 10.5449 13.3065 10.8885C13.4782 11.0669 13.6124 11.2778 13.7011 11.5089C13.7899 11.74 13.8314 11.9866 13.8233 12.234Z" fill="url(#paint0_linear_1120_18)"/>
                            <path d="M17.265 8.00251C17.1512 7.72147 16.9821 7.46616 16.7677 7.25177C16.5533 7.03738 16.298 6.86827 16.017 6.75451C15.734 6.6439 15.4336 6.58448 15.1297 6.57901H8.96775C8.35828 6.58158 7.7745 6.82483 7.34353 7.25579C6.91256 7.68676 6.66931 8.27054 6.66675 8.88001V15.159C6.66718 15.4621 6.72772 15.762 6.84487 16.0415C6.96202 16.321 7.13346 16.5745 7.34925 16.7873C7.78005 17.2145 8.36105 17.456 8.96775 17.46H15.1297C15.7365 17.456 16.3174 17.2145 16.7482 16.7873C16.964 16.5745 17.1355 16.321 17.2526 16.0415C17.3698 15.762 17.4303 15.4621 17.4307 15.159V8.88976C17.4286 8.58652 17.3725 8.28608 17.265 8.00251ZM14.8275 13.443C14.6807 13.808 14.4619 14.1396 14.184 14.418C13.9033 14.7005 13.5727 14.9286 13.209 15.0908C12.8413 15.2452 12.4475 15.3279 12.0487 15.3345C11.592 15.3435 11.139 15.2507 10.7225 15.0631C10.306 14.8754 9.93649 14.5974 9.64066 14.2493C9.34483 13.9012 9.1301 13.4917 9.01204 13.0504C8.89397 12.6091 8.87551 12.1471 8.958 11.6978C9.07814 11.1002 9.36554 10.5491 9.78675 10.1085C10.2167 9.67755 10.7637 9.38224 11.3599 9.25919C11.9561 9.13613 12.5753 9.19075 13.1407 9.41626C13.7083 9.64548 14.1917 10.0437 14.5252 10.557C14.8625 11.0518 15.0457 11.6353 15.0517 12.234C15.066 12.6485 14.9894 13.0612 14.8275 13.443ZM15.9 8.62651C15.9105 8.705 15.904 8.78482 15.8811 8.86063C15.8582 8.93643 15.8194 9.00647 15.7673 9.06605C15.7151 9.12562 15.6508 9.17337 15.5787 9.20608C15.5066 9.23879 15.4283 9.25571 15.3491 9.25571C15.2699 9.25571 15.1917 9.23879 15.1196 9.20608C15.0474 9.17337 14.9831 9.12562 14.931 9.06605C14.8788 9.00647 14.84 8.93643 14.8171 8.86063C14.7942 8.78482 14.7878 8.705 14.7982 8.62651C14.7878 8.54802 14.7942 8.4682 14.8171 8.39239C14.84 8.31659 14.8788 8.24655 14.931 8.18697C14.9831 8.1274 15.0474 8.07965 15.1196 8.04694C15.1917 8.01423 15.2699 7.99731 15.3491 7.99731C15.4283 7.99731 15.5066 8.01423 15.5787 8.04694C15.6508 8.07965 15.7151 8.1274 15.7673 8.18697C15.8194 8.24655 15.8582 8.31659 15.8811 8.39239C15.904 8.4682 15.9105 8.54802 15.9 8.62651Z" fill="url(#paint1_linear_1120_18)"/>
                            <path d="M16.875 2.25H7.125C5.83207 2.25 4.59209 2.76361 3.67785 3.67785C2.76361 4.59209 2.25 5.83207 2.25 7.125V16.875C2.25 18.1679 2.76361 19.4079 3.67785 20.3221C4.59209 21.2364 5.83207 21.75 7.125 21.75H16.875C18.1679 21.75 19.4079 21.2364 20.3221 20.3221C21.2364 19.4079 21.75 18.1679 21.75 16.875V7.125C21.75 5.83207 21.2364 4.59209 20.3221 3.67785C19.4079 2.76361 18.1679 2.25 16.875 2.25ZM18.942 15.0615C18.9512 15.5729 18.8549 16.0807 18.6593 16.5533C18.2637 17.5007 17.5104 18.2539 16.563 18.6495C16.0904 18.8452 15.5826 18.9414 15.0712 18.9323H9.075C8.56368 18.9406 8.05605 18.8444 7.58325 18.6495C7.1146 18.4523 6.68776 18.1678 6.3255 17.811C5.96665 17.4504 5.6818 17.0232 5.487 16.5533C5.28129 16.0828 5.17507 15.5749 5.175 15.0615V9.0165C5.16665 8.50518 5.26287 7.99755 5.45775 7.52475C5.8445 6.57362 6.59073 5.81338 7.5345 5.409C8.02498 5.21821 8.549 5.12866 9.075 5.14575H15.12C15.6268 5.14127 16.1294 5.23816 16.5982 5.43071C17.067 5.62327 17.4926 5.9076 17.85 6.267C18.2068 6.62926 18.4913 7.0561 18.6885 7.52475C18.8834 7.99755 18.9796 8.50518 18.9713 9.0165L18.942 15.0615Z" fill="url(#paint2_linear_1120_18)"/>
                            <defs>
                            <linearGradient id="paint0_linear_1120_18" x1="13.8243" y1="10.3133" x2="10.1548" y2="13.9513" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#F64F50"/>
                            <stop offset="1" stop-color="#FF6D42"/>
                            </linearGradient>
                            <linearGradient id="paint1_linear_1120_18" x1="17.4307" y1="6.57901" x2="6.68773" y2="17.4807" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#E0437C"/>
                            <stop offset="1" stop-color="#FFA14B"/>
                            </linearGradient>
                            <linearGradient id="paint2_linear_1120_18" x1="21.75" y1="1.86071" x2="2.25" y2="21.75" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#791CC9"/>
                            <stop offset="0.24887" stop-color="#C938A8"/>
                            <stop offset="0.546875" stop-color="#FE5340"/>
                            <stop offset="0.953125" stop-color="#FFD854"/>
                            </linearGradient>
                            </defs>
                            </svg>
                        <h1 class="mr px-3 py-2">Instagram</h1>
                    </a>

                    <a class="mr-4 inline-flex items-center shadow-md rounded-md px-3 py-2 text-black hover:bg-red-400 hover:text-white" href="https://www.youtube.com/@sdn_banjarejo">
                        <svg xmlns='http://www.w3.org/2000/svg' width='35' height='35' viewBox='0 0 24 24' class="rounded-md bg-white shadow-lg "><title>youtube_fill</title><g id="youtube_fill" fill='none' fill-rule='evenodd'><path d='M24 0v24H0V0zM12.593 23.258l-.011.002-.071.035-.02.004-.014-.004-.071-.035c-.01-.004-.019-.001-.024.005l-.004.01-.017.428.005.02.01.013.104.074.015.004.012-.004.104-.074.012-.016.004-.017-.017-.427c-.002-.01-.009-.017-.017-.018m.265-.113-.013.002-.185.093-.01.01-.003.011.018.43.005.012.008.007.201.093c.012.004.023 0 .029-.008l.004-.014-.034-.614c-.003-.012-.01-.02-.02-.022m-.715.002a.023.023 0 0 0-.027.006l-.006.014-.034.614c0 .012.007.02.017.024l.015-.002.201-.093.01-.008.004-.011.017-.43-.003-.012-.01-.01z'/><path fill='#FF6252FF' d='M12 4c.855 0 1.732.022 2.582.058l1.004.048.961.057.9.061.822.064a3.802 3.802 0 0 1 3.494 3.423l.04.425.075.91c.07.943.122 1.971.122 2.954 0 .983-.052 2.011-.122 2.954l-.075.91c-.013.146-.026.287-.04.425a3.802 3.802 0 0 1-3.495 3.423l-.82.063-.9.062-.962.057-1.004.048A61.59 61.59 0 0 1 12 20a61.59 61.59 0 0 1-2.582-.058l-1.004-.048-.961-.057-.9-.062-.822-.063a3.802 3.802 0 0 1-3.494-3.423l-.04-.425-.075-.91A40.662 40.662 0 0 1 2 12c0-.983.052-2.011.122-2.954l.075-.91c.013-.146.026-.287.04-.425A3.802 3.802 0 0 1 5.73 4.288l.821-.064.9-.061.962-.057 1.004-.048A61.676 61.676 0 0 1 12 4m-2 5.575v4.85c0 .462.5.75.9.52l4.2-2.425a.6.6 0 0 0 0-1.04l-4.2-2.424a.6.6 0 0 0-.9.52Z'/></g></svg>
                        <h1 class="mr px-3 py-2">Youtube</h1>
                    </a>

                    <a class="mr-4 inline-flex items-center shadow-md rounded-md px-3 py-2 text-black hover:bg-gray-400 hover:text-white" href="https://www.tiktok.com/@sdn_banjarejo">
                        <svg width="35" height="35" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="rounded-full bg-white shadow-lg">
                            <path d="M19.7745 6.32657C19.7697 6.19464 19.7166 6.06907 19.6252 5.97379C19.5338 5.87852 19.4106 5.82019 19.279 5.80992C18.4024 5.72824 17.5661 5.40321 16.8644 4.87152C16.3438 4.47689 15.9082 3.98115 15.5839 3.41404C15.2596 2.84693 15.0532 2.22015 14.9771 1.57131C14.9668 1.43969 14.9085 1.31644 14.8132 1.22505C14.7179 1.13367 14.5924 1.08052 14.4604 1.07574H12.3517C12.2829 1.07573 12.2149 1.08943 12.1515 1.11605C12.0881 1.14268 12.0307 1.18168 11.9826 1.23077C11.9344 1.27987 11.8966 1.33808 11.8713 1.40199C11.8459 1.46589 11.8336 1.53421 11.835 1.60294V14.1923C11.8291 14.7754 11.641 15.3421 11.2968 15.8129C10.9527 16.2837 10.4699 16.6351 9.91604 16.8177C9.57631 16.9292 9.21861 16.9757 8.86166 16.9547C8.41106 16.93 7.97322 16.7962 7.58586 16.5646C7.03091 16.2361 6.6056 15.7269 6.38105 15.1224C6.15649 14.5178 6.14632 13.8545 6.35223 13.2433C6.56342 12.6358 6.97482 12.1181 7.51892 11.7752C8.06302 11.4323 8.70751 11.2844 9.34668 11.356C9.41743 11.3634 9.48896 11.3558 9.55658 11.3337C9.62421 11.3116 9.68641 11.2755 9.73914 11.2278C9.79187 11.18 9.83394 11.1217 9.86261 11.0565C9.89127 10.9914 9.90589 10.921 9.9055 10.8499V8.38262C9.9055 8.38262 9.00927 8.31935 8.7035 8.31935C7.91938 8.3157 7.14431 8.48701 6.43477 8.8208C5.72523 9.1546 5.09909 9.64247 4.60196 10.2489C3.94086 10.9606 3.46949 11.8272 3.23126 12.7688C2.9868 13.7574 3.00408 14.7927 3.28141 15.7726C3.55874 16.7525 4.08654 17.6432 4.81283 18.3571C4.98684 18.5324 5.17363 18.6945 5.37165 18.8421C6.41359 19.6458 7.6934 20.0798 9.00928 20.0757C9.30914 20.0759 9.60864 20.0548 9.9055 20.0125C11.1571 19.8269 12.3169 19.247 13.2163 18.3571C13.7661 17.8176 14.2039 17.1747 14.5042 16.4653C14.8046 15.756 14.9617 14.9942 14.9665 14.2239V7.28606C16.1839 8.22553 17.6505 8.78651 19.1841 8.89926C19.2554 8.90366 19.3269 8.89319 19.394 8.86854C19.4611 8.84388 19.5224 8.80556 19.5739 8.75601C19.6255 8.70646 19.6661 8.64675 19.6934 8.58066C19.7207 8.51457 19.7339 8.44355 19.7323 8.37207V6.32657H19.7745Z" fill="#74F0EF"/>
                            <path d="M21.4346 7.75082C21.4299 7.61889 21.3767 7.49333 21.2853 7.39805C21.1939 7.30277 21.0707 7.24445 20.9391 7.23418C20.0625 7.15249 19.2262 6.82747 18.5246 6.29578C18.0039 5.90115 17.5684 5.4054 17.244 4.8383C16.9197 4.27119 16.7133 3.64441 16.6372 2.99556C16.6269 2.86394 16.5686 2.7407 16.4733 2.64931C16.3781 2.55792 16.2525 2.50478 16.1206 2.5H14.0118C13.943 2.49999 13.875 2.51369 13.8116 2.54031C13.7482 2.56693 13.6908 2.60593 13.6427 2.65503C13.5946 2.70413 13.5567 2.76234 13.5314 2.82624C13.5061 2.89015 13.4937 2.95846 13.4951 3.02719V15.6165C13.4893 16.1997 13.3011 16.7663 12.957 17.2372C12.6128 17.708 12.13 18.0593 11.5762 18.2419C11.2364 18.3534 10.8787 18.3999 10.5218 18.379C10.0712 18.3543 9.63334 18.2204 9.24599 17.9889C8.69104 17.6603 8.26572 17.1512 8.04117 16.5466C7.81662 15.9421 7.80645 15.2787 8.01236 14.6676C8.22355 14.0601 8.63494 13.5424 9.17904 13.1995C9.72314 12.8565 10.3676 12.7087 11.0068 12.7802C11.0776 12.7876 11.1491 12.7801 11.2167 12.758C11.2843 12.7359 11.3465 12.6998 11.3993 12.652C11.452 12.6043 11.4941 12.5459 11.5227 12.4808C11.5514 12.4157 11.566 12.3453 11.5656 12.2741V9.80687C11.5656 9.80687 10.6694 9.74361 10.3636 9.74361C9.5795 9.73995 8.80444 9.91127 8.0949 10.2451C7.38536 10.5789 6.75921 11.0667 6.26208 11.6731C5.60099 12.3848 5.12961 13.2514 4.89139 14.1931C4.64692 15.1817 4.66421 16.2169 4.94153 17.1968C5.21886 18.1767 5.74667 19.0675 6.47296 19.7813C6.64697 19.9566 6.83375 20.1187 7.03178 20.2663C8.07371 21.07 9.35352 21.5041 10.6694 21.5C10.9693 21.5002 11.2688 21.479 11.5656 21.4367C12.8172 21.2512 13.977 20.6713 14.8764 19.7813C15.4263 19.2418 15.864 18.5989 16.1644 17.8896C16.4648 17.1802 16.6219 16.4185 16.6267 15.6482V8.71031C17.844 9.64979 19.3106 10.2108 20.8442 10.3235C20.9156 10.3279 20.987 10.3174 21.0541 10.2928C21.1213 10.2681 21.1825 10.2298 21.234 10.1803C21.2856 10.1307 21.3263 10.071 21.3535 10.0049C21.3808 9.93883 21.3941 9.8678 21.3925 9.79633V7.75082H21.4346Z" fill="#ED515D"/>
                            <path d="M20.3573 7.75082C20.3526 7.61889 20.2994 7.49333 20.208 7.39805C20.1166 7.30277 19.9934 7.24445 19.8618 7.23418C18.9852 7.15249 18.1489 6.82747 17.4472 6.29578C16.9266 5.90115 16.4911 5.4054 16.1667 4.8383C15.8424 4.27119 15.636 3.64441 15.5599 2.99556C15.5496 2.86394 15.4913 2.7407 15.396 2.64931C15.3007 2.55792 15.1752 2.50478 15.0432 2.5H12.9345C12.8657 2.49999 12.7977 2.51369 12.7343 2.54031C12.6709 2.56693 12.6135 2.60593 12.5654 2.65503C12.5173 2.70413 12.4794 2.76234 12.4541 2.82624C12.4288 2.89015 12.4164 2.95846 12.4178 3.02719V15.6165C12.412 16.1997 12.2238 16.7663 11.8796 17.2372C11.5355 17.708 11.0527 18.0593 10.4989 18.2419C10.1591 18.3534 9.80142 18.3999 9.44447 18.379C8.99387 18.3543 8.55603 18.2204 8.16867 17.9889C7.61372 17.6603 7.18841 17.1512 6.96386 16.5466C6.7393 15.9421 6.72913 15.2787 6.93504 14.6676C7.14623 14.0601 7.55763 13.5424 8.10173 13.1995C8.64582 12.8565 9.29032 12.7087 9.92949 12.7802C10.0002 12.7876 10.0718 12.7801 10.1394 12.758C10.207 12.7359 10.2692 12.6998 10.322 12.652C10.3747 12.6043 10.4168 12.5459 10.4454 12.4808C10.4741 12.4157 10.4887 12.3453 10.4883 12.2741V9.80687C10.4883 9.80687 9.59208 9.74361 9.28631 9.74361C8.50219 9.73995 7.72712 9.91127 7.01758 10.2451C6.30804 10.5789 5.6819 11.0667 5.18477 11.6731C4.52367 12.3848 4.0523 13.2514 3.81407 14.1931C3.5696 15.1817 3.58689 16.2169 3.86422 17.1968C4.14155 18.1767 4.66935 19.0675 5.39564 19.7813C5.56965 19.9566 5.75644 20.1187 5.95446 20.2663C6.9964 21.07 8.27621 21.5041 9.59208 21.5C9.89195 21.5002 10.1914 21.479 10.4883 21.4367C11.7399 21.2512 12.8997 20.6713 13.7991 19.7813C14.3489 19.2418 14.7867 18.5989 15.087 17.8896C15.3874 17.1802 15.5446 16.4185 15.5493 15.6482V8.71031C16.7667 9.64979 18.2333 10.2108 19.7669 10.3235C19.8382 10.3279 19.9097 10.3174 19.9768 10.2928C20.0439 10.2681 20.1052 10.2298 20.1567 10.1803C20.2083 10.1307 20.249 10.071 20.2762 10.0049C20.3035 9.93883 20.3167 9.8678 20.3152 9.79633V7.75082H20.3573Z" fill="black"/>
                            </svg>
                        <h1 class="mr px-3 py-2">Tiktok</h1>
                    </a>
                        <a class="mr-4 inline-flex items-center shadow-md rounded-md px-3 py-2 text-black hover:bg-gradient-to-r from-red-500 to-white hover:text-white" href="https://www.tiktok.com/@sdn_banjarejo">
                        <svg xmlns='http://www.w3.org/2000/svg' width='35' height='35' viewBox='0 0 24 24'class="rounded-md bg-white shadow-lg "><title>mail_line</title><g id="mail_line" fill='none'><path d='M24 0v24H0V0zM12.593 23.258l-.011.002-.071.035-.02.004-.014-.004-.071-.035c-.01-.004-.019-.001-.024.005l-.004.01-.017.428.005.02.01.013.104.074.015.004.012-.004.104-.074.012-.016.004-.017-.017-.427c-.002-.01-.009-.017-.017-.018m.265-.113-.013.002-.185.093-.01.01-.003.011.018.43.005.012.008.007.201.093c.012.004.023 0 .029-.008l.004-.014-.034-.614c-.003-.012-.01-.02-.02-.022m-.715.002a.023.023 0 0 0-.027.006l-.006.014-.034.614c0 .012.007.02.017.024l.015-.002.201-.093.01-.008.004-.011.017-.43-.003-.012-.01-.01z'/><path fill='#09244BFF' d='M20 4a2 2 0 0 1 1.995 1.85L22 6v12a2 2 0 0 1-1.85 1.995L20 20H4a2 2 0 0 1-1.995-1.85L2 18V6a2 2 0 0 1 1.85-1.995L4 4zm0 3.414-6.94 6.94a1.5 1.5 0 0 1-2.12 0L4 7.414V18h16zM18.586 6H5.414L12 12.586z'/></g></svg>
                        <h1 class="mr px-3 py-2">Email</h1>
                    </a>
                    <a class="mr-4 inline-flex items-center shadow-md rounded-md px-3 py-2 text-black hover:bg-green-400 hover:text-white" href="https://wa.me/62085777697128">
                        <svg width="35" height="35" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="rounded-full bg-white shadow-lg">
                            <path d="M13.79 2.63999L13.22 2.55999C11.5069 2.31265 9.75885 2.55734 8.17954 3.26555C6.60023 3.97376 5.25476 5.11631 4.3 6.55999C3.28416 7.93997 2.67859 9.57873 2.55298 11.2877C2.42737 12.9966 2.78684 14.7063 3.59 16.22C3.6722 16.3717 3.72337 16.5382 3.74054 16.7099C3.75771 16.8816 3.74053 17.055 3.69 17.22C3.28 18.63 2.9 20.05 2.5 21.54L3 21.39C4.35 21.03 5.7 20.67 7.05 20.34C7.33494 20.2807 7.63112 20.3086 7.9 20.42C9.1112 21.0111 10.4348 21.3363 11.782 21.3738C13.1293 21.4112 14.4689 21.1601 15.7111 20.6372C16.9533 20.1143 18.0692 19.3318 18.9841 18.3421C19.899 17.3524 20.5915 16.1785 21.0153 14.8991C21.4392 13.6198 21.5844 12.2645 21.4414 10.9244C21.2983 9.58426 20.8703 8.29023 20.1859 7.12914C19.5016 5.96806 18.5769 4.96678 17.4737 4.19251C16.3706 3.41824 15.1146 2.88889 13.79 2.63999ZM16.31 15.76C15.9466 16.0854 15.5034 16.3086 15.0256 16.407C14.5478 16.5054 14.0524 16.4753 13.59 16.32C11.4946 15.73 9.67661 14.4152 8.46 12.61C7.99529 11.9715 7.6217 11.2715 7.35 10.53C7.20285 10.0998 7.17632 9.63746 7.27327 9.19322C7.37023 8.74899 7.58698 8.33978 7.9 8.00999C8.05239 7.8155 8.25981 7.67142 8.49526 7.5965C8.7307 7.52159 8.98325 7.51932 9.22 7.58999C9.42 7.63999 9.56 7.92999 9.74 8.14999C9.88636 8.56288 10.0566 8.9669 10.25 9.35999C10.3964 9.56049 10.4576 9.81079 10.4201 10.0562C10.3826 10.3017 10.2496 10.5223 10.05 10.67C9.6 11.07 9.67 11.4 9.99 11.85C10.6974 12.8692 11.6736 13.6722 12.81 14.17C13.13 14.31 13.37 14.34 13.58 14.01C13.67 13.88 13.79 13.77 13.89 13.65C14.47 12.92 14.29 12.93 15.21 13.33C15.5031 13.4532 15.7871 13.5969 16.06 13.76C16.33 13.92 16.74 14.09 16.8 14.33C16.8577 14.5904 16.8425 14.8616 16.7561 15.1139C16.6696 15.3662 16.5153 15.5897 16.31 15.76Z" fill="url(#paint0_linear_1034_57)"/>
                            <path d="M13.8153 2.53089C18.5508 3.4416 21.7688 7.54354 21.6072 12.4203C21.4582 16.9163 17.82 20.8364 13.2383 21.3983C11.3696 21.6274 9.57155 21.334 7.8574 20.5281C7.61219 20.4128 7.27105 20.3821 7.00649 20.4455C5.63491 20.7743 4.27396 21.147 2.90877 21.5023C2.75916 21.5413 2.60607 21.5669 2.38704 21.6129C2.79686 20.1129 3.17971 18.674 3.58821 17.2425C3.69194 16.8789 3.66332 16.5896 3.49606 16.2343C1.90269 12.8497 2.04146 9.52944 4.2121 6.46031C6.38927 3.38196 9.46084 2.06859 13.2387 2.45195C13.4166 2.47 13.5937 2.49536 13.8153 2.53089ZM19.7993 13.666C20.0711 12.5563 20.1025 11.4319 19.838 10.3282C19.0407 7.00127 16.9705 4.84492 13.6053 4.16342C10.3028 3.49464 7.53079 4.60868 5.57486 7.33803C3.61503 10.0728 3.57275 13.0049 5.18968 15.9425C5.39758 16.3202 5.45342 16.6298 5.32693 17.0336C5.09626 17.7701 4.91641 18.5224 4.69114 19.358C5.62677 19.1156 6.44767 18.8822 7.27993 18.7004C7.5151 18.649 7.82387 18.6759 8.03072 18.7898C12.7817 21.4056 18.4291 18.9926 19.7993 13.666Z" fill="white"/>
                            <defs>
                            <linearGradient id="paint0_linear_1034_57" x1="11.9974" y1="2.46628" x2="11.9974" y2="21.54" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#57D163"/>
                            <stop offset="1" stop-color="#23B33A"/>
                            </linearGradient>
                            </defs>
                        </svg>                            
                        <h1 class="mr px-3 py-2">Whatsapp</h1>
                    </a>
                </div>
              </div>
            </div>
          </section>
    </main>

    <footer class="bg-gray-100">
      <div class="relative mx-auto max-w-(--breakpoint-xl) px-4 py-16 sm:px-6 lg:px-8 lg:pt-24">
        <div class="absolute end-4 top-4 sm:end-6 sm:top-6 lg:end-8 lg:top-8">
          <a class="inline-block rounded-full bg-teal-600 p-2 text-white shadow-sm transition hover:bg-teal-500 sm:p-3 lg:p-4" href="#">
            <span class="sr-only">Back to top</span>

            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
          </a>
        </div>

        <div class="lg:flex lg:items-end lg:justify-between">
          <div>
            <div class="flex justify-center text-teal-600 lg:justify-start">
                <a href="#">
                    <img src="{{ asset('images/LogoSD.png') }}" alt="logo" class="h-20 w-18 shadow-lg rounded-2xl">
                </a>
                <h1 class="text-3xl font-semibold ml-2 items-center mt-6">SDN BANJAREJO</h1>
            </div>

            <p class="mx-auto mt-6 max-w-md text-center leading-relaxed text-gray-500 lg:text-left">Terimakasih atas kunjungan anda di situs website SDN Banjarejo</p>
          </div>

          <ul class="mt-12 flex flex-wrap justify-center gap-6 md:gap-8 lg:mt-0 lg:justify-end lg:gap-12">
            <li>
              <a class="text-gray-700 transition hover:text-gray-700/75" href="#tentang"> Tentang </a>
            </li>
            <li>
              <a class="text-gray-700 transition hover:text-gray-700/75" href="#visi-misi"> Visi & Misi </a>
            </li>
            <li>
              <a class="text-gray-700 transition hover:text-gray-700/75" href="#ekstra"> Ekstrakulikuler </a>
            </li>
            <li>
              <a class="text-gray-700 transition hover:text-gray-700/75" href="#prestasi"> Prestasi </a>
            </li>
            <li>
              <a class="text-gray-700 transition hover:text-gray-700/75" href="#galeri"> Galeri </a>
            </li>
            <li>
              <a class="text-gray-700 transition hover:text-gray-700/75" href="#kontak"> Kontak </a>
            </li>
          </ul>
        </div>

        <p class="mt-12 text-center text-sm text-gray-500 lg:text-right">Copyright &copy; <a href="https://sdn-banjarejo.sch.id" class="text-gray-700 underline">SDN Banjarejo</a> 2025. All rights reserved.</p>
      </div>
    </footer>
    <script src="https://unpkg.com/typed.js@2.1.0/dist/typed.umd.js"></script>

   <script>
      var typed = new Typed('.auto_type', {
        strings: ['Situs', 'Website'],
        typeSpeed: 100,
        typebackSpeed: 50,
        loop: true
      });
  </script>
  </body>
</html>
