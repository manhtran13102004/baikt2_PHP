<?php
$imageDir = 'uploads/';
$imageName = basename($_POST['image'] ?? '');
$imagePath = $imageDir . $imageName;

// Tọa độ crop
$x = (int) $_POST['x'];
$y = (int) $_POST['y'];
$width = (int) $_POST['width'];
$height = (int) $_POST['height'];

if (!file_exists($imagePath)) {
    die('❌ Ảnh không tồn tại.');
}

// Xác định loại ảnh
$type = exif_imagetype($imagePath);

switch ($type) {
    case IMAGETYPE_JPEG:
        $src = imagecreatefromjpeg($imagePath);
        break;
    case IMAGETYPE_PNG:
        $src = imagecreatefrompng($imagePath);
        break;
    case IMAGETYPE_GIF:
        $src = imagecreatefromgif($imagePath);
        break;
    default:
        die('❌ Không hỗ trợ định dạng ảnh.');
}

// Kích thước ảnh gốc
$srcWidth = imagesx($src);
$srcHeight = imagesy($src);

// Đảm bảo crop nằm trong ảnh
if ($x < 0 || $y < 0 || $x + $width > $srcWidth || $y + $height > $srcHeight) {
    die('❌ Tọa độ crop nằm ngoài kích thước ảnh.');
}

// Crop ảnh
$cropped = imagecrop($src, ['x' => $x, 'y' => $y, 'width' => $width, 'height' => $height]);

if ($cropped !== false) {
    // Ghi đè hoặc lưu mới
    $newPath = 'crops/' . $imageName;

    switch ($type) {
        case IMAGETYPE_JPEG:
            imagejpeg($cropped, $newPath, 90);
            break;
        case IMAGETYPE_PNG:
            imagepng($cropped, $newPath);
            break;
        case IMAGETYPE_GIF:
            imagegif($cropped, $newPath);
            break;
    }

    imagedestroy($cropped);
    imagedestroy($src);

    echo '<h3>✅ Crop thành công!</h3>';
    echo '<p><a href="' . $newPath . '" target="_blank">Xem ảnh đã crop</a></p>';
    echo '<a href="gallery.php">← Quay lại gallery</a>';
} else {
    echo '❌ Không crop được ảnh.';
}
?>
