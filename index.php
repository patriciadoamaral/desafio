<!--JS-->
<script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>

<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>

<!--CSS-->
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/css/bootstrap.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css">

<script type="text/javascript" language="javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/plug-ins/1.10.21/sorting/datetime-moment.js"></script>
<link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous"/>

<?php

    $json_data = json_decode(file_get_contents('tickets.json'));

    // Função para procurar várias palavras em uma string
    function procpalavras ($mensagem, $palavras, $resultado = 0) {
        foreach ( $palavras as $key => $value ) {
            $pos = stripos($mensagem, $value);
            if ($pos !== false) { $resultado = 1; break; }
        }
        return $resultado;
    }

    $palavras = array('problema', 'procon', 'reclame aqui', 'reclameAqui', 'insatisfeito', 'cancelar', 'defeito', 'cancelamento', 'reclamação', 'providências');
  
    foreach ($json_data as $data) {
        $busca = 0;
        $data->Prioridade = "Normal";
        $data->Pontuacao = 0;

        foreach($data->Interactions as $v){
            ### Prioridade Alta:        
            # Consumidor insatisfeito com produto ou serviço
            # Consumidor sugere abrir reclamação como exemplo Procon ou ReclameAqui            

            //Se encontrar alguma mensagem do consumidor com alguma palavra chave
            if($v->Sender == "Customer"){
                if( procpalavras($v->Message, $palavras) == 1 || procpalavras($v->Subject, $palavras) == 1){
                   //echo $v->Message."<hr>";
                    $busca = 1;
                }
            } 
        }

        if(!empty($busca)){
            $data->Prioridade = "Alta";
            $data->Pontuacao += 50;
        }        

        # Prazo de resolução do ticket alta    
        $data_ini = $data->DateCreate;
        $data_fim = date('Y-m-d H:i:s');
        $d1=new DateTime($data_ini);
        $d2=new DateTime($data_fim);
        $diff = $d2->diff($d1);

        $anos = $diff->y;
        $meses = $diff->m;
        $dias = $diff->d;
        $horas = $diff->h;

        $tempo = (!empty($anos) ? $anos." anos " : '');
        $tempo .= (!empty($meses) ? $meses." meses " : '');
        $tempo .= $dias." dias e ". $horas." hrs";

        if( $dias >= 1){
            $data->Tempo = $tempo;
            $data->Prioridade = "Alta";
            $data->Pontuacao += 50;
        } 
    }
    
    $fp = fopen('tickets.json', 'w'); // abre o ficheiro em modo de escrita    
    fwrite($fp, json_encode($json_data)); // escreve no ficheiro em json    
    fclose($fp); // fecha o ficheiro

    //echo "<pre>"; print_r($json_data);
?>

<script>
$(document).ready( function () {    

    //---DATATABLES
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
    //---FIM DATABLES


    //---MODAL    
    $(document).on("click", ".abrirModal", function () {
        var ticket = $(this).data('id');

        var url = "tickets.json";         
        $.getJSON(url, function (data) {
            $.each(data, function (key, model) {                
                if (model.TicketID == ticket) {                    
                    $(".modal-title").html( "#"+ticket );

                    var html = "";
                    $.each(model.Interactions, function (key, model) {                        
                        date = model.DateCreate
                        html += "<p><strong>" + moment(date).format("DD/MM/YYYY HH:mm:ss") + "</strong> - "+model.Sender+"</p>";
                        html += "<p><strong>Assunto: </strong>"+model.Subject+"</p>";
                        html += "<p>"+model.Message+"</p><hr>";
                    })
                    $(".modal-body").html(html);
                }
            })
        });
    })
    //---FIM MODAL

});
</script>

<div class="container mt-2">

    <div class="form-inline d-flex justify-content-end mb-2">    
        <label class="mr-2">Busca Data Criação e Prioridade</label>
        <input type="date" id="min-date" class="date-range-filter form-control form-control-sm">
        <input type="date" id="max-date" class="date-range-filter form-control form-control-sm"> 
        <select id="prioridade" class="date-range-filter form-control form-control-sm">
            <option value="Normal">Normal</option>
            <option value="Alta">Alta</option>
        </select>   
    </div>

    <table id="example" class="table table-striped table-bordered" style="width:100%">
        <thead>
            <tr>
                <th>Ticket ID</th>
                <th>Data Criação</th>
                <th>Data Atualização</th>
                <th>Prioridade</th>
                <th>Ver</th>
            </tr>
        </thead>
    </table>
    
</div>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>