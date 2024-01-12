<?php
$URL = $_SERVER['REQUEST_URI'];
$idunidade = (isset($_GET['idunidade']) and !empty($_GET['idunidade'])) ? $_GET['idunidade'] : '';
$idconta = (isset($_GET['idconta']) and !empty($_GET['idconta'])) ? $_GET['idconta'] : '';
date_default_timezone_set('America/Sao_Paulo');
$data_atual = date('Y-m-d');
include("conexao.php");
if (isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    $rtn = array('sucess' => true, 'msg' => 'Tudo Lido e Enviado com Sucesso');
    if ($_POST['ajax'] == "importar") {
        if (isset($_FILES['file'])) {
            $arquivo = $_FILES['file'];
            $caminhoTemporario = $_FILES['file']['tmp_name'];
            $csvData = array();
            $arrofx = array();
            $arr2 = array();
            if (($handle = fopen($caminhoTemporario, "r")) !== false) {
                fgetcsv($handle, 1000, ';');
                $saldo = floatval(0);
                while (($linha = fgetcsv($handle, 1000, ';')) !== false) {
                        $data = $linha[0];
                        $desc = $linha[1];
                        $val = preg_replace("/[^0-9.,\-+]/", "", str_replace('.', '', $linha[2]));
                        $val = floatval(str_replace(',', '.', $val));
                        $tp = $linha[3];
                        $csvData[] = array(
                            'data' => $data,
                            'desc' => $desc,
                            'val' => $val,
                            'tp' => $tp
                        );
                        $saldo = ($saldo + $val);
                        $arrofx[] = array(NULL, '', '$data_atual', '', '', '', '$_POST[unidade]', '$desc', '$val1', '$tp1', '$_POST[conta]', '', '', '', '', '', '', '', '', '', '', '');
                        $arr2[] = array($linha);
                    
                }
                fclose($handle);
                $dt_end = $csvData[0]['data'];
                $dtendobj = DateTime::createFromFormat('d/m/Y H:i', $dt_end);
                $dtend = $dtendobj->format('Y-m-d');
                $dt_start = end($csvData)['data'];
                $dtstobj = DateTime::createFromFormat('d/m/Y H:i', $dt_start);
                $dtstart = $dtstobj->format('Y-m-d');
            } else {
                $rtn = array('sucess' => false, 'msg' => 'arquivo não encontrado');
                echo json_encode($rtn);
                die;

            }
        }
    } else {
        $rtn = array('sucess' => false, 'msg' => 'metodo errado');
        echo json_encode($rtn);
        die();
    }
    $enviarofx = mysqli_query(
        $mysqli,
        "INSERT INTO `lbox_financeiro_extrato_ofx` (`id`, `lixo`, `data`, `id_conta`, `id_unidade`, `saldo`, `data_inicio`, `data_fim`) 
    VALUES (NULL, '', '$data_atual', '$_POST[conta]','$_POST[unidade]','$saldo','$dtstart', '$dtend')"
    );
    $idofx = $mysqli->insert_id;
    foreach ($arr2 as $arrteste => $teste4) {
        $info = $teste4[0];
        $desc1 = $info[1];
        $val1 = preg_replace("/[^0-9.,\-+]/", "", str_replace('.', '', $info[2]));
        $val1 = floatval(str_replace(',', '.', $val1));
        $tp1 = $info[3];
        $arrofx1[] = array("NULL", '""', '"' . $data_atual . '"', '""', '""', '""', '"' . $_POST['unidade'] . '"', '"' . $desc1 . '"', '"' . $val1 . '"', '"OTHER"', '"' . $_POST['conta'] . '"', '""', '""', '""', '""', '""', '""', '""', '""', '""', '"' . $idofx . '"', '""');

    }
    $strarray = implode(',', array_map(function ($item) {
        return "(" . implode(',', $item) . ")";
    }, $arrofx1));

    $enviarmvm = mysqli_query(
        $mysqli,
        "INSERT INTO `lbox_financeiro_extrato` (`id`, `lixo`, `data_extrato`, `ajuste`, `uniqueid`, `checknumber`, `id_unidade`, `descricao`, 
    `valor`, `tipo`, `id_conta`, `id_despesa`, `juros`, `multa`, `desconto`, `obs`, `id_usuario`, `id_fluxo_criacao`, `transferencia`, 
    `id_transferencia`, `id_ofx`, `checknum`) 
    VALUES $strarray"
    );
die;
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.7.1.js"></script>

    <title>OFX</title>
</head>

<body>
    <form method="POST" id="formy">
        <input hidden id="unidade" hidden name="unidade" value="<?php echo $idunidade ?>" type="number">
        <input id="conta" hidden name="conta" value="<?php echo $idconta ?>" type="number">
        <input id="file" type="file">



    </form>
    <button onclick="this.disabled = true" id="btn_sbmt" type="button">Enviar</button>
    <script>
        $(document).ready(function () {
            $("#btn_sbmt").click(function (event) {
                event.preventDefault();

                var url = "<?php echo ($URL); ?>";
                const conta = $("#conta").val();
                const unidade = $("#unidade").val();
                var fileInput = $("#file")[0];
                var file = fileInput.files[0];

                var formData = new FormData();
                formData.append('ajax', 'importar');
                formData.append('conta', conta);
                formData.append('unidade', unidade);
                formData.append('file', file);

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData
                    ,
                    processData: false,
                    contentType: false,
                    success: function (rtn) {
                        console.log(rtn);
                    },
                    error: function (xhr, status, error) {
                        console.error(xhr.responseText);
                    }
                })


            });


        });

    </script>
</body>


</html>