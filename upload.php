<?php
session_start();

// Tự động xóa ảnh lỗi sau 5 phút
$uploadDir = 'uploads/';
$expirationTime = 5*60; //5 phút

foreach (glob($uploadDir . "invalid_*.*") as $file) {
    if (filemtime($file) + $expirationTime < time()) {
        unlink($file);
    }
}

$validImages = $invalidImages = [];

// Hàm nén ảnh (có resize nếu ảnh quá lớn)
function compressImage($source, $destination, $quality = 75, $maxDim = 1920) {
    $info = getimagesize($source);
    if (!$info) return false;

    $mime = $info['mime'];
    switch ($mime) {
        case 'image/jpeg': $image = imagecreatefromjpeg($source); break;
        case 'image/png':  $image = imagecreatefrompng($source); break;
        case 'image/gif':  $image = imagecreatefromgif($source); break;
        default: return false;
    }

    $width = imagesx($image);
    $height = imagesy($image);
    if ($width > $maxDim || $height > $maxDim) {
        $scale = min($maxDim / $width, $maxDim / $height);
        $newWidth = floor($width * $scale);
        $newHeight = floor($height * $scale);
        $resized = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($image);
        $image = $resized;
    }

    if ($mime !== 'image/jpeg') {
        $bg = imagecreatetruecolor(imagesx($image), imagesy($image));
        imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
        imagecopy($bg, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
        imagedestroy($image);
        $image = $bg;
    }

    $destination = preg_replace('/\.(png|gif)$/i', '.jpg', $destination);
    imagejpeg($image, $destination, $quality);
    imagedestroy($image);
    return $destination;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dir = "uploads/";
    $allowed = ['jpg', 'gif', 'png'];
    $maxSize = 2 * 1024 * 1024; // 2MB

    $shouldCompress = isset($_POST['compress']);

    if (!is_dir($dir)) mkdir($dir, 0777, true);

    foreach ($_FILES['images']['name'] as $i => $name) {
        $tmp = $_FILES['images']['tmp_name'][$i];
        $size = $_FILES['images']['size'][$i];
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $newName = uniqid() . ".$ext";
        $path = $dir . $newName;

        if (in_array($ext, $allowed)) {
            if ($shouldCompress) {
                $compressedPath = compressImage($tmp, $path);
                if ($compressedPath && file_exists($compressedPath) && filesize($compressedPath) <= $maxSize) {
                    $validImages[] = $compressedPath;
                } else {
                    $errorPath = $dir . 'invalid_' . uniqid() . ".$ext";
                    move_uploaded_file($tmp, $errorPath);
                    $invalidImages[] = [
                        'path' => $errorPath,
                        'name' => $name,
                        'reason' => 'Dung lượng quá lớn sau khi nén'
                    ];
                }
            } else {
                if ($size <= $maxSize && move_uploaded_file($tmp, $path)) {
                    $validImages[] = $path;
                } else {
                    $errorPath = $dir . 'invalid_' . uniqid() . ".$ext";
                    move_uploaded_file($tmp, $errorPath);
                    $invalidImages[] = [
                        'path' => $errorPath,
                        'name' => $name,
                        'reason' => 'Dung lượng quá lớn hoặc lỗi khi lưu'
                    ];
                }
            }
        } else {
            $errorPath = $dir . 'invalid_' . uniqid() . ".$ext";
            move_uploaded_file($tmp, $errorPath);
            $invalidImages[] = [
                'path' => $errorPath,
                'name' => $name,
                'reason' => 'Sai định dạng ảnh'
            ];
        }
    }

    $_SESSION['all_uploaded_images'] = array_merge(
        $_SESSION['all_uploaded_images'] ?? [],
        $validImages
    );
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Upload Ảnh</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <h2>Upload Ảnh</h2>
    <form action="upload.php" method="post" enctype="multipart/form-data">
        <input type="file" name="images[]" multiple class="form-control mb-2" accept=".jpg,.gif,.png">
        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="compress" id="compress" checked>
            <label class="form-check-label" for="compress">Nén ảnh trước khi lưu</label>
        </div>
        <button type="submit" class="btn btn-primary">Upload</button>
    </form>

    <?php if ($validImages || $invalidImages): ?>
        <h3 class="mt-4">Kết quả Upload</h3>
        <div class="row">
            <?php foreach ($validImages as $img): ?>
                <div class="col-md-3 mb-3">
                    <img src="<?= $img ?>" class="img-thumbnail" alt="Ảnh hợp lệ">
                </div>
            <?php endforeach; ?>

            <?php foreach ($invalidImages as $err): ?>
                <div class="col-md-3 mb-3">
                    <div class="card border-danger">
                        <img src="<?= $err['path'] ?>" class="card-img-top" alt="Lỗi">
                        <div class="card-body text-center">
                            <p class="text-danger fw-bold"> <?= htmlspecialchars($err['name']) ?></p>
                            <p class="text-warning"><?= $err['reason'] ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <a href="gallery.php" class="btn btn-secondary mt-3">Xem thư viện ảnh</a>
</body>
</html>
