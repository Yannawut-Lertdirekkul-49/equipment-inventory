<?php
session_start();
require "db.php";

if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION["role"] !== "Admin") {
    header("Location: room.php");
    exit;
}

$id = (int)$_GET["id"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $floor = (int)$_POST["floor"];
    $room  = trim($_POST["room"]);
    if ($room === "") {
        $message = "กรุณากรอกชื่อห้อง";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id FROM rooms WHERE name = ? AND id <> ?");
        mysqli_stmt_bind_param($stmt, "si", $room, $id);
        mysqli_stmt_execute($stmt);
        if (mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))) {
            $message = "มีห้องนี้แล้ว";
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE rooms SET name = ?, floor = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "sii", $room, $floor, $id);
            if (mysqli_stmt_execute($stmt)) {
                header("Location: room.php?msg=updated");
                exit;
            } else {
                $message = "แก้ไขไม่สำเร็จ: " . mysqli_stmt_error($stmt);
            }
        }
    }
}
// สำเร็จแล้ว redirect ไป room.php?msg=updated และ exit;

$stmt = mysqli_prepare($conn, "SELECT floor, name AS room FROM rooms WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
// ดึงแถวเดียวมาเก็บใน $row (ใช้ mysqli_fetch_assoc)
// อ่านค่าที่เลือกมาแก้ ใช้แสดงเป็นค่าเริ่มต้นในกล่องแก้
if (!$row) {
    header("Location: room.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขห้อง</title>
</head>
<body style="background-color:powderblue;">
    <h1>แก้ไขห้อง</h1>
    <form method="post">
        <label>ห้อง: <input type="text" name="room" value="<?php echo htmlspecialchars($row["room"]); ?>"></label><br>
        <label for="floor">ชั้น:</label>
        <select name="floor" id="floor">
            <option value="6" <?php if ($row["floor"] == 6) echo "selected"; ?>>6</option>
            <option value="7" <?php if ($row["floor"] == 7) echo "selected"; ?>>7</option>
        </select>
        <button type="submit">บันทึก</button>
        <a href="room.php">ยกเลิก</a>
    </form>
    <?php if (isset($message)) echo $message; ?>
</body>
</html>