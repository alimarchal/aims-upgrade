<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight inline-block">
            {{ __('Permissions') }}
        </h2>



        <div class="flex justify-center items-center float-right">
            <a href="{{ route('permissions.create') }}" class="flex items-center px-4 py-2 text-gray-600 bg-white border rounded-lg focus:outline-none hover:bg-gray-100 transition-colors duration-200 transform dark:text-gray-200 dark:border-gray-200  dark:hover:bg-gray-700 ml-2" title="Members List">
                <img src="https://img.icons8.com/?size=512&id=f3o1AGoVZ2Un&format=png"  class="h-5 w-5"  alt="">
                <span class="hidden md:inline-block ml-2">Create New Permissions</span>
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-2 lg:p-2 bg-white dark:bg-gray-800 dark:bg-gradient-to-bl dark:from-gray-700/50 dark:via-transparent border-b border-gray-200 dark:border-gray-700">


                    <table class="w-full text-sm border-collapse border border-slate-400 text-left text-black dark:text-gray-400">
                        <thead class="text-black uppercase bg-gray-50 dark:bg-gray-700 ">
                        <tr>
                            <th scope="col" class="px-1 py-3 border border-black " width="33.34%">
                                Permission Name
                            </th>
                            <th scope="col" class="px-1 py-3 border border-black  text-center" width="33.33%">
                                Actions
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($permissions as $permission)
                            <tr class="bg-white  border-b dark:bg-gray-800 dark:border-black text-left">
                                <th class="border px-2 py-2  border-black font-medium text-black dark:text-white">
                                    {{ $permission->name }}
                                </th>
                                <th class="border px-2 py-2 border-black font-medium text-black dark:text-white text-center">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('permissions.edit', $permission) }}" class="text-blue-500 hover:text-blue-700">Edit</a>
                                        <form action="{{ route('permissions.destroy', $permission) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this permission?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700">Delete</button>
                                        </form>
                                    </div>
                                </th>
                            </tr>
                        @endforeach

                        </tbody>
                    </table>



                </div>
            </div>
        </div>
    </div>
</x-app-layout>

