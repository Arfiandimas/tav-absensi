<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Data Absensi') }}
        </h2>
    </x-slot>

    <div class="py-2">
        @if (session('success'))
            <div class="mb-4 rounded-lg bg-green-100 px-4 py-3 text-green-700">
                {{ session('success') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-100 px-4 py-3 text-red-700">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="mx-auto max-w-7xl space-y-10 sm:px-6 lg:px-8">
            
            {{-- Untuk tamu (belum login) --}}
            @if (!session('is_logged_in'))
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <p>
                            Please <a href="{{ route('login') }}" class="text-blue-500">login</a>
                        </p>
                    </div>
                </div>
            @endif

            @if (session('is_logged_in'))
                <div class="container mx-auto p-4">
                    
                    <form method="GET" action="{{ url()->current() }}" 
                        class="mb-6 bg-white shadow rounded-2xl p-4 md:p-6">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

                            {{-- Filter Departemen --}}
                            <div>
                                <label for="departemen_id" class="block text-sm font-medium text-gray-700 mb-1">Departemen</label>
                                <select name="departemen_id" id="departemen_id"
                                    class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                    <option value="">Semua Departemen</option>
                                    @foreach ($departements as $departemen)
                                        <option value="{{ $departemen->id }}" {{ request('departemen_id') == $departemen->id ? 'selected' : '' }}>
                                            {{ $departemen->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Filter User --}}
                            <div>
                                <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1">User</label>
                                <select name="user_id" id="user_id"
                                    class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                    <option value="">Semua User</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->nama_lengkap}} 
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Filter Tanggal Mulai --}}
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                                <input type="date" name="start_date" id="start_date"
                                    value="{{ request('start_date', $start) }}"
                                    class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                            </div>

                            {{-- Filter Tanggal Selesai --}}
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                                <input type="date" name="end_date" id="end_date"
                                    value="{{ request('end_date', $end) }}"
                                    class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                            </div>
                        </div>

                        {{-- Tombol Aksi --}}
                        <div class="flex flex-wrap items-center justify-between mt-6">
                            <div class="flex gap-3">
                                <button type="submit"
                                    class="px-5 py-2 bg-blue-600 text-white rounded-xl shadow hover:bg-blue-700 transition">
                                    Filter
                                </button>

                                <a href="{{ url()->current() }}"
                                    class="px-5 py-2 bg-gray-200 text-gray-800 rounded-xl shadow hover:bg-gray-300 transition">
                                    Reset
                                </a>
                            </div>

                            <div class="flex gap-3">
                                <a href="javascript:void(0)"
                                    id="btnTambah"
                                    class="px-5 py-[11px] bg-orange-400 text-white rounded-xl shadow hover:bg-orange-700 transition">
                                    Tambah
                                </a>
                                <a href="javascript:void(0)" id="btn-export"
                                    class="px-5 py-[11px] bg-green-600 text-white rounded-xl shadow hover:bg-green-700 transition">
                                    Export
                                </a>
                            </div>
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full border border-gray-300 rounded shadow">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-2 border">#</th>
                                    <th class="px-4 py-2 border">Nama</th>
                                    <th class="px-4 py-2 border">Tanggal</th>
                                    <th class="px-4 py-2 border">Clock In</th>
                                    <th class="px-4 py-2 border">Lokasi In</th>
                                    <th class="px-4 py-2 border">Clock In Siang</th>
                                    <th class="px-4 py-2 border">Lokasi In Siang</th>
                                    <th class="px-4 py-2 border">Clock Out</th>
                                    <th class="px-4 py-2 border">Lokasi Out</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($results as $index => $item)
                                    <tr class="border-t">
                                        <td class="px-4 py-2 border">{{ $index + 1 }}</td>
                                        <td class="px-4 py-2 border">{{ $item->full_name }}</td>
                                        <td class="px-4 py-2 border">{{ $item->tanggal }}</td>
                                        <td class="px-4 py-2 border">{{ $item->clock_in_time ? date('H:i', strtotime($item->clock_in_time)) : '-' }}</td>
                                        <td class="px-4 py-2 border">
                                            @if($item->clock_in_mlat && $item->clock_in_mlong)
                                                <a href="https://maps.google.com/?q={{ $item->clock_in_mlat }},{{ $item->clock_in_mlong }}" target="_blank" class="text-blue-600 underline">
                                                    Lihat Lokasi
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 border">{{ $item->clock_in_siang_time ? date('H:i', strtotime($item->clock_in_siang_time)) : '-' }}</td>
                                        <td class="px-4 py-2 border">
                                            @if($item->clock_in_siang_mlat && $item->clock_in_siang_mlong)
                                                <a href="https://maps.google.com/?q={{ $item->clock_in_siang_mlat }},{{ $item->clock_in_siang_mlong }}" target="_blank" class="text-blue-600 underline">
                                                    Lihat Lokasi
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 border">{{ $item->clock_out_time ? date('H:i', strtotime($item->clock_out_time)) : '-' }}</td>
                                        <td class="px-4 py-2 border">
                                            @if($item->clock_out_mlat && $item->clock_out_mlong)
                                                <a href="https://maps.google.com/?q={{ $item->clock_out_mlat }},{{ $item->clock_out_mlong }}" target="_blank" class="text-blue-600 underline">
                                                    Lihat Lokasi
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="mt-5">
                            {{ $results->links() }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal Create --}}
    <div
        id="modalTambah"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">

        <div class="bg-white w-full max-w-xl rounded-lg p-6">
            <h2 class="text-lg font-semibold mb-4">Tambah Absensi</h2>

            <form method="POST" action="{{ route('absensi.store') }}">
                @csrf

                {{-- Departemen --}}
                <div>
                    <label for="form_departemen_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Departemen
                    </label>
                    <select
                        name="departemen_id"
                        id="form_departemen_id"
                        required
                        class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                        <option value="">Pilih Departemen</option>
                        @foreach ($departements as $departemen)
                            <option value="{{ $departemen->id }}">
                                {{ $departemen->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- User --}}
                <div class="mt-3">
                    <label for="form_user_id" class="block text-sm font-medium text-gray-700 mb-1">
                        User
                    </label>
                    <select
                        name="user_id"
                        id="form_user_id"
                        required
                        class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                        <option value="">Pilih User</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">
                                {{ $user->nama_lengkap }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Type --}}
                <div class="mt-3">
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-1">
                        Type
                    </label>
                    <select
                        name="type"
                        id="type"
                        required
                        class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                        <option value="">Pilih Type</option>
                        <option value="Clock In">Clock In</option>
                        <option value="Clock Out">Clock Out</option>
                    </select>
                </div>

                {{-- Tanggal & Jam --}}
                <div class="mt-3">
                    <label for="tanggal" class="block text-sm font-medium text-gray-700 mb-1">
                        Tanggal & Jam
                    </label>
                    <input
                        type="datetime-local"
                        name="tanggal"
                        id="tanggal"
                        required
                        class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                </div>

                {{-- Buttons --}}
                <div class="mt-6 flex justify-end gap-2">
                    <button
                        type="button"
                        id="btnClose"
                        class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
                        Batal
                    </button>

                    <button
                        type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if (session('is_logged_in'))
        @section('script')
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script>
                $(document).ready(function () {
                    function handleDepartemenChange(departemenSelector, userSelector) {
                        $(departemenSelector).on('change', function () {
                            const departemenId = $(this).val();

                            if (!departemenId) {
                                $(userSelector).html(`
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->nama_lengkap }}</option>
                                    @endforeach
                                `).prop('disabled', false);
                                return;
                            }

                            $(userSelector)
                                .html('<option value="">Loading...</option>')
                                .prop('disabled', true);

                            $.ajax({
                                url: '{{ route("users.byDepartemen") }}',
                                type: 'GET',
                                data: { departemen_id: departemenId },
                                success: function (response) {
                                    let options = '<option value="">Semua User</option>';
                                    if (departemenSelector == '#form_departemen_id' || userSelector == 'form_user_id') {
                                        options = '<option value="">Pilih User</option>';
                                    }
                                    

                                    response.forEach(user => {
                                        options += `<option value="${user.id}">${user.nama_lengkap}</option>`;
                                    });

                                    $(userSelector)
                                        .html(options)
                                        .prop('disabled', false);
                                },
                                error: function () {
                                    $(userSelector)
                                        .html('<option value="">Error memuat user</option>')
                                        .prop('disabled', true);
                                }
                            });
                        });
                    }

                    handleDepartemenChange('#departemen_id', '#user_id');
                    handleDepartemenChange('#form_departemen_id', '#form_user_id');

                    // Export tetap sama
                    $('#btn-export').on('click', function () {
                        const params = $.param({
                            departemen_id: $('#departemen_id').val(),
                            user_id: $('#user_id').val(),
                            start_date: $('#start_date').val(),
                            end_date: $('#end_date').val()
                        });

                        window.location.href = '{{ route("export.excel") }}?' + params;
                    });

                });

                // HANDLE MODAL
                const modal = document.getElementById('modalTambah');
                const btnTambah = document.getElementById('btnTambah');
                const btnClose = document.getElementById('btnClose');
                btnTambah.addEventListener('click', () => {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                });
                btnClose.addEventListener('click', () => {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                });
            </script>
        @endsection
    @endif
</x-app-layout>