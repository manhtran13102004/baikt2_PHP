<?php
$imageDir = 'uploads/';
$imageName = basename($_GET['image'] ?? '');
$imagePath = $imageDir . $imageName;

if (!file_exists($imagePath)) {
    die('Ảnh không tồn tại.');
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Crop Ảnh</title>
</head>
<body>
    <h2>✂ CROP ẢNH</h2>
    <p>Ảnh: <strong><?= htmlspecialchars($imageName) ?></strong></p>

    <img src="<?= $imagePath ?>" alt="Ảnh cần crop" style="max-width: 500px;"><br><br>

    <form action="do_crop.php" method="post">
        <input type="hidden" name="image" value="<?= htmlspecialchars($imageName) ?>">

        X: <input type="number" name="x" value="0" required><br>
        Y: <input type="number" name="y" value="0" required><br>
        Rộng: <input type="number" name="width" value="200" required><br>
        Cao: <input type="number" name="height" value="200" required><br><br>

        <button type="submit">Thực hiện Crop</button>
    </form>

    <br>
    <a href="gallery.php">← Quay lại Gallery</a>
</body>
</html>
