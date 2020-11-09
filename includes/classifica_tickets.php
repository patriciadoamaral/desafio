<?php 

    $json_data = json_decode(file_get_contents('./tickets.json'));

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
?>
