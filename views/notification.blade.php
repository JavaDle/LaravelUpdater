@if(config('updater.enable_jquery'))
    <script src="{{asset('/javadle/updater/jquery-3.7.0.min.js')}}"></script>
@endif
@if(config('updater.enable_sweet_alert2'))
    <script src="{{asset('/javadle/updater/sweetalert2.js')}}"></script>
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
                            `<h1>@lang('updater.Version'): ${response.version}</h1>` +
                            `<p>@lang('updater.Changes'): <br> ${response.description}</p>`,
                        icon: 'info',
                        showCancelButton: true,
                        confirmButtonText: '@lang('updater.UPDATE_NOW')',
                        cancelButtonText: `@lang('updater.Cancel')`,
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