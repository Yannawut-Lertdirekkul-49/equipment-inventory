<?php
session_start();
require "db.php";
$msg = $_GET["msg"] ?? "";

if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit;
}

$role = $_SESSION["role"];
$is_admin = ($role === "Admin");
$can_edit = in_array($role, ["Staff", "Admin"]);

if (isset($msg)) { // เช็คทุกครั้งหลัง reload page ว่าคำสั่งครั้งก่อนทำเสร็จแล้ว
    if ($msg === "saved") $message = "บันทึกแล้ว";
    elseif ($msg === "deleted") $message = "ลบแล้ว";
    elseif ($msg === "updated") $message = "แก้ไขแล้ว";
}

if (isset($_POST["delete_id"])) { // ลบห้อง
    if (!$is_admin) {
        $message = "คุณไม่มีสิทธิ์ลบ";
    } else {
        $id = (int)$_POST["delete_id"];
        $stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS n FROM equipment WHERE room_id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $count = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))["n"];
        if ($count > 0) {
            $message = "ห้องนี้ยังมีอุปกรณ์อยู่ ต้องลบอุปกรณ์ก่อน";
        } else {
            $stmt = mysqli_prepare($conn, "DELETE FROM rooms WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            if (mysqli_stmt_execute($stmt)) {
                header("Location: room.php?msg=deleted");
            exit;
            } else {
                $message = "ลบไม่สำเร็จ: " . mysqli_stmt_error($stmt);
            }
        }
    }
} elseif ($_SERVER["REQUEST_METHOD"] === "POST") { // เพิ่มห้อง
    if (!$is_admin) {
        $message = "คุณไม่มีสิทธิ์เพิ่มห้อง";
    } else {
        $room  = trim($_POST["room"]);
        $floor = (int)$_POST["floor"];
        if ($room === "") {
            $message = "กรุณากรอกชื่อห้อง";
        } elseif ($floor !== 6 && $floor !== 7) {
            $message = "ชั้นไม่ถูกต้อง";
        } else {
            $stmt = mysqli_prepare($conn, "SELECT id FROM rooms WHERE name = ?");
            mysqli_stmt_bind_param($stmt, "s", $room);
            mysqli_stmt_execute($stmt);
            $found = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            if (!$found) {
                $stmt = mysqli_prepare($conn, "INSERT INTO rooms (name, floor) VALUES (?, ?)");
                mysqli_stmt_bind_param($stmt, "si", $room, $floor);
                if (mysqli_stmt_execute($stmt)) {
                    header("Location: room.php?msg=saved");
                    exit;
                } else {
                $message = "บันทึกไม่สำเร็จ: " . mysqli_stmt_error($stmt);
                }
            } else {
                $message = "มีห้องนี้แล้ว";
            }
        }
    }
}

$sql = "SELECT id, rooms.name AS room, floor FROM rooms";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Equipment Inventory</title>
</head>
<body style="background-color:powderblue;">
    <h1>รายการห้อง</h1>

    <p><a href="hello.php">&lt; อุปกรณ์</a></p> 
    
    <table border="1">
        <tr><th>ห้อง</th><th>ชั้น</th><?php if ($is_admin) { ?><th>จัดการ</th><?php } ?></tr> 

        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?php echo htmlspecialchars($row["room"]); ?></td>
            <td><?php echo htmlspecialchars($row["floor"]); ?></td> 
            <?php /* column แก้ไข */ ?>
            <?php if ($is_admin) { ?>
                <td>
                <a href="edit_room.php?id=<?php echo $row["id"]; ?>">แก้ไข</a>
                <form method="post" onsubmit="return confirm('ลบรายการนี้?');">
                <input type="hidden" name="delete_id" value="<?php echo $row["id"]; ?>">
                <button type="submit">ลบ</button>
                </form>
                </td> 
            <?php } ?>    
        </tr>
        <?php } ?>

    </table>
    <?php if (isset($message)) echo $message; ?>

    <?php if ($is_admin) { ?>
    <h2>เพิ่มห้อง</h2>
    <form method="post">
        <label>ห้อง: <input type="text" name="room"></label><br>
        <label for="floor">ชั้น:</label>
        <select name="floor" id="floor">
            <option value="6">6</option>
            <option value="7">7</option>
        </select>
        <button type="submit">บันทึก</button>
    </form>
     <?php } ?>

    <a href="logout.php">ออกจากระบบ</a>

</body>
</html>