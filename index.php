<html>

<head>
<title>Exemplo PHP</title>
</head>
<body>

<?php
ini_set("display_errors", 1);
header('Content-Type: text/html; charset=iso-8859-1');



echo 'Versao Atual do PHP: ' . phpversion() . '<br>';

$servername = getenv('DB_HOST');
$username = getenv('DB_USER');
$password = getenv('DB_PASSWORD');
$database = getenv('DB_NAME');

// Criar conexão
$link = new mysqli($servername, $username, $password, $database);

/* check connection */
if ($link->connect_error) {
    die("Connection failed: " . $link->connect_error);
}

$valor_rand1 = rand(1, 999);
$valor_rand2 = strtoupper(substr(bin2hex(random_bytes(4)), 1));
$host_name = gethostname();

// Usar prepared statements para prevenir SQL Injection
$query = "INSERT INTO dados (AlunoID, Nome, Sobrenome, Endereco, Cidade, Host) VALUES (?, ?, ?, ?, ?, ?)";

if ($stmt = $link->prepare($query)) {
    // 'isssss' especifica o tipo de cada variável: (i)nteger, (s)tring, (s)tring, (s)tring, (s)tring, (s)tring
    $stmt->bind_param("isssss", $valor_rand1, $valor_rand2, $valor_rand2, $valor_rand2, $valor_rand2, $host_name);

    if ($stmt->execute()) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Error preparing statement: " . $link->error;
}

$link->close();

?>
</body>
</html>
