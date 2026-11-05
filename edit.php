<?php
session_start();
require "db.php";

if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit;
}

$id = (int)$_GET["id"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $type = $_POST["type"];
    $qty  = (int)$_POST["quantity"];
    $stmt = mysqli_prepare($conn, "UPDATE equipment SET type = ?, quantity = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "sii", $type, $qty, $id);
    if (mysqli_stmt_execute($stmt)) {
        header("Location: hello.php?msg=updated");
        exit;
    } else {
        $message = "แก้ไขไม่สำเร็จ: " . mysqli_stmt_error($stmt);
    }
    // UPDATE equipment SET type = ?, quantity = ? WHERE id = ?
    // สำเร็จแล้ว redirect ไป hello.php?msg=updated และ exit;
}

$stmt = mysqli_prepare($conn, "SELECT type, quantity FROM equipment WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
// SELECT type, quantity FROM equipment WHERE id = ?
// ดึงแถวเดียวมาเก็บใน $row (ใช้ mysqli_fetch_assoc)
// อ่านค่าที่เลือกมาแก้ ใช้แสดงเป็นค่าเริ่มต้นในกล่องแก้
if (!$row) {
    header("Location: hello.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขอุปกรณ์</title>
</head>
<body style="background-color:powderblue;">
    <h1>แก้ไขอุปกรณ์</h1>
    <form method="post">
        <label for="type">ประเภท:</label>
        <select name="type" id="type">
            <?php foreach ($types as $t) { ?>
            <option value="<?php echo $t; ?>" <?php if ($row["type"] === $t) echo "selected"; ?>><?php echo $t; ?></option>
            <?php } ?>
            <?php /* ของเก่า <option value="โต๊ะ" <?php if ($row["type"] === "โต๊ะ") echo "selected"; ?>>โต๊ะ</option> */ ?>
        </select>
        <label>จำนวน: <input type="number" name="quantity" value= "<?php echo $row["quantity"]; ?>" min="0" required></label><br>
        <button type="submit">บันทึก</button>
        <a href="hello.php">ยกเลิก</a>
    </form>
</body>
</html>