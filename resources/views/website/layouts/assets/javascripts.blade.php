<script src="//ajax.googleapis.com/ajax/libs/jquery/2.2.2/jquery.min.js"></script>

<script type="text/javascript" src="{{ asset('js/app.js') }}"></script>

<script type="text/javascript">
    $(document).ready(function () {
        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        });

        $(window).scroll(function () {
            var scroll = $(window).scrollTop();

            if (scroll >= 100) {
                $("#navbar").addClass("navbar-scroll");
            } else {
                $("#navbar").removeClass("navbar-scroll");
            }
        });
    });
</script>