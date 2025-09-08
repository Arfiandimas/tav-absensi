<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Data Absensi') }}
        </h2>
    </x-slot>

    <div class="py-2">
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
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
                            {{-- Filter Office --}}
                            <div>
                                <label for="office_id" class="block text-sm font-medium text-gray-700 mb-1">Office</label>
                                <select name="office_id" id="office_id"
                                    class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                    <option value="">Semua Office</option>
                                    @foreach ($offices as $office)
                                        <option value="{{ $office->id }}" {{ request('office_id') == $office->id ? 'selected' : '' }}>
                                            {{ $office->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

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
                                            {{ $user->first_name }} {{ $user->last_name }}
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

                            <div>
                                <a href="javascript:void(0)" id="btn-export"
                                    class="px-5 py-2 bg-green-600 text-white rounded-xl shadow hover:bg-green-700 transition">
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
                                    <th class="px-4 py-2 border">Foto In</th>
                                    <th class="px-4 py-2 border">Clock Out</th>
                                    <th class="px-4 py-2 border">Lokasi Out</th>
                                    <th class="px-4 py-2 border">Foto Out</th>
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
                                        <td class="px-4 py-2 border">
                                            @if($item->clock_in_foto)
                                                <img src="{{ asset('storage/foto/' . $item->clock_in_foto) }}" alt="Clock In Foto" class="h-12 w-12 object-cover rounded">
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
                                        <td class="px-4 py-2 border">
                                            @if($item->clock_out_foto)
                                                <img src="{{ asset('storage/foto/' . $item->clock_out_foto) }}" alt="Clock Out Foto" class="h-12 w-12 object-cover rounded">
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

    @section('script')
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
            $( document ).ready(function() {
                $('#departemen_id, #office_id').on('change', function () {
                    const departemenId = $('#departemen_id').val();
                    const officeId = $('#office_id').val();

                    if (!departemenId && !officeId) {
                        $('#user_id').html(`@foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->first_name }} {{ $user->last_name }}</option>
                        @endforeach`);
                        return;
                    }

                    $('#user_id').html('<option value="">Loading...</option>').prop('disabled', true);

                    $.ajax({
                        url: '{{ route("users.byOfficeAndDepartemen") }}',
                        type: 'GET',
                        data: {
                            departemen_id: departemenId,
                            office_id: officeId
                        },
                        success: function (response) {
                            let options = '<option value="">Semua User</option>';
                            response.forEach(user => {
                                options += `<option value="${user.id}">${user.first_name} ${user.last_name}</option>`;
                            });

                            $('#user_id').html(options).prop('disabled', false);
                        },
                        error: function () {
                            $('#user_id').html('<option value="">Error memuat user</option>').prop('disabled', true);
                        }
                    });
                });

                $('#btn-export').on('click', function () {
                    const officeId = $('#office_id').val();
                    const departemenId = $('#departemen_id').val();
                    const userId = $('#user_id').val();
                    const startDate = $('#start_date').val();
                    const endDate = $('#end_date').val();

                    // Bangun query string
                    let params = $.param({
                        office_id: officeId,
                        departemen_id: departemenId,
                        user_id: userId,
                        start_date: startDate,
                        end_date: endDate
                    });

                    // Redirect ke URL export dengan query string
                    window.location.href = '{{ route("export.excel") }}' + '?' + params;
                });
            });
        </script>
    @endsection
</x-app-layout>