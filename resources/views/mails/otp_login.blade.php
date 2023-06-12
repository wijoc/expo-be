@extends('layouts/mail')

@section('content')
  <div class="container flex flex-col items-center justify-center h-full py-10 backdrop-blur-lg">
    <div class="h-full w-[90%] py-2 px-3 rounded bg-white border-[1px] border-gray-200 text-sm text-justify flex flex-col gap-3">
      <p>Halo teman UMKM,</p>
      <p>Login OTP Anda adalah: </p>
      <div class="flex justify-center w-full py-3 text-4xl font-bold tracking-[1rem] text-tertiary">
        {{ $otp }}
      </div>
      <p>OTP ini hanya berlaku satu kali untuk setiap proses login. OTP hanya berlaku selama 5 menit, sampai dengan
        <span class="font-medium underline">{{ $valid_until }} - {{ $valid_tz }} timezone</span>.</p>
      <p class="font-medium">Mohon untuk tidak membagikan OTP ini kepada pihak lain!</p>
      <p> Jika Anda tidak melakukan proses login, Anda dapat menghubungi Customer Service kami
        <a href="#belumada" target="_blank" class="cursor-pointer text-tertiary hover:underline">di sini</a>.
      </p>
      <div class="flex flex-col items-center gap-3">
        <h1 class="text-xs font-light text-center">Atau hubungi kami melalui</h1>
        <div class="flex w-1/2 flex-nowrap justify-evenly">
          <a target="_blank" href="#" class="p-2 bg-gray-200 rounded-full">
            <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512" class="text-black fill-current w-7 h-7">
              <!--! Font Awesome Free 6.4.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. -->
              <path d="M504 256C504 119 393 8 256 8S8 119 8 256c0 123.78 90.69 226.38 209.25 245V327.69h-63V256h63v-54.64c0-62.15 37-96.48 93.67-96.48 27.14 0 55.52 4.84 55.52 4.84v61h-31.28c-30.8 0-40.41 19.12-40.41 38.73V256h68.78l-11 71.69h-57.78V501C413.31 482.38 504 379.78 504 256z"/>
            </svg>
          </a>
          <a target="_blank" href="#" class="p-2 bg-gray-200 rounded-full">
            <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 448 512" class="text-black fill-current w-7 h-7">
              <!--! Font Awesome Free 6.4.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. -->
              <path d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z"/>
            </svg>
          </a>
          <a target="_blank" href="mailto:umkmvirtualexpo@gmail.com" class="p-2 bg-gray-200 rounded-full">
            <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512" class="text-black fill-current w-7 h-7">
              <!--! Font Awesome Free 6.4.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. -->
              <path d="M48 64C21.5 64 0 85.5 0 112c0 15.1 7.1 29.3 19.2 38.4L236.8 313.6c11.4 8.5 27 8.5 38.4 0L492.8 150.4c12.1-9.1 19.2-23.3 19.2-38.4c0-26.5-21.5-48-48-48H48zM0 176V384c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V176L294.4 339.2c-22.8 17.1-54 17.1-76.8 0L0 176z"/>
            </svg>
          </a>
          <a target="_blank" href="https://mail.google.com/mail/?view=cm&to=umkmvirtualexpo@gmail.com" class="p-2 bg-gray-200 rounded-full">
            <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 488 512" class="text-black fill-current w-7 h-7">
              <!--! Font Awesome Free 6.4.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. -->
              <path d="M488 261.8C488 403.3 391.1 504 248 504 110.8 504 0 393.2 0 256S110.8 8 248 8c66.8 0 123 24.5 166.3 64.9l-67.5 64.9C258.5 52.6 94.3 116.6 94.3 256c0 86.5 69.1 156.6 153.7 156.6 98.2 0 135-70.4 140.8-106.9H248v-85.3h236.1c2.3 12.7 3.9 24.9 3.9 41.4z"/>
            </svg>
          </a>
        </div>
      </div>
      <hr class="bg-black">
      <div class="text-xs font-light tracking-wider text-center">
        <p>Ini adalah email otomatis. Mohon untuk tidak membalas email ini.</p>
        <a href="" target="_blank">umkmvirtualexpo.domain</a>
      </div>
    </div>
  </div>
@endsection