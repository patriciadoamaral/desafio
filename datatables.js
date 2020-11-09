$(document).ready( function () {  
   
    table = $('#example').DataTable({
        language: {
            "url": "https://cdn.datatables.net/plug-ins/1.10.21/i18n/Portuguese-Brasil.json"
        },        
        ajax:
        {
           url: "tickets.json",
           dataSrc: ''
        },
        columns: [
            {data: 'TicketID'},
            
            { data: 'DateCreate', "render": function(data, type, full) 
                {
                    return moment(new Date(data)).format('DD/MM/YYYY HH:mm:ss');            
                }
            },            

            { data: 'DateUpdate' , "render": function(data, type, full) 
                {
                    return moment(new Date(data)).format('DD/MM/YYYY HH:mm:ss');            
                }
            },

            { data: 'Prioridade', "render": function(data, type, full) 
                {
                    if(data == "Alta"){
                        return "<span class='badge badge-danger'>"+data+"</span>"; 
                    }else if(data == "Normal"){
                        return "<span class='badge badge-success'>"+data+"</span>"; 
                    }           
                } 
            },

            { data: 'TicketID', "render": function (data, type, full, meta){
                return "<a href='#exampleModal' data-toggle='modal' data-id='"+data+"' class='abrirModal'><i class='far fa-eye'></i></a>";            
                }
            }
        ]        
    });

    //Formata e ordena datas
    $.fn.dataTable.moment('DD/MM/YYYY HH:mm:ss');

    $.fn.dataTable.moment = function ( format, locale ) {
        var types = $.fn.dataTable.ext.type;
    
        // Add type detection
        types.detect.unshift( function ( d ) {
            return moment( d, format, locale, true ).isValid() ?
                'moment-'+format :
                null;
        } );
    
        // Add sorting method - use an integer for the sorting
        types.order[ 'moment-'+format+'-pre' ] = function ( d ) {
            return moment( d, format, locale, true ).unix();
        };
    };

    //Função para buscar por intervalo de data de criação e prioridade
    $.fn.dataTableExt.afnFiltering.push(
        function( settings, data, dataIndex ) {
            var min  = $('#min-date').val()
            var max  = $('#max-date').val()
            var prioridade  = $('#prioridade').val()

            var createdAt = data[1] || 0; // Our date column in the table
            var startDate  = moment(min).add('-1','days');
            var endDate  = moment(max).add('1','days');
            var diffDate = moment(createdAt, "DD/MM/YYYY");
            var createdPrior = data[3] || 0;

            if (
              (min == "" || max == "") ||
              (diffDate.isBetween(startDate, endDate) && createdPrior == prioridade)
            ) {  return true;  }
            return false;
        }
    );
        $('.date-range-filter').change( function() {
        table.draw();
    } );
  
})