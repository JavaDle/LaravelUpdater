@if(config('updater.enable_jquery'))
    <script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
@endif
@if(config('updater.enable_sweet_alert2'))
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endif
<script>
    $(document).ready(function() {
        $.ajax({
            type: 'GET',
            url: 'updater.check',
            async: false,
            success: function(response) {
                if (response !== '') {
                    Swal.fire({
                        title: '@lang('updater.UPDATE_AVAILABLE')',
                        html:
                            `<h1>Version: ${response.version}</h1>` +
                            `<p>Change Log: <br> ${response.description}</p>`,
                        icon: 'info',
                        showCancelButton: true,
                        confirmButtonText: '@lang('updater.UPDATE_NOW')',
                        denyButtonText: `@lang('Cancel')`,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                type: 'GET',
                                url: 'updater.update',
                                success: function(response) {
                                    if (response !== '') {
                                        Swal.fire({
                                            title: '@lang('updater.UPDATED')',
                                            html:
                                                `<p>${response}</p>`,
                                            icon: 'info',
                                        })
                                    }
                                },
                                error: function(response) {
                                    if (response !== '') {
                                        Swal.fire({
                                            title: '@lang('updater.error_try_again')',
                                            html:
                                                `<p>${response}</p>`,
                                            icon: 'info',
                                            showCancelButton: false,
                                        })
                                    }
                                }
                            });
                        }
                    })
                }
            }
        });
    });
</script>