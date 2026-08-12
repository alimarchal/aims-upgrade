<div>
    @if (session()->has('message'))
    <div class="bg-green-500 text-white font-bold py-2 px-4 rounded">
        {{ session('message') }}
    </div>
    @endif

    @if (session()->has('success'))
    <div class="bg-green-500 text-white font-bold py-2 px-4 rounded">
        {{ session('success') }}
    </div>
    @endif

    @if (session()->has('error'))
    <div class="bg-red-500 text-white font-bold py-2 px-4 rounded">
        {{ session('error') }}
    </div>
    @endif
</div>