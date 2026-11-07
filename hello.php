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
    elseif ($msg === "added") $message = "เพิ่มห้องใหม่แล้ว";
    elseif ($msg === "login_success") $message = "ล็อกอินสำเร็จ";
}

if (isset($_POST["delete_id"])) { // ลบ row อุปกรณ์
    if (!$is_admin) {
        $message = "คุณไม่มีสิทธิ์ลบ";
    } else {
        $id = (int)$_POST["delete_id"];
        $stmt = mysqli_prepare($conn, "DELETE FROM equipment WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) {
            header("Location: hello.php?msg=deleted");
            exit;
        } else {
            $message = "ลบไม่สำเร็จ: " . mysqli_stmt_error($stmt);
        }
    }
} elseif (isset($_POST["add_room"])) { // เพิ่มห้อง
    if (!$is_admin) {
        $message = "คุณไม่มีสิทธิ์เพิ่มห้อง";
    } else {
        $room  = trim($_POST["room"]);
        $floor = (int)$_POST["floor"];
        if ($room === "") {
            $message = "กรุณากรอกชื่อห้อง";
        } else {
            $stmt = mysqli_prepare($conn, "SELECT id FROM rooms WHERE name = ?");
            mysqli_stmt_bind_param($stmt, "s", $room);
            mysqli_stmt_execute($stmt);
            $found = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            if (!$found) {
                $stmt = mysqli_prepare($conn, "INSERT INTO rooms (name, floor) VALUES (?, ?)");
                mysqli_stmt_bind_param($stmt, "si", $room, $floor);
                if (mysqli_stmt_execute($stmt)) {
                    header("Location: hello.php?msg=added");
                    exit;
                } else {
                    $message = "บันทึกไม่สำเร็จ: " . mysqli_stmt_error($stmt);
                }
            } else {
                $message = "มีห้องนี้แล้ว";
            }
        }
    }
    
} elseif ($_SERVER["REQUEST_METHOD"] === "POST") { // เพิ่ม row 
    if (!$is_admin) {
        $message = "คุณไม่มีสิทธิ์เพิ่มอุปกรณ์";
    } else {
        $room = trim($_POST["room"]);
        $type = $_POST["type"];
        $qty  = (int)$_POST["quantity"];

        $stmt = mysqli_prepare($conn, "SELECT id FROM rooms WHERE name = ?");
        mysqli_stmt_bind_param($stmt, "s", $room);
        mysqli_stmt_execute($stmt);
        $found = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        if ($qty < 0) {
            $message = "จำนวนห้ามติดลบ";
        } elseif ($found) {
            $room_id = $found["id"];
            $stmt = mysqli_prepare($conn, "INSERT INTO equipment (room_id, type, quantity) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "isi", $room_id, $type, $qty);
            if (mysqli_stmt_execute($stmt)) {
                header("Location: hello.php?msg=saved");
                exit;
            } else {
            $message = "บันทึกไม่สำเร็จ: " . mysqli_stmt_error($stmt);
            }
        } else {
            $message = "ไม่พบห้องนี้";
        }
    }
}

$sql = "SELECT equipment.id, rooms.name AS room, equipment.type, equipment.quantity
        FROM equipment
        JOIN rooms ON equipment.room_id = rooms.id";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Equipment Inventory</title>
</head>
<body style="background-color:powderblue;">
    <?php if (isset($message) and $msg === "login_success") echo $message; ?>
    <h1>รายการอุปกรณ์ในห้อง</h1>

    
    <table border="1">
        <tr><th>ห้อง</th><th>ประเภท</th><th>จำนวน</th><?php if ($can_edit) { ?><th>จัดการ</th><?php } ?></tr> 

        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?php echo htmlspecialchars($row["room"]); ?></td>
            <td><?php echo htmlspecialchars($row["type"]); ?></td> 
            <?php /* เตือน: ต้องครอบ htmlspecialchars เสมอ ไม่งั้นโดน XSS คล้าย SQL Injection. Quantity เป็น int ไม่มีผล*/ ?>
            <td><?php echo $row["quantity"]; ?></td>
            <?php /* column แก้ไข */ ?>
            <?php if ($can_edit) { ?>
                <td>
                <a href="edit.php?id=<?php echo $row["id"]; ?>">แก้ไข</a>
                <?php if ($is_admin) { ?>
                <form method="post" onsubmit="return confirm('ลบรายการนี้?');">
                <input type="hidden" name="delete_id" value="<?php echo $row["id"]; ?>">
                <button type="submit">ลบ</button>
                </form>
                <?php } ?>
                </td> 
            <?php } ?>    
        </tr>
        <?php } ?>

    </table>
    <?php if (isset($message) and $msg !== "login_success") echo $message; ?>

    <?php if ($can_edit) { ?>
    <h2>เพิ่มอุปกรณ์</h2>
    <form method="post">
        <label>ห้อง: <input type="text" name="room"></label><br>
        <label for="type">ประเภท:</label>
        <select name="type" id="type">
            <?php foreach ($types as $t) { ?>
            <option value="<?php echo $t; ?>"> <?php echo $t; ?></option>
            <?php } ?>
            <?php /* <option value="แอร์">แอร์</option> */ ?>
        </select>
        <label>จำนวน: <input type="number" name="quantity" min="0" required></label><br>
        <button type="submit">บันทึก</button>
    </form>
    <?php } ?>

    <?php if ($is_admin) { ?>
    <h2>เพิ่มห้อง</h2>
    <form method="post">
        <input type="hidden" name="add_room" value="1">
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