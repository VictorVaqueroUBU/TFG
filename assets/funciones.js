import $ from 'jquery';
function mostrarContenidoAjax() {
    let $body = $('body');

    /**
     *  Muestra contenido ajax por GET utilizando URL
     */
    $body.on('click', '.mostrarContenidoAjaxGetUrl', function () {
        let elemento = $(this);
        let url = elemento.data('url');
        let contenedor = elemento.attr('href');
        console.log('get');
        $.ajax({
            url: url,
            type: 'GET',
            async: true,
            beforeSend: function () {
                $(contenedor).html('<i class="fa fa-spinner fa-spin"></i> Cargando ...');
            },
            success: function (response) {
                $(contenedor).html(response);
            },
        });
    });

    /**
     *  Muestra contenido ajax por POST utilizando URL
     */
    $body.on('click', '.mostrarContenidoAjaxPostUrl', function () {
        let elemento = $(this);
        let url = elemento.data('url');
        let contenedor = elemento.attr('href');
        $(contenedor).empty();
        console.log('post');
        $.ajax({
            url: url,
            type: 'POST',
            async: true,
            beforeSend: function () {
                $(contenedor).html('<i class="fa fa-spinner fa-spin"></i> Cargando ...');
            },
            success: function (response) {
                $(contenedor).html(response);
            },
        });
    });

    /**
     *  Muestra contenido ajax por POST utilizando HREF
     */
    $body.on('click', '.mostrarContenidoAjaxPostHref', function () {
        let elemento = $(this);
        let url = elemento.attr('href');
        let contenedor = elemento.data('response');
        console.log('post');
        $.ajax({
            url: url,
            type: 'POST',
            async: true,
            beforeSend: function () {
                $(contenedor).html('<i class="fa fa-spinner fa-spin"></i> Cargando ...');
            },
            success: function (response) {
                $(contenedor).html(response);
            },
        });
    });

    /**
     * Mostrar contenidos tab
     */
    $body.on('click', '.tabContenido', function () {
        let elemento = $(this);
        let url = elemento.data('url');
        let contenedor = elemento.attr('href');
        let tab = elemento.closest('ul').data('tab');
        console.log('post');
        $.ajax({
            url: url,
            type: 'POST',
            async: true,
            beforeSend: function () {
                $('.' + tab).empty();
                $(contenedor).html('<i class="fa fa-spinner fa-spin"></i> Cargando ...');
            },
            success: function (response) {
                $(contenedor).html(response);
            },
        });
    });
}

function tablas() {
    $('.tablaTipo1').dataTable({
        retrieve: true,
        language: {
            url: '{{ asset("build/includes/datatable_info_media.es.json") }}',
        },
        order: [
            [1, 'asc'],
        ],
    });
}

