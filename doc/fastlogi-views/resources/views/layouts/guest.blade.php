@extends('layouts.base')

@section('body_class', 'bg-surface font-body-md text-on-surface antialiased min-h-screen flex flex-col justify-between')

@section('body')
<header class="w-full py-space-md px-margin-desktop flex items-center justify-between"><div class="flex items-center gap-space-sm"><img alt="FastLogi Logo" class="h-7 w-auto object-contain" src="https://lh3.googleusercontent.com/aida/AEtjO1Wvdd5_rBH0xSqpbopsGvcLkdZvn1LynW-D5SKLpKi9M9_gYx6GOgGxpa2exaZsR3i0er3SJisxTK05nkRxJ3ffQfaz6cYfzx5HS0_GvlM5bj5_sRVt7WJfCS8Rao1QncGiiGpqsznvXbZEFR_aMB3FpnrYyHIfzAjuF0LrYSky7jp2Hf9H2BAewUehrBjkZpcj9ASD1qy031NXwrxiFoowv434IWE1e5hS1jzMxxEKOZqI57DMW80RrQeT"/><span class="font-headline-sm text-headline-sm text-on-surface uppercase tracking-wider font-bold">FastLogi</span></div><span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-widest">Colombia Network</span></header>
<main class="flex-1 flex items-center justify-center w-full px-margin">
@yield('content')
</main>
<footer class="w-full py-space-md px-margin-desktop flex items-center justify-center"><span class="font-label-md text-label-md text-on-surface-variant tracking-wider uppercase">Nexlab Studio</span></footer>
@endsection
