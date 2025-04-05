<?php
session_start();

$targetDir = "uploads/";
$allImages = isset($_SESSION['all_uploaded_images']) ? $_SESSION['all_uploaded_images'] : [];

if (isset($_GET['delete'])) {
    $fileToDelete = $targetDir . basename($_GET['delete']);
    
    if (file_exists($fileToDelete)) {
        unlink($fileToDelete);
        $_SESSION['all_uploaded_images'] = array_filter($allImages, function ($image) use ($fileToDelete) {
            return $image !== $fileToDelete;
        });
    }

    header("Location: gallery.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thư viện Ảnh</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <h2>Thư viện Ảnh</h2>
    <a href="upload.php" class="btn btn-primary mb-3">Quay lại Upload</a>

    <?php if (!empty($allImages)): ?>
        <div class="row">
        <?php foreach ($allImages as $image): ?>
    <?php if (!file_exists($image)) continue; ?>

    <div class="col-md-3 mb-3">
        <div class="card">
            <img src="<?php echo htmlspecialchars($image); ?>" class="card-img-top" alt="Image" style="height: 200px; object-fit: cover;">
            <div class="card-body text-center">

                <!-- Nút CROP -->
                <a href="crop.php?image=<?php echo urlencode(basename($image)); ?>" class="btn btn-primary mb-1">✂ Crop</a><br>

                <!-- Nút XÓA -->
                <a href="?delete=<?php echo urlencode(basename($image)); ?>" class="btn btn-danger">🗑 Xóa</a>

            </div>
        </div>
    </div>
<?php endforeach; ?>

        </div>
    <?php else: ?>
        <p class="text-muted">Chưa có ảnh nào trong thư viện.</p>
    <?php endif; ?>
</body>
</html>
