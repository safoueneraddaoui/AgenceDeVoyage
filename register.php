<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

function msg($success,$status,$message,$extra = []){
    return array_merge([
        'success' => $success,
        'status' => $status,
        'message' => $message
    ],$extra);
}

// INCLUDING DATABASE AND MAKING OBJECT
require __DIR__.'/classes/Database.php';
$db_connection = new Database();
$conn = $db_connection->dbConnection();

// GET DATA FORM REQUEST
$data = json_decode(file_get_contents("php://input"));
$returnData = [];

// IF REQUEST METHOD IS NOT POST
if($_SERVER["REQUEST_METHOD"] != "POST"):
    $returnData = msg(0,404,'Page Not Found!');

// CHECKING EMPTY FIELDS
elseif(!isset($data->nom_client)
    || !isset($data->prenom_client)
    || !isset($data->age_client)
    || !isset($data->adress_client)
    || !isset($data->num_tel_client)
    || !isset($data->cin_client)
    || !isset($data->sex_client)
    || !isset($data->adr_mail_client)
    || !isset($data->civilisation_client)
    || !isset($data->password)
    || empty(trim($data->nom_client))
    || empty(trim($data->prenom_client))
    || empty(trim($data->age_client))
    || empty(trim($data->adress_client))
    || empty(trim($data->num_tel_client))
    || empty(trim($data->cin_client))
    || empty(trim($data->sex_client))
    || empty(trim($data->adr_mail_client))
    || empty(trim($data->civilisation_client))
    || empty(trim($data->password))
    ):

    $fields = ['fields' => ['nom_client','prenom_client','age_client','adress_client','num_tel_client','cin_client','sex_client','adr_mail_client','civilisation_client','password']];
    $returnData = msg(0,422,'Please Fill in all Required Fields!',$fields);

// IF THERE ARE NO EMPTY FIELDS THEN-
else:
    
    $nom_client = trim($data->nom_client);
    $prenom_client = trim($data->prenom_client);
    $age_client = trim($data->age_client);
    $adress_client = trim($data->adress_client);
    $num_tel_client = trim($data->num_tel_client);
    $cin_client = trim($data->cin_client);
    $sex_client = trim($data->sex_client);
    $adr_mail_client = trim($data->adr_mail_client);
    $civilisation_client = trim($data->civilisation_client);
    $password = trim($data->password);

    if(!filter_var($adr_mail_client, FILTER_VALIDATE_EMAIL)):
        $returnData = msg(0,422,'Invalid Email Address!');

    elseif(strlen($password) < 8):
        $returnData = msg(0,422,'Your password must be at least 8 characters long!');

    elseif(strlen($nom_client) < 3):
        $returnData = msg(0,422,'Your name must be at least 3 characters long!');

    elseif(strlen($prenom_client) < 3):
        $returnData = msg(0,422,'Your name must be at least 3 characters long!');

    elseif(strlen($adress_client) < 3):
        $returnData = msg(0,422,'Your name must be at least 3 characters long!');

    elseif(strlen($sex_client) < 3):
        $returnData = msg(0,422,'Your name must be at least 3 characters long!');

    elseif(strlen($civilisation_client) < 3):
        $returnData = msg(0,422,'Your name must be at least 3 characters long!');

    elseif(strlen($adress_client) < 3):
        $returnData = msg(0,422,'Your name must be at least 3 characters long!');



    else:
        try{

            $check_email = "SELECT `adr_mail_client` FROM `clients` WHERE `adr_mail_client`=:adr_mail_client";
            $check_email_stmt = $conn->prepare($check_email);
            $check_email_stmt->bindValue(':adr_mail_client', $adr_mail_client,PDO::PARAM_STR);
            $check_email_stmt->execute();

            if($check_email_stmt->rowCount()):
                $returnData = msg(0,422, 'This E-mail already in use!');

            else:
                $insert_query = "INSERT INTO `clients`(`nom_client`,`prenom_client`,`age_client`,`adress_client`,`num_tel_client`,`cin_client`,`sex_client`,`adr_mail_client`,`civilisation_client`,`password`) VALUES(:nom_client,:prenom_client,:age_client,:adress_client,:num_tel_client,:cin_client,:sex_client,:adr_mail_client,:civilisation_client,:password)";

                $insert_stmt = $conn->prepare($insert_query);

                // DATA BINDING
                $insert_stmt->bindValue(':nom_client', htmlspecialchars(strip_tags($nom_client)),PDO::PARAM_STR);
                $insert_stmt->bindValue(':prenom_client', htmlspecialchars(strip_tags(prenom_client)),PDO::PARAM_STR);
                $insert_stmt->bindValue(':age_client', $age_client,PDO::PARAM_STR);
                $insert_stmt->bindValue(':adress_client', $adress_client,PDO::PARAM_STR);
                $insert_stmt->bindValue(':num_tel_client', $num_tel_client,PDO::PARAM_STR);
                $insert_stmt->bindValue(':cin_client', $cin_client,PDO::PARAM_STR);
                $insert_stmt->bindValue(':sex_client', $sex_client,PDO::PARAM_STR);
                $insert_stmt->bindValue(':adr_mail_client', $adr_mail_client,PDO::PARAM_STR);
                $insert_stmt->bindValue(':civilisation_client', $civilisation_client,PDO::PARAM_STR);
                $insert_stmt->bindValue(':password', password_hash($password, PASSWORD_DEFAULT),PDO::PARAM_STR);

                $insert_stmt->execute();

                $returnData = msg(1,201,'You have successfully registered.');

            endif;

        }
        catch(PDOException $e){
            $returnData = msg(0,500,$e->getMessage());
        }
    endif;
    
endif;

echo json_encode($returnData);