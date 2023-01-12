if ( typeof dataJSon != "undefined" ) {
    let project_datatable = $(".project_datatable").mDatatable({
        // datasource definition
        data: {
            type: 'local',
            source: dataJSon,
            pageSize: 10,
            serverPaging: true,
            saveState: false,
        },

        // layout definition
        layout: {
            theme: 'default', // datatable theme
            class: '', // custom wrapper class
            scroll: false, // enable/disable datatable scroll both horizontal and vertical when needed.
            // height: 450, // datatable's body's fixed height
            footer: false // display/hide footer
        },

        translate: {
            records: {
                processing: 'Espere porfavor...',
                noRecords: 'No hay registros'
            },
            toolbar: {
                pagination: {
                    items: {
                        default: {
                            first: 'Primero',
                            prev: 'Anterior',
                            next: 'Siguiente',
                            last: 'Último',
                            more: 'Más páginas',
                            input: 'Número de página',
                            select: 'Seleccionar tamaño de página'
                        },
                        info: 'Mostrando {{start}} - {{end}} de {{total}} registros'
                    }
                }
            }
        },
        
        // column sorting
        sortable: true,
        pagination: true,

        search: {
            input: $('#generalSearch')
        },

        columns: [
            {
                field: 'recordId',
                title: '#',
                sortable: false,
                width: 0,
                textAlign: 'center',
                selector: {class: 'm-checkbox--solid m-checkbox--brand d-none'},
            },
            {
                field: "order",
                title: "#",
                width: 50,
                sortable: true,
                textAlign: 'center',
            },
            {
                field: "tracking_id",
                title: "",
                width: 50,
                sortable: true,
                textAlign: 'center',
            },
            {
                field: "consignee",
                title: "Consignatario",
                width: 200,
                sortable: true,
                textAlign: 'left',
            },
            {
                field: "shop",
                title: "Tienda",
                width: 200,
                sortable: true,
                textAlign: 'left',
            },
            {
                field: "courier",
                title: "Courier",
                width: 100,
                sortable: true,
                textAlign: 'left',
            },
            {
                field: "package_value",
                title: "Valor del Paquete (USD)",
                width: 200,
                sortable: true,
                textAlign: 'left',
            },
            {
                field: "actions",
                title: "Acciones",
                textAlign: 'center',
                template: function(row) {
                    return "<a href='"+ row.href +"' class='m-portlet__nav-link btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill edit' title='Editar'><i class='la la-edit edit'></i></a><a href='"+ row.delete +"' class='m-portlet__nav-link btn m-btn m-btn--hover-danger m-btn--icon m-btn--icon-only m-btn--pill delete' title='Eliminar'><i class='la la-trash delete'></i></a>";
                }
            },
            {
                field: "id",
                title: "id",
                width: 0
            }
        ]
    });

    project_datatable.columns("recordId").visible( false );
    project_datatable.columns("id").visible( false );
}


$('.preview-close').click(function(event) {
    event.preventDefault();
    let $this = $(this);
    let type = $this.attr('data-type');
    let projectID = $('#id').val();

    swal({
        title: '¿Eliminar?',
        text: 'Una vez eliminada la imagen, no se podrá restaurar',
        type: "warning",
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar!'
    }).then(function(result) {
        if (result.value) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                url: urlDeleteImages,
                type: 'POST',
                data: {
                    id: projectID,
                    type: type,
                }
            }).done(function(result) {
                $this.parent().remove();
            }).fail(function(result) {
                
            });
        }
    });
});