<?php
    include("includes/header.php");
    include("includes/classifica_tickets.php");
?>

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

<script>
$(document).ready( function () { 
       
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
});
</script>