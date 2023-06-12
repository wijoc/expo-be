<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Mail</title>

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: '#84dcc6',
            'lighter-primary': '#a5ffd6',
            'darker-primary': '#6eaf9f',
            secondary: '#ff686b',
            'lighter-secondary': '#ffa69e',
            'darker-secondary': '#cc494b',
            tertiary: '#FF5666',
            quartyary: '#d0e2ff',
            'space-black': '#22223B'
          },
          fontFamily: {
            inter: ['Inter']
          },
          letterSpacing: {
            xtrawide: '0.15em',
            superwide: '0.2em'
          }
        }
      }
    }
  </script>
</head>
<body class="flex flex-col items-center justify-center py-2 bg-gray-100 font-inter">
  <div class="flex items-center justify-center w-full">
    <img src="{{ asset('img/Logo.png') }}" alt="UMKM Virtual Expo logo" class="w-7 h-7">
    <span id="brand" class="ml-1 text-lg font-semibold text-black uppercase">UMKM Expo</span>
  </div>
  @yield('content')
  <div class="text-xs font-light tracking-wider text-center">
    Check this project's repo in <a href="https://github.com/wijoc" target="_blank" class="cursor-pointer">@wijoc</a>
  </div>
</body>
</html>