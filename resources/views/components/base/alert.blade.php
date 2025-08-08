<div id="alert-additional-content-1" @class([
    'p-4 mb-4 text-slate-800 border rounded-lg shadow-xl md:max-w-md pointer-events-auto',
    'hidden' => $title == '',
    'block' => $title != '',
    'bg-green-50 text-green-800' => $title == 'success',
    'bg-yellow-50 text-yellow-800' => $title == 'warning',
    'bg-red-50 text-red-800' => $title == 'error' || $title == 'danger',
    'bg-blue-50 text-blue-800' =>
        $title != 'success' &&
        $title != 'danger' &&
        $title != 'error' &&
        $title != 'warning',
]) role="alert">

    <div class="flex items-center justify-between">
        <div class="flex items-center">
            <svg class="flex-shrink-0 w-4 h-4 mr-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                fill="currentColor" viewBox="0 0 20 20">
                <path
                    d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
            </svg>
            <span class="sr-only">alert</span>
            <h3 class="text-lg font-medium capitalize">{{ $title }} </h3>
        </div>


        <button id="close-btn"
            class="text-gray-400 bg-transparent hover:bg-gray-200/60 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center transition-all"
            type="button" data-dismiss-target="#alert-additional-content-1" aria-label="Close">
            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 14 14">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
            </svg>

        </button>

    </div>

    <div class="mx-6 mt-2 mb-4 text-sm w-52 md:w-96 capitalize">
        {{ $message }}
    </div>

</div>

<script>
    const alertBox = document.getElementById('alert-additional-content-1');
    const closeBtn = document.getElementById('close-btn');

    function removeAlert() {
        alertBox?.remove();
        fetch('/session/clear', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            },
        });
    }

    closeBtn?.addEventListener('click', removeAlert);

    setTimeout(() => {
        removeAlert();
    }, 3000);
</script>