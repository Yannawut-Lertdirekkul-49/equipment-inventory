<?php
session_start();
require "db.php";

if (isset($_SESSION["username"])) {
    header("Location: hello.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $stmt = mysqli_prepare($conn, "SELECT password, role FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $found = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if ($found && password_verify($password, $found["password"])) {
        session_regenerate_id(true);
        $_SESSION["username"] = $username;
        $_SESSION["role"] = $found["role"];
        header("Location: hello.php?msg=login_success");
        exit;
    } else {
        $message = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
    }
}

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Equipment Inventory</title>
</head>
<body style="background-color:powderblue;">
    <h1>ล็อกอิน</h1>
    <form method="post">
        <label>ชื่อผู้ใช้: <input type="text" name="username"></label><br>
        <label>รหัสผ่าน: <input type="password" name="password"></label><br>
        <button type="submit">ล็อกอิน</button>
    </form>
    <?php if (isset($message)) echo $message; ?>

</body>
</html>