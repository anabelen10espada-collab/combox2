<?php
    //get database connection
    require("./config/database.php");

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: register.html");
        exit;
    }

    // Get data from html form (client)
    $f_name = $_POST['fname'] ?? '';
    $l_name = $_POST['lname'] ?? '';
    $m_phone = $_POST['mphone'] ?? '';
    $e_mail = $_POST['email'] ?? '';
    $p_assword = $_POST['psw'] ?? '';
    $pass_enc = md5($p_assword);

    //sql para validar si el correo o el numero de celular ya existen
    $sql_validation = "
        SELECT email, mobile_phone
        FROM users
        WHERE email = '$e_mail' OR mobile_phone = '$m_phone'
    ";

    $supa_res = pg_query($supa_conn, $sql_validation);
    $total = $supa_res ? pg_num_rows($supa_res) : 0;

    if ($total > 0) {
        $msg = "User email or mobile phone already exists :::";
        while ($row = pg_fetch_assoc($supa_res)) {
            if ($row['email'] === $e_mail && $row['mobile_phone'] === $m_phone) {
                $msg = "Both email and mobile phone already exist :::";
                break;
            } elseif ($row['email'] === $e_mail) {
                $msg = "User email already exists :::";
            } elseif ($row['mobile_phone'] === $m_phone) {
                $msg = "Mobile phone already exists :::";
            }
        }
        echo "<script>alert('$msg'); window.location.href='register.html';</script>";
        exit;
    } else {
        //prepared query
        $sql = "
            INSERT INTO users (
                firstname, lastname, mobile_phone, email, password
            )
            VALUES (
                '$f_name', '$l_name', '$m_phone', '$e_mail', '$pass_enc'
            )
        ";

        $supa_res = pg_query($supa_conn, $sql);

        if ($supa_res) {
            //Redirecciona a login
            echo "<script>alert('User has been created successfylly:::'); window.location.href='login.html';</script>";
        } else {
            echo "<br> User hasn't been created into supa database!!!";
        }
    }
?>